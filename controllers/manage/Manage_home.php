<?php

class Manage_home extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_home",$data); // 陣列資料 data 與 View Rendering
    }

    function accessLog(){
        $postData['showOnlyManage'] = 
        $accessLog = $this->getAccessLog();
        foreach ($accessLog as $timeKey => $value) {
            if(strpos($accessLog[$timeKey]['url'], 'manage/manage')>0&&!preg_match('/manage_home/',$accessLog[$timeKey]['url'])){
                if(!$accessLog[$timeKey]['param']){continue;}
                $accessData['manager'][$timeKey] = $accessLog[$timeKey];
            }else if(!strpos($accessLog[$timeKey]['url'], 'manage/manage')){
                if(str_replace('10.118', '', $accessLog[$timeKey]['from'])==$accessLog[$timeKey]['from']){
                    $accessData['visitor'][$timeKey] = $accessLog[$timeKey];
                }
            }
        }
        $resData = $this->Api_common->getDataCustom('csSysID,csType,csProduct,csName,csEmail,csCompany,csFromIP,csCreateDTime','cms_form','all','csSysID Desc');
        foreach ($resData as $key => $value) {
            $accessData['form'][$key] = $resData[$key];
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$accessData);
        exit; 
    }

    private function getAccessLog(){
        $this->load->helper('file');
        $this->load->model('Api_table_generate');
        $fileAry = get_dir_file_info(APPPATH.'files/temp/access_log');
        $tableContent = array();
        $pos=0;
        $urlList = $this->urlList();
        foreach ($fileAry as $fileName => $value) {
            $fileMeta = explode('_', $fileName);
            //if($fileMeta[2]=="T180055.txt"){continue;}
            if($fileMeta[2]==".txt"){$fileMeta[2] = "not login";$pos=1;}
            //if(!$postData['showNotLogin']&&$fileMeta[2] == "not login"){continue;}
            $str = read_file(APPPATH.'files/temp/access_log/'.$fileName);
            
            $str = explode("\r\n", $str);
            if($postData['preg']!=""){
                $preg = '/'.$postData['preg'].'/';
            }
            $isIgnore = 'Y';
            foreach ($str as $key2 => $logText) {
                if($preg&&!preg_match($preg, $logText)){continue;}
                $str[$key2] = explode('->', $str[$key2]);
                $str[$key2] = str_replace('--', '', $str[$key2]);
                if(strlen($str[$key2][1])<2){continue;}
                if($str[$key2][2]=='return'){continue;}
                if(preg_match('/10\.118|111\.250\.84\.193|111\.243\.199\.60|211\.75\.14\.193/', $str[$key2][5])){
                    continue;
                }
                if(!$str[$key2][6]){
                    continue;
                }
                $content['empID'] = $fileMeta[2];
                $content['DTime'] = $str[$key2][1];
                $content['refer'] = $str[$key2][2];
                $content['model'] = $str[$key2][3];
                $content['name'] = $str[$key2][4];
                $content['from'] = $str[$key2][5];
                if(!$str[$key2][6]){
                    $content['browser'] = 'Spider Bot';
                }else{
                    $content['browser'] = $str[$key2][6];
                }
                $content['url'] = $str[$key2][7];
                if($content['url'] == '/'){
                    $content['urlName'] = 'Home';
                }else if($urlList[str_replace('/pages/data/', '', $str[$key2][7])]){
                    $content['urlName'] = $urlList[str_replace('/pages/data/', '', $str[$key2][7])];
                }else{
                    $content['urlName'] = str_replace('/pages/data/', '', $str[$key2][7]);
                }
                $content['pageID'] = $str[$key2][8];
                $temp = explode(';', $str[$key2][9]);
                $content['param'] = $temp[0];
                $strTime = strtotime($str[$key2][1]);
                $tableContent[$strTime.'-'.$fileMeta[2].'-'.$str[$key2][7]] = $content;
            }
            $pos=0;
        }
        krsort($tableContent);

        return $tableContent;
    }

    private function urlList(){
        $resData = $this->Api_common->getDataCustom('caSysID,caURL,caTitleEN','cms_article','all','caDate DESC');
        foreach ($resData as $key => $value) {
            $urlList[$resData[$key]['caURL']] = $resData[$key]['caTitleEN'];
            $urlList[$resData[$key]['caSysID']] = $resData[$key]['caTitleEN'];
        }
        return $urlList;
    }
}
