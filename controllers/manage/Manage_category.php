<?php

class Manage_category extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_category",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','cms_categorys','ccSysID = "'.$sysID.'"','',array('manage'=>'Y'));
            $retData['cateName'] = $resData[0]['ccName'.LANG];
            $retData['cateDesc'] = $resData[0]['ccDesc'.LANG];
            $retData['cateUrl'] = strtolower($resData[0]['ccURL']); 
            $retData['cateSysID'] = $hash;
            $retData['cateParent'] = $resData[0]['ccParent'];

            $retData['cateImageUrl'] = $resData[0]['ccImageUrl'.LANG];
            $retData['cateIconUrl'] = $resData[0]['ccIconUrl'];
            $retData['cateTemplate'] = $resData[0]['ccTemplate'.LANG];
            $retData['cateRelated'] = $resData[0]['ccRelated'];
            $retData['cateOrderBy'] = $resData[0]['ccOrderBy'];
        }else{
            $retData['cateName'] = '';
            $retData['cateDesc'] = '';
            $retData['cateUrl'] = ''; 
            $retData['cateSysID'] = '';
            $retData['cateParent'] = '';
            $retData['cateImageUrl'] = '';
            $retData['cateIconUrl'] = '';
            $retData['cateTemplate'] = '';
            $retData['cateRelated'] = 'Y';
            $retData['cateOrderBy'] = 'caSysID';
        } 
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function del($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $this->db->update('cms_categorys', array("ccIsDel"=>"Y"), "ccSysID = '".$sysID."'");
        }        
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }

    function loadCateTree(){
        $retData = $this->Api_common->loadCateTree();
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostNull($postData);
        if($postData['cateSysID']!=""){
            $insertMode = 'update';
            $sysID = $this->Api_common->stringHash('decrypt',$postData['cateSysID']);
            $resData = $this->Api_common->getDataCustom('*','cms_categorys','ccSysID = "'.$sysID.'"','',array('manage'=>'Y'));
        }else{
            $insertMode = 'insert';
        }
        
        $chkData = $this->Api_common->getDataCustom('*','cms_categorys','ccSysID = "'.$postData['cateParent'].'"','',array('manage'=>'Y'));
        if(
            $insertMode=='update'&&
            ($sysID==$chkData[0]['ccParent']||$sysID==$resData[0]['ccParent']||$sysID==$postData['cateParent'])
        ){
            echo $this->Api_common->setFrontReturnMsg('901','目錄指定為循環參照!',$postData);
            exit;
        }
        if($postData['toAllLang']=='true'||$postData['forceRemark']=='true'){
            foreach (LANG_ARRAY as $langKey => $langType) {
                if($postData['forceRemark']=='true'){
                    //強制複寫 
                    //新資料也如此處理
                    $submitData['ccName'.$langKey] = $postData['cateName'];
                    $submitData['ccDesc'.$langKey] = $postData['cateDesc'];
                    $submitData['ccTemplate'.$langKey] = $postData['cateTemplate'];
                    $submitData['ccImageUrl'.$langKey] = $postData['cateImageUrl'];
                }else{
                    //套用到所有語言 但排除已存在資料語言
                    if(!$resData[0]['ccName'.$langKey]){
                        $submitData['ccName'.$langKey] = $postData['cateName'];
                        $submitData['ccDesc'.$langKey] = $postData['cateDesc'];
                        $submitData['ccTemplate'.$langKey] = $postData['cateTemplate'];
                        $submitData['ccImageUrl'.$langKey] = $postData['cateImageUrl'];
                    }
                    $submitData['ccName'.LANG] = $postData['cateName'];
                    $submitData['ccDesc'.LANG] = $postData['cateDesc'];
                    $submitData['ccTemplate'.LANG] = $postData['cateTemplate'];
                    $submitData['ccImageUrl'.LANG] = $postData['cateImageUrl'];
                }
            }
        }else{
            $submitData['ccName'.LANG] = $postData['cateName'];
            $submitData['ccDesc'.LANG] = $postData['cateDesc'];
            $submitData['ccTemplate'.LANG] = $postData['cateTemplate'];
            $submitData['ccImageUrl'.LANG] = $postData['cateImageUrl'];
        }
        
        $submitData['ccParent'] = $postData['cateParent'];
        $submitData['ccURL'] = strtolower($postData['cateUrl']);
        
        $submitData['ccIconUrl'] = $postData['cateIconUrl'];
        
        $submitData['ccRelated'] = $postData['cateRelated'];
        $submitData['ccOrderBy'] = $postData['cateOrderBy'];

        if($insertMode == 'insert'){
            $this->db->insert('cms_categorys', $submitData); 
        }else{
            $this->db->where('ccSysID', $sysID);
            $this->db->update('cms_categorys', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$submitData);
        exit;
    }
}
