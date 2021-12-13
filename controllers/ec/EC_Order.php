<?php

class EC_Order extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
        $this->load->model('Api_common');
        $this->load->model('Api_data');
        $this->load->model('Api_ec');
        $this->load->model('Api_ragic');
        $this->load->model('Api_table_generate');        
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->chkBlockIP();
        $this->Api_common->initLang();
        if($_GET['utm_source']){
            $utm = 'utm_source='.$_GET['utm_source'].'&utm_medium='.$_GET['utm_medium'].'&utm_campaign='.$_GET['utm_campaign'].'&utm_term='.$_GET['utm_term'].'&utm_content='.$_GET['utm_content'];
            setcookie('source', $this->Api_common->stringHash('encrypt',$utm), time() + (3600 * 4), "/");
        }
        define('LANG',$this->Api_common->getCookie('lang'));
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    // 主畫面
    function index(){
        $this->Api_common->redirectHttps();
        $user_detail=$this->session->all_userdata();
        $getData = $this->input->get();

        //商品資訊載入
        $itemData = $this->Api_ec->getItemData((int)$getData['itemID']);
        $data['itemStatus'] = $itemData[0]['eiStatus'];
        $data['title'] = $itemData[0]['eiName'];

        $data['setting'] = json_decode($itemData[0]['eiSetting'],true);
        $data['canSale'] = false;
        $data['isMember'] = false;
        $data['itemType'] = [];
        foreach ($data['setting'] as $sku => $value) {
            unset($json);unset($inventoryChk);unset($note);
            if(!$data['itemPrice']){$data['itemPrice'] = $value['price'];}
            //取得庫存
            $json = json_decode($this->Api_common->cache('load','inventory_'.$sku,null,['return'=>true]),true)['data'];
            if($json){$inventoryChk[0] = $json;}
            //被設定售完商品強制庫存為0
            if($data['itemStatus']=='售完'){$inventoryChk[0]['qty'] = 0;}
            //低庫存商品顯示剩餘件數
            if($inventoryChk[0]['qty']>0&&$inventoryChk[0]['qty']<10){
                $note = ' --剩餘 '.$inventoryChk[0]['qty'].' 件';
            }
            //無庫存商品不能選擇
            if(!$inventoryChk||$inventoryChk[0]['qty']<=0){
                $isDisabled = true;
            }else{
                $isDisabled = false;
                $data['canSale'] = true;
                if(!$data['itemType'][0]['name']){
                    $data['itemType'][0] = ['name'=>$value['name'].$note,'value'=>$sku,'disabled'=>$isDisabled,'price_txt'=>$price_txt];
                    continue;
                }
            }
            array_push($data['itemType'], ['name'=>$value['name'].$note,'value'=>$sku,'disabled'=>$isDisabled,'price_txt'=>$price_txt]);
        }

        if($user_detail['m_email']){
            $data['isMember'] = true;
        }

        $data['setting'] = $itemData[0]['eiSetting'];
        $data['itemSellType'] = $itemData[0]['eiItemType'];

        $data['itemID'] = $this->Api_common->stringHash('encrypt',$itemData[0]['eiSysID']);
        $data['desc'] = $itemData[0]['eiDesc'];
        $data['html'] = $itemData[0]['eiHtml'];
        $data['itemImg'] = $itemData[0]['eiImg'];

        //付款方式
        $resData = $this->Api_common->getSysConfig('ecPayCate');
        $data['payType'] = explode(';', $resData['scValue1']);

        //隱藏賣場不顯示topMenu
        if($itemData[0]['eiStatus']!='隱藏賣場'){
            $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
        }
        //dataLayer
        $data['dataLayer'] = $this->setDataLayer('檢視商品',$itemData);

        $this->load_ECView("ec/ec_order",$data); // 陣列資料 data 與 View Rendering
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);    
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);
        $this->fieldChk($postData);
        $return = $this->insertData($postData);
        if($return['orderNo']){
            echo $this->Api_common->setFrontReturnMsg('200','',$return);
            exit; 
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','',null);
            exit; 
        }
        
    }

    private function fieldChk($postData){
        $msg = array();
        if(!$postData['name']){
            array_push($msg, '缺少收件人姓名');
        }
        if(!$postData['email']){
            array_push($msg, '缺少電子信箱');
        }
        if(!$postData['phone']){
            array_push($msg, '缺少連絡電話');
        }
        if(($postData['logisticType']=='cvs'||$postData['logisticType']=='cvs_pay')&&!$postData['cvsID']){
            array_push($msg, '請選擇超商門市');
        }
        if(!$postData['postCode']&&$postData['logisticType']=='home'){
            array_push($msg, '缺少郵遞區號');
        }
        if(!$postData['addr']&&!$postData['cvsAddr']){
            array_push($msg, '缺少收件地址');
        }
        if($postData['addr']){
            if(!preg_match('/縣|市|區|號|鄉|鎮|路|街|道/', $postData['addr'])){
                //array_push($msg, '地址路名不完整，請重新輸入');
            }
            if(!preg_match('/縣|市|區/', $postData['addr'])){
                //array_push($msg, '地址縣市不完整，請重新輸入');
            }
        }
        if(!checkdnsrr(array_pop(explode("@",$postData['email'])),"MX")){
            array_push($msg, '電子信箱格式不正確');
        }
        if(!$this->isPhone($postData['phone'])){
            array_push($msg, '連絡電話格式不正確');
        }
        if(!$this->isPostCode($postData['postCode'])&&$postData['logisticType']=='home'){
            array_push($msg, '郵遞區號格式不正確');
        }
        if(!preg_match("/^[0-9]{8}$/", $postData['invoiceComNo'])&&$postData['invoiceComNo']){
            array_push($msg, '統一編號格式不正確');
        }
        if($postData['invoiceType']=='手機條碼'&&(strlen($postData['invoiceMeta'])!=8||substr($postData['invoiceMeta'], 0,1)!='/')){
            array_push($msg, '手機條碼格式不正確');
        }

        
        if(count($msg)>0){
            echo $this->Api_common->setFrontReturnMsg('901','資訊錯誤:'.str_replace(';', '、', $this->Api_common->setArrayToList($msg)),null);
            exit;
        }
    }

    function previewOrder(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->fieldChk($postData);
        $itemID = $this->Api_common->stringHash('decrypt',$postData['itemID']);
        if(!$itemID){exit;}
        $itemData = $this->Api_ec->getItemData($itemID);
        
        if($itemData[0]['eiSetting']){
            $data['setting'] = json_decode($itemData[0]['eiSetting'],true);
            if(!$data['setting'][$postData['itemType']]){echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(1)',null);exit;}
            $return['itemTypeName'] = $data['setting'][$postData['itemType']]['name'];            
            $return['price'] = $data['setting'][$postData['itemType']]['price'];
        }else{
            if($postData['itemType']!='單一規格'&&!in_array($postData['itemType'], explode(';', $itemData[0]['eiItemType']))){echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(1)',null);exit;}
            $return['itemTypeName'] = $postData['itemType'];
            $return['price'] = $itemData[0]['eiPrice'];
        }
        $return['amount'] = (int)$postData['orderQty']*$return['price'];
        $return['discount'] = $itemData[0]['discount'];
        $return['amount'] = ($return['amount']-$return['discount'])+$itemData[0]['shipAmount'];
        $return['ship'] = $itemData[0]['shipAmount'];

        if($return['price']<1||$return['amount']<1||!$return['itemTypeName']){
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(98)',null);exit;
        }


        $inventoryChk = $this->Api_common->getDataCustom('eiItemNo,sum(eiItemQty) as qty','ec_inventory','eiItemNo = "'.$postData['itemType'].'"');
        $this->Api_common->cache('save','inventory_'.$postData['itemType'],$inventoryChk[0]);
        usleep(rand(100,800));
        $qty = (int)$postData['orderQty'];
        if(!$inventoryChk||$inventoryChk[0]['qty']<=0){
            echo $this->Api_common->setFrontReturnMsg('901','商品已銷售完畢',null);
            exit;
        }
        if($inventoryChk[0]['qty']-$qty<0){
            echo $this->Api_common->setFrontReturnMsg('901','商品組合僅剩 '.$inventoryChk[0]['qty'].' 件，請重新選擇',null);
            exit;
        }

        if($itemData[0]['eiItemType']=='定期配'){
            $ragicItemData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms4/4?where=1000324,eq,'.urlencode($data['setting'][$postData['itemType']]['source']).'&where=1000326,eq,'.$itemID.'&where=1000336,eq,'.$postData['itemType'].'&where=1000327,eq,1&limit=0,100', $ckfile),true);
            if(!$ragicItemData){
                echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(96)',null);
                exit;
            }
            foreach ($ragicItemData as $key => $value) {
                if($value['方案金額']!=$return['amount']){
                    echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(95)',null);
                    exit;
                }
            }
        }
        

        echo $this->Api_common->setFrontReturnMsg('200','',$return);
        exit; 
    }

    private function insertData($postData){
        $membData = $this->Api_common->getDataIsExist('emMemberNo','ec_member','emEmail = "'.$postData['email'].'"');
        if($membData['mode']=='insert'){
            $insertMember['emMemberNo'] = TICKET_ID.'C'.str_pad($this->chkNextNum('ec_member','emMemberNo','-5',null,'emSysID'),5,'0',STR_PAD_LEFT);
            $insertMember['emMemberName'] = $postData['name'];
            $insertMember['emEmail'] = $postData['email'];
            $insertMember['emPhone'] = $postData['phone'];
            $insertMember['emCreateDTime'] = date('Y-m-d H:i:s');
            $insertOrder['eoMemberNo'] = $insertMember['emMemberNo'];
        }else{
            $insertOrder['eoMemberNo'] = $membData['data'][0]['emMemberNo'];
        }
        //訂單資訊
        $randTxt = ['A','B','C','D','E','F','H','I','J','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $insertOrder['eoOrderNo'] = TICKET_ID.date('Ymd').$randTxt[rand(0,23)].$randTxt[rand(0,23)].str_pad( $this->chkNextNum('ec_order','eoOrderNo','-4','eoOrderDate = "'.date('Y-m-d').'"','eoSysID') ,4,'0',STR_PAD_LEFT);
        if($postData['payType']=='超商取貨付款'){
            $insertOrder['eoOrderStatus'] = '待出貨';
            $insertOrder['eoPayStatus'] = '超商取貨付款';
        }else if($postData['payType']=='貨到付款'){
            $insertOrder['eoOrderStatus'] = '待出貨';
            $insertOrder['eoPayStatus'] = '貨到付款';
        }else{
            $insertOrder['eoOrderStatus'] = '待付款';
            $insertOrder['eoPayStatus'] = '待付款';
        }
        
        $insertOrder['eoDate'] = date('Y-m-d');
        $insertOrder['eoOrderDate'] = date('Y-m-d');
        $insertOrder['eoPlainShipDate'] = date('Y-m-d');

        //商品資訊
        $itemID = $this->Api_common->stringHash('decrypt',$postData['itemID']);
        if(!$itemID){exit;}
        $itemData = $this->Api_ec->getItemData($itemID);
        
        //彙整商品
        $insertOrder['eoItemNo'] = $itemData[0]['eiSysID'];
        $insertOrder['eoItemName'] = $itemData[0]['eiName'];

        $data['setting'] = json_decode($itemData[0]['eiSetting'],true);
        if(!$data['setting'][$postData['itemType']]){echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(99)',null);exit;}
        $insertOrder['eoItemSKU'] = $postData['itemType'];  
        $insertOrder['eoItemType'] = $data['setting'][$postData['itemType']]['name'];
        $insertOrder['eoItemPrice'] = $data['setting'][$postData['itemType']]['price'];

        //UTM
        if($postData['utm_source']){
            $insertOrder['eoUtmCamp'] = $postData['utm_source'].'_'.$postData['utm_medium'];  
            $insertOrder['eoUtmContent'] = $postData['utm_campaign'].'_'.$postData['utm_content'];
        }

        //定期配額外資訊
        if($itemData[0]['eiItemType']=='定期配'){
            if($postData['payType']!='信用卡'){
                echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(94)',null);exit;
            }
            $insertOrder['eoOrderDate'] = date('Y-m-d',strtotime($postData['arsShipStart']));
            $insertOrder['eoPlainShipDate'] = date('Y-m-d',strtotime($postData['arsShipStart']));
            $insertOrder['eoIsARS'] = 'Y';
            $insertOrder['eoARSOrderNo'] = $insertOrder['eoOrderNo'];
            $insertOrder['eoARSPeriods'] = 1;
            $insertOrder['eoARSPeriodType'] = $data['setting'][$postData['itemType']]['periodType'];
            $insertOrder['eoARSFreq'] = $data['setting'][$postData['itemType']]['periodFreq'];
            $insertOrder['eoARSPeriodsTotal'] = $data['setting'][$postData['itemType']]['periods'];
            $insertOrder['eoARSDeliverDay'] = (int)$postData['arsDate'];
        }else{
            $insertOrder['eoIsARS'] = 'N';
        }

        $insertOrder['eoItemQty'] = (int)$postData['orderQty'];
        $insertOrder['eoItemSubTotal'] = $insertOrder['eoItemPrice']*$insertOrder['eoItemQty'];
        $insertOrder['eoOrderDiscount'] = $itemData[0]['discount'];
        $insertOrder['eoOrderAmount'] = ($insertOrder['eoItemSubTotal']-$insertOrder['eoOrderDiscount'])+$itemData[0]['shipAmount'];
        $insertOrder['eoPayAmount'] = 0;
        $insertOrder['eoOrderShipAmount'] = $itemData[0]['shipAmount'];

        $resData = $this->Api_common->getSysConfig('ecPayCate');
        $payType = explode(';', $resData['scValue1']);
        if(in_array($postData['payType'], $payType)){
            $insertOrder['eoPayType'] = $postData['payType'];
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(1)',null);exit;
        }

        //寄件資訊
        if($postData['logisticType']=='cvs_pay'||$postData['logisticType']=='cvs'){
            //超取
            $insertOrder['eoReceiverPostCode'] = $postData['cvsID'];
            $insertOrder['eoReceiverAddr'] = $postData['cvsAddr'].'('.$postData['cvsName'].')';
            $insertOrder['eoDeliverCvsID'] = $postData['cvsID'];
            $insertOrder['eoDeliverCvsName'] = $postData['cvsName'];
            $insertOrder['eoDeliverName'] = $postData['cvsType'];
        }else if($postData['logisticType']=='home'){
            //宅配
            $insertOrder['eoReceiverPostCode'] = (int)$postData['postCode'];
            $insertOrder['eoReceiverAddr'] = $postData['area'].$postData['addr'];
        }
        $insertOrder['eoReceiverName'] = $postData['name'];
        $insertOrder['eoDeliverTime'] = $postData['deliverTime'];
        $insertOrder['eoReceiverPhone'] = $postData['phone'];
        $insertOrder['eoReceiverEmail'] = $postData['email'];
        $insertOrder['eoMemberNote'] = $postData['note'];
        $insertOrder['eoCreateDTime'] = date('Y-m-d H:i:s');

        //發票資訊
        $insertOrder['eoInvoiceStatus'] = '未開立';        

        if($postData['invoiceType']=='自然人憑證'){
            $insertOrder['eoInvoiceType'] = 'CQ0001';
            $insertOrder['eoInvoiceMeta'] = $postData['invoiceMeta'];
        }else if($postData['invoiceType']=='手機條碼'){
            $insertOrder['eoInvoiceType'] = '3J0002';
            $insertOrder['eoInvoiceMeta'] = $postData['invoiceMeta'];
        }else if($postData['invoiceType']=='會員載具'){
            $insertOrder['eoInvoiceType'] = '';
            $insertOrder['eoInvoiceMeta'] = '';
        }else if($postData['invoiceType']=='愛心碼'){
            $insertOrder['eoInvoiceType'] = '';
            $insertOrder['eoInvoiceLoveCode'] = $postData['invoiceMeta'];
        }else if($postData['invoiceType']=='開立統編'){
            $insertOrder['eoInvoiceComNo'] = (int)$postData['invoiceComNo'];
            $insertOrder['eoInvoiceCom'] = $postData['invoiceComTitle'];
        }

        if($insertOrder['eoItemQty']<1||$insertOrder['eoOrderAmount']<1){
            echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(98)',null);exit;
        }

        if($insertMember){
            $this->db->insert('ec_member', $insertMember);
        }

        //最終檢查是否有足夠數量可販售
        $this->db->trans_start();        
        $inventoryChk = $this->Api_common->getDataCustom('eiItemNo,sum(eiItemQty) as qty','ec_inventory','eiItemNo = "'.$insertOrder['eoItemSKU'].'"');
        usleep(rand(100,800));
        $qty = (int)$insertOrder['eoItemQty'];
        if(!$inventoryChk||$inventoryChk[0]['qty']<=0){
            $this->db->trans_complete();
            echo $this->Api_common->setFrontReturnMsg('901','商品已銷售完畢',null);
            exit;
        }
        if($inventoryChk[0]['qty']-$qty<0){
            $this->db->trans_complete();
            echo $this->Api_common->setFrontReturnMsg('901','商品組合僅剩 '.$inventoryChk[0]['qty'].' 件，請重新選擇',null);
            exit;
        }

        //寫入訂單子表
        if($itemData[0]['eiItemType']=='定期配'){
            //$ragicItemData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms4/4?where=1000324,eq,'.urlencode($data['setting'][$postData['itemType']]['source']).'&where=1000326,eq,'.$itemID.'&where=1000336,eq,'.$postData['itemType'].'&where=1000327,eq,1&limit=0,100', $ckfile),true);
            $ragicItemData = $this->Api_ec->getARSData($itemID,$postData['itemType'],1);
            if(!$ragicItemData){
                echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(96)',null);
                exit;
            }
            $num = 0;
            foreach ($ragicItemData as $key => $value) {
                if($insertOrder['eoOrderAmount']!=$value['方案金額']){
                    echo $this->Api_common->setFrontReturnMsg('901','參數錯誤(95)',null);
                    exit;
                }
                foreach ($ragicItemData[$key]['_subtable_1000332'] as $key2 => $value2) {
                    $num++;
                    $insertOrderDetail['eodOrderNo'] = $insertOrder['eoOrderNo'];
                    $insertOrderDetail['eodOrderSubNo'] = $num;
                    $insertOrderDetail['eodDate'] = $insertOrder['eoDate'];
                    $insertOrderDetail['eodOrderDate'] = $insertOrder['eoOrderDate'];
                    $insertOrderDetail['eodCreateDTime'] = $insertOrder['eoCreateDTime'];
                    $insertOrderDetail['eodItemNo'] = $insertOrder['eoItemNo'];
                    $insertOrderDetail['eodItemName'] = $value2['商品名稱'];
                    $insertOrderDetail['eodItemType'] = '單一規格';
                    $insertOrderDetail['eodItemSKU'] = $value2['商品規格編號'];
                    $insertOrderDetail['eodItemQty'] = $value2['商品數量'];
                    $insertOrderDetail['eodItemPrice'] = $value2['單價'];
                    $insertOrderDetail['eodItemSubTotal'] = $value2['小計'];
                    $insertOrderDetail['eodOrderAmount'] = $insertOrder['eoOrderAmount'];                
                    $this->db->insert('ec_order_detail', $insertOrderDetail);
                }
            }

            //寫入ARS子表
            $insertARS['eaARSOrderNo'] = $insertOrder['eoOrderNo'];
            $insertARS['eaARSMemberNo'] = $insertOrder['eoMemberNo'];
            $insertARS['eaARSStatus'] = $insertOrder['eoOrderStatus'];
            $insertARS['eaARSPeriodType'] = $insertOrder['eoARSPeriodType'];
            $insertARS['eaARSPeriods'] = $insertOrder['eoARSPeriods'];
            $insertARS['eaARSFreq'] = $insertOrder['eoARSFreq'];
            $insertARS['eaARSPeriodsTotal'] = $insertOrder['eoARSPeriodsTotal'];
            $insertARS['eaARSDeliverDay'] = $insertOrder['eoARSDeliverDay'];

            $insertARS['eaReceiverName'] = $insertOrder['eoReceiverName'];
            $insertARS['eaReceiverPhone'] = $insertOrder['eoReceiverPhone'];
            $insertARS['eaReceiverAddr'] = $insertOrder['eoReceiverAddr'];
            $insertARS['eaReceiverEmail'] = $insertOrder['eoReceiverEmail'];
            $insertARS['eaReceiverPostCode'] = $insertOrder['eoReceiverPostCode'];
            $insertARS['eaItemName'] = $insertOrder['eoItemName'];
            $insertARS['eaItemType'] = $insertOrder['eoItemType'];

            $insertARS['eaLastDeliverDate'] = date('Y-m-d');
            $insertARS['eaNextDeliverDate'] = $this->Api_ec->getNextMonthDate(date('Y-m-d'),$insertOrder['eoARSPeriodType'],$insertOrder['eoARSFreq']);
            $insertARS['eaCreateDTime'] = date('Y-m-d H:i:s');
            $insertARS['eaUpdateDTime'] = date('Y-m-d H:i:s');
            $this->db->insert('ec_order_ars', $insertARS);
        }else{
            $insertOrderDetail['eodOrderNo'] = $insertOrder['eoOrderNo'];
            $insertOrderDetail['eodOrderSubNo'] = 1;
            $insertOrderDetail['eodDate'] = $insertOrder['eoDate'];
            $insertOrderDetail['eodOrderDate'] = $insertOrder['eoOrderDate'];
            $insertOrderDetail['eodItemNo'] = $insertOrder['eoItemNo'];
            $insertOrderDetail['eodItemName'] = $insertOrder['eoItemName'];
            $insertOrderDetail['eodItemType'] = $insertOrder['eoItemType'];
            $insertOrderDetail['eodItemSKU'] = $insertOrder['eoItemSKU'];
            $insertOrderDetail['eodItemQty'] = $insertOrder['eoItemQty'];
            $insertOrderDetail['eodItemPrice'] = $insertOrder['eoItemPrice'];
            $insertOrderDetail['eodItemSubTotal'] = $insertOrder['eoItemSubTotal'];
            $insertOrderDetail['eodOrderAmount'] = $insertOrder['eoOrderAmount'];
            $insertOrderDetail['eodCreateDTime'] = $insertOrder['eoCreateDTime'];
            $this->db->insert('ec_order_detail', $insertOrderDetail);
        }


        //寫入銷貨資料
        $insertInventory['eiItemNo'] = $insertOrder['eoItemSKU'];
        $insertInventory['eiItemQty'] = (int)(0-$insertOrder['eoItemQty']);
        $insertInventory['eiTicketNo'] = $insertOrder['eoOrderNo'];
        $insertInventory['eiTicketType'] = '銷貨單';
        $insertInventory['eiDate'] = date('Y-m-d');
        $insertInventory['eiCreateEmp'] = 'Server';
        $this->db->insert('ec_inventory', $insertInventory);

        //寫入訂單
        $this->db->insert('ec_order', $insertOrder);
        $retData['orderNo'] = $this->Api_common->stringHash('encrypt',$insertOrder['eoOrderNo']);
        $retData['orderNo2'] = $insertOrder['eoOrderNo'];
        $postData['orderNo'] = $retData['orderNo'];
        
        //寫入待發送信件
        if($postData['payType']=='貨到付款'||$postData['payType']=='超商取貨付款'){
            $this->Api_ec->sendOrderMail('待出貨',$postData);
        }else{
            $this->Api_ec->sendOrderMail('待付款',$postData);
        }

        $this->db->trans_complete();
        

        //回寫庫存占存
        $inventoryChk = $this->Api_common->getDataCustom('eiItemNo,sum(eiItemQty) as qty','ec_inventory','eiItemNo = "'.$insertOrder['eoItemSKU'].'"');
        $this->Api_common->cache('save','inventory_'.$insertOrder['eoItemSKU'],$inventoryChk[0]);
        return $retData;
    }

    private function chkNextNum($table,$field,$offset,$where=null,$order_by=null){
        return $this->Api_ec->chkNextNum($table,$field,$offset,$where,$order_by);
    }

    private function isPhone($str) {
        if (preg_match("/^09[0-9]{2}-[0-9]{3}-[0-9]{3}$/", $str)) {
            return true;    // 09xx-xxx-xxx
        } else if(preg_match("/^09[0-9]{2}-[0-9]{6}$/", $str)) {
            return true;    // 09xx-xxxxxx
        } else if(preg_match("/^09[0-9]{8}$/", $str)) {
            return true;    // 09xxxxxxxx
        } else if(preg_match("/^02|03|04|05|06|07|09[0-9]{7}$/", $str)) {
            return true;   
        } else if(preg_match("/^02|03|04|05|06|07|09[0-9]{8}$/", $str)) {
            return true;   
        } else if(preg_match("/^02|03|04|05|06|07|09[0-9]{10}$/", $str)) {
            return true;   
        } else {
            return false;
        }
    }

    private function isPostCode($str) {
        if(preg_match("/^[0-9]{3}$/", $str)) {
            return true;   
        }else if(preg_match("/^[0-9]{5}$/", $str)) {
            return true;   
        } else {
            return false;
        }
    }

    private function setDataLayer($actionType,$itemData){
        if($actionType=='檢視商品'){
            $retData['event'] = 'view_item';
            $retData['item_name'] = $itemData[0]['eiName'];
            $retData['item_id'] = $itemData[0]['eiSysID'];
            $retData['item_brand'] = '';
        }else{
            return '';
        }

        return $retData;
    }

    /*
    function ragicMember(){
        $this->load->model('Api_ragic');
        $url = "https://ap3.ragic.com/hugePlus/forms/2?v=3&api";
        $retData = $this->Api_ragic->getRagicFullText('forms/2',$user_detail['m_email'],100);
        $this->Api_common->dataDump($retData);
    }*/
}
