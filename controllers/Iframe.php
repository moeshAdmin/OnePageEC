<?php

class Iframe extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
    }

    // 主畫面
    function index(){ 
        $this->load_MyView("iframe",$data);
    }


}
