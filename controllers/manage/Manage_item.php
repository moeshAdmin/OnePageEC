<?php

class Manage_item extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_item",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','ec_item','eiSysID = "'.$sysID.'"');
            $retData['name'] = $resData[0]['eiName'];
            $retData['setting'] = $resData[0]['eiSetting'];
            $retData['desc'] = $resData[0]['eiDesc']; 
            $retData['html'] = $resData[0]['eiHtml']; 
            $retData['img'] = $resData[0]['eiImg']; 
            $retData['price'] = $resData[0]['eiPrice']; 
            $retData['status'] = $resData[0]['eiStatus']; 
            $retData['type'] = $resData[0]['eiItemType']; 
            $retData['hash'] = $hash;
            $retData['itemID'] = $sysID;
        }else{
            $resData = $this->Api_common->getDataCustom('*','ec_item','all');
            foreach ($resData as $key => $value) {
                $resData[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eiSysID']);
            }
            $retData['object'] = $resData;
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function submit(){
        $postData = $this->input->post();
        $json = json_decode($postData['setting'],true);
        if($postData['type']=='定期配'){
            $field = ['name','nprice','price','periods'];
        }else{
            $field = ['name','nprice','price'];
        }       
        foreach ($json as $key => $value) {
            foreach ($field as $key2 => $fieldName) {
                if(!$value[$fieldName]){
                    echo $this->Api_common->setFrontReturnMsg('901',$fieldName.' 設定不完整',$value);
                    exit;
                }
            }
        }
        $submitData['eiHtml'] = $postData['html'];
        $submitData['eiSetting'] = $postData['setting'];
        $submitData['eiImg'] = $postData['img'];
        $postData = $this->Api_common->cleanPostData($postData);

        $submitData['eiName'] = $postData['name'];
        $submitData['eiDesc'] = $postData['desc'];        
        $submitData['eiPrice'] = $postData['price'];
        $submitData['eiStatus'] = $postData['status'];
        $submitData['eiItemType'] = $postData['type'];
        if($postData['hash']==""){
            $this->db->insert('ec_item', $submitData); 
        }else{
            $sysID = $this->Api_common->stringHash('decrypt',$postData['hash']);
            $this->db->where('eiSysID', $sysID);
            $this->db->update('ec_item', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }

    function del($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $this->db->delete('ec_item', array('eiSysID' => $sysID)); 
        }        
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }

    function test(){
        $item['A001']['規格'] = 'A001 規格1';
        $item['A001']['建議售價'] = '1680';
        $item['A001']['特價'] = '1280';
        echo json_encode($item,true);
    }
}
