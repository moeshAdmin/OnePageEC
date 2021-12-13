<?php

class Manage_user extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_user",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','sys_user','suSysID = "'.$sysID.'"');
            $retData['userActor'] = explode(';', $resData[0]['suActor']);
            $retData['Active'] = $resData[0]['suIsDisabled']; 
            $retData['CName'] = $resData[0]['suCName']; 
            $retData['Email'] = $resData[0]['suEmail']; 
            $retData['hash'] = $hash;
        }else{
            $resData = $this->Api_common->getDataCustom('*','sys_user','all');
            foreach ($resData as $key => $value) {
                $resData[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['suSysID']);
            }
            $retData['object'] = $resData;
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function getEPData($type,$postData){
        $postData['hash'] = $this->Api_common->stringHash('encrypt',date('mdyHi'));
        $json = $this->Api_common->getCurl('https://tti-ep.tti.tv/ep/portal/ttiWebApi/'.$type.'/',$postData);
        $userData = json_decode($json,true);
        return $userData['data'];
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        
        $submitData['suCName'] = $postData['CName'];
        $submitData['suEName'] = $postData['CName'];
        $submitData['suEmpID'] = $postData['CName'];
        $submitData['suEmail'] = $postData['Email'];        
        $submitData['suActor'] = str_replace(',', ';', $postData['userActor']);
        $submitData['suIsDisabled'] = $postData['Active'];
        
        $skey = $this->Api_common->getSysConfig('passHash');

        if($postData['hash']==""){
            $passHash = $this->Api_common->serverHash('encrypt',$skey['scValue1'].$postData['Password'].$skey['scValue2']); 
            $submitData['suPassword'] = $passHash;            
            $this->db->insert('sys_user', $submitData); 
        }else{
            if($postData['Password']){
                $passHash = $this->Api_common->serverHash('encrypt',$skey['scValue1'].$postData['Password'].$skey['scValue2']); 
                $submitData['suPassword'] = $passHash;
            }
            $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
            $this->db->where('suSysID', $sysID);
            $this->db->update('sys_user', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$submitData);
        exit;
    }

    function del($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $this->db->delete('sys_user', array('suSysID' => $sysID)); 
        }        
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }
}
