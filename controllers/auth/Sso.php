<?php

class sso extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		ini_set('display_errors', '0');
        $this->load->model('Api_common');
        $this->Api_common->chkBlockIP();
        $this->Api_common->browserLog($user_detail,'');
    }

    // 主畫面
    function index(){
        if(empty($this->session->userdata('username'))){
            session_write_close();
            $this->load_MyView("auth/login");
        }else{
            echo '<script>window.location.href = "'.base_url().'manage/manage_dashboard";</script>';
        }
        
    }

    function oauth(){

        // 0) 設定 client 端的 id, secret
        $client = new Google_Client;
        $client->setClientId(OAUTH_CLIENT_ID);
        $client->setClientSecret(OAUTH_CLIENT_SECRET);
        
        // 2) 使用者認證後，可取得 access_token 
        if (isset($_GET['code'])) 
        {
            $client->setRedirectUri(base_url()."auth/sso/oauth");
            $result = $client->authenticate($_GET['code']);
         
            if (isset($result['error'])) 
            {
                die($result['error_description']);
            }
            $this->session->set_userdata($result);
            header("Location:".base_url()."auth/sso/oauth?action=profile");
        }
         
        // 3) 使用 id_token 取得使用者資料。另有 setAccessToken()、getAccessToken() 可以設定與取得 token

        elseif ($_GET['action'] == "profile")
        {
             
            $user_detail = $this->session->all_userdata();
            //$this->Api_common->dataDump($_SESSION);exit;
            $profile = $client->verifyIdToken($_SESSION['id_token']);

            if(strpos($profile['email'], OAUTH_ALLOW_ACCOUNT)>0){
                $this->setUserSession($profile);
                echo '<script>window.location.href = "'.base_url().'auth/sso?url='.str_replace(SUB_SITE_PATH.'/', '', $_SERVER['REQUEST_URI']).'";</script>';
            }else{
                echo '<script>alert("請使用管理信箱登入!");window.history.go(-1); </script>';
            }
            
            
            exit();
        }
         
        // 1) 前往 Google 登入網址，請求用戶授權
        else 
        {
            $client->revokeToken();
            session_destroy();
         
            // 添加授權範圍，參考 https://developers.google.com/identity/protocols/googlescopes
            $client->addScope(['https://www.googleapis.com/auth/userinfo.profile']);
            $client->addScope(['https://www.googleapis.com/auth/userinfo.email']);
            
            $client->setRedirectUri(base_url()."auth/sso/oauth");
            $url = $client->createAuthUrl();
            header("Location:{$url}");
        }
    }

    function setUserSession($profile){
        $this->load->model('Api_common');
        $data = $this->Api_common->getDataCustom('suCName,suEName,suEmail,suActor','sys_user','suEmail = "'.$profile['email'].'" AND suIsDisabled = "N"',null,null);
        if($data){
            $stu = explode('@', $profile['email']);
            $newdata = array(
                'username'  => $data[0]['suCName'],
                'account'  => $data[0]['suEName'],
                'empID'  => $data[0]['suEName'],
                'email'  => $data[0]['suEmail'],
                'actor'  => $data[0]['suActor'],
                'logged_in' => TRUE
            );
            $this->session->set_userdata($newdata);
            session_write_close();
        }else{
            echo '<script>alert("請使用管理信箱登入!");window.history.go(-1); </script>';
        }
    }

}
