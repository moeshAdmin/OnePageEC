<?php

class Manage_report extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_ragic');
        $this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        $this->load_MyView("/manage/manage_report",$data); // 陣列資料 data 與 View Rendering
    }

    //從Ragic取得配送SMS名單
    function getSMSData($cate=null,$type=null){
    }
}
