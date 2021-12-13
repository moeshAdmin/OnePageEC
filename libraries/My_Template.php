<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class My_Template {
	// Codeigniter Instance
	protected $ci;

	//private $page_id='';
	//private $view_id='';
	
	// Platform Selector
	//private $platform;

	// Theme Selector
	private $theme='';

	// Asset Selector
	private $asset;

	// Layout Selector
	//private $layout=array('page_id'=>'', 'view_id'=>'');

	// Default Theme Selector
	//private $default_theme = 'default';

	function __construct() {
		$this->ci =& get_instance();
	}

	/**
	 * Setting CSS files.
	 * @param $css_file 	string	file path or url
	 * @param $source 	string	(local|remote)
	 * @return void
	 */
	public function set_favicon($icon_file) {
		if ( strpos($icon_file, 'http') !== false ) {
			$url = $icon_file;
		} else {
				$url = "/images/".rtrim($icon_file,".ico").".ico";
		}
		$this->asset['header']['css'][]	= '<link rel="shortcut icon" href="'.$url.'" type="image/x-icon" />';
	}

	public function set_JSorCSS($tmp_file, $location = 'header') {
		$tmp = explode('/', $tmp_file);
		$file_name = array_pop($tmp);
		$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		switch ( $file_ext ) {
			case 'js' :
				$this->set_js($tmp_file, $location);
			break;
			case 'css' :
				$this->set_css($tmp_file, $location);
			break;			
		}
	}
	
	/**
	 * Setting CSS files.
	 * @param $css_file 	string	file path or url
	 * @param $source 	string	(local|remote)
	 * @return void
	 */
	public function set_css($css_file) {
		if ( strpos($css_file, 'http') !== false ) {
			$url = $css_file;
		} else {
			$url = URL_API.trim($css_file);
			// Check is file exists
			//if(!file_exists($url))
				//show_error("Cannot locate stylesheet file: {$url}.");
		}

		$this->asset['header']['css'][]	= '<link rel="stylesheet" href="'.$url.'" />';
	}
	/**
	 * Get CSS Files
	 * @return array
	 */
	public function get_css() {
		if ( isset($this->asset['header']['css']) )
			return $this->asset['header']['css'];
		else 
			return '';
	}

	/**
	 * Setting JS files.
	 * @param $js_file	string	file path or url
	 * @param $location 	string 	(header|footer)
	 * @param $source 	string	(local|remote)
	 * @return void
	 */
	public function set_js($js_file, $location = 'header') {
		if ( strpos($js_file, 'http') !== false ) {
			$url = $js_file;
		} else {
				$url = URL_API.trim($js_file);

			// Check is file exists
			//if(!file_exists($url))
				//show_error("Cannot locate javascript file: {$url}.");
		}

		$this->asset[$location]['js'][]	= '<script type="text/javascript" src="'.$url.'"></script>';
	}
	/**
	 * Get JS Files
	 * @param $location 	(header|footer)
	 * @return array
	 */
	public function get_js($location = 'header') {
		if ( isset($this->asset[$location]['js']) )
			return $this->asset[$location]['js'];
		else 
			return '';
	}

	/**
	 * Setting Meta Tags
	 * @param $meta_name 	string	meta tag name
	 * @param $meta_content string	meta tag content
	 * @return void
	 */
	public function set_meta($meta_name, $meta_content)	{
		$this->asset['header']['meta'][$meta_name] = '<meta name="' . $meta_name . '" content="' . $meta_content . '"/>';
	}
	/**
	 * Get Meta Tags
	 * @return array
	 */
	public function get_meta($meta_name='') {
		if ( $meta_name == '' )
			return $this->asset['header']['meta'];
		else
			return $this->asset['header']['meta'][$meta_name];
	}	

	/**
	 * Setting Meta Tags of Charset & http-equiv
	 * @return void
	 */
	public function set_charset($charset)	{
		$this->asset['header']['charset'] = $charset;
		$this->asset['header']['meta']['charset'] = '<meta charset="'.$charset.'" />';
		$this->asset['header']['meta']['http-equiv']  = '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />';		
		$this->asset['header']['meta']['http-equiv'] .= '<meta http-equiv="Content-type" value="text/html; charset='.$charset.'" />';		
	}
	/**
	 * Get Meta Tags of Charset
	 * @return array
	 */
	public function get_charset() {
		if ( isset($this->asset['header']['charset']) )
			return $this->asset['header']['charset'];
		else 
			return '';
	}

	/**
	 * Set Page Title
	 * @param $title string
	 * @return void
	 */
	public function set_title($title) {
		$this->asset['header']['title'] = '<title>' . $title . '</title>';
	}

	/**
	 * Get Page Title
	 * @return string
	 */
	public function get_title() {
		if ( isset($this->asset['header']['title']) )
			return $this->asset['header']['title'];
		else 
			return '';
	}

		/**
	 * Set Page Title
	 * @param $title string
	 * @return void
	 */
	public function set_base_href( $url='' ) {
		if ( $url=='') $url=base_url();
		$this->asset['header']['base_href'] = '<base href="'.$url.'" />';
	}

	/**
	 * Get Page Title
	 * @return string
	 */
	public function get_base_href() {
		if ( isset($this->asset['header']['base_href']) )
			return $this->asset['header']['base_href'];
		else 
			return '';
	}
	
	/**
	 * Set Theme
	 * @param $theme string
	 * @return void
	 */
	public function set_theme($theme='') {
		$this->theme = $theme;
		if ($this->theme != '' ) { 
			if( !is_dir( DIR_SITE_THEME.$theme))
				show_error("Cannot find theme folder: {$theme}.");
		}
	}
	/**
	 * Get Theme
	 * @return string
	 */
	public function get_theme() {
		return $this->theme;
	}
	
	public function get_asset() {
		return $this->asset;
	}


	/**
	 * Render Layout
	 * @param $data array
	 * @return void
	 */
	public function render($view_id, $data = array()) {
		//$data['theme']['assets'] = $this->asset;
		$this->ci->load->view($view_id, $data);
		//return $this->ci->load->view($this->layout, $data, TRUE);
	}
}
