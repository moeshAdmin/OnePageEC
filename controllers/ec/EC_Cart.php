<?php

class EC_Cart extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
        $this->load->model('Api_common');
        $this->load->model('Api_data');
        $this->load->model('Api_ec');
        $this->load->model('Api_table_generate');
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->chkBlockIP();
        $this->Api_common->initLang();
        define('LANG',$this->Api_common->getCookie('lang'));
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    // 主畫面
    function index(){
        exit; 
    }

    function payOrder(){ //訂購後尚未付款
        $gtm = $this->Api_common->getSysConfig('GTM_PAYPAGE');
        $this->orderDetail('訂購後尚未付款');
    }

    function finish(){ //付款已經完成
        $this->orderDetail('付款已經完成');
    }

    function status(){ //查詢狀態
        $this->orderDetail('查詢狀態');
    }

    function startPay($orderHash,$byPass=null){ //進入付款流程
        $orderNo = $this->Api_common->stringHash('decrypt',$orderHash);
        if(!$orderNo){exit;}
        $orderData = $this->Api_ec->getOrderData($orderNo);
        //非信用卡件跳開
        if(!preg_match('/信用卡/', $orderData[0]['eoPayType'])){
            redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);
            exit;
        }
        //檢查是否已付款
        if(preg_match('/已付款|退款中|已退款/', $orderData[0]['eoPayStatus'])){
            redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);
            exit;
        }        
        //若未付款 產生付款隨機碼
        if(!$byPass&&$orderData[0]['eoPayRand']>0&&$orderData[0]['eoPayStatus']=='待付款'){
            echo '<p>此筆訂單前次付款未完成，請按以下連結繼續進行付款<br><a href="'.base_url().'ec/EC_Cart/startPay/'.$orderHash.'/true">前往付款</a></p><span style="color:red"><b>若您已完成付款，請與客服聯繫確認款項，請勿再次付款</b></span>';
            exit;
        }
        $this->Api_ec->setOrderStatus('生成付款隨機碼',$orderNo,null,$orderData,$postData);
        $orderData = $this->Api_ec->getOrderData($orderNo);
        $payType = $this->Api_common->getSysConfig('ecPayType');

        if($payType['scValue1']=='Test'){
            $this->Api_ec->ec_pay($orderData,'1');
        }else if($payType['scValue1']=='Normal'){
            $this->Api_ec->ec_pay($orderData,'0');
        }else{
            echo '金流設定錯誤';exit;
        }
        
    }    

    function chkOrder($orderHash,$detail=null){ //檢查付款狀態
        $orderNo = $this->Api_common->stringHash('decrypt',$orderHash);
        if(!$orderNo){exit;}
        $orderData = $this->Api_ec->getOrderData($orderNo);//取得訂單資料
        if($orderData[0]['eoPayStatus']=='已付款'){
            if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);}
            exit;
        }

        if($orderData[0]['eoOrderStatus']=='已取消'){exit;}

        $payType = $this->Api_common->getSysConfig('ecPayType');

        if($payType['scValue1']=='Test'){
            $result = $this->Api_ec->ec_query($orderData,'1');//綠界查詢是否已付款
        }else if($payType['scValue1']=='Normal'){
            $result = $this->Api_ec->ec_query($orderData,'0');//綠界查詢是否已付款
        }
        if($result['TradeStatus']==1){
            $this->Api_ec->setOrderStatus('已付款',$orderNo,$result,$orderData);
            if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);}
        }else{
            $this->load->library('My_SendMail');
            if($result['TradeStatus']==0){
                if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/payOrder?orderNo='.$orderHash);}
            }else if($result['TradeStatus']==10100058){
                echo '<script>alert("付款失敗，請重新執行付款程序")</script>';
                if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);}
            }else if($result['TradeStatus']==10800001){
                echo '<script>alert("因風險控管限制(credit)無法繼續操作，請您與綠界客服聯繫，謝謝")</script>';
                if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);}
            }else if($result['TradeStatus']==10100059){
                
                $resData = $this->Api_common->getSysConfig('ecSender');
                $receiveEmp = explode(';', $resData['scValue1']);
                
                $data = array(
                    'recipient'=>$receiveEmp,
                    'cc'=>'', 
                    'subject' => $orderNo.'請確認款項', 
                    'content' => $orderNo.'請確認款項',
                    'sender'=>'TTI_Tatung Technology Inc'); 
                $this->my_sendmail->sendOut($data);

                $this->Api_ec->setOrderStatus('確認中',$orderNo,$result,$orderData);
                echo '<script>alert("系統正在確認您的款項，付款狀態稍後將更新")</script>';
                if(!$detail['no-redirect']){redirect(base_url().'ec/EC_Cart/finish?orderNo='.$orderHash);}
            }else{
                echo '<script>alert("金流發生錯誤，請聯繫客服('.$result['TradeStatus'].')")</script>';exit;
                $resData = $this->Api_common->getSysConfig('ecSender');
                $receiveEmp = explode(';', $resData['scValue1']);
                $data = array(
                    'recipient'=>$receiveEmp,
                    'cc'=>'', 
                    'subject' => $orderNo.'發生錯誤 - '.$result['TradeStatus'], 
                    'content' => $orderNo.'發生錯誤 - '.$result['TradeStatus'],
                    'sender'=>'TTI_Tatung Technology Inc'); 
                $this->my_sendmail->sendOut($data);

            }
        }
    }    

    function paymentReturn($hash){

        $allowIP = ['175.99.72.1','175.99.72.11','175.99.72.24','175.99.72.28','175.99.72.32','175.99.72.41'];
        if(!in_array($_SERVER['HTTP_X_REAL_IP'], $allowIP)){exit;}
        $ary = $this->Api_common->stringHash('decrypt',$hash);
        if(!$ary){exit;}
        
        $orderNo = explode('_', $ary);
        $arsData = $this->Api_ec->getOrderARSData($orderNo[0]);//取得訂單資料
        //非ARS跳過程序
        if($data[0]['detail'][0]['eoIsARS']=='N'){
            echo '1|OK';exit;
        }

        $this->Api_common->saveReceiveMsg('receive','ecpay',['msg'=>'start receive']);

        $orderData = $arsData[0]['detail'];
        if($orderData[0]['eoOrderNo'].'R'.$orderData[0]['eoPayRand']!=$_POST['MerchantTradeNo']){
            $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'參數錯誤(81)']);
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(81)',null);
            exit;
        }

        $payType = $this->Api_common->getSysConfig('ecPayType');
        
        if($payType['scValue1']=='Test'){
            $result = $this->Api_ec->ec_query_period($orderData,'1');//綠界查詢是否已付款
        }else if($payType['scValue1']=='Normal'){
            $result = $this->Api_ec->ec_query_period($orderData,'0');//綠界查詢是否已付款
        }

        //若定期定額已停扣不執行
        if($result['ExecStatus']==0){
            $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'參數錯誤(89)']);
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(89)',null);
            exit;
        }
        //定期定額第一次不執行
        if($_POST['TotalSuccessTimes']==1){
            echo '1|OK';exit;
        }

        //扣款失敗不執行
        if($_POST['RtnCode']!='1'){
            exit;
        }
        

        $allowCreate = false;
        //模擬付款
        if($_POST['SimulatePaid']=='1'){
            //測試環境-->直接產生訂單
            if($payType['scValue1']=='Test'){
                $allowCreate = true;
            //正式環境-->直接回傳ok
            }else if($payType['scValue1']=='Normal'){
                echo '1|OK';exit;
            }
        //正式付款
        }else{
            //若成功次數與查詢結果不符不執行
            if($result['TotalSuccessTimes']!=$_POST['TotalSuccessTimes']){
                $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'參數錯誤(80)']);
                echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(80)',null);
                exit;
            }
            //檢查期數是否大於
            if((int)$_POST['TotalSuccessTimes']>(int)$arsData[0]['eaArsPeriods']){
                $allowCreate = true;
            }else{
                $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'參數錯誤(82)']);
                echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(82)',null);
                exit;
            }
        }

        if($allowCreate){
            $arsResult = $this->createARS($orderData[0]['eoOrderNo'],$arsData);
            $newOrderData = $this->Api_common->getDataCustom('*,max(eoARSPeriods) as nowPeriod','ec_order','eoARSOrderNo = "'.$orderData[0]['eoOrderNo'].'" AND eoIsReturn = "N"');
            //檢查最新期數是否符合
            if($newOrderData[0]['nowPeriod']==(int)$_POST['TotalSuccessTimes']){                
                //發信通知
                $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$arsResult['eoOrderNo']);
                $this->Api_ec->sendOrderMail('ARS待出貨',$postData);
                $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'return OK']);
                echo '1|OK';exit;
            }
        }else{
            $this->Api_common->saveReceiveMsg('return','ecpay',['msg'=>'參數錯誤(83)']);
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(83)',null);
            exit;
        }
    }

    private function orderDetail($title,$detail=null){
        $actionType = $title;
        $getData = $this->input->get();
        $user_detail = $this->session->all_userdata();
        if($getData['orderNo']){
            $orderNo = $this->Api_common->stringHash('decrypt',$getData['orderNo']);
            if(!$orderNo){exit;}
            $orderData = $this->Api_ec->getOrderData($orderNo);
            if(!$orderData){redirect(base_url());}
        }else if($getData['uat']){
            $email = explode('$$$', base64_decode($this->Api_common->stringHash('decrypt',$getData['uat'])));
            if(!$email){exit;}
            $orderData = $this->Api_ec->getOrderDataByEmail($email[0]);
            if(!$orderData){redirect(base_url());}
        }else if($user_detail['m_email']){
            $orderData = $this->Api_ec->getOrderDataByEmail($user_detail['m_email']);
            if(!$orderData){
                echo '<script>alert("無訂單資料");history.go(-1);</script>';exit;
            }
        }

        
        foreach ($orderData as $key => $value) {
            if($orderData[$key]['eoIsARS']=='Y'){
                $orderType = 'ARS';
                $orderTable[$orderType][$key]['合約編號'] = $orderData[$key]['eoARSOrderNo'];
                $orderTable[$orderType][$key]['配送期數'] = $orderData[$key]['eoARSPeriods'];
                $orderTable[$orderType][$key]['預計配送日'] = $orderData[$key]['eoPlainShipDate'];
                $orderTable[$orderType][$key]['方案內容'] = $orderData[$key]['eoItemName'].'<br>'.$orderData[$key]['eoItemType'];
            }else{
                $orderType = 'Normal';
            }
            $orderTable[$orderType][$key]['檢視'] = '<a target="blank" style="color:#008ea6" href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderData[$key]['eoOrderNo']).'">[檢視]</a>';
            $orderTable[$orderType][$key]['訂單編號'] = $orderData[$key]['eoOrderNo'];
            $orderTable[$orderType][$key]['訂單狀態'] = $orderData[$key]['eoOrderStatus'];
            $orderTable[$orderType][$key]['商品品項'] = $orderData[$key]['eoItemName'].'<br>'.$orderData[$key]['eoItemType'];
            $orderTable[$orderType][$key]['單價'] = $orderData[$key]['eoItemPrice'];
            $orderTable[$orderType][$key]['折扣'] = $orderData[$key]['eoOrderDiscount'];
            $orderTable[$orderType][$key]['訂單總金額'] = $orderData[$key]['eoOrderAmount'];
            $orderTable[$orderType][$key]['訂購日期'] = $orderData[$key]['eoOrderDate'];

            if($orderData[$key]['eoOrderStatus']=='已取消'){
                $orderTable[$orderType][$key]['付款'] = '已取消';
                $orderData[$key]['payBtn'] = '已取消';
                $title = '訂單已取消，期待下次再為您服務!';
            }else if($orderData[$key]['eoOrderStatus']=='已出貨'){
                $orderTable[$orderType][$key]['付款'] = $orderData[$key]['eoPayType'];
                $orderData[$key]['payBtn'] = $orderData[$key]['eoPayStatus'];
                $title = '商品已出貨，感謝您的選購!';
            }else if($orderData[$key]['eoPayStatus']=='確認中'){
                $orderTable[$orderType][$key]['付款'] = '確認中';
                $orderData[$key]['payBtn'] = '款項確認中';
                $title = '確認已付款! 我們會盡快為您出貨';
            }else if($orderData[$key]['eoOrderStatus']=='待付款'&&$orderData[$key]['eoPayType']=='信用卡'){
                if($orderData[$key]['eoIsARS']=='Y'){
                    $target = date('Ym',strtotime('+'.($orderData[$key]['eoARSPeriodsTotal']+1).' months',strtotime(date('Ym'))));
                    $orderTable[$orderType][$key]['付款'] = '<div class="btn btn-info btn-sm" @click=validChk("'.$this->Api_common->stringHash('encrypt',$orderData[$key]['eoOrderNo']).'","'.$target.'")>前往 信用卡 付款</div>';
                }else{
                    $orderTable[$orderType][$key]['付款'] = '<a href="'.base_url().'ec/EC_Cart/startPay/'.$this->Api_common->stringHash('encrypt',$orderData[$key]['eoOrderNo']).'"><div class="btn btn-info btn-sm">前往 信用卡 付款</div></a>';
                }
                $orderData[$key]['payBtn'] = $orderTable[$orderType][$key]['付款'];
                $title = '感謝您的訂購! 請繼續進行付款';
            }else if($orderData[$key]['eoOrderStatus']=='待付款'&&$orderData[$key]['eoPayType']=='現金匯款'){
                $orderTable[$orderType][$key]['付款'] = CASH_META;
                $orderData[$key]['payBtn'] = $orderTable[$orderType][$key]['付款'];
                $title = '感謝您的訂購! 請繼續進行付款';
            }else if($orderData[$key]['eoPayType']=='貨到付款'||$orderData[$key]['eoPayType']=='超商取貨付款'){
                $orderTable[$orderType][$key]['付款'] = $orderData[$key]['eoPayType'];
                $orderData[$key]['payBtn'] = $orderData[$key]['eoPayType'];
                $title = '感謝您! 我們會盡快為您出貨';
            }else if($orderData[$key]['eoPayStatus']=='已付款'){
                $orderTable[$orderType][$key]['付款'] = '付款完成';
                $orderData[$key]['payBtn'] = $orderData[$key]['eoPayStatus'];
                $title = '確認已付款! 我們會盡快為您出貨';
            }else if($orderData[$key]['eoPayStatus']=='退款中'){
                $orderTable[$orderType][$key]['付款'] = '退款中';
                $orderData[$key]['payBtn'] = '退款中';
                $title = '款項退款中，我們盡快處理中!';
            }else if($orderData[$key]['eoPayStatus']=='已退款'){
                $orderTable[$orderType][$key]['付款'] = '已退款';
                $orderData[$key]['payBtn'] = '已退款';
                $title = '款項已退回，期待下次再為您服務!';
            }

            //$orderData[$key]['changeBtn'] = '<a target="blank" href="'.base_url().'ec/EC_Cart/changeOrder/'.$this->Api_common->stringHash('encrypt',$orderData[$key]['eoOrderNo']).'"><div class="btn btn-danger btn-sm disabled">申請 訂單異動</div></a>';
            $orderData[$key]['changeBtn'] = '<div class="btn btn-danger btn-sm" @click=RequireLoad("'.$this->Api_common->stringHash('encrypt',$orderData[$key]['eoOrderNo']).'")>申請 訂單異動</div>';
            
            //if(count($orderData[0]['detail'])==1){
                $itemData = $this->Api_ec->getItemData($orderData[$key]['eoItemNo']);
                $data['itemImg'][$key] = explode(';', $itemData[0]['eiImg']);
            //}
        }

        $data['itemID'] = $orderData[$key]['eoItemNo'];
        $data['title'] = $title;
        $data['orderData'] = $orderData;

        if(($getData['uat']||$user_detail['m_email'])&&!$getData['orderNo']){
            $data['title'] = '以下是您的訂購紀錄';
            $data['viewType'] = '訂購紀錄';
            $detail['title'] = array('檢視','訂購日期','訂單編號','訂單狀態','商品品項','訂單總金額');
            $detail['fontSize'] = 10;
            $detail['alignLeft'] = array('收貨資訊');
            krsort($orderTable['ARS']);
            krsort($orderTable['Normal']);
            if($orderTable['Normal']){
                $data['orderTable'] = $this->Api_table_generate->drawTable($orderTable['Normal'],$detail,$data);
            }
            if($orderTable['ARS']){
                $arsData = $this->Api_common->getDataCustom('*','ec_order_ars','eaReceiverEmail = "'.$user_detail['m_email'].'"');
                $data['orderTableARS'] = $this->initARSTable($arsData,$orderTable['ARS']);
            }   
        }



        //$allItemData = $this->Api_ec->getAllItemData();
        //$data['allItemData'] = $allItemData;
        //隱藏賣場不顯示topMenu
        if($itemData[0]['eiStatus']!='隱藏賣場'){
            $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
        }
        $data['dataLayer'] = $this->setDataLayer($actionType,$data);
        $this->load_ECView("ec/ec_cart",$data);
        
    }

    private function initARSTable($arsData,$orderTable){
        //$this->Api_common->dataDump($arsData);
        foreach ($arsData as $key => $value) {
            $name = mb_substr($value['eaReceiverName'], 0,1);
            if(mb_strlen($value['eaReceiverName'])==2){
                $name .= '*';
            }else if(mb_strlen($value['eaReceiverName'])==3){
                $name .= '**';
            }else if(mb_strlen($value['eaReceiverName'])==4){
                $name .= '***';
            }
            $addr = $orderData[0]['eoReceiverPostCode'].' '.mb_substr($value['eaReceiverAddr'], 0,8).'******';
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['本次出貨日期'] = $value['eaLastDeliverDate'];
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['下期出貨日期'] = $value['eaNextDeliverDate'];
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['收件者/電話/配送地址'] = $name.' '.substr($value['eaReceiverPhone'], 0,4).'***'.substr($orderData[0]['eoReceiverPhone'], 4,-3).'<br>'.$addr.'<br><a style="color:#008ea6" href="#" @click=editForm("'.$this->Api_common->stringHash('encrypt',$value['eaARSOrderNo']).'")>[異動配送資訊]</a>';
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['合約編號'] = $value['eaARSOrderNo'].'('.$value['eaARSStatus'].')';
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['配送方案'] = $value['eaItemName'].'<br>'.$value['eaItemType'];
            $arsTicket[$value['eaARSOrderNo']]['data'][0]['配送資訊'] = '第 '.$value['eaARSPeriods'].' 期/共 '.$value['eaARSPeriodsTotal'].' 期';
        }
        foreach ($orderTable as $key => $value) {
            if($arsTicket[$value['合約編號']]){
                $arsTicket[$value['合約編號']]['orders'][$key] = $value;
            }
        }

        $detail['fontSize'] = 10;
        foreach ($arsTicket as $key => $value) {
            $detail['title'] = array('配送期數','預計配送日','檢視');
            $arsTicket[$key]['data'][0]['配送明細'] = $this->Api_table_generate->drawTable($value['orders'],$detail,$data);
            $detail['title'] = array('合約編號','配送方案','收件者/電話/配送地址','配送資訊','下期出貨日期','配送明細');
            $arsTable[$key] = $arsTicket[$key]['data'][0];
            
        }

        $setStr .= $this->Api_table_generate->drawTable($arsTable,$detail,$data);
        
        return $setStr;

    }

    private function setDataLayer($actionType,$data){
        $orderData = $data['orderData'];
        if($actionType=='付款已經完成'){
            $retData['event'] = 'purchase';
            $retData['transaction_id'] = $orderData[0]['eoOrderNo'];
            $retData['value'] = $orderData[0]['eoOrderAmount'];
            $retData['coupon'] = $orderData[0]['eoEvent'].$orderData[0]['eoUtmCamp'].$orderData[0]['eoUtmContent'];
            $retData['item_name'] = $orderData[0]['eoItemName'];
            $retData['item_id'] = $orderData[0]['eoItemSKU'];
            $retData['item_price'] = $orderData[0]['eoItemPrice'];
            $retData['item_brand'] = '';
            $retData['item_category'] = '';
            $retData['item_variant'] = $orderData[0]['eoItemType'];
            $retData['quantity'] = $orderData[0]['eoItemQty'];
            $retData['item_coupon'] = '';
        }else if($actionType=='訂購後尚未付款'){
            $retData['event'] = 'begin_checkout';
            $retData['value'] = $orderData[0]['eoOrderAmount'];
            $retData['item_name'] = $orderData[0]['eoItemName'];
            $retData['item_id'] = $orderData[0]['eoItemSKU'];
            $retData['item_brand'] = '';
            $retData['item_variant'] = $orderData[0]['eoItemType'];
            $retData['quantity'] = $orderData[0]['eoItemQty'];
        }else{
            return '';
        }

        return $retData;
    }

    private function RequireLoad(){
        $retData = $this->Api_ec->RequireLoad();
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit; 
    }

    private function RequireSubmit(){
        $this->Api_ec->RequireSubmit();
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit; 
    }

    private function createARS($arsOrderNo,$arsData){
        //$arsOrderNo = 'JP20210415NN0001';
        $orderData = $this->Api_common->getDataCustom('*,max(eoARSPeriods) as nowPeriod','ec_order','eoARSOrderNo = "'.$arsOrderNo.'" AND eoIsReturn = "N"');
        $nowPeriod = $orderData[0]['nowPeriod'];

        $unsetAry = ['eoSysID','eoRagicID','eoInvoiceNo','eoInvoiceTime','eoDeliverCode','eoDeliverOutFile','eoDeliverReceiveFile','eoMemberNote','eoOrderEmp','eoOrderNote','eoInnerNote','eoShipProcess1Time','eoShipProcess2Time','eoShipProcess3Time','nowPeriod','detail'];
        foreach ($unsetAry as $key => $value) {
            unset($orderData[0][$value]);
        }

        $randTxt = ['A','B','C','D','E','F','H','I','J','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        //訂單主檔
        $orderData[0]['eoOrderNo'] = TICKET_ID.date('Ymd').$randTxt[rand(0,23)].$randTxt[rand(0,23)].str_pad( $this->Api_ec->chkNextNum('ec_order','eoOrderNo','-4','eoOrderDate = "'.date('Y-m-d').'"','eoSysID') ,4,'0',STR_PAD_LEFT);
        $orderData[0]['eoReceiverName'] = $arsData[0]['eaReceiverName'];
        $orderData[0]['eoReceiverPhone'] = $arsData[0]['eaReceiverPhone'];
        $orderData[0]['eoReceiverAddr'] = $arsData[0]['eaReceiverAddr'];
        $orderData[0]['eoReceiverPostCode'] = $arsData[0]['eaReceiverPostCode'];
        $orderData[0]['eoOrderStatus'] = '待出貨';
        $orderData[0]['eoPayStatus'] = '已付款';
        $orderData[0]['eoDate'] = date('Y-m-d');
        $orderData[0]['eoOrderDate'] = date('Y-m-d');
        //如果有指定下次配送日期，依指定日期為主
        //沒有就是訂單產生日起配
        if(strtotime($arsData[0]['eaRequestDeliverDate'])>strtotime(date('Y-m-d'))){
            $orderData[0]['eoPlainShipDate'] = $arsData[0]['eaRequestDeliverDate'];
        }else{
            $orderData[0]['eoPlainShipDate'] = date('Y-m-d');
        }        
        $orderData[0]['eoPayDate'] = date('Y-m-d');
        $orderData[0]['eoInvoiceStatus'] = '未開立';
        $orderData[0]['eoIsReturn'] = 'N';
        $orderData[0]['eoARSPeriods'] = $nowPeriod+1;
        $orderData[0]['eoCreateDTime'] = date('Y-m-d H:i:s');
        $orderData[0]['eoUpdateDTime'] = date('Y-m-d H:i:s');

        $ragicItemData = $this->Api_ec->getARSData($orderData[0]['eoItemNo'],$orderData[0]['eoItemSKU'],(int)$orderData[0]['eoARSPeriods']);
        if(!$ragicItemData){
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(96)',null);
            exit;
        }

        $this->db->insert('ec_order', $orderData[0]);
        //訂單子表
        $num = 0;
        foreach ($ragicItemData as $key => $value) {
            foreach ($ragicItemData[$key]['_subtable_1000332'] as $key2 => $value2) {
                $num++;
                $insertOrderDetail['eodOrderNo'] = $orderData[0]['eoOrderNo'];
                $insertOrderDetail['eodOrderSubNo'] = $num;
                $insertOrderDetail['eodDate'] = $orderData[0]['eoDate'];
                $insertOrderDetail['eodOrderDate'] = $orderData[0]['eoOrderDate'];

                $insertOrderDetail['eodCreateDTime'] = date('Y-m-d H:i:s');
                $insertOrderDetail['eodItemNo'] = $orderData[0]['eoItemNo'];
                $insertOrderDetail['eodItemName'] = $value2['商品名稱'];
                $insertOrderDetail['eodItemType'] = '單一規格';
                $insertOrderDetail['eodItemSKU'] = $value2['商品規格編號'];
                $insertOrderDetail['eodItemQty'] = $value2['商品數量'];
                $insertOrderDetail['eodItemPrice'] = $value2['單價'];
                $insertOrderDetail['eodItemSubTotal'] = $value2['小計'];
                $insertOrderDetail['eodOrderAmount'] = $orderData[0]['eoOrderAmount'];                
                $this->db->insert('ec_order_detail', $insertOrderDetail);
            }
        }

        //ARS子表更新
        $updateARS['eaARSPeriods'] = $orderData[0]['eoARSPeriods'];
        $updateARS['eaLastDeliverDate'] = $orderData[0]['eoPlainShipDate'];
        $updateARS['eaNextDeliverDate'] = $this->Api_ec->getNextMonthDate(date('Y-m-d'),$orderData[0]['eoARSPeriodType'],$orderData[0]['eoARSFreq']);
        $updateARS['eaRequestDeliverDate'] = null;
        $updateARS['eaUpdateDTime'] = date('Y-m-d H:i:s');
        $this->db->where('eaARSOrderNo', $arsOrderNo);
        $this->db->update('ec_order_ars', $updateARS);

        $newData = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSOrderNo = "'.$arsOrderNo.'"');
        foreach ($newData as $key => $value) {
            if($value['eaARSPeriods']==$value['eaARSPeriodsTotal']){
                $updateARS2['eaARSStatus'] = '已結案';
                $updateARS2['eaNextDeliverDate'] = null;
                $this->db->where('eaARSOrderNo', $arsOrderNo);
                $this->db->update('ec_order_ars', $updateARS2);
            }
        }
        
        $return['eoOrderNo'] = $orderData[0]['eoOrderNo'];

        return $return;
    }

    function loadARSData($hash){
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$hash);
        if(!$arsOrderNo){exit;}
        $arsData = $this->Api_ec->getOrderARSData($arsOrderNo);
        $retData['orderData']['ReceiverName'] = $arsData[0]['eaReceiverName'];
        $retData['orderData']['ReceiverPhone'] = $arsData[0]['eaReceiverPhone'];
        $retData['orderData']['ReceiverPostCode'] = $arsData[0]['eaReceiverPostCode'];
        $retData['orderData']['ReceiverAddr'] = $arsData[0]['eaReceiverAddr'];
        $retData['orderData']['NextDeliver'] = $arsData[0]['eaNextDeliverDate'];
        $retData['orderData']['Hash'] = $hash;
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit; 
    }

    function setARSData($hash){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$hash);
        if(!$arsOrderNo){exit;}
        $updatData['eaReceiverName'] = $postData['ReceiverName'];
        $updatData['eaReceiverPhone'] = $postData['ReceiverPhone'];
        $updatData['eaReceiverPostCode'] = $postData['ReceiverPostCode'];
        $updatData['eaReceiverAddr'] = $postData['ReceiverAddr'];
        $this->db->where('eaARSOrderNo', $arsOrderNo);
        $this->db->update('ec_order_ars', $updatData);
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit; 
    }

    
}
