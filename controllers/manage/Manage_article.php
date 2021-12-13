<?php

class Manage_article extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_article",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        $lang = LANG;
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','cms_article','caSysID = "'.$sysID.'"','',array('manage'=>'Y'));
            foreach ($resData as $key => $value) {
                $retData['articleHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['caSysID']);
                if($resData[$key]['caTitle'.$lang]){
                    $retData['articleTitle'] = $resData[$key]['caTitle'.$lang];
                }else{
                    $retData['articleTitle'] = $resData[$key]['caTitleEN'].'(EN)';
                }
                $retData['articleType'] = $resData[$key]['caType'.$lang];
                $retData['articleContent'] = $resData[$key]['caContent'.$lang];
                $retData['articleMeta'] = $resData[$key]['caMeta'.$lang];
                $retData['articleBanner'] = $resData[$key]['caBanner'.$lang];
                $retData['articleCategory'] = $resData[$key]['caCateID'];
                
                $retData['articleDate'] = $resData[$key]['caDate'];
                $retData['articleURL'] = strtolower($resData[$key]['caURL']);
                
                $retData['articleIcon'] = $resData[$key]['caIcon'];
                $retData['articleSlider'] = $resData[$key]['caSlider'];
                
            }

        }else{
            $retData['articleHash'] = '';
            $retData['articleTitle'] = '';
            $retData['articleCategory'] = ''; 
            $retData['articleType'] = '';
            $retData['articleDate'] = date('Y-m-d');
            $retData['articleContent'] = '';
            $retData['articleURL'] = '';
            $retData['articleBanner'] = '';
            $retData['articleIcon'] = '';
            $retData['articleMeta'] = '';
            $retData['articleSlider'] = '';
        } 
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function loadAll(){
        $lang = LANG;
        $cateData = $this->Api_common->getDataCustom('*','cms_categorys','all','',array('manage'=>'Y'));
        foreach ($cateData as $key => $value) {
            $cateAry[$cateData[$key]['ccSysID']] = $cateData[$key]['ccName'.$lang];
        }
        $resData = $this->Api_common->getDataCustom('*','cms_article','all','',array('manage'=>'Y'));
        foreach ($resData as $key => $value) {
            $retData['articleData'][$key]['articleID'] = $resData[$key]['caSysID'];
            if($resData[$key]['caTitle'.$lang]){
                $retData['articleData'][$key]['articleTitle'] = $resData[$key]['caTitle'.$lang];
            }else{
                $retData['articleData'][$key]['articleTitle'] = $resData[$key]['caTitleEN'].'(EN)';
            }
            
            $retData['articleData'][$key]['articleCategory'] = $cateAry[$resData[$key]['caCateID']];
            $retData['articleData'][$key]['articleType'] = $resData[$key]['caType'.$lang];
            $retData['articleData'][$key]['articleDate'] = $resData[$key]['caDate'];
            $retData['articleData'][$key]['articleHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['caSysID']);
            if($resData[$key]['caType']=='event'){
                $tp = 'event';
            }else{
                $tp = 'article';
            }
            if($resData[$key]['caURL']){
                $retData['articleData'][$key]['articleURL'] = base_url().'pages/'.$tp.'/'.$resData[$key]['caURL'];
            }else{
                $retData['articleData'][$key]['articleURL'] = base_url().'pages/'.$tp.'/'.$resData[$key]['caSysID'];
            }
            
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function del($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $this->db->delete('cms_article', array("caSysID"=>$sysID));
        }        
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostNull($postData);
        $postData['articleContent'] = $this->base64ImageTrans($postData['articleContent']);
        if($postData['articleHash']!=""){
            $insertMode = 'update';
            $sysID = $this->Api_common->stringHash('decrypt',$postData['articleHash']);
            $resData = $this->Api_common->getDataCustom('*','cms_article','caSysID = "'.$sysID.'"','',array('manage'=>'Y'));
        }else{
            $insertMode = 'insert';
        }

        if($postData['toAllLang']=='true'||$postData['forceRemark']=='true'){
            foreach (LANG_ARRAY as $langKey => $langType) {
                if($postData['forceRemark']=='true'){
                    //強制複寫 
                    //新資料也如此處理
                    $submitData['caTitle'.$langKey] = $postData['articleTitle'];
                    $submitData['caContent'.$langKey] = $postData['articleContent'];
                    $submitData['caType'.$langKey] = $postData['articleType'];
                    $submitData['caMeta'.$langKey] = $postData['articleMeta'];
                    $submitData['caBanner'.$langKey] = $postData['articleBanner'];
                }else{
                    //套用到所有語言 但排除已存在資料語言
                    if(!$resData[0]['caTitle'.$langKey]){
                        $submitData['caTitle'.$langKey] = $postData['articleTitle'];
                        $submitData['caContent'.$langKey] = $postData['articleContent'];
                        $submitData['caType'.$langKey] = $postData['articleType'];
                        $submitData['caMeta'.$langKey] = $postData['articleMeta'];
                        $submitData['caBanner'.$langKey] = $postData['articleBanner'];
                    }
                    $submitData['caTitle'.LANG] = $postData['articleTitle'];
                    $submitData['caContent'.LANG] = $postData['articleContent'];
                    $submitData['caType'.LANG] = $postData['articleType'];
                    $submitData['caMeta'.LANG] = $postData['articleMeta'];
                    $submitData['caBanner'.LANG] = $postData['articleBanner'];
                }
            }
        }else{
            $submitData['caTitle'.LANG] = $postData['articleTitle'];
            $submitData['caContent'.LANG] = $postData['articleContent'];
            $submitData['caType'.LANG] = $postData['articleType'];
            $submitData['caMeta'.LANG] = $postData['articleMeta'];
            $submitData['caBanner'.LANG] = $postData['articleBanner'];
        }
        
        $submitData['caCateID'] = $postData['articleCategory'];
        $submitData['caDate'] = $postData['articleDate'];
        $submitData['caURL'] = strtolower($postData['articleURL']);
        $submitData['caIcon'] = $postData['articleIcon'];
        
        $submitData['caSlider'] = $postData['articleSlider'];
        if($insertMode == 'insert'){
            $this->db->insert('cms_article', $submitData); 
        }else{
            $this->db->where('caSysID', $sysID);
            $this->db->update('cms_article', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$postData);
        exit;
    }

    function loadCateTree(){
        $retData = $this->Api_common->loadCateTree();
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function base64ImageTrans($postData){
        //$postData = preg_replace("/style=.+?['|\"]/i",'',$postData);
        preg_match_all('/src=\"data:image\/([a-zA-Z]*);base64,([^\"]*)\"/i', $postData, $results);
        foreach ($results[0] as $key => $value) {
            $resStr = $results[0][$key];
            $imgData = $results[2][$key];
            $imgFormat = $results[1][$key];
            $fileName = substr(MD5(date('YmdHis').rand(1000,9999)), 6).'.'.$imgFormat;
            file_put_contents(UPLOAD_FILE.'cke_img/'.$fileName, base64_decode($imgData));
            $postData = str_replace($resStr, 'src="https:'.base_url().'uploads/cke_img/'.$fileName.'"', $postData);
        }
        
        return $postData;
    }
}
