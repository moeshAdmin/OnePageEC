<?php
class Api_ret_invoice extends CI_Model{
   
    function __construct() {
        parent::__construct();
        $this->load->model('Api_common');        
    }

    function run($invoiceType,$action,$ecpayDetail,$orderDetail){
        //$ecpayDetail
        if($invoiceType=='ecpay'&&$action=='query'){
            $retData = $this->ecpay_queryInvoice($ecpayDetail,$orderDetail);
        }else if($invoiceType=='ecpay'&&$action=='retInvoice'){
            $retData = $this->ecpay_retInvoice($ecpayDetail,$orderDetail);
        }
        return $retData;
    }

    
    function ecpay_queryInvoice($ecpayDetail,$orderDetail){
        $postData['MerchantID'] = $ecpayDetail['MerchantID'];
        $postData['RqHeader']['Timestamp'] = strtotime(date('YmdHis'));
        $postData['RqHeader']['Revision'] = '3.4.3';
        $invoiceData = '{"MerchantID":"'.$ecpayDetail['MerchantID'].'","InvoiceNo":"'.$orderDetail['InvoiceNo'].'","InvoiceDate":"'.$orderDetail['InvoiceDate'].'"}';
        $postData['Data'] = $this->ecpay_stringHash('encrypt',$invoiceData,$ecpayDetail);
        
        $header = ['Content-Type:application/json'];
        if($ecpayDetail['Type']=='Normal'){
            $url = 'https://einvoice.ecpay.com.tw/B2CInvoice/GetIssue';
        }else if($ecpayDetail['Type']=='Test'){
            $url = 'https://einvoice-stage.ecpay.com.tw/B2CInvoice/Allowance';
        }
        
        $result = $this->Api_common->getCurl($url, json_encode($postData), $header, $ckfile);
        $retData = json_decode($result,true);

        $retData = $this->ecpay_stringHash('decrypt',$retData['Data'],$ecpayDetail);
        return $retData;
    }

    function ecpay_retInvoice($ecpayDetail,$orderDetail){
        $postData['MerchantID'] = $ecpayDetail['MerchantID'];
        $postData['RqHeader']['Timestamp'] = strtotime(date('YmdHis'));
        $postData['RqHeader']['Revision'] = '3.4.3';
        $invoiceRetData = '{"MerchantID":"'.$ecpayDetail['MerchantID'].'","InvoiceNo":"'.$orderDetail['InvoiceNo'].'","InvoiceDate":"'.$orderDetail['InvoiceDate'].'","AllowanceNotify":"N","AllowanceAmount":"'.$orderDetail['InvoiceRetAmount'].'","Items": [{"ItemSeq": 1,"ItemName": "無感退費","ItemCount": 1,"ItemWord": "件","ItemPrice": '.$orderDetail['InvoiceRetAmount'].',"ItemAmount": '.$orderDetail['InvoiceRetAmount'].'}]}';
        $postData['Data'] = $this->ecpay_stringHash('encrypt',$invoiceRetData,$ecpayDetail);
        $header = ['Content-Type:application/json'];
        if($ecpayDetail['Type']=='Normal'){
            $url = 'https://einvoice.ecpay.com.tw/B2CInvoice/Allowance';
        }else if($ecpayDetail['Type']=='Test'){
            $url = 'https://einvoice-stage.ecpay.com.tw/B2CInvoice/Allowance';
        }

        $result = $this->Api_common->getCurl($url, json_encode($postData), $header, $ckfile);
        $retData = json_decode($result,true);

        $retData = $this->ecpay_stringHash('decrypt',$retData['Data'],$ecpayDetail);
        return $retData;
    }

    function ecpay_stringHash($type,$str,$ecpayDetail){
        if($type=='encrypt'){
            $str = urlencode($str);
            $str = str_replace(['%21','%2a','%28','%29'], ['!','*','(',')'], $str);
            $str = openssl_encrypt($str, "AES-128-CBC", $ecpayDetail['HashKey'], 0, $ecpayDetail['HashIV']);
        }else if($type=='decrypt'){
            $str = openssl_decrypt($str, "AES-128-CBC", $ecpayDetail['HashKey'], 0, $ecpayDetail['HashIV']);
            $str = json_decode(urldecode($str),true);
        }
        
        return $str;
    }

    function ecpay_retInvoice22(){
        
        $sMsg = '' ;
        // 1.載入SDK
        require_once(APPPATH.'libraries/My_EcpayInvoice.php');
        $ecpay_invoice = new EcpayInvoice ;
        
        // 2.寫入基本介接參數
        $ecpay_invoice->Invoice_Method      = 'ALLOWANCE';
        $ecpay_invoice->Invoice_Url         = 'https://einvoice-stage.ecpay.com.tw/Invoice/Allowance' ;
        $ecpay_invoice->MerchantID      = '2000132' ;
        $ecpay_invoice->HashKey         = 'ejCk326UnaZWKisg' ;
        $ecpay_invoice->HashIV          = 'q9jcZX8Ib9LM8wYk' ;
        
        // 3.寫入發票相關資訊
        $ecpay_invoice->Send['InvoiceNo']   = 'MJ80025142'; // 發票號碼
        $ecpay_invoice->Send['InvoiceDate']     = '2021-08-16'; 
        $ecpay_invoice->Send['AllowanceNotify']     = 'N'; 
        $ecpay_invoice->Send['AllowanceAmount']     = '400'; 
        
        array_push($ecpay_invoice->Send['Items'], array('ItemSeq' => 0, 'ItemName' => '無感退費', 'ItemCount' => 1, 'ItemWord' => '批', 'ItemPrice' => 400, 'ItemAmount' => 400,'ItemTaxType'=>1)) ;

        // 4.送出
        $aReturn_Info = $ecpay_invoice->Check_Out();
        
        // 4.返回
        foreach($aReturn_Info as $key => $value)
        {
            $sMsg .=   $key . ' => ' . $value . '<br>' ;    
        }

        echo $sMsg;
    }

    function ecpay_invoice22(){
        $sMsg = '' ;
        // 1.載入SDK程式
        require_once(APPPATH.'libraries/My_EcpayInvoice.php');
        $ecpay_invoice = new EcpayInvoice ;
        
        // 2.寫入基本介接參數
        $ecpay_invoice->Invoice_Method          = 'INVOICE' ;
        $ecpay_invoice->Invoice_Url             = 'https://einvoice-stage.ecpay.com.tw/Invoice/Issue' ;
        $ecpay_invoice->MerchantID          = '2000132' ;
        $ecpay_invoice->HashKey             = 'ejCk326UnaZWKisg' ;
        $ecpay_invoice->HashIV              = 'q9jcZX8Ib9LM8wYk' ;
        
        // 3.寫入發票相關資訊
        $aItems = array();
        // 商品資訊
        array_push($ecpay_invoice->Send['Items'], array('ItemName' => '商品名稱一', 'ItemCount' => 1, 'ItemWord' => '批', 'ItemPrice' => 0, 'ItemTaxType' => 1, 'ItemAmount' => 0, 'ItemRemark' => '商品備註一'  )) ;
        array_push($ecpay_invoice->Send['Items'], array('ItemName' => '商品名稱二', 'ItemCount' => 1, 'ItemWord' => '批', 'ItemPrice' => 150, 'ItemTaxType' => 1, 'ItemAmount' => 150, 'ItemRemark' => '商品備註二' )) ;
        array_push($ecpay_invoice->Send['Items'], array('ItemName' => '商品名稱二', 'ItemCount' => 1, 'ItemWord' => '批', 'ItemPrice' => 250, 'ItemTaxType' => 1, 'ItemAmount' => 250, 'ItemRemark' => '商品備註三' )) ;
        
        $RelateNumber = 'ECPAY'. date('YmdHis') . rand(1000000000,2147483647) ; // 產生測試用自訂訂單編號
        $ecpay_invoice->Send['RelateNumber']            = $RelateNumber ;
        $ecpay_invoice->Send['CustomerID']          = '' ;
        $ecpay_invoice->Send['CustomerIdentifier']      = '' ;
        $ecpay_invoice->Send['CustomerName']            ='' ;
        $ecpay_invoice->Send['CustomerAddr']            = '' ;
        $ecpay_invoice->Send['CustomerPhone']           = '' ;
        $ecpay_invoice->Send['CustomerEmail']           = 'test@localhost.com' ;
        $ecpay_invoice->Send['ClearanceMark']           = '' ;
        $ecpay_invoice->Send['Print']               = '0' ;
        $ecpay_invoice->Send['Donation']            = '0' ;
        $ecpay_invoice->Send['LoveCode']            = '' ;
        $ecpay_invoice->Send['CarruerType']             = '' ;
        $ecpay_invoice->Send['CarruerNum']          = '' ;
        $ecpay_invoice->Send['TaxType']             = 1 ;
        $ecpay_invoice->Send['SalesAmount']             = 400 ;
        $ecpay_invoice->Send['InvoiceRemark']           = 'v1.0.190822' ;   
        $ecpay_invoice->Send['InvType']             = '07' ;
        $ecpay_invoice->Send['vat']                 = '' ;
        // 4.送出
        $aReturn_Info = $ecpay_invoice->Check_Out();
        
        // 5.返回
        foreach($aReturn_Info as $key => $value)
        {
            $sMsg .=   $key . ' => ' . $value . '<br>' ;
        }
        echo $RelateNumber.'<br>';
        echo $sMsg;
    }
}
?>