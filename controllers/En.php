<?php

class En extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
        define('SET_LANG', 'EN');
		$this->load->model('Api_common');
        $this->Api_common->chkBlockIP();
        setcookie('lang',  SET_LANG, time() + (3600 * 4), "/");
        if(!$this->Api_common->getCookie('st')){
            setcookie('st', $this->Api_common->stringHash('encrypt','1_'.date('His')), time() + (3600 * 4), "/");
        }
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);
        define('LANG', SET_LANG);
        $this->load->model('Lang');
    }

    // 主畫面
    function index(){
        $this->Api_common->redirectHttps();
        $user_detail=$this->session->all_userdata();
        $this->load_frontEndView("portal",$data); // 陣列資料 data 與 View Rendering
        $nowPage = explode('/', $_SERVER['REQUEST_URI']);
        $this->Api_common->browserLog($user_detail,$nowPage);    
    }

    function pages($type,$id){        
        $this->load->model('Api_data');
        $this->load->model('Lang');
        $lang = SET_LANG;
        $this->Api_common->redirectHttps();
        $cache = $this->Api_common->cache('titleCache','title');
        $cache['data'] = json_decode($cache['data'],true);
        if($type=='article'||$type=='category'){
            $data['title'] = $cache['data'][$type][$id][$lang]['title'];
            $data['desc'] = $cache['data'][$type][$id][$lang]['desc'];
            $data['template'] = $cache['data'][$type][$id][$lang]['template'];
            $this->load_frontEndView($type,$data); 
            $this->Api_common->initGoogleLD('breadcrumb',$cache['data'][$type][$id][$lang]['ld-breadcrumb']);
            if($type=='category'){
                $this->Api_common->initGoogleLD('carousel',$cache['data'][$type][$id][$lang]['ld-carousel']);
            }
        }else if($type=='data'){
            $this->load->model('Users_auth');
            $this->Api_data->data($id);
        }
    }

}
