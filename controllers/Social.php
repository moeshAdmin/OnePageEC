<?php

class Social extends Ci_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_ragic');
        $this->Api_common->chkBlockIP();
        $this->Api_common->browserLog($user_detail,$nowPage);
    }

    // 主畫面
    function index(){
        exit;  
    }

    function oauth($ordHash=null){
        if(!$ordHash){exit;}
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $token = "EAAPBi4UdeaoBAGPnhIYHDogZC10fOa7UJrRzRnz0zWp4SZCCa0NVYZBultAs8rQzMHNtxctTSDFmh0ZC3QS3CCrJ5dKUkOZBj9MJ7Vq0DhreTxjkWIOc8YzZAZBISxRDF8ZCr01OO1Pdt4DXZBL8ZBL3XoovWpvql4i9ySr5RUZBckZA0DR9WyxPxL54gZAtOxJ1KYRk35A1cOycDcARIwJbj6o77tJzFdWe5N0P8iKhvMladzUH7kiRSMShg1kb5c47NzikZD";
        $id = (int)$postData['userID'];
        $id = 4908726239145027;
        //get profile
        $result = $this->Api_common->basicCurl("https://graph.facebook.com/v9.0/".$id."?fields=id%2Cname%2Cemail&access_token=".$token, $ckfile, null);
        //$this->Api_common->dataDump($result);

        $postData['messaging_type'] = 'RESPONSE';
        $postData['recipient']['id'] = 4908726239145027;
        $postData['message']['text'] = 'hello, world!';
        $message = $this->Api_common->basicCurl("https://graph.facebook.com/v9.0/".$id."/messages?access_token=".$token, $ckfile, $postData);

        //$orderNo = $this->Api_common->stringHash('decrypt',$ordHash);
        //$orderData = $this->Api_ec->getOrderData($orderNo);
        $this->Api_common->dataDump($message);
        echo $this->Api_common->setFrontReturnMsg('200','',$message);exit;


    }

    function webhooks(){
        $hubVerifyToken = VERIFY_TOKEN;
        $accessToken = ACCESS_TOKEN;
        
        // check token at setup
        if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe') {
            if($_REQUEST['hub_verify_token'] == $hubVerifyToken) {
                echo $_REQUEST['hub_challenge'];
                exit;
            }
        }

        $input = json_decode(file_get_contents('php://input'), true);

        foreach ($_POST as $key => $value) {
            $str .= $key.':'.$value."\n";
        }
        foreach ($_SERVER as $key => $value) {
            $str .= $key.':'.$value."\n";
        }
        foreach ($input as $key => $value) {
            $str .= $key.':'.$value."\n";
        }
        $str .= $this->Api_common->dataDump($input,'return');

        $inputMessage = $input['entry'][0]['messaging'][0];
        $senderId = $inputMessage['sender']['id'];
        $messageText = $inputMessage['message']['text'];
        $answer = $messageText;
        $receiveData = $inputMessage['optin']['ref'];

        if(!$receiveData&&!$messageText){exit;}
        if($receiveData){
            $orderID = $this->Api_common->stringHash('decrypt',$receiveData);
            $str .= $this->Api_common->dataDump('receive:'.$senderId.' orderid:'.$orderID.' orgin:'.$receiveData,'return');
            $this->sendText($senderId,'您的訂單編號是: '.$orderID.' ，感謝您的選購! ');
            sleep(1);
            $this->sendOrderTicket($senderId,$receiveData,$accessToken);

        }else{
            
            $response = [
                'recipient' => [ 'id' => $senderId ],
                'message'   => [ 'text' => '我們已收到您的訊息，服務人員會盡快與您聯繫' ]
            ]; 
            $ch = curl_init('https://graph.facebook.com/v9.0/me/messages?access_token='.$accessToken);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $result = curl_exec($ch);
            curl_close($ch);
        }        

        $str .= $this->Api_common->dataDump('all done','return');
        $this->Api_common->saveData(DIR_SITE_FILE.'temp/hook.txt','w+',$str);
    }

    private function sendText($senderId,$text){
        $accessToken = ACCESS_TOKEN;
        $response = [
            'recipient' => [ 'id' => $senderId ],
            'message'   => [ 'text' => $text ]
        ]; 
        $ch = curl_init('https://graph.facebook.com/v9.0/me/messages?access_token='.$accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
    }

    private function sendOrderTicket($senderId=null,$receiveData=null,$accessToken=null,$msg=null){

        $response = [
            'recipient' => [ 'id' => $senderId ]
        ];
        $orderID = $this->Api_common->stringHash('decrypt',$receiveData);
        $orderData = $this->Api_ec->getOrderData($orderID);
        $itemData = $this->Api_ec->getItemData((int)$orderData[0]['eoItemNo']);

        $itemName = $itemData[0]['eiName'];
        $itemImg = explode(';', $itemData[0]['eiImg']);
        
        //$response['message']['attachment']['payload']['elements']['type'] = 'web_url';
        $elements[0]['title'] = '購買商品 - '.$itemName;
        $elements[0]['subtitle'] = '您的訂單編號是: '.$orderID.' ，您可以隨時透過下方連結查閱訂單狀態，感謝您的選購! ';
        $elements[0]['image_url'] = $itemImg[0];
        
        $elements[0]['buttons'][0]['type'] = 'web_url';
        $elements[0]['buttons'][0]['url'] = base_url().'ec/EC_Cart/status?orderNo='.$receiveData;
        $elements[0]['buttons'][0]['title'] = '檢視訂單';

        $elements[0]['buttons'][1]['type'] = 'web_url';
        $elements[0]['buttons'][1]['url'] = base_url().'ec/EC_Order?itemID='.$orderData[0]['eoItemNo'];
        $elements[0]['buttons'][1]['title'] = '再次訂購';

        $response['message']['attachment']['type'] = 'template';
        $response['message']['attachment']['payload']['elements'] = $elements;
        $response['message']['attachment']['payload']['template_type'] = 'generic';

        $ch = curl_init('https://graph.facebook.com/v9.0/me/messages?access_token='.$accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
    /*
    function sendOrderTicket2($senderId=null,$receiveData=null){
        $accessToken = ACCESS_TOKEN;

        $response = [
            'recipient' => [ 'id' => $senderId ]
        ];
        $orderID = $this->Api_common->stringHash('decrypt',$receiveData);
        echo $orderID.'<br>';
        echo $receiveData.'<br>';
        $orderData = $this->Api_ec->getOrderData($orderID);
        $itemData = $this->Api_ec->getItemData((int)$orderData[0]['eoItemNo']);

        $itemName = $itemData[0]['eiName'];
        $itemImg = explode(';', $itemData[0]['eiImg']);
        
        //$response['message']['attachment']['payload']['elements']['type'] = 'web_url';
        $elements[0]['title'] = '購買商品 - '.$itemName;
        $elements[0]['subtitle'] = '您的訂單編號是: '.$orderID.' ，您可以隨時透過下方連結查閱訂單狀態，感謝您的選購! ';
        $elements[0]['image_url'] = $itemImg[0];
        
        $elements[0]['buttons'][0]['type'] = 'web_url';
        $elements[0]['buttons'][0]['url'] = base_url().'ec/EC_Cart/status?orderNo='.$receiveData;
        $elements[0]['buttons'][0]['title'] = '檢視訂單';

        $elements[0]['buttons'][1]['type'] = 'web_url';
        $elements[0]['buttons'][1]['url'] = base_url().'ec/EC_Order?itemID=7';
        $elements[0]['buttons'][1]['title'] = '再次訂購';

        $response['message']['attachment']['type'] = 'template';
        $response['message']['attachment']['payload']['elements'] = $elements;
        $response['message']['attachment']['payload']['template_type'] = 'generic';

        $ch = curl_init('https://graph.facebook.com/v9.0/me/messages?access_token='.$accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }*/

    function retLineKey(){
        $getData = $_GET;
        $result = $this->lineNotifiy($getData);
        if($result['status']=='200'){
            echo '<script>alert("Line授權通知聯動成功!");window.close();</script>';
        }else{
            echo '<script>alert("發生錯誤!Code:'.$result['status'].'");window.close();</script>';
        }
    }  

    function lineNotifiy($getData){
        
        $postData['grant_type'] = 'authorization_code';
        $postData['code'] = $getData['code'];
        $postData['redirect_uri'] = 'https://crm.jp-labo.com:8888/Social/retLineKey';
        $postData['client_id'] = 'isp5wDnxSoTxWVtjEmuGYj';
        $postData['client_secret'] = 'uumTpGoqPWn6BR6lkkmTzqtUjkwKh4R63qdzCXwwlj5';

        $json = json_decode($this->Api_common->getCurl('https://notify-bot.line.me/oauth/token',$postData),true);
        
        if($json['access_token']&&$json['status']=='200'){//design_1
            $insertData['1000258'] = $json['access_token'];//token
            $insertData['1000259'] = 'Y';//是否綁定
            $returnData = $this->Api_ragic->syncToRagic('forms4/2/'.$getData['state'],$insertData);
            $result = $this->sendLineNotifiy($json['access_token'],'已完成 '.$returnData['data']['1000256'].' 通知綁定，系統將透過此群組通知訊息');
        }

        return $json;
    }

    function sendLineNotifiy($token=NULL,$msg=NULL){
        $header[] = 'Authorization: Bearer '.$token;
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        if(!$msg){
          $postData['message'] = '現在可以透過LINE來接收通知了! 通知時間：'.date('Y-m-d H:i:s');
        }else{
          $postData['message'] = $msg;
        }
        $result = json_decode($this->Api_common->getCurl('https://notify-api.line.me/api/notify',$postData,$header),true);
        return $result;
    }

    function sendLineNotifiySMS($token=NULL){
        $header[] = 'Authorization: Bearer '.$token;
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        $postData = json_decode(file_get_contents('php://input'), true);
        //$postData = $this->Api_common->cleanPostData($postData);
        $json = json_decode($postData['sms'],true);
        foreach ($json['data'] as $key => $value) {
            $postData['message'] = '['.$value['from'].']'."\r\n".$value['content'];
            $result = json_decode($this->Api_common->getCurl('https://notify-api.line.me/api/notify',$postData,$header),true);
            sleep(1);
        } 
        echo $this->Api_common->setFrontReturnMsg('200','done',$json);
        exit;
    }

    function lineTest(){
        $id = $_GET['id'];
        $form = $_GET['form'];
        $lineData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms4/2?where=1000257,eq,'.urlencode($form).'&where=1000281,eq,Y', $ckfile),true);
        foreach ($lineData as $key => $value) {
            $token = $value['token'];
            $msgData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/'.$form.'/'.$id.'?v=3&api', $ckfile),true);
            if($msgData[$id]['通知文字']){
                $msg = '['.$msgData[$id]['通知標題'].']'."\r\n".$msgData[$id]['通知文字'];
            }else{
                $msg = '表單 ['.$msgData[$id]['工作內容＆備註'].'] 已有更新，請前往查看'."\r\n".'https://ap3.ragic.com/hugePlus/'.$form.'/'.$id;
            }
            $msg .= "\r\n"."https://ap3.ragic.com/hugePlus/".$form."/".$_GET['id'].".xhtml";
            if($msgData[$id]['是否通知']!='Y'){
                $result = $this->sendLineNotifiy($token,$msg);
            }
            
        }
        echo $this->Api_common->setFrontReturnMsg('200','done',$result);
        exit;
      //return $result;
    }
}
