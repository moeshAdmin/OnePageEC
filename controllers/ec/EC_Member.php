<?php

class EC_Member extends My_Controller {
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
            $userData = $this->Api_common->getDataCustom('emMemberNo,emMemberName,emEmail,emPhone,emIsSSO,emFBID,emGoogleID,emLineID','ec_member','emEmail = "'.$user_detail['m_email'].'" AND emStatus = "正常"',null,null);
            $data['title'] = '定期配會員專區';
        }else if($_GET['type']=='login'){
            $data['title'] = '定期配會員登入';
        }else if($_GET['type']=='regiest'){
            $data['title'] = '啟動定期配';
        }else if($_GET['type']=='reset'){
            $data['title'] = '忘記密碼';
        }else if($_GET['type']=='active'){
            $data['title'] = '重寄啟用信件';
        }else{
            redirect(base_url().'ec/EC_Member?type=login');exit;
        }

        if($userData[0]['emIsSSO']=='Y'){
            $data['isSSO'] = 'Y';
            $ary = ['emFBID'=>'Facebook','emGoogleID'=>'Google','emLineID'=>'Line'];
            foreach ($ary as $key => $value) {
                if($userData[0][$key]){
                    if(!$data['ssoType']){
                        $data['ssoType'] = $value;
                    }else{
                        $data['ssoType'] .= ','.$value;
                    }
                }
            }
        }else{
            $data['isSSO'] = 'N';
        }

        $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
        $this->load_ECView("ec/ec_member",$data); // 陣列資料 data 與 View Rendering
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);    
    }

    function socialLogin($type){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $token = $postData['accessToken'];
        $id = $postData['userID'];
        //get profile        
        if($type=='fb'){
            $result = json_decode($this->Api_common->basicCurl("https://graph.facebook.com/v9.0/".$id."?fields=id%2Cname%2Cemail&access_token=".$token, $ckfile, null),true);
            $userData['email'] = $result['email'];
            $userData['id'] = $result['id'];
            $userData['name'] = $result['name'];
        }else if($type=='google'){
            $payload = json_decode($this->Api_common->basicCurl("https://oauth2.googleapis.com/tokeninfo?id_token=".$postData['id_token'], $ckfile, null),true);            
            if($payload['email_verified']=='true') {
                $userData['email'] = $payload['email'];
                $userData['id'] = $payload['sub'];
                $userData['name'] = $payload['name'];
            } else {
              echo $this->Api_common->setFrontReturnMsg('401','',null);exit;
            }
        }else if($type=='line'){
            $chk = $this->Api_common->stringHash('decrypt',$_GET['state']);
            if($chk){
                $header[0] = 'application/x-www-form-urlencoded';
                $postData['grant_type'] = 'authorization_code';
                $postData['code'] = $_GET['code'];
                $postData['client_id'] = OAUTH_LINE_CLIENT_ID;
                $postData['client_secret'] = OAUTH_LINE_CLIENT_SECRET;
                $postData['redirect_uri'] = base_url().'ec/EC_Member/socialLogin/line';
                $result = json_decode($this->Api_common->getCurl("https://api.line.me/oauth2/v2.1/token", $postData,$header),true);

                if($result){
                    $postData2['id_token'] = $result['id_token'];
                    $postData2['client_id'] = OAUTH_LINE_CLIENT_ID;
                    $result2 = json_decode($this->Api_common->getCurl("https://api.line.me/oauth2/v2.1/verify", $postData2,$header),true);
                }
                
                if($result2){
                    $userData['email'] = $result2['email'];
                    $userData['id'] = $result2['sub'];
                    $userData['name'] = $result2['name'];
                }
                
            }
            
        }
        
        if(!$userData['email']){
            echo $this->Api_common->setFrontReturnMsg('401','此帳號未綁定信箱，請改用其他帳號',null);exit;
        }
        if($userData){
            $this->setMemberSession($type,$userData);
            if($type=='line'){
                $itemID = explode('_', $chk);
                if($itemID[1]){
                    redirect(base_url().'ec/EC_Order?itemID='.$itemID[1]);exit;
                }else{
                    redirect(base_url().'ec/EC_Member');exit;
                }
            }else{
                echo $this->Api_common->setFrontReturnMsg('200','',null);exit;
            }
            
        }
        
        
    }

    function setMemberSession($type,$result){
        $this->load->model('Api_common');
        $data = $this->Api_common->getDataCustom('emSysID,emMemberNo,emMemberName,emEmail','ec_member','emEmail = "'.$result['email'].'" AND emStatus = "正常"',null,null);
        
        if(!$data){
            //新會員
            $insertMember['emMemberNo'] = TICKET_ID.'C'.str_pad($this->Api_ec->chkNextNum('ec_member','emMemberNo','-5'),5,'0',STR_PAD_LEFT);
            $insertMember['emMemberName'] = $result['name'];
            $insertMember['emEmail'] = $result['email'];
            $insertMember['emIsSSO'] = 'Y';
            $insertMember['emCreateDTime'] = date('Y-m-d H:i:s');
            if($type=='fb'){
                $insertMember['emFBID'] = $result['id'];
            }else if($type=='line'){
                $insertMember['emLineID'] = $result['id'];
            }else if($type=='google'){
                $insertMember['emGoogleID'] = $result['id'];
            }
            $this->db->insert('ec_member', $insertMember);
            $name = $insertMember['emMemberName'];
            $membID = $insertMember['emMemberNo'];
        
        }else{
            //既有會員
            if($type=='fb'){
                $updateData['emFBID'] = $result['id'];
            }else if($type=='line'){
                $updateData['emLineID'] = $result['id'];
            }else if($type=='google'){
                $updateData['emGoogleID'] = $result['id'];
            }
            $updateData['emIsSSO'] = 'Y';
            if($updateData){
                $this->db->where('emSysID', $data[0]['emSysID']);
                $this->db->update('ec_member', $updateData);
            }
            $name = $data[0]['emMemberName'];
            $membID = $data[0]['emMemberNo'];
        }

        $newdata = array(
            'm_name'  => $name,
            'm_email'  => $result['email'],
            'm_memberID'  => $membID
        );

        $this->session->set_userdata($newdata);
        session_write_close();
    }

    function logout(){
        $this->session->sess_destroy();
        redirect(base_url());
    }

    //註冊會員
    function regiest(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);
        $msg = [];
        //檢查
        if(!checkdnsrr(array_pop(explode("@",$postData['email'])),"MX")){
            array_push($msg, '電子信箱格式不正確');
        }
        if(strlen($postData['password'])<8){
            array_push($msg, '密碼應至少8個字元');
        }
        if($postData['password']!=$postData['password2']){
            array_push($msg, '兩次輸入密碼不一致');
        }
        if(count($msg)>0){
            echo $this->Api_common->setFrontReturnMsg('901','資訊錯誤:'.str_replace(';', '、', $this->Api_common->setArrayToList($msg)),null);
            exit;
        }

        //寫入資料
        $membData = $this->Api_common->getDataIsExist('emMemberNo','ec_member','emEmail = "'.$postData['email'].'"');
        if($membData['mode']=='insert'){
            $insertMember['emMemberNo'] = TICKET_ID.'C'.str_pad($this->Api_ec->chkNextNum('ec_member','emMemberNo','-5',null,'emSysID'),5,'0',STR_PAD_LEFT);
            $insertMember['emMemberName'] = $postData['name'];
            $insertMember['emEmail'] = $postData['email'];
            $insertMember['emPhone'] = $postData['phone'];
            $insertMember['emStatus'] = '未啟用';
            $insertMember['emPassword'] = $this->Api_common->stringHash('encrypt',base64_encode('$5sAcf'.$postData['password'].'&4B?1Cse'));
            $insertMember['emCreateDTime'] = date('Y-m-d H:i:s');
            $this->db->insert('ec_member', $insertMember);
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','Email已曾註冊，請直接登入',null);
            exit;
        }

        $this->sendMail('active',$postData);
        
        echo $this->Api_common->setFrontReturnMsg('200','已發送啟用信件，請至信箱查看!',null);
        exit; 

    }

    //郵件認證
    function mailAction($type,$hash){
        $refData = explode('$$$', base64_decode($this->Api_common->stringHash('decrypt',$hash)));

        if($type=='active'){
            $data = $this->Api_common->getDataCustom('emSysID,emMemberNo,emMemberName,emEmail','ec_member','emEmail = "'.$refData[0].'" AND emStatus = "未啟用"',null,null);
            if(!$data){
                echo '<script>alert("帳號已啟用");location.href="'.base_url().'ec/EC_Member?type=login";</script>';
                exit;
            }
            if((strtotime($refData[1])+30*60)<strtotime(date('Y-m-d H:i:s'))){
                echo '<script>alert("帳號啟用已過期，已發送新信件，請再次啟用");</script>';
                $this->sendMail('active',['email'=>$refData[0],'itemID'=>$refData[2]]);
                exit;
            }
            $updateData['emStatus'] = '正常';
            $this->db->where('emEmail', $refData[0]);
            $this->db->update('ec_member', $updateData);
        }else if($type=='reset'){
            $data = $this->Api_common->getDataCustom('emSysID,emMemberNo,emMemberName,emEmail,emIsSSO','ec_member','emEmail = "'.$refData[0].'" AND emStatus = "正常"',null,null);
            if((strtotime($refData[1])+5*60)<strtotime(date('Y-m-d H:i:s'))){
                echo '<script>alert("已失效");</script>';exit;
            }
            
        }

        if(!$data){exit;}

        $newdata = array(
            'm_name'  => $data[0]['emMemberName'],
            'm_email'  => $data[0]['emEmail'],
            'm_memberID'  => $data[0]['emMemberNo']
        );
        $this->session->set_userdata($newdata);
        session_write_close();

        if($type=='active'){
            if($refData[2]){
                echo '<script>alert("帳號啟用完成，請繼續購買");location.href="'.base_url().'ec/EC_Order?itemID='.$refData[2].'";</script>';
            }else{
                echo '<script>alert("帳號啟用完成");location.href="'.base_url().'ec/EC_Member";</script>';
            }
            exit;
        }else if($type=='reset'){
            echo '<script>alert("請重設您的密碼");location.href="'.base_url().'ec/EC_Member?reset=Y";</script>';
            exit;
        }
    }

    //發送信件
    function sendMail($type,$postData){

        if($type=='active'){
            //發送啟用信
            $url = base_url().'ec/EC_Member/mailAction/active/'.$this->Api_common->stringHash('encrypt',base64_encode($postData['email'].'$$$'.date('Y-m-d H:i:s').'$$$'.$postData['itemID']));
            $h2 = '您於 '.BRAND_NAME.' 註冊帳號啟用信件';
            $mailContent = '您的啟用連結：<a href="'.$url.'">'.$url.'</a><br>';
            $subject = '感謝您註冊 '.BRAND_NAME.' 會員!';
        }else if($type=='reset'){
            //發送啟用信
            $url = base_url().'ec/EC_Member/mailAction/reset/'.$this->Api_common->stringHash('encrypt',base64_encode($postData['email'].'$$$'.date('Y-m-d H:i:s').'$$$'.$postData['itemID']));
            $h2 = '您於 '.BRAND_NAME.' 網站申請重設密碼';
            $mailContent = '您的重設密碼連結：<a href="'.$url.'">'.$url.'</a><br>若您未申請，請忽略此信件，並立即更換您的密碼';
            $subject = '您於 '.BRAND_NAME.' 網站申請重設密碼';
        }
        
        $logoURL = EMAIL_IMAGE;
        $brandName = BRAND_NAME;
        $brandColor = '#008ea6';
        $mailContent = '<html><body><div style="width: 100%;margin: 20px auto;max-width: 800px;border: 1px solid #4e96a2;padding: 50px;border-radius: 5px;"><div><img height="40px" src="'.$logoURL.'"><hr><h2 style="color: '.$brandColor.';padding: 20px 0px;">'.$h2.'</h2>'.$mailContent.'</div><hr><center>'.FOOT_META.'</center></div></body></html>';

        //寄信        
        $this->load->library('My_SendMail');
            //寄給表單填寫人
            $data = array(
                'recipient'=>array($postData['email']),
                'cc'=>'', 
                'subject' => $subject, 
                'content' => $mailContent,
                'sender'=>MAIL_CONFIG['senderName']); 
            $result = $this->my_sendmail->sendOut($data);
        if(preg_match('/Success/', $result)){            
            //插入發送紀錄
            $insertData['emReceiver'] = $postData['email'];
            $insertData['emSubject'] = $subject;
            $insertData['emContent'] = $mailContent;
            $insertData['emStatus'] = '已發送';
            $insertData['emSendType'] = '註冊啟用';
            $insertData['emSendTime'] = date('Y-m-d H:i:s');
            $this->db->insert('ec_mail', $insertData);
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','發生異常 請稍後再試',null);
            exit;
        }
    }

    //登入
    function login(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);

        if(!$postData['password']||!$postData['email']){
            echo $this->Api_common->setFrontReturnMsg('901','請輸入完整資訊',null);
            exit;
        }
        if(!checkdnsrr(array_pop(explode("@",$postData['email'])),"MX")){
            echo $this->Api_common->setFrontReturnMsg('901','電子信箱格式不正確',null);
            exit;
        }
        $pass = $this->Api_common->stringHash('encrypt',base64_encode('$5sAcf'.$postData['password'].'&4B?1Cse'));
        $data = $this->Api_common->getDataCustom('emSysID,emMemberNo,emMemberName,emEmail,emStatus,emIsSSO','ec_member','emEmail = "'.$postData['email'].'" AND emPassword = "'.$pass.'"',null,null);
        if(!$data){
            echo $this->Api_common->setFrontReturnMsg('901','帳號密碼錯誤',null);
            exit;
        }

        if($data[0]['emStatus']=='未啟用'){
            echo $this->Api_common->setFrontReturnMsg('901','帳號尚未啟用，請再次啟用',null);
            exit;
        }else if($data[0]['emStatus']!='正常'){
            echo $this->Api_common->setFrontReturnMsg('901','帳號已被停用',null);
            exit;
        }

        if($data[0]['emIsSSO']=='Y'){
            echo $this->Api_common->setFrontReturnMsg('901','此帳號已完成社群綁定，請改用社群帳號登入',null);
            exit;
        }

        $newdata = array(
            'm_name'  => $data[0]['emMemberName'],
            'm_email'  => $data[0]['emEmail'],
            'm_memberID'  => $data[0]['emMemberNo']
        );
        $this->session->set_userdata($newdata);
        session_write_close();
        echo $this->Api_common->setFrontReturnMsg('200','登入成功',null);
        exit;
    }

    //申請重設密碼
    function reset(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);
        if(!checkdnsrr(array_pop(explode("@",$postData['email'])),"MX")){
            echo $this->Api_common->setFrontReturnMsg('901','電子信箱格式不正確',null);
            exit;
        }

        $data = $this->Api_common->getDataCustom('emSysID,emMemberNo,emMemberName,emEmail,emStatus,emIsSSO','ec_member','emEmail = "'.$postData['email'].'"',null,null);
        if(!$data){
            echo $this->Api_common->setFrontReturnMsg('901','重設密碼信件已發送至信箱' ,null);
            exit;
        }

        if($data[0]['emStatus']=='未啟用'){
            echo $this->Api_common->setFrontReturnMsg('901','帳號尚未啟用，請再次啟用',null);
            exit;
        }else if($data[0]['emStatus']!='正常'){
            echo $this->Api_common->setFrontReturnMsg('901','帳號已被停用',null);
            exit;
        }
        if($data[0]['emIsSSO']=='Y'){
            echo $this->Api_common->setFrontReturnMsg('901','此帳號已完成社群綁定，請改用社群帳號登入',null);
            exit;
        }

        $this->sendMail('reset',$postData);
        echo $this->Api_common->setFrontReturnMsg('200','重設密碼信件已發送至信箱' ,null);
        exit;
    }

    //會員資料修改
    function memberEdit(){
        $user_detail=$this->session->all_userdata();
        if(!$user_detail['m_email']){exit;}
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $this->Api_ec->reCaptchaChk($postData);
        $msg = [];
        //檢查
        if($postData['reset']=='Y'){
            if(!$postData['password']||!$postData['password2']){
                array_push($msg, '請輸入新設密碼');
            }
            if(strlen($postData['password'])<8){
                array_push($msg, '密碼應至少8個字元');
            }
            if($postData['password']!=$postData['password2']){
                array_push($msg, '兩次輸入密碼不一致');
            }
        }

        
        if(count($msg)>0){
            echo $this->Api_common->setFrontReturnMsg('901','資訊錯誤:'.str_replace(';', '、', $this->Api_common->setArrayToList($msg)),null);
            exit;
        }

        //寫入資料
        $updateData['emMemberName'] = $postData['name'];
        $updateData['emPassword'] = $this->Api_common->stringHash('encrypt',base64_encode('$5sAcf'.$postData['password'].'&4B?1Cse'));

        $this->db->where('emEmail', $user_detail['m_email']);
        $this->db->update('ec_member', $updateData);

        $data = $this->Api_common->getDataCustom('emMemberNo,emMemberName,emEmail','ec_member','emEmail = "'.$user_detail['m_email'].'" AND emStatus = "正常"',null,null);
        if($data){
            $newdata = array(
                'm_name'  => $data[0]['emMemberName'],
                'm_email'  => $data[0]['emEmail'],
                'm_memberID'  => $data[0]['emMemberNo']
            );
            $this->session->set_userdata($newdata);
            session_write_close();

            echo $this->Api_common->setFrontReturnMsg('200','資料更新完成',null);
            exit; 
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','',null);
            exit; 
        }
        
    }


}
