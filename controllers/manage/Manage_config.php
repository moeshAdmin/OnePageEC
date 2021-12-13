<?php

class Manage_config extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_config",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','sys_config','scSysID = "'.$sysID.'"');
            $retData['name'] = $resData[0]['scName'];
            $retData['value1'] = $resData[0]['scValue1']; 
            $retData['value2'] = $resData[0]['scValue2']; 
            $retData['hash'] = $hash;
        }else{
            $resData = $this->Api_common->getDataCustom('*','sys_config','all');
            foreach ($resData as $key => $value) {
                if($resData[$key]['scName']=="secretKey"){
                    unset($resData[$key]);continue;
                }
                $resData[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['scSysID']);
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

        $submitData['scName'] = $postData['name'];
        $submitData['scValue1'] = $postData['value1'];
        $submitData['scValue2'] = $postData['value2'];
        if($postData['hash']==""){
            $this->db->insert('sys_config', $submitData); 
        }else{
            $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
            $this->db->where('scSysID', $sysID);
            $this->db->update('sys_config', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
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
