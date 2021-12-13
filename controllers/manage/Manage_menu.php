<?php

class Manage_menu extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_myView("/manage/manage_menu",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','cms_menu','cmSysID = "'.$sysID.'"','',array('manage'=>'Y'));
            $retData['itemName'] = $resData[0]['cmName'.LANG];
            $retData['menuType'] = $resData[0]['cmMenuType']; 
            $retData['itemType'] = $resData[0]['cmTargetType']; 
            $retData['itemTargetID'] = $resData[0]['cmTargetID']; 
            $retData['urlID'] = $resData[0]['cmTargetURL']; 
            $retData['itemParent'] = $resData[0]['cmParent'];
            $retData['itemOrder'] = $resData[0]['cmOrder'];
            $retData['itemTab1'] = $resData[0]['cmTab1'];
            $retData['itemTab2'] = $resData[0]['cmTab2'];
            $retData['template'] = $resData[0]['cmTemplate'.LANG];
            $retData['itemMeta'] = $resData[0]['cmMeta'.LANG];
            $retData['itemImgurl'] = $resData[0]['cmImgurl'];
            $retData['itemSysID'] = $this->Api_common->stringHash('encrypt',$resData[0]['cmSysID']);
        }else{
            $retData['itemName'] = '';
            $retData['itemTargetID'] = '';
            $retData['urlID'] = '';
            $retData['itemParent'] = '';
            $retData['itemOrder'] = '';
            $retData['itemSysID'] = '';
            $retData['itemTab1'] = '';
            $retData['itemTab2'] = '';
            $retData['itemMeta'] = '';
            $retData['itemImgurl'] = '';
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function submit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostNull($postData);
        if($postData['itemSysID']!=""){
            $insertMode = 'update';
            $sysID = $this->Api_common->stringHash('decrypt',$postData['itemSysID']);
            $resData = $this->Api_common->getDataCustom('*','cms_menu','cmSysID = "'.$sysID.'"','',array('manage'=>'Y'));
        }else{
            $insertMode = 'insert';
        }

        $chkData = $this->Api_common->getDataCustom('*','cms_menu','cmSysID = "'.$postData['cateParent'].'"','',array('manage'=>'Y'));

        if($sysID==$chkData[0]['cmParent']||$sysID==$resData[0]['cmParent']||$sysID==$postData['itemParent']){
            if($postData['itemParent']!=0){
                //echo $this->Api_common->setFrontReturnMsg('901','目錄指定為循環參照!',$postData);
                //exit;
            }
            
        }
        $submitData['cmMenuType'] = $postData['menuType'];
        
        $submitData['cmParent'] = $postData['itemParent'];
        $submitData['cmTargetType'] = $postData['itemType'];
        $submitData['cmTargetID'] = $postData['itemTargetID'];
        $submitData['cmTargetURL'] = $postData['urlID'];
        $submitData['cmOrder'] = $postData['itemOrder'];
        $submitData['cmTab1'] = $postData['itemTab1'];
        $submitData['cmTab2'] = $postData['itemTab2'];        
        
        $submitData['cmImgurl'] = $postData['itemImgurl'];

        if($postData['toAllLang']=='true'||$postData['forceRemark']=='true'){
            foreach (LANG_ARRAY as $langKey => $langType) {
                if($postData['forceRemark']=='true'){
                    //強制複寫 
                    //新資料也如此處理
                    $submitData['cmMeta'.$langKey] = $postData['itemMeta'];
                    $submitData['cmName'.$langKey] = $postData['itemName'];
                    $submitData['cmTemplate'.$langKey] = $postData['template'];
                }else{
                    //套用到所有語言 但排除已存在資料語言
                    if(!$resData[0]['cmMeta'.$langKey]){
                        $submitData['cmMeta'.$langKey] = $postData['itemMeta'];
                    }
                    if(!$resData[0]['cmTemplate'.$langKey]){
                        $submitData['cmTemplate'.$langKey] = $postData['template'];
                    }
                    $submitData['cmMeta'.LANG] = $postData['itemMeta'];
                    $submitData['cmName'.LANG] = $postData['itemName'];
                    $submitData['cmTemplate'.LANG] = $postData['template'];
                }
            }
        }else{
            $submitData['cmMeta'.LANG] = $postData['itemMeta'];
            $submitData['cmName'.LANG] = $postData['itemName'];
            $submitData['cmTemplate'.LANG] = $postData['template'];
        }
        
        if($insertMode == 'insert'){
            $this->db->insert('cms_menu', $submitData); 
        }else{
            $this->db->where('cmSysID', $sysID);
            $this->db->update('cms_menu', $submitData); 
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$postData);
        exit;
    }

    function del($hash=null){
        //$lang = $this->Api_common->getCookie('lang');
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $this->db->delete('cms_menu', array("cmSysID"=>$sysID));
        }        
        echo $this->Api_common->setFrontReturnMsg('200','',$sysID);
        exit;
    }

    function loadCateTree($type){
        $resData = $this->Api_common->getDataCustom('*','cms_menu','cmMenuType = "'.$type.'"','cmOrder',array('manage'=>'Y'));
        foreach ($resData as $key => $value) {
            if(!$resData[$key]['cmName'.LANG]){
                $lang = 'TW';
            }else{
                $lang = LANG;
            }
            $element[$key]['id'] = $resData[$key]['cmSysID'];
            $element[$key]['parent'] = (int)$resData[$key]['cmParent'];
            $element[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['cmSysID']);
            $element[$key]['name'] = $resData[$key]['cmName'.$lang].'('.$resData[$key]['cmSysID'].'-'.$resData[$key]['cmOrder'].')';
            $parentData[$resData[$key]['cmSysID']]['name'] = $resData[$key]['cmName'.$lang];
        }
        foreach ($element as $key => $value) {
            $element[$key]['parentName'] = $parentData[$element[$key]['parent']]['name'];
        }
        $treeData = $this->Api_common->buildTree($element,0);
        $retData['parentOptionData'][$type] = array();
        $retData['parentOptionData'][$type] = $this->Api_common->treeToAry($retData['parentOptionData'][$type],$treeData,'　',0);

        $retData['name'] = 'root';
        $retData['id'] = '';
        $retData['hash'] = '';
        $retData['children'] = $treeData;

        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function loadTypeData($type=null){
        if($type=="category"){
            $resData = $this->Api_common->getDataCustom('*','cms_categorys','all','',array('manage'=>'Y'));
            foreach ($resData as $key => $value) {
                $retData['itemTypeOptionData'][$type][$key]['name'] = $resData[$key]['ccName'.LANG];
                $retData['itemTypeOptionData'][$type][$key]['value'] = $resData[$key]['ccSysID'];
            }
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;
        }else if($type=="article"){
            $resData = $this->Api_common->getDataCustom('*','cms_article','all','',array('manage'=>'Y'));
            foreach ($resData as $key => $value) {
                $retData['itemTypeOptionData'][$type][$key]['name'] = $resData[$key]['caTitle'.LANG];
                $retData['itemTypeOptionData'][$type][$key]['value'] = $resData[$key]['caSysID'];
            }
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            exit;
        }
    }

    function rebuild(){
        $this->load->helper('file');
        $fileAry = get_dir_file_info(APPPATH.'/files/cache');
        foreach ($fileAry as $fileName => $value) {
            unlink(APPPATH.'/files/cache/'.$fileName);
        }
        $caStr = 'caSysID,caCateID,caURL,caSlider';
        $ccStr = 'ccSysID,ccURL,ccParent,ccIconUrl';
        foreach (LANG_ARRAY as $lang => $value) {
            $caStr .= ',caTitle'.$lang.',caType'.$lang.',caContentEN';
            $ccStr .= ',ccName'.$lang.',ccTemplate'.$lang;
        }
        $resData['article'] = $this->Api_common->getDataCustom($caStr,'cms_article','all',null,array('manage'=>'Y'));
        $resData['category'] = $this->Api_common->getDataCustom($ccStr,'cms_categorys','all',null,array('manage'=>'Y'));
        foreach ($resData['category'] as $key => $value) {
            $cateID = $resData['category'][$key]['ccSysID'];
            foreach (LANG_ARRAY as $lang => $value2) {
                if(!$resData['category'][$key]['ccName'.$lang]){
                    $cateAry[$cateID][$lang]['title'] = $resData['category'][$key]['ccNameEN'];
                }else{
                    $cateAry[$cateID][$lang]['title'] = $resData['category'][$key]['ccName'.$lang];
                }
                $cateAry[$cateID][$lang]['parentID'] = $resData['category'][$key]['ccParent'];
            }
        }
        foreach ($resData as $key => $value) {
            if($key=='article'){
                $title = 'ca';
                $title2 = 'caTitle';
            }else if($key=='category'){
                $title = 'cc';
                $title2 = 'ccName';
            }
            foreach ($resData[$key] as $key2 => $value2) {
                $urlKey = $resData[$key][$key2][$title.'URL'];
                $idKey = $resData[$key][$key2][$title.'SysID'];
                $ldData = array();
                foreach (LANG_ARRAY as $lang => $value3) {
                    if(!$resData[$key][$key2][$title2.$lang]){
                        $targetLang = 'TW';
                    }else{
                        $targetLang = $lang;
                    }
                    $retData[$key][$idKey][$lang]['title'] = strip_tags($resData[$key][$key2][$title2.$targetLang]);
                    if($urlKey){
                        $retData[$key][$urlKey][$lang]['title'] = strip_tags($resData[$key][$key2][$title2.$targetLang]);
                    }

                    if($key=='article'){
                        $str = str_replace(array("\r", "\n", "\r\n", "\n\r",'keyfeature','specification','setup','download','faq','support','[',']','&nbsp;','/'), '', strip_tags($resData[$key][$key2]['caContentEN']));
                        $str = str_replace("\t", ' ', $str);
                        $retData[$key][$urlKey][$lang]['desc'] = $str;
                        $retData[$key][$idKey][$lang]['desc'] = $str;

                        $cateID = $resData[$key][$key2]['caCateID'];
                        $retData[$key][$urlKey][$lang]['cate'] = $cateAry[$cateID][$lang]['title'];
                        $retData[$key][$idKey][$lang]['cate'] = $cateAry[$cateID][$lang]['title'];

                        $retData[$key][$urlKey][$lang]['template'] = $resData[$key][$key2][$title.'Type'.$targetLang];
                        $retData[$key][$idKey][$lang]['template'] = $resData[$key][$key2][$title.'Type'.$targetLang];
                        $retData[$key][$idKey][$lang]['image'] = $resData[$key][$key2]['caSlider'];
                    }else if($key=='category'){
                        $retData[$key][$urlKey][$lang]['template'] = $resData[$key][$key2][$title.'Template'.$targetLang];
                        $retData[$key][$idKey][$lang]['template'] = $resData[$key][$key2][$title.'Template'.$targetLang];
                        $retData[$key][$idKey][$lang]['image'] = $resData[$key][$key2]['ccIconUrl'];

                        $carousel = $this->SetGoogleLD($key,'carousel',$cateAry,$resData[$key][$key2],$detail);
                        $retData[$key][$idKey][$lang]['ld-carousel'] = $carousel;
                        $retData[$key][$urlKey][$lang]['ld-carousel'] = $carousel;
                    }

                    //Google Breadcrumb LD
                    $detail['cateID'] = $cateID;
                    $detail['lang'] = $lang;
                    $detail['idKey'] = $idKey;
                    $detail['title'] = $retData[$key][$idKey][$lang]['title'];
                    $breadcrumb = $this->SetGoogleLD($key,'breadcrumb',$cateAry,$resData[$key][$key2],$detail);
                    $retData[$key][$idKey][$lang]['ld-breadcrumb'] = $breadcrumb;
                    $retData[$key][$urlKey][$lang]['ld-breadcrumb'] = $breadcrumb;
                    
                }
            }
        }

        $retData = json_encode($retData);
        $this->Api_common->cache('titleSave','title',$retData);

        //重建庫存快取
        $this->Api_ec->rebuildInventory();
        
        echo $this->Api_common->setFrontReturnMsg('200','','');
        exit;
    }

    private function SetGoogleLD($dataType,$returnType,$cateAry,$resData,$detail){
        $cateID = $detail['cateID'];
        $lang = $detail['lang'];
        $idKey = $detail['idKey'];
        $title = $detail['title'];

        if($dataType=='article'&&$returnType=='breadcrumb'){
            $ccParentID = $cateAry[$cateID][$lang]['parentID'];
            $ldData[0]['@type'] = 'ListItem';
            $ldData[0]['position'] = 1;
            if(!$ccParentID){
                $ldData[0]['name'] = 'Home';
                $ldData[0]['item'] = 'https:'.base_url();
            }else{
                $ldData[0]['name'] = $cateAry[$ccParentID][$lang]['title'];
                $ldData[0]['item'] = 'https:'.base_url().'pages/category/'. $ccParentID;
            }
            $ldData[1]['@type'] = 'ListItem';
            $ldData[1]['position'] = 2;
            $ldData[1]['name'] = $cateAry[$cateID][$lang]['title'];
            $ldData[1]['item'] = 'https:'.base_url().'pages/category/'.$cateID;

            $ldData[2]['@type'] = 'ListItem';
            $ldData[2]['position'] = 3;
            $ldData[2]['name'] = $title;
            $ldData[2]['item'] = 'https:'.base_url().'pages/article/'.$idKey;
        }else if($dataType=='category'&&$returnType=='breadcrumb'){
            $ccParentID = $cateAry[$idKey][$lang]['parentID'];
            $ldData[0]['@type'] = 'ListItem';
            $ldData[0]['position'] = 1;
            if(!$ccParentID){
                $ldData[0]['name'] = 'Home';
                $ldData[0]['item'] = 'https:'.base_url();
            }else{
                $ldData[0]['name'] = $cateAry[$ccParentID][$lang]['title'];
                $ldData[0]['item'] = 'https:'.base_url().'pages/category/'. $ccParentID;
            }

            $ldData[1]['@type'] = 'ListItem';
            $ldData[1]['position'] = 2;
            $ldData[1]['name'] = $cateAry[$idKey][$lang]['title'];
            $ldData[1]['item'] = 'https:'.base_url().'pages/category/'.$idKey;
        }else if($dataType=='category'&&$returnType=='carousel'){
            $caStr = 'caTitle'.$lang.',caType'.$lang.',caTitleEN,caURL,caSysID,caIcon';
            $articleData = $this->Api_common->getDataCustom($caStr,'cms_article','caCateID = "'.$cateID.'"',null,array('manage'=>'Y'));
            $ldData = array();
            foreach ($articleData as $key => $value) {
                $ary['@type'] = 'ListItem';
                $ary['position'] = count($ldData)+1;
                $ary['name'] = $value['caTitleEN'];
                $ary['image'] = $value['caIcon'];
                $ary['url'] = 'https:'.base_url().'pages/article/'.$value['caSysID'];
                array_push($ldData, $ary);
            }
        }
        

        return $ldData;
    }
    
}
