<?php

class EC_Query extends My_Controller {
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
        $this->Api_common->redirectHttps();
        $user_detail=$this->session->all_userdata();
        
        if($user_detail['m_email']){
            redirect(base_url().'ec/EC_Member_Query');
        }else{
            $data['title'] = '訂單查詢';
            $allItemData = $this->Api_ec->getAllItemData();
            $data['allItemData'] = $allItemData;
            $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
            $this->load_ECView("ec/ec_query",$data); // 陣列資料 data 與 View Rendering
            $nowPage = explode('/', $_SERVER['REQUEST_URI']);
            $this->Api_common->browserLog($user_detail,$nowPage);    
        }
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);
        if(!$postData['email']){
            echo $this->Api_common->setFrontReturnMsg('901','請輸入信箱',null);
            exit; 
        }
        if(!checkdnsrr(array_pop(explode("@",$postData['email'])),"MX")){
            echo $this->Api_common->setFrontReturnMsg('901','電子信箱格式不正確!',null);
            exit; 
        }
        $chkData = $this->Api_common->getDataCustom('emReceiver','ec_mail','emReceiver = "'.$postData['email'].'" AND emSendType = "查詢" AND emCreateDTime BETWEEN "'.date('Y-m-d 00:00:00').'" AND "'.date('Y-m-d 23:59:00').'"');
        if(count($chkData)>3){
            echo $this->Api_common->setFrontReturnMsg('901','您的查詢要求已超過單日上限，如有任何問題，請直接與客服聯繫',null);
            exit; 
        }
        $memberData = $this->Api_common->getDataCustom('emEmail','ec_member','emEmail = "'.$postData['email'].'"');

        if(!$memberData[0]['emEmail']){
            echo $this->Api_common->setFrontReturnMsg('200','查詢連結已發送至您的信箱!',null);
            exit; 
        }

        $url = base_url().'ec/EC_Cart/status?uat='.$this->Api_common->stringHash('encrypt',base64_encode($memberData[0]['emEmail'].'$$$'.date('Y-m-d H:i:s')));

        $logoURL = EMAIL_IMAGE;
        $brandName = BRAND_NAME;
        $brandColor = '#008ea6';
        $h2 = '您的訂購查詢連結';
        $mailContent = '您的訂購查詢連結：<a href="'.$url.'">'.$url.'</a><br>';
        $subject = '您訂購的產品訂單查詢連結';

        $mailContent = '<html><body><div style="width: 100%;margin: 20px auto;max-width: 800px;border: 1px solid #4e96a2;padding: 50px;border-radius: 5px;"><div><img height="40px" src="'.$logoURL.'"><hr><h2 style="color: '.$brandColor.';padding: 20px 0px;">'.$h2.'</h2>'.$mailContent.'</div><hr><center>'.FOOT_META.'</center></div></body></html>';

        //寄信        
        $this->load->library('My_SendMail');
            //寄給表單填寫人
            $data = array(
                'recipient'=>array($memberData[0]['emEmail']),
                'cc'=>'', 
                'subject' => $subject, 
                'content' => $mailContent,
                'sender'=>MAIL_CONFIG['senderName']); 
            $result = $this->my_sendmail->sendOut($data);
        if(preg_match('/Success/', $result)){            
            //插入發送紀錄
            $insertData['emReceiver'] = $memberData[0]['emEmail'];
            $insertData['emSubject'] = $subject;
            $insertData['emContent'] = $mailContent;
            $insertData['emStatus'] = '已發送';
            $insertData['emSendType'] = '查詢';
            $insertData['emSendTime'] = date('Y-m-d H:i:s');
            $this->db->insert('ec_mail', $insertData);
            echo $this->Api_common->setFrontReturnMsg('200','查詢連結已發送至您的信箱!',null);
            exit; 
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','發生異常 請稍後再試',null);
            exit;
        }
        
    }

    function cvs(){
        $get = $this->input->get();
        $get = $this->Api_common->cleanPostData($get);
        if($get['utm_source']){
            $utm = '&utm_source='.$get['utm_source'].'&utm_medium='.$get['utm_medium'].'&utm_campaign='.$get['utm_campaign'].'&utm_content='.$get['utm_content'];
        }
        // 電子地圖
        require(APPPATH.'libraries/My_EcLogistic.php');
        try {
            if($get['logis']=='cvs'){
                $isPay = EcpayIsCollection::NO;
            }else{
                $isPay = EcpayIsCollection::YES;
            }
            $AL = new EcpayLogistics();
            $AL->Send = array(
                'MerchantID' => ECPay_MerchantID,
                'MerchantTradeNo' => 'no' . date('YmdHis'),
                'LogisticsSubType' => EcpayLogisticsSubType::FAMILY,
                'IsCollection' => $isPay,
                'ServerReplyURL' => base_url().'ec/EC_Order?itemID='.$get['itemID'].'&type='.$get['type'].'&qty='.$get['qty'].'&logis='.$get['logis'].'&utm_source='.$get['utm_source'].'&utm_medium='.$get['utm_medium'].'&utm_campaign='.$get['utm_campaign'].'&utm_content='.$get['utm_content'],
                'ExtraData' => null,
                'Device' => EcpayDevice::PC
            );
            // CvsMap(Button名稱, Form target)
            $html = $AL->CvsMap('電子地圖');
            echo '地圖載入中..';
            echo '<div style="display:none;">'.$html.'</div>';
            echo '<script type="text/javascript">setTimeout(function(){document.getElementById("ECPayForm").submit();}, 1);</script>';
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }


}
