<?php

class Portal extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_data');
        $this->Api_common->chkBlockIP();
        $this->Api_common->initLang();
        define('LANG',$this->Api_common->getCookie('lang'));
        $this->load->model('Lang');
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    // 主畫面
    function index(){
        $this->Api_common->redirectHttps();
        $user_detail=$this->session->all_userdata();
        //$data['googleld'] .= $this->Api_common->initGoogleLD('organiztion',$data['ld-breadcrumb']);
        $data['title'] = 'Home';
        $data['url'] = 'https:'.base_url();
        $data['desc'] = "";
        echo '<script language="javascript">document.location.href="'.base_url().'pages/article/8";</script>';exit;
        $allItemData = $this->Api_ec->getAllItemData();
        
        $data['cardElement'] = $this->Api_data->getItemCardElements($allItemData)['cardElement'];
        $data['topMenu'] = $this->getMenu('topMenu_return');

        $this->load_frontEndView("portal",$data); // 陣列資料 data 與 View Rendering
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    function getMenu($type){
        return $this->Api_data->getMenu($type);
    }

    function nf404(){
        echo '<script>window.location.href = "'.base_url().'"</script>';
        exit;
    }

    function sitemap(){
        $str .=  '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $resData = $this->Api_common->getDataCustom('caSysID,caURL','cms_article','caType != "private"','caDate DESC');
        foreach ($resData as $key => $value) {
            if($resData[$key]['caURL']){
                $str .=  '<url><loc>https:'.base_url().'pages/article/'.$resData[$key]['caURL'].'</loc></url>';
            }
            $str .=  '<url><loc>https:'.base_url().'pages/article/'.$resData[$key]['caSysID'].'</loc></url>';
        }

        $resData2 = $this->Api_common->getDataCustom('ccSysID,ccURL','cms_categorys','all');
        foreach ($resData2 as $key => $value) {
            if($resData2[$key]['ccURL']){
                $str .=  '<url><loc>https:'.base_url().'pages/category/'.$resData2[$key]['ccURL'].'</loc></url>';
            }
            $str .=  '<url><loc>https:'.base_url().'pages/category/'.$resData2[$key]['ccSysID'].'</loc></url>';
        }
        
        $str .=  '</urlset>';
        $this->Api_common->saveData(APPPATH.'sitemap.xml','w',$str);
        echo $str;
    }

    function getProcess($empID,$pageID){
        //if(!$this->input->is_ajax_request()){echo '非ajax呼叫';}
        if($empID&&$pageID){
            $msg = file_get_contents(DIR_SITE_FILE."temp/process_log/".$empID."_".$pageID.".txt");
            echo $msg;
        }
        exit;
    }

    private function mailTest(){
        $this->load->library('My_SendMail');
        $data = array('recipient'=>array('admin@moesh.tw','sc024500@gmail.com'),'cc'=>'', 'subject' => mb_convert_encoding('中文標題測試3333', "UTF-8","auto"), 'content' => '中文測試2222','sender'=>'日研專科 客服信箱');   
        header("Content-Type:text/html; charset=utf-8");
        $str = $this->my_sendmail->sendOut($data);
        $this->Api_common->dataDump($str);
    }
    /*
    function test(){
        $postData['name'] = 'Honeycomb'.strtotime(date('YmdHis'));
        $postData['customer_name'] = 'Mom';
        $postData['channel_id'] = '3';
        $postData['is_pending'] = 'true';
        $postData['total_price'] = '555';
        $postData['shipping_type'] = 'TCAT_COD';
        $postData['give_and_take'] = 'true';
        $postData['order_products[][id]']= 6;
        $postData['order_products[][quantity]'] = 2;
        $postData['order_products[][product_type]'] = 'normal';
        $postData['order_products[][stock_required]'] = 'false';
        $postData['order_products[][is_bundle]'] = 'false';
        $json = json_decode($this->honeycombAPI('POST','/v2/orders',$postData),true);
        $this->Api_common->dataDump($json);
    }

    function test2(){
        //Honeycomb1608862692
        //883613
        $orderID = 883613;
        $json = json_decode($this->honeycombAPI('GET','/v2/orders/'.$orderID,null),true);
        $this->Api_common->dataDump($json);
    }

    function honeycombAPI($type,$actionURL,$postData){
        $url = 'https://api.honeycomb.com.tw'.$actionURL;
        date_default_timezone_set("UTC");
        $method = $type.' '.$actionURL.' HTTP/1.1';
        $username = 'apidemo';
        $password = 'apidemo';

        
        //$payload = 'name='.'Honeycomb'.strtotime(date('YmdHis')).'&customer_name=Mom&channel_id=3&is_pending=true&total_price=555&shipping_type=TCAT_COD&give_and_take=true&order_products[][id]=6&order_products[][quantity]=2&order_products[][product_type]=normal&order_products[][stock_required]=false&order_products[][is_bundle]=false&order_products[][id]=7&order_products[][quantity]=2&order_products[][product_type]=normal&order_products[][stock_required]=false&order_products[][is_bundle]=false';
        //echo $payload.'<br>';
        if($postData){
            $payload = urldecode(http_build_query($postData, null, '&'));
        }        
        //echo $payload;exit;

        $x_date = gmdate("D, d M Y H:i:s T",strtotime(date('Y-m-d H:i:s')));

        if($payload){
            $digest = 'SHA-256='.base64_encode(hash('sha256', $payload,true));
            $sig_str = "x-date: ".$x_date."\n".$method."\n"."digest: ".$digest;
        }else{
            $sig_str = 'x-date: '.$x_date."\n".$method;
        }
        $signature = base64_encode((hash_hmac("sha256",$sig_str, $password, true)));

        $header[] = 'X-Date: '.$x_date;
        if($payload){
            $header[] = 'Digest: '.$digest;
            $header[] = 'Authorization: hmac username="'.$username.'", algorithm="hmac-sha256", headers="x-date request-line digest", signature="'.$signature.'"';
        }else{
            $header[] = 'Authorization: hmac username="'.$username.'", algorithm="hmac-sha256", headers="x-date request-line", signature="'.$signature.'"';
        }        
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
         
        return $this->getCurl($url,$payload,$header);
    }

    function getCurl($url,$postData,$header=null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko");
        curl_setopt($ch, CURLOPT_URL, $url);       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if($postData){
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        }

        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            echo curl_errno($ch).":".str_replace("'","",curl_error($ch));
        }
        curl_close($ch);
        $Output = str_replace(array("\r","\n"),"", strip_tags($Output));

        return $Output;      
    }
    
    function stest(){
        $this->Api_common->dataDump($_SERVER);
    }*/

    function ragicLogistic($type,$id){
        $this->load->model('Api_ragic');
        if($type=='order'){
            $url = "https://ap3.ragic.com/hugePlus/forms/2/".(int)$id."?v=3&api";  
        }else if($type=='crm'){
            $url = "https://ap3.ragic.com/hugePlus/forms/5/".(int)$id."?v=3&api";  
        }
        $retData = json_decode($this->Api_ragic->ragicCurl($url,$ckfile),true);
        foreach ($retData as $key => $value) {
            $logistic['出貨方式'] = $value['出貨方式'];
            $logistic['物流單號'] = $value['物流單號'];
        }
        
        if($logistic['出貨方式']=='GoodDeal黑貓物流'){            
            $postData['Search_Store'] = 70;
            $postData['OrderNo'] = $logistic['物流單號'];
        }else if($logistic['出貨方式']=='GoodDeal新竹物流'){            
            $postData['Search_Store'] = 74;
            $postData['OrderNo'] = $logistic['物流單號'];
        }else if($logistic['出貨方式']=='日翊全家超取貨到付款'){
        }else if($logistic['出貨方式']=='宅配通'){
            $postData['txtMainID_1'] = $logistic['物流單號'];
            $postData['html'] = true;
        }else if(preg_match('/本島宅配/', $logistic['出貨方式'])){
            $postData['html'] = true;
        }
        if(!$value['物流單號']){
            echo '該訂單尚無託運單號';
            exit;
        }

        if(preg_match('/GoodDeal/', $logistic['出貨方式'])){
            $url = 'https://gdsearch.gooddeal.com.tw/search_order.php';
            echo '
                    <form id="form" action="'.$url.'" method="post">
                      <p><input type="text" name="Search_Store" value="'.$postData['Search_Store'].'" /></p>
                      <p><input type="text" name="OrderNo" value="'.$postData['OrderNo'].'" /></p>
                      <p><input type="text" name="cfmSearch" value="" /></p>
                      <input type="submit" value="Submit" />
                    </form>
                    <script type="text/javascript">
                        formSubmit();
                        function formSubmit(){document.getElementById("form").submit()}
                    </script>
                    ';
        }else if(preg_match('/全家/', $logistic['出貨方式'])){
            $url = 'https://ecfme.famiport.com.tw/fmedcfpwebv2/index.aspx/GetOrderDetail';

            echo '
            <body>
            '.$value['出貨方式'].'
            <h2 id="deliverCode">'.$logistic['物流單號'].'</h2>
            <p><a href="https://ecfme.famiport.com.tw/fmedcfpwebv2/index.aspx">前往查詢</a></p>
            </body>
            ';

        }else if($logistic['出貨方式']=='宅配通'){
            $url = 'http://query2.e-can.com.tw/self_link/id_link_c.asp?txtMainid='.$logistic['物流單號'];            
            header("Content-Type:text/html; charset=big5");
            echo $this->Api_common->getCurl($url,$postData);
        }else if(preg_match('/本島宅配/', $logistic['出貨方式'])){
            $url = 'https://www.t-cat.com.tw/Inquire/TraceDetail.aspx?BillID='.str_replace('-', '', $logistic['物流單號']).'&ReturnUrl=Trace.aspx';
            echo '<script>location.href="'.$url.'"</script>';
        }
        
        
    }

}
