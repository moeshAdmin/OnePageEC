<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_Controller extends CI_Controller { 
	protected $view_data = array();                    // 要整合入 view 中所使用的資料
	protected $ctrl_data = array();
		
	/*Loading the default libraries, helper, language */
	public function __construct($page_id = ''){
		//$this->set_debug('E_ALL');
		parent::__construct();
		$this->load->library('My_Template');
		if(preg_match('/manage_/', $page_id)){
			$this->load_MyTemplate();
		}else if(preg_match('/ec_/', $page_id)){
			$this->load_ECTemplate();
		}else{
			$this->load_FrontTemplate();
		}
		$this->view_data['page_id'] = $page_id;
		$this->view_data['view_id'] = '';
		$this->view_data['view_page'] = '';		
		$this->view_data['message'] = ""; // 存放執行結果的訊息
		$this->load->model('page');
	}

	function load_FrontTemplate(){

		// 設定共用 META 
		$this->my_template->set_meta('viewport','width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimal-ui');
		$this->my_template->set_meta('apple-mobile-web-app-capable','yes');
		$this->my_template->set_meta('apple-mobile-web-app-status-bar-style','yes');
		
		// 其他基本的套件
		$ver_Bootstrap = '4.1.3';
		$ver_jQuery = '3.1.0';
		$this->my_template->set_JSorCSS('/vue.js');
		$this->my_template->set_JSorCSS('/vue2-animate.min.css');
		
		$this->my_template->set_JSorCSS('/jQuery/'.$ver_jQuery.'/jquery.min.js');
		$this->my_template->set_JSorCSS('/slide/dist/camroll_slider.css');
		$this->my_template->set_JSorCSS('/slide/dist/camroll_slider.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap.min.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/bootstrap.min.css');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap-msgalert.min.js');

		$this->my_template->set_JSorCSS('/ajax.js');
		$this->my_template->set_JSorCSS('/vueInit.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/font-awesome-all.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/customfront.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/custom.css');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/animation.css');	
		$this->my_template->set_JSorCSS('/init.js');
	}

	function load_ECTemplate(){

		// 設定共用 META 
		$this->my_template->set_meta('viewport','width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimal-ui');
		$this->my_template->set_meta('apple-mobile-web-app-capable','yes');
		$this->my_template->set_meta('apple-mobile-web-app-status-bar-style','yes');
		
		// 其他基本的套件
		$ver_Bootstrap = '4.1.3';
		$ver_jQuery = '3.1.0';
		$this->my_template->set_JSorCSS('/vue.js');
		$this->my_template->set_JSorCSS('/vue2-animate.min.css');
		
		$this->my_template->set_JSorCSS('/jQuery/'.$ver_jQuery.'/jquery.min.js');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap.min.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/bootstrap.min.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap-msgalert.min.js');

		$this->my_template->set_JSorCSS('/ajax.js');
		$this->my_template->set_JSorCSS('/vueInit.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/font-awesome-all.css');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/customfront.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/custom_ec.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/animation.css');	
		$this->my_template->set_JSorCSS('/init.js');

		$this->my_template->set_JSorCSS('/slide/dist/camroll_slider.css');
		$this->my_template->set_JSorCSS('/slide/dist/camroll_slider.js');
	}

    function load_MyTemplate(){
		// 設定共用 META 
		$this->my_template->set_meta('viewport','width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimal-ui');
		$this->my_template->set_meta('apple-mobile-web-app-capable','yes');
		$this->my_template->set_meta('apple-mobile-web-app-status-bar-style','yes');
		
		// 其他基本的套件
		$ver_Bootstrap = '4.1.3';
		$ver_jQuery = '3.1.0';
		$this->my_template->set_JSorCSS('https://cdn.jsdelivr.net/npm/vue/dist/vue.js');
		$this->my_template->set_JSorCSS('/jQuery/'.$ver_jQuery.'/jquery.min.js');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap.min.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/bootstrap.min.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/dashboard.css');	

		$this->my_template->set_JSorCSS('/datetimepicker/jquery.datetimepicker.full.min.js');
		$this->my_template->set_JSorCSS('/datetimepicker/jquery.datetimepicker.min.css');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap.bundle.js');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/popper.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/tooltip.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/linkify.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/bootstrap-msgalert.min.js');
		
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/material-icons-min.css');

		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/js/slimselect.min.js');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/slimselect.min.css');
		$this->my_template->set_JSorCSS('/init.js');
		
		//upload相關
		$this->my_template->set_JSorCSS('/upload/css/jquery.fileupload.css');
		$this->my_template->set_JSorCSS('/upload/js/vendor/jquery.ui.widget.js');
		$this->my_template->set_JSorCSS('/upload/js/load-image.all.min.js');
		$this->my_template->set_JSorCSS('/upload/js/canvas-to-blob.min.js');
		$this->my_template->set_JSorCSS('/upload/js/jquery.iframe-transport.js');
		$this->my_template->set_JSorCSS('/upload/js/jquery.fileupload.js');
		$this->my_template->set_JSorCSS('/upload/js/jquery.fileupload-process.js');
		$this->my_template->set_JSorCSS('/upload/js/jquery.fileupload-image.js');
		$this->my_template->set_JSorCSS('/upload/js/jquery.fileupload-validate.js');
		$this->my_template->set_JSorCSS('/upload/js/init.js');
		
		$this->my_template->set_JSorCSS('/ckeditor/ckeditor.js');

		$this->my_template->set_JSorCSS('/ajax.js');
		$this->my_template->set_JSorCSS('/crypto-js.min.js');
		$this->my_template->set_JSorCSS('/vueInit.js');
		$this->my_template->set_JSorCSS('/Bootstrap/'.$ver_Bootstrap.'/css/font-awesome-all.css');
		$this->my_template->set_JSorCSS('/bootstrap/'.$ver_Bootstrap.'/css/custom.css');
	}

	
	/**
	 * Get Theme
	 * @return string
	 */
	public function get_pageID() {
		return $this->view_data['page_id'];
	}

	/**
	 * Get Theme
	 * @return string
	 */
	public function get_viewID() {
		return $this->view_data['view_id'];
	}	
	
	/**
	 * Set View File
	 * @param $layout string
	 * @return void
	 */
	public function set_layout($my_view='') {
		$this->view_data['view_id'] = '';
		$this->view_data['view_page'] = '';

		// 'blank' 表不載入 view 內容		
		if ( $my_view != 'blank' ) {
			if ( $my_view == '' )  {
				$this->view_data['view_id'] = 'view_'.$this->view_data['page_id'];
				$this->view_data['view_page'] = $this->view_data['view_id'];	
			}
			else {
				$data = explode("/", $my_view);
				$view_id = end($data);
								
				$view_path = '';
				$arr_length = count($data)-1;
				for($i=0;$i<$arr_length;$i++) {
					$view_path .= $data[$i]."/";
				}
				$this->view_data['view_id'] = 'view_'.$view_id;
				$this->view_data['view_page'] = $view_path.$this->view_data['view_id'];	
				//echo "=== view_id=".$this->view_data['view_id']." & view_page=".$this->view_data['view_page']; exit;			
				//$this->set_pageID($layout);
				//$this->set_viewID($layout);
			}
		}
		//echo "view_id=".$this->view_data['view_id']." & view_page=".$this->view_data['view_page']; exit;
	}

	
	// 載入將被顯示的網頁, 所有網頁均由 template/index.php 所負責顯示)
	public function load_MyView($my_view='', $data=array(), $show=array(true,true,true,true) ) {
	    $content = array();	// View 要顯示的 data 來源 
		
		// header
		if ( $show[0] )
			$content['header'] = $this->load->view('template/page_head', $this->my_template, TRUE); // ex. head (可含 logo, banner,... )

		// menu
		if ( $show[1] )
			$content['menu'] = $this->load->view('template/page_menu', $this->view_data, TRUE); // ex. menu (可含 menu, nav bar,... )
		
		// body
		if ( $show[2] ) {
			$this->set_layout($my_view);
			$content['body']  = $this->load->view($this->view_data['view_page'], $data, true); // ex. body (left-side, main-body, right-side, ...)
		}
		
		// footer
		if ( $show[3] ) {
			$content['footer'] = $this->load->view('template/page_foot', $this->my_template, TRUE); // ex. foot (copyright, company address, ...)
		}
		//var_dump($content); //exit;
        // 處理完後,  再統一一併丟入 index.php 樣版中顯示
		if ( !empty($content) ) {
			$this->load->view('template/index', $content);
		}
	}

	public function load_frontEndView($my_view='', $data=array(), $show=array(true,true,true,true) ) {
	    $content = array();	// View 要顯示的 data 來源 
		// header
		if ( $show[0] )
			$content['header'] = $this->load->view('template/front_head', $data, TRUE); // ex. head (可含 logo, banner,... )

		// menu
		if ( $show[1] )
			$content['menu'] = $this->load->view('template/front_menu', $this->view_data, TRUE); // ex. menu (可含 menu, nav bar,... )
		
		// body
		if ( $show[2] ) {
			$this->set_layout($my_view);
			$content['body']  = $this->load->view($this->view_data['view_page'], $data, true); // ex. body (left-side, main-body, right-side, ...)
		}
		
		// footer
		if ( $show[3] ) {
			$content['footer'] = $this->load->view('template/front_foot', $this->my_template, TRUE); // ex. foot (copyright, company address, ...)
		}
		$content['vue_template'] = $this->load->view('template/front_vue_template', $data, TRUE);
		if(preg_match('/news-list|product-line|product-list/', $data['template'])){
			$target = $data['template'];
			$content['sub_template'] = $this->load->view('template/sub_template/'.$target, $data, TRUE);
		}
        // 處理完後,  再統一一併丟入 index.php 樣版中顯示
		if ( !empty($content) ) {
			$this->load->view('template/index', $content);
		}
	}
	
	public function load_ECView($my_view='', $data=array(), $show=array(true,true,true,true) ) {
	    $content = array();	// View 要顯示的 data 來源 
		// header
		if ( $show[0] )
			$content['header'] = $this->load->view('template/ec_head', $data, TRUE); // ex. head (可含 logo, banner,... )

		// menu
		if ( $show[1] )
			$content['menu'] = $this->load->view('template/ec_menu', $this->view_data, TRUE); // ex. menu (可含 menu, nav bar,... )
		
		// body
		if ( $show[2] ) {
			$this->set_layout($my_view);
			$content['body']  = $this->load->view($this->view_data['view_page'], $data, true); // ex. body (left-side, main-body, right-side, ...)
		}
		
		// footer
		if ( $show[3] ) {
			$content['footer'] = $this->load->view('template/ec_foot', $this->my_template, TRUE); // ex. foot (copyright, company address, ...)
		}

        // 處理完後,  再統一一併丟入 index.php 樣版中顯示
		if ( !empty($content) ) {
			$this->load->view('template/index', $content);
		}
	}

	public function load_CiView($view_id, $data=array() ) {
		$this->load->view($view_id, $data);
	}	
	// ##### 以上部分, 除特殊需求外, 一般不須修改 #####
	
}

    