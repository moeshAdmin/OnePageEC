<?php

class Pages extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_data');
        $this->Api_common->chkBlockIP();
        $this->Api_common->initLang();
        if(!$this->Api_common->getCookie('st')){
            setcookie('st', $this->Api_common->stringHash('encrypt','1_'.date('His')), time() + (3600 * 4), "/");
        }
        if($_GET['utm_source']){
            $utm = 'utm_source='.$_GET['utm_source'].'&utm_medium='.$_GET['utm_medium'].'&utm_campaign='.$_GET['utm_campaign'].'&utm_term='.$_GET['utm_term'].'&utm_content='.$_GET['utm_content'];
            setcookie('source', $this->Api_common->stringHash('encrypt',$utm), time() + (3600 * 4), "/");
        }
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);
        define('LANG',$this->Api_common->getCookie('lang'));
        $this->load->model('Lang');
    }

    // 主畫面
    function index(){
        exit;        
    }

    function article($id){
        if(!preg_match('/[a-zA-Z]|\d/', $id)){exit;}
        if(!LANG){
            $lang = 'EN';
        }else{
            $lang = LANG;
        }
        $this->Api_common->redirectHttps();
        $cache = $this->Api_common->cache('titleCache','title');
        $id = strtolower($id);
        $cache['data'] = json_decode($cache['data'],true);
        $data['title'] = $cache['data']['article'][$id][$lang]['title'];
        $data['cate'] = $cache['data']['article'][$id][$lang]['cate'];
        $data['desc'] = $cache['data']['article'][$id][$lang]['desc'];
        $data['template'] = $cache['data']['article'][$id][$lang]['template'];
        $data['url'] = base_url().'pages/article/'.$id;
        $data['googleld'] .= $this->Api_common->initGoogleLD('breadcrumb',$cache['data']['article'][$id][$lang]['ld-breadcrumb']);
        $data['googleld'] .= $this->Api_common->initGoogleLD('article',$cache['data']['article'][$id][$lang],$data);
        $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
        $detail['postData']['type'] = 'article';
        $detail['return'] = true;
        $data['contain'] = $this->data($id,$detail);
        $this->load_frontEndView("article",$data);
    }

    function category($id){
        if(!preg_match('/[a-zA-Z]|\d/', $id)){exit;}
        if(!LANG){
            $lang = 'EN';
        }else{
            $lang = LANG;
        }
        $this->Api_common->redirectHttps();
        //英文版特殊指向
        if($lang=='EN'&&strtolower($id)=='products'){
            $xredir="https:".base_url().'pages/category/set-top-box'; 
            echo '<script>window.location = "'.$xredir.'";</script>';exit;
        }else if($lang=='EN'&&strtolower($id)=='solutions'){
            $xredir="https:".base_url().'pages/category/70';
            echo '<script>window.location = "'.$xredir.'";</script>'; exit;
        }else if(strtolower($id)=='wizelink'){
            $xredir="https:".base_url().'pages/category/set-top-box';
            echo '<script>window.location = "'.$xredir.'";</script>'; exit;
        }else if(strtolower($id)=='37'){
            $xredir="https:".base_url().'pages/category/43';
            echo '<script>window.location = "'.$xredir.'";</script>'; exit;
        }

        $cache = $this->Api_common->cache('titleCache','title');
        $id = strtolower($id);
        $cache['data'] = json_decode($cache['data'],true);
        $data['title'] = $cache['data']['category'][$id][$lang]['title'];
        $data['id'] = $id;
        if($cache['data']['category'][$id][$lang]['desc']){
            $data['desc'] = $cache['data']['category'][$id][$lang]['desc'];
        }else{
            $data['desc'] = $data['title'];
        }
        $data['url'] = 'https:'.base_url().'pages/category/'.$id;
        $data['template'] = $cache['data']['category'][$id][$lang]['template'];
        $data['googleld'] .= $this->Api_common->initGoogleLD('breadcrumb',$cache['data']['category'][$id][$lang]['ld-breadcrumb']);
        $data['googleld'] .= $this->Api_common->initGoogleLD('carousel',$cache['data']['category'][$id][$lang]['ld-carousel']);
        $data['topMenu'] = $this->Api_data->getMenu('topMenu_return');
        $this->load_frontEndView("category",$data);
    }

    function event($id){
        if(!preg_match('/[a-zA-Z]|\d/', $id)){exit;}
        if(!LANG){
            $lang = 'EN';
        }else{
            $lang = LANG;
        }
        $this->Api_common->redirectHttps();
        $cache = $this->Api_common->cache('titleCache','title');
        $id = strtolower($id);
        $cache['data'] = json_decode($cache['data'],true);
        $data['template'] = $cache['data']['article'][$id][$lang]['template'];

        $postData['type'] = 'event';
        $retData = $this->Api_data->data($id,['return'=>true,'postData'=>$postData]);
        
        $data['title'] = $retData['articleData'][0]['title'];
        $data['html'] = $retData['articleData'][0]['content'];
        $this->load_frontEndView("event",$data);
    }

    function data($id,$detail=null){
        if(!preg_match('/[a-zA-Z]|\d/', $id)){exit;}
        return $this->Api_data->data($id,$detail);
    }

    function adReport(){
        $this->load->helper('file');
        $this->load->model('Api_excel');
        $this->load->model('Api_table_generate');
        $fileAry = get_dir_file_info(APPPATH.'files/report/fr');
        foreach ($fileAry as $fileName => $value) {
            //echo $fileName;
            if($fileName=='done'){continue;}
            $fileAry[$fileName]['data'] = $this->Api_excel->readMultipleExcel(APPPATH.'files/report/fr/'.$fileName);
        }

        foreach ($fileAry as $fileName => $value) {
            foreach ($fileAry[$fileName]['data'] as $sheetName => $value2) {
                if(preg_match('/Youtube圖像表現|關鍵字文案表現|原生素材表現|GDN素材表現CPM/', $sheetName)){continue;}
                //sheet內資料
                foreach ($fileAry[$fileName]['data'][$sheetName] as $row => $value3) {
                    //取得title
                    if(in_array('Spending', $value3)&&!$title){
                        foreach ($value3 as $column => $value4) {
                            $title[$value4] = $column;
                        }
                        continue;
                    }
                    if(preg_match('/Creative|Total/', str_replace([' ',',','$'], '', $value3[$title['Creative']].$value3[$title['廣告活動']].$value3[$title['廣告標題1']].$value3[$title['廣告標題2']]))){
                        continue;
                    }
                    if($value3[$title['Spending']]&&$value3[$title['Impressions']]){
                        $mKey = $fileName.$sheetName.$row;
                        if(preg_match('/hy_/', $fileName)){
                            $tableData[$mKey]['source'] = '黑松';
                        }else{
                            $tableData[$mKey]['source'] = '好菌家';
                        }
                        $tableData[$mKey]['month'] = str_replace('.xlsx', '', explode('_', $fileName)[1]);
                        $tableData[$mKey]['fileName'] = $fileName;
                        $tableData[$mKey]['sheetName'] = $sheetName;
                        $tableData[$mKey]['Creative'] = str_replace([' ',',','$'], '', $value3[$title['Creative']]);
                        $tableData[$mKey]['Creative Name'] = str_replace([' ',',','$'], '', $value3[$title['Creative Name']]);
                        if(preg_match('/關鍵字/', $sheetName)){
                            $tableData[$mKey]['Creative'] = str_replace([' ',',','$'], '', $value3[$title['廣告活動']]);
                            $tableData[$mKey]['Creative Name'] = str_replace([' ',',','$'], '', $value3[$title['廣告標題1']])."\r\n".str_replace([' ',',','$'], '', $value3[$title['廣告標題2']]);
                        }
                        $tableData[$mKey]['Spending'] = str_replace([' ',',','$'], '', $value3[$title['Spending']]);
                        $tableData[$mKey]['Impressions'] = str_replace([' ',',','$'], '', $value3[$title['Impressions']]);
                        $tableData[$mKey]['Clicks'] = str_replace([' ',',','$'], '', $value3[$title['Clicks']]);
                        $tableData[$mKey]['購買'] = str_replace([' ',',','$'], '', $value3[$title['購買']]);

                        $matchName = $tableData[$mKey]['Creative'].$tableData[$mKey]['Creative Name'];
                        if($tableData[$mKey]['source']=='黑松'){
                            if(preg_match('/LUT|葉黃素|晶亮/', $matchName)){
                            $tableData[$mKey]['產品'] = '葉黃素';
                            }else if(preg_match('/137|過敏|免疫/', $matchName)){
                                $tableData[$mKey]['產品'] = '137';
                            }else if(preg_match('/GS|人蔘/', $matchName)){
                                $tableData[$mKey]['產品'] = '人蔘';
                            }else if(preg_match('/AC/', $matchName)){
                                $tableData[$mKey]['產品'] = '御樟芝';
                            }else if(preg_match('/UCII/', $matchName)){
                                $tableData[$mKey]['產品'] = 'UCII';
                            }
                        }else if($tableData[$mKey]['source']=='好菌家'){
                            if(preg_match('/晚安/', $matchName)){
                                $tableData[$mKey]['產品'] = '晚安';
                            }else if(preg_match('/黑酵素|黑暢酵/', $matchName)){
                                $tableData[$mKey]['產品'] = '黑暢酵';
                            }else if(preg_match('/油切/', $matchName)){
                                $tableData[$mKey]['產品'] = '油切';
                            }else if(preg_match('/水汪汪/', $matchName)){
                                $tableData[$mKey]['產品'] = '水汪汪';
                            }else if(preg_match('/植敏菌/', $matchName)){
                                $tableData[$mKey]['產品'] = '植敏菌';
                            }
                        }

                        if(preg_match('/代言人/', $matchName)){
                            $tableData[$mKey]['KOL'] = '代言人';
                        }
                        $tableData[$mKey]['KOL'] = str_replace('KOL', '', explode('_', $tableData[$mKey]['Creative Name'])[2]);
                        $kolList[$tableData[$mKey]['KOL']] = $tableData[$mKey]['KOL'];
                        if(!$tableData[$mKey]['產品']){
                            $tableData[$mKey]['產品'] = '全產品';
                        }
                        if(preg_match('/KOL/', $matchName)){
                            $tableData[$mKey]['是否KOL'] = 'Y';
                        }else{
                            $tableData[$mKey]['是否KOL'] = 'N';
                        }

                    }
                }
                unset($title);
            }
        }

        //$this->Api_common->dataDump($tableData);exit;
        $detail['title'] = ['fileName','source','month','sheetName','KOL','產品','Creative','Creative Name','Spending','Impressions','Clicks','購買','是否KOL'];
        $detail['allBorder'] = $detail['title'];
        $detail['fontSize'] = 10;
        foreach ($tableData as $mKey => $value) {
            foreach ($kolList as $key2 => $value2) {
                $preg = '/'.$value2.'/';
                if(preg_match($preg, $value['Creative Name'])){
                    if(!$tableData[$mKey]['KOL']){
                        $tableData[$mKey]['KOL'] .= $value2;
                    }
                }
            }
        }
        echo $this->Api_table_generate->drawTable($tableData,$detail,$data);
    }

}
