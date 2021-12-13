<?php
class Api_invoice extends CI_Model{
   
    function __construct() {
        parent::__construct();
        $this->load->model('Api_common');        
    }

    function uploadInvoice($orderData,$isTest,$detail){
        if($isTest==1){
            $url = EINVOICE_URL_Test.'/PayNowEInvoice.asmx?WSDL';
            $postData['mem_cid'] = EINVOICE_CID_Test;
            $postData['mem_password'] = EINVOICE_PW_Test;
        }else if($isTest==0){
            //$url = EINVOICE_URL.'/PayNowEInvoice.asmx?WSDL';
            $url = EINVOICE_URL.'/PayNowEInvoice.asmx?WSDL';
            $postData['mem_cid'] = EINVOICE_CID;
            $postData['mem_password'] = EINVOICE_PW;
        }  
        
        $objSoapClient = new SoapClient($url);        
        
        $csv[0]['orderno'] = $orderData[0]['eoOrderNo'];

        if($orderData[0]['eoInvoiceComNo']){
            $csv[0]['buyer_id'] = $orderData[0]['eoInvoiceComNo'];
            $csv[0]['buyer_name'] = $orderData[0]['eoInvoiceCom'];
        }else{
            $csv[0]['buyer_id'] = '';
            $csv[0]['buyer_name'] = $orderData[0]['eoReceiverName'];;
        }        
        $csv[0]['buyer_add'] = '';
        $csv[0]['buyer_phone'] = $orderData[0]['eoReceiverPhone'];
        $csv[0]['buyer_email'] = $orderData[0]['eoReceiverEmail'];
        $csv[0]['CarrierType'] = $orderData[0]['eoInvoiceType'];
        $csv[0]['CarrierID_1'] = $orderData[0]['eoInvoiceMeta'];
        $csv[0]['CarrierID_2'] = $orderData[0]['eoInvoiceMeta'];
        $csv[0]['LoveCode'] = $orderData[0]['eoInvoiceLoveCode'];
        $csv[0]['Description'] = str_replace(' ', '_', $orderData[0]['eoItemName'].$orderData[0]['eoItemType']);
        $csv[0]['Quantity'] = (int)$orderData[0]['eoItemQty'];
        $csv[0]['UnitPrice'] = (int)$orderData[0]['eoItemPrice'];
        $csv[0]['Amount'] = (int)$orderData[0]['eoOrderAmount'];
        $csv[0]['Remark'] = '';

        $csv[0]['ItemTaxtype'] = 1;
        $csv[0]['IsPassCustoms'] = 0;

        foreach ($csv[0] as $key => $value) {if(strlen($value)==0){$csv[0][$key] = '';}}

        foreach ($csv as $key => $value) {
            $postData['csvStr'] = 
            "'".$value['orderno'].",'".$value['buyer_id'].",'".$value['buyer_name'].",'".$value['buyer_add'].",'".$value['buyer_phone'].",'".$value['buyer_email'].",'".$value['CarrierType'].",'".$value['CarrierID_1'].",'".$value['CarrierID_2'].",'".$value['LoveCode'].",'".$value['Description'].",'".$value['Quantity'].",'".$value['UnitPrice'].",'".$value['Amount'].",'".$value['Remark'].",'".$value['ItemTaxtype'].",'".$value['IsPassCustoms'];
            //if($key+1!=count($csv)){$postData['csvStr'] .= chr(10);}
        }
        //$postData['csvStr'] = "'201611220999,'28229955,'立吉富限上金流,'台北市中山區松山路 207 號 9 樓,'0225172626,'service@paynow.com.tw,',',',','測試 3,'2,'1000,'2000,'ps_測試,'1,'0";
        $postData['csvStr'] = urlencode(str_replace('', '', base64_encode($postData['csvStr'])));
        $out = $objSoapClient->UploadInvoice_Patch($postData);

        return $out;
    }

    function queryInvoice($InvoiceNo,$isTest,$detail){
        if($isTest==1){
            $url = EINVOICE_URL_Test.'/PayNowEInvoice.asmx?WSDL';
            $postData['mem_cid'] = EINVOICE_CID_Test;
        }else if($isTest==0){
            //$url = EINVOICE_URL.'/PayNowEInvoice.asmx?WSDL';
            $url = EINVOICE_URL.'/PayNowEInvoice.asmx?WSDL';
            $postData['mem_cid'] = EINVOICE_CID;
        }
        //$postData['OrderNo'] = 'T20201028D0005';
        $postData['InvoiceNo'] = $InvoiceNo;

        $objSoapClient = new SoapClient($url);
        $out = $objSoapClient->Invoice_Info($postData)->Invoice_InfoResult;

        return $out;
    }

    function confirmInvoice(){
        $user_detail=$this->session->all_userdata();
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_invoice.txt","w+","Start..");
        
        //開立發票
        $payType = $this->Api_common->getSysConfig('ecPayType');
        if($payType['scValue1']=='Test'){
            $isTest = 1;
        }else if($payType['scValue1']=='Normal'){
            $isTest = 0;
        }


        $resData = $this->Api_common->getDataCustom('*','ec_order','eoOrderStatus = "已出貨" AND eoInvoiceStatus = "未開立"');
        
        $total = count($resData);
        foreach ($resData as $key => $value) {
            $orderData[0] = $resData[$key];
            $invoice = $this->Api_invoice->uploadInvoice($orderData,$isTest,$detail);
            if(preg_match('/^S_/', $invoice->UploadInvoice_PatchResult)){
                //完成開立
                $inv = explode('_', $invoice->UploadInvoice_PatchResult);
                $submitData['eoInvoiceStatus'] = '已開立';
                $submitData['eoInvoiceTime'] = date('Y-m-d H:i:s');
                $submitData['eoInvoiceNo'] = $inv[2];
                $success++;
            }else{
                //失敗
                $submitData['eoInvoiceStatus'] = $invoice->UploadInvoice_PatchResult;
                $error++;
            }
            $this->db->where('eoSysID', $resData[$key]['eoSysID']);
            $this->db->update('ec_order', $submitData);
            unset($submitData);
            //進度
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_invoice.txt","w+","發票開立中，進度: [".$key."/".$total."] ");
        }

        echo $this->Api_common->setFrontReturnMsg('200','完成開立 '.(int)$success.' 筆, 失敗 '.(int)$error.' 筆',null);
        exit;
    }
}
?>