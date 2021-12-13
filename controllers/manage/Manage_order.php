<?php

class Manage_order extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_invoice');
        $this->load->model('Api_excel');
        $this->load->model('Api_table_generate');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $itemData = $this->Api_common->getDataCustom('eiName,eiSysID,eiStatus','ec_item','all');
        foreach ($itemData as $key => $value) {
            $data['itemData'][$key]['name'] = '['.$value['eiStatus'].'] '.$value['eiName'];
            $data['itemData'][$key]['id'] = $value['eiSysID'];
        }
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_order",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
            $field = array('eoReceiverName','eoReceiverPhone','eoReceiverEmail','eoReceiverPostCode','eoReceiverAddr','eoInvoiceMeta','eoInvoiceType','eoInvoiceComNo','eoInvoiceCom','eoInvoiceAddr','eoInnerNote','eoDeliverOutFile','eoDeliverReceiveFile','eoPlainShipDate');
            foreach ($field as $key => $value) {
               $retData['orderData'][$value] = $resData[0][$value];
            }
            if($retData['orderData']['eoInvoiceCom']){
                $retData['orderData']['isComInv'] = 'true';
            }else{
                $retData['orderData']['isComInv'] = 'false';
            }
            $retData['orderData']['hash'] = $hash;
        }else{
            $postData = $this->input->post();
            $postData = $this->Api_common->cleanPostData($postData);
            if($postData['payStatus']){
                $postData['payStatus'] = explode(',', $postData['payStatus']);
            }
            if($postData['shipStatus']){
                $postData['shipStatus'] = explode(',', $postData['shipStatus']);
            }
            //取得訂單
            $resData = $this->Api_common->getDataCustom('*','ec_order','eoPlainShipDate BETWEEN "'.$postData['dateFrom'].'" AND "'.$postData['dateTo'].'"');
            foreach ($resData as $key => $value) {
                $ordLst[$value['eoOrderNo']] = $value['eoOrderNo'];
            }
            //取得訂單子表
            $resData2 = $this->Api_common->getDataInCustom('*','ec_order_detail','eodOrderNo',$ordLst,'none','in');
            //$resData2 = $this->Api_common->getDataCustom('*','ec_order_detail','eodPlainShipDate BETWEEN "'.$postData['dateFrom'].'" AND "'.$postData['dateTo'].'"');
            foreach ($resData2 as $key => $value) {
                $resDetail[$value['eodOrderNo']][$value['eodOrderSubNo']] = $value;
            }

            foreach ($resData as $key => $value) {
                if($postData['payStatus']&&!in_array($resData[$key]['eoPayStatus'], $postData['payStatus'])){continue;}
                if($postData['shipStatus']&&!in_array($resData[$key]['eoOrderStatus'], $postData['shipStatus'])){continue;}
                if($postData['itemID']!='all'&&$resData[$key]['eoItemNo']!=$postData['itemID']){continue;}
                if($postData['invStatus']=='未開立'){
                    if($resData[$key]['eoOrderStatus']=='已出貨'&&$resData[$key]['eoInvoiceStatus']=='未開立'){

                    }else{
                        continue;
                    }
                }
                if($postData['invStatus']=='已開立'&&$resData[$key]['eoInvoiceStatus']!='已開立'){continue;}
                if($postData['invStatus']=='開立錯誤'&&($resData[$key]['eoInvoiceStatus']=='已開立'||$resData[$key]['eoInvoiceStatus']=='未開立')){continue;}
                if($postData['shipFileStatus']=='未拋檔'){
                    if($resData[$key]['eoOrderStatus']=='待出貨'&&!$resData[$key]['eoDeliverOutFile']){

                    }else{
                        continue;
                    }
                }

                if($postData['shipFileStatus']=='已拋未回應'){
                    if($resData[$key]['eoOrderStatus']=='待出貨'&&$resData[$key]['eoDeliverOutFile']&&!$resData[$key]['eoDeliverReceiveFile']){
                    }else{continue;}
                }
                if($postData['shipFileStatus']=='已出貨'&&preg_match('/已取貨|已收貨/', $resData[$key]['eoShipProcess'])){
                    continue;
                }
                if($postData['shipFileStatus']=='已出貨'&&!$resData[$key]['eoDeliverReceiveFile']){
                    continue;
                }
                if($postData['shipFileStatus']=='已收貨'&&!preg_match('/已取貨|已收貨/', $resData[$key]['eoShipProcess'])){
                    continue;
                }
                
                $ordNo = $resData[$key]['eoOrderNo'];
                $newData[$ordNo] = $resData[$key];
                $newData[$ordNo]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eoSysID']);
                $newData[$ordNo]['eoOrderNoHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eoOrderNo']);
                $newData[$ordNo]['eoARSOrderNoHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eoARSOrderNo']);
                
                $newData[$ordNo]['msg'] = 0;
                $newData[$ordNo]['detail'] =  $resDetail[$ordNo];
            }
            /*
            $msgData = $this->Api_common->getDataCustom('*','ec_require','erIsRead = "N"');
            foreach ($msgData as $key => $value) {
                $ordNo = $msgData[$key]['erOrderNo'];
                $newData[$ordNo]['msg'] += 1;
            }*/
            $retData['object'] = $newData;

        }

        if($postData['export']=='excel'){
            $retData = $this->exportData($newData,'ec_order',$postData);
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;            
        }else if($postData['export']=='shipExcel'){
            //一般物流
            $postData['exportType'] = 'normal';
            $exData = $this->exportExcel($newData,'ec_order',$postData);
            $retData['url'][0] = $exData['url'];
            $this->setExportStatus($newData,$exData['hash'],$postData);
            //echo $this->Api_common->setFrontReturnMsg('901','',$retData);
            //exit;
            //輸出
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;
        }else if($postData['export']=='shipExcel_cvs'){
            //超商物流
            $postData['exportType'] = 'cvs';
            $exData = $this->exportExcel($newData,'ec_order',$postData);
            $retData['url'][0] = $exData['url'];
            $this->setExportStatus($newData,$exData['hash'],$postData);
            //echo $this->Api_common->setFrontReturnMsg('901','',$retData);
            //exit;
            //輸出
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;
        }else{
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;
        }
    }

    function setExportStatus($orderData,$hash,$postData){
        $fileName = $this->Api_common->stringHash('decrypt',$hash);
        foreach ($orderData as $ordNo => $value) {
            if($postData['exportType']=='cvs'){
                if(!$orderData[$ordNo]['eoDeliverCvsID']){continue;}
            }else if($postData['exportType']=='normal'){
                if($orderData[$ordNo]['eoDeliverCvsID']){continue;}
            }
            $updateData[$ordNo]['eoOrderNo'] = $ordNo;
            $updateData[$ordNo]['eoShipProcess'] = '已拋檔';
            $updateData[$ordNo]['eoDeliverOutFile'] = $fileName;
            $updateData[$ordNo]['eoShipProcess1Time'] = date('Y-m-d H:i:s');
        }
        if($updateData){
            $this->db->update_batch('ec_order', $updateData, 'eoOrderNo');
        }
    }

    function cancelOrder(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
        $user_detail=$this->session->all_userdata();

        //取得訂單確認是否已退款
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
        $payType = $this->Api_common->getSysConfig('ecPayType');
        $this->chkOrder($payType,$orderData);
        
        $submitData['eoOrderStatus'] = '已取消';
        $submitData['eoPayStatus'] = '已取消';
        $submitData['eoIsReturn'] = 'Y';
        $submitData['eoOrderNote'] = $orderData[0]['eoOrderNote'].' 已取消 '.date('Y-m-d H:i:s');
        $submitData['eoUpdateEmpName'] = $user_detail['account'];
        $submitData['eoUpdateDTime'] = date('Y-m-d H:i:s');
        $this->db->where('eoSysID', $sysID);
        $this->db->update('ec_order', $submitData);

        //寫入退貨資料
        $insertInventory['eiItemNo'] = $orderData[0]['eoItemSKU'];
        $insertInventory['eiItemQty'] = (int)abs($orderData[0]['eoItemQty']);
        $insertInventory['eiTicketNo'] = $orderData[0]['eoOrderNo'];
        $insertInventory['eiTicketType'] = '退貨單';
        $insertInventory['eiDate'] = date('Y-m-d');
        $insertInventory['eiCreateEmp'] = $user_detail['account'];
        $this->db->insert('ec_inventory', $insertInventory);

        //發信通知
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
        $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']);
        $this->Api_ec->sendOrderMail('已取消',$postData);
        
        //重建庫存快取
        $this->Api_ec->rebuildInventory();

        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function retOrder(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
        $user_detail=$this->session->all_userdata();

        //取得訂單確認是否已退款
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
        $payType = $this->Api_common->getSysConfig('ecPayType');
        $this->chkOrder($payType,$orderData);
        
        $submitData['eoOrderStatus'] = '已退貨';
        $submitData['eoPayStatus'] = '已退款';
        $submitData['eoIsReturn'] = 'Y';
        $submitData['eoOrderNote'] = $orderData[0]['eoOrderNote'].' 已退貨 '.date('Y-m-d H:i:s');
        $submitData['eoUpdateEmpName'] = $user_detail['account'];
        $submitData['eoUpdateDTime'] = date('Y-m-d H:i:s');
        $this->db->where('eoSysID', $sysID);
        $this->db->update('ec_order', $submitData);

        //寫入退貨資料
        $insertInventory['eiItemNo'] = $orderData[0]['eoItemSKU'];
        $insertInventory['eiItemQty'] = (int)abs($orderData[0]['eoItemQty']);
        $insertInventory['eiTicketNo'] = $orderData[0]['eoOrderNo'];
        $insertInventory['eiTicketType'] = '退貨單';
        $insertInventory['eiDate'] = date('Y-m-d');
        $insertInventory['eiCreateEmp'] = $user_detail['account'];
        $this->db->insert('ec_inventory', $insertInventory);

        //發信通知
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
        $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']);
        $this->Api_ec->sendOrderMail('已退貨',$postData);

        //重建庫存快取
        $this->Api_ec->rebuildInventory();
        
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function confirmShipping(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $user_detail=$this->session->all_userdata();
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);

        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');        

        //更新狀態
        $submitData['eoOrderStatus'] = '已出貨';
        $submitData['eoDeliverName'] = $postData['deliverName'];
        $submitData['eoDeliverCode'] = $postData['deliverCode'];
        $submitData['eoShipProcess'] = '已出貨';
        if(!$orderData[0]['eoShipProcess1Time']){
            $submitData['eoShipProcess1Time'] = date('Y-m-d H:i:s');
        }
        $submitData['eoShipProcess2Time'] = date('Y-m-d H:i:s');
        $submitData['eoUpdateEmpName'] = $user_detail['account'];
        $submitData['eoUpdateDTime'] = date('Y-m-d H:i:s');
        $this->db->where('eoSysID', $sysID);
        $this->db->update('ec_order', $submitData);

        //發信通知        
        $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']);
        $this->Api_ec->sendOrderMail('已出貨',$postData);
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function confirmInvoice(){
        $this->Api_invoice->confirmInvoice();
    }

    function confirmPaid(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $user_detail=$this->session->all_userdata();
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);

        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');
        //更新狀態
        $submitData['eoPayDate'] = $postData['payDate'];
        $submitData['eoInnerNote'] = $orderData[0]['eoInnerNote'].''.$postData['payNote'];
        $submitData['eoPayAmount'] = $orderData[0]['eoOrderAmount'];
        $submitData['eoPayStatus'] = '已付款';
        $submitData['eoOrderStatus'] = '待出貨';
        $submitData['eoUpdateEmpName'] = $user_detail['account'];
        $submitData['eoUpdateDTime'] = date('Y-m-d H:i:s');
        $this->db->where('eoSysID', $sysID);
        $this->db->update('ec_order', $submitData);

        //發信通知        
        $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$orderData[0]['eoOrderNo']);
        $this->Api_ec->sendOrderMail('待出貨',$postData);
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function editOrder(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
        if($postData['isComInv']=='true'){
            $postData['eoInvoiceType'] = '';
            $postData['eoInvoiceMeta'] = '';
        }else if($postData['isComInv']=='false'){
            $postData['eoInvoiceCom'] = NULL;
            $postData['eoInvoiceComNo'] = NULL;
        }
        unset($postData['isComInv']);
        unset($postData['hash']);
        $submitData = $postData;
        $this->db->where('eoSysID', $sysID);
        $this->db->update('ec_order', $submitData);
        echo $this->Api_common->setFrontReturnMsg('200','',$submitData);
        exit;
    }

    function chkOrderIsPay(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoSysID = "'.$sysID.'"');

        if($orderData[0]['eoIsARS']=='Y'){
            $arsData[0]['eoOrderNo'] = $orderData[0]['eoARSOrderNo'];
            $arsData[0]['eoPayRand'] = $orderData[0]['eoPayRand'];
        }
        //確認付款狀態
        $payType = $this->Api_common->getSysConfig('ecPayType');        
        if($payType['scValue1']=='Test'){
            if($orderData[0]['eoIsARS']=='N'){
                $result = $this->Api_ec->ec_query($orderData,'1');//綠界查詢是否已付款
            }else{
                $result = $this->Api_ec->ec_query($orderData,'1');//綠界查詢是否已付款                
                $result2 = $this->Api_ec->ec_query_period($arsData,'1');
                $result['ARS'] = $result2;
            }
            $finalPayAmount = '測試環境無實際授權';
        }else if($payType['scValue1']=='Normal'){
            if($orderData[0]['eoIsARS']=='N'){
                $result = $this->Api_ec->ec_query($orderData,'0');//綠界查詢是否已付款
            }else{
                $result = $this->Api_ec->ec_query($orderData,'0');//綠界查詢是否已付款
                $result2 = $this->Api_ec->ec_query_period($arsData,'0');
                $result['ARS'] = $result2;
            }
            if($result['TradeStatus']==1){
                $detailResult = $this->Api_ec->ec_query2($result,'0');//綠界查詢是否已付款
                foreach ($detailResult['RtnValue']['close_data'] as $key => $value) {
                    $finalPayAmount += $value['amount'];
                    if($value['amount']<0){
                        $refund = $value['amount'];
                    }
                }
            }
        }

        if($result['TradeStatus']==1){
            if($refund){
                $msg = '已取消授權金額:'.number_format(abs($refund));
            }else{
                $msg = '訂單已授權，授權金額: '.number_format($finalPayAmount);
            }            
            echo $this->Api_common->setFrontReturnMsg('200',$msg,$result);
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','未付款，交易代碼: '.$result['TradeStatus'],$result);
        }
        
        exit;
    }

    private function chkOrder($payType,$orderData){
        if($payType['scValue1']=='Test'){
            $sysType = 1;
        }else if($payType['scValue1']=='Normal'){
            $sysType = 0;
        }
        if($orderData[0]['eoPayType']=='信用卡'){
            // 功能已串接，但判斷未完成，需要有測試資料判斷
            $payResult = $this->Api_ec->ec_query($orderData,$sysType);//綠界查詢是否已付款            
            if($payResult['TradeStatus']==1){
                $detailResult = $this->Api_ec->ec_query2($payResult,$sysType);//綠界查詢是否已付款
                foreach ($detailResult['RtnValue']['close_data'] as $key => $value) {
                    $finalPayAmount += $value['amount'];
                    if($value['amount']<0){
                        $refund = $value['amount'];
                    }
                }
                if($finalPayAmount>0){
                    $data['payResult'] = $payResult;
                    $data['detailResult'] = $detailResult;
                    echo $this->Api_common->setFrontReturnMsg('901','訂單尚未取消授權，請至綠界後台進行取消授權，目前授權金額: '.round($finalPayAmount),$data);
                    exit;
                }
            }
        }
        if($orderData[0]['eoInvoiceStatus']=='已開立'){
            $invoiceResult = $this->Api_invoice->queryInvoice($invoiceNo,$sysType,$detail);//發票狀態
            if(!preg_match('/作廢|折讓|沒有此筆發票資訊/', $invoiceResult)){
                echo $this->Api_common->setFrontReturnMsg('901','發票未折讓或作廢',null);
                exit;
            }
        }
        return $payResult;
    }

    function resetInvoice(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $user_detail=$this->session->all_userdata();
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);

        $orderData = $this->Api_common->getDataCustom('eoInvoiceStatus,eoOrderNo','ec_order','eoSysID = "'.$sysID.'"');
        if($orderData[0]['eoInvoiceStatus']=='未開立'||$orderData[0]['eoInvoiceStatus']=='已開立'){
            echo $this->Api_common->setFrontReturnMsg('901','發票為不可重設狀態',$orderData);
            exit;
        }else{
            $updateData['eoInvoiceStatus'] = '未開立';
            $this->db->where('eoSysID', $sysID);
            $this->db->update('ec_order', $updateData);
        }

        echo $this->Api_common->setFrontReturnMsg('200',$orderData[0]['eoOrderNo'].' 發票已完成重設',$orderData);
        exit;
        
    }

    function queryInvoice($invoiceNo){
        $payType = $this->Api_common->getSysConfig('ecPayType');
        if($payType['scValue1']=='Test'){
            $isTest = 1;
        }else if($payType['scValue1']=='Normal'){
            $isTest = 0;
        }
        $result = $this->Api_invoice->queryInvoice($invoiceNo,$isTest,$detail);
        echo $this->Api_common->setFrontReturnMsg('200',$result,null);
        exit;
    }

    function queryInvoiceErr(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
        $orderData = $this->Api_common->getDataCustom('eoInvoiceStatus','ec_order','eoSysID = "'.$sysID.'"');
        echo $this->Api_common->setFrontReturnMsg('200',$orderData[0]['eoInvoiceStatus'],$sysID);
        exit;
    }

    function sendNotifiy(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $orderNo = $this->Api_common->stringHash('decrypt',$postData['orderNoHash']);

        //檢查是否有尚未通知訊息
        $msgData = $this->Api_common->getDataCustom('*','ec_require','erIsRead = "N" AND erOrderNo = "'.$orderNo.'"');
        if(!$msgData){
            echo $this->Api_common->setFrontReturnMsg('901','所有訊息均已通知',null);
            exit;
        }

        //發送通知給客戶
        $postData['orderNo'] = $postData['orderNoHash'];
        $this->Api_ec->sendOrderMail('通知',$postData);

        //設定訊息均已通知
        $submitData['erIsRead'] = 'Y';
        $this->db->where('erOrderNo', $orderNo);
        $this->db->update('ec_require', $submitData);

        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit; 
    }

    function RequireLoad(){
        $retData = $this->Api_ec->RequireLoad();
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit; 
    }

    function RequireReturnSubmit(){
        $this->Api_ec->RequireReturnSubmit();
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit; 
    }

    private function exportData($resData,$name,$postData){
        $num = 2;
        $title = array();
        $exceptPreg = '/^eoItem|hash|eoOrderNoHash|msg/';
        foreach ($resData as $key => $value) {
            foreach ($resData[$key] as $key2 => $value2) {
                if(preg_match($exceptPreg, $key2)){unset($resData[$key][$key2]);continue;}
            }
            foreach ($value['detail'] as $key2 => $value2) {
                $newData[$key.'-'.$key2] = array_merge($resData[$key],$value2);
                unset($newData[$key.'-'.$key2]['detail']);
            }
            
        }

        foreach ($newData as $key => $value) {
            if($num==2){
                foreach ($newData[$key] as $key2 => $value2) {
                    array_push($title, $key2);
                }
            }
            $excelKey = "A".$num;
            $excelExport[$excelKey] = array();
            array_push($excelExport[$excelKey], $newData[$key]);
            $num++;
        }
        $detail['downloadName'] = 'orderData_'.$postData['dateFrom'].'_'.$postData['dateTo'];
        $detail['sheetName'] = "orderData";
        $detail['startLine'] = 2;
        $detail['totalLine'] = count($excelExport);
        $detail['titleLine'] = 1;
        $this->Api_excel->export_Excel($name,$excelExport,false,$title,$detail);

        $hash = $this->Api_common->stringHash('encrypt',$detail['downloadName']);
        $postData['url'][0] = base_url().'manage/manage_file/downloadFile/'.$hash;
        echo $this->Api_common->setFrontReturnMsg('200','',$postData);
        exit;
    }

    private function exportExcel($resData,$name,$postData){
        $num = 2;
        $titleAry = array();
        $ts = $this->excelTitleTrans($type,$detail);
        foreach ($ts as $titleName => $value) {
            array_push($titleAry, $titleName);
        }

        foreach ($resData as $key => $value) {
            foreach ($value['detail'] as $key2 => $value2) {
                $newData[$key.'-'.$key2] = array_merge($resData[$key],$value2);
                unset($newData[$key.'-'.$key2]['detail']);
            }
        }

        foreach ($newData as $key => $value) {
            if($postData['exportType']=='cvs'){
                if(!$value['eoDeliverCvsID']){continue;}
            }else if($postData['exportType']=='normal'){
                if($value['eoDeliverCvsID']){continue;}
            }
            foreach ($ts as $titleName => $sqlField) {
                if($sqlField=='itemName'){
                    $table[$key][$titleName] = $value['eodItemName'].' '.$value['eodItemType'];
                }else if($sqlField=='eoReceiverAddr'&&$value['eoPayType']=='貨到付款'){
                    $table[$key][$titleName] = $value[$sqlField].' (貨到付款)';
                }else if($sqlField=='cvsType'){
                    if($value['eoDeliverName']=='FAMI'){
                        $table[$key][$titleName] = 1;
                    }
                }else if($sqlField=='deliverPay'&&$value['eoPayType']=='貨到付款'){
                    $table[$key][$titleName] = 1;
                }else if($sqlField=='deliverPay'&&$value['eoPayType']=='超商取貨付款'){
                    $table[$key][$titleName] = 1;
                    $table[$key]['地址'] .= ' (超商取貨付款)';
                }else if($sqlField=='deliverPay'&&$value['eoPayType']=='信用卡'){
                    $table[$key][$titleName] = "";
                }else if($sqlField=='eoDeliverTime'){
                    $table[$key][$titleName] = '';
                }else if($value[$sqlField]){
                    $table[$key][$titleName] = $value[$sqlField];
                }
                if(!$table[$key][$titleName]){
                    $table[$key][$titleName] = '';
                }
            }
        }

        foreach ($table as $key => $value) {
            $excelKey = "A".$num;
            $excelExport[$excelKey] = array();
            array_push($excelExport[$excelKey], $table[$key]);
            $num++;
        }
        //if($postData['exportType']=='cvs'){
        //$this->Api_common->dataDump($table);
        //}
        
        $detail['downloadName'] = TICKET_ID.'_'.$postData['exportType'].date('Ymd').'R'.rand(100,999);
        $detail['sheetName'] = "orderData_".$postData['exportType'];
        $detail['startLine'] = 2;
        $detail['totalLine'] = count($excelExport);
        $detail['titleLine'] = 1;
        if($excelExport){
            $this->load->model('Api_excel');
            $this->Api_excel->export_Excel($name,$excelExport,false,$titleAry,$detail);
            $hash = $this->Api_common->stringHash('encrypt',$detail['downloadName']);
            $postData['url'] = base_url().'manage/manage_file/downloadFile/'.$hash;
            $postData['hash'] = $hash;
        }else{
            $postData['url'] = '';
            $postData['hash'] = '';
        }
        
        return $postData;
    }

    private function excelTitleTrans($type,$detail){
        $data = array(
            '訂單編號'=>'eoOrderNo',
            '收件人姓名'=>'eoReceiverName',
            '地址'=>'eoReceiverAddr',
            'Email'=>'eoReceiverEmail',
            '電話'=>'eoReceiverPhone',
            '品號'=>'eodItemSKU',
            '品名'=>'itemName',
            '數量'=>'eodItemQty',
            '貨品狀態（良品: 空白或0，瑕疵品: 1，報廢品: 2）'=>'',
            '批號（不指定批號請留白） '=>'',
            '是否都出同效期（否: 空白或0 ，是: １）  '=>'',
            '預約出貨日期（年-月-日）(註: 為作業日期，非到貨日) '=>'',
            '代收超商* (註: 非企業版商家目前禁止匯入 7-11訂單)（空白: 不使用超取，0: 7-11，1: 全家，2: 萊爾富)'=>'cvsType',
            '超商代碼*（若使用超取，請填超商代碼。不使用超取，請填地址'=>'eoDeliverCvsID',
            '指定到貨時段*（空白: 不指定，1: 中午前，2: 12-17時，3: 17-20時）（超取無法指定時段）'=>'eoDeliverTime',
            '來回件*（空白: 否，1: 是）（超取無法指定來回件）'=>'',
            '貨到付款*（空白: 純配送，1: 貨到付款）'=>'deliverPay',
            '總金額*（請輸入訂單總金額）'=>'eoOrderAmount',
            '備註'=>'eoInnerNote',
            'version3.0'=>'',
            'version3.0 更新:   「非企業版」商家目前禁止匯入 7-11 訂單 ( 超取訂單 M欄代收超商不得填 \'0\' )'=>''

        );

        return $data;
    }

    function outputTable(){
        $this->db->select('DATE_FORMAT(eoShipProcess1Time, "%Y-%m-%d") as 拋檔輸出日期,eoDeliverOutFile as 檔案名稱,count(eoOrderNo) as 資料筆數');
        $this->db->from('ec_order');
        $this->db->where("eoOrderStatus in ('待出貨','已出貨','已收貨')");
        $this->db->where("eoShipProcess1Time BETWEEN '".date('Y-m-d 23:59:00',strtotime(date('Y-m-d'))-86400*5)."' AND '".date('Y-m-d 23:59:00')."'");
        $this->db->group_by('eoDeliverOutFile');
        $this->db->order_by('eoShipProcess1Time DESC');
        $query = $this->db->get();
        $ary = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                if($row['拋檔輸出日期']==''){
                    $row['拋檔輸出日期'] = '尚未拋檔';
                }
                array_push($ary, $row);
            }
        }

        $this->load->model('Api_table_generate');
        $detail['title'] = array('拋檔輸出日期','檔案名稱','資料筆數');
        $detail['allBorder'] = $detail['title'];
        $retData['table'] = $this->Api_table_generate->drawTable($ary,$detail,$data);

        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function getARSOrderDetail($hash){
        $this->load->model('Api_ragic');
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$hash);
        $arsOrderNo = 'HA20210610LL0001';
        $result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/8?where=1000342,eq,'.$arsOrderNo.'', $ckfile),true);
        foreach ($result as $key => $value) {
            echo '<script>location.href = "https://ap3.ragic.com/hugePlus/forms/8/'.$key.'";</script>';
            exit;
        }
    }

}
