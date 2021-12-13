<?php
class Users extends My_Controller{
   
    function __construct() {
        parent::__construct( strtolower(__CLASS__) );
        $this->load->model('Api_common');
        $this->load->helper('url');
        $this->load->library('session');
        $this->Api_common->chkBlockIP();
        $this->Api_common->browserLog($user_detail,$nowPage);
    }
    function index(){
        if(empty($this->session->userdata('username'))){
            $this->load_CiView("auth/view_login");
        }else{
            $this->redirect_user(base_url().'manage/manage_dashboard');
        }
        
    }

    function login(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        if(!$postData['email']||!$postData['password']){
            echo $this->Api_common->setFrontReturnMsg('401','請輸入完整資訊','');
            exit;
        }

        $skey = $this->Api_common->getSysConfig('passHash');
        $postData['password'] = $this->Api_common->serverHash('encrypt',$skey['scValue1'].$postData['password'].$skey['scValue2']); 

        $userData = $this->Api_common->getDataCustom('suEmail,suActor,suIsDisabled','sys_user','suEmail="'.$postData['email'].'" AND suPassword = "'.$postData['password'].'"');

        if($userData[0]){
            if($userData[0]['suIsDisabled']=="N"){
                $this->setUserSession($userData[0]);
                echo $this->Api_common->setFrontReturnMsg('200','OK','');
                exit;
            }else{
                echo $this->Api_common->setFrontReturnMsg('401','帳戶已被停用，若有疑問請聯繫IT','');
                exit;
            }
            
        }else{
            echo $this->Api_common->setFrontReturnMsg('401','帳號密碼不正確',null);
            exit;
        }
        exit;
        
    }

    function setUserSession($userData){
        //$acl = $this->Api_common->getACL($userData);
        $newdata = array(
            'username'  => $userData['suCName'],
            'account'  => $userData['suEmail'],
            'email'  => $userData['suEmail'],
            'empID' => $userData['suEmpID'],
            'phone_ext'  => $userData['suPhoneExt'],
            'deptID' => $userData['suDeptID'],
            'actor' => $userData['suActor'],
            'acl' => $acl['acl'],
            'menu' => $acl['menu'],
            'logged_in' => TRUE
        );
        $this->session->set_userdata($newdata);
        session_write_close();
    }
   
    function redirect_user($refUrl){
        if(!$refUrl){
            $refUrl = $this->input->get('url', TRUE);
        }        
        redirect($refUrl);
       
        //$session_id = $this->session->all_userdata();
        //var_dump($session_id);
    }

    function logout(){
        $this->session->sess_destroy();
        redirect(base_url().'auth/users');
    }



}
?>