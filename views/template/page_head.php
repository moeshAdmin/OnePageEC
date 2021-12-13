<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept');
$this->load->helper('cookie');
?>

<html>
<head>

	<?php
		//var_dump($this->my_template->get_asset());
		// Page BASE URL
		if ( !empty($this->my_template->get_base_href()) ) 
			echo $this->my_template->get_base_href().PHP_EOL;
			
		// Page Title
		if ( !empty($this->my_template->get_title()) ) 
			echo $this->my_template->get_title().PHP_EOL;

		// Meta Tags
		if ( !empty($this->my_template->get_meta()) ) {
			foreach($this->my_template->get_meta() as $meta_tag) {
				echo $meta_tag.PHP_EOL;
			}
		}

		// Custom CSS Files
		if ( !empty($this->my_template->get_css()) ) {
			foreach($this->my_template->get_css() as $css_file) {
				echo $css_file.PHP_EOL;
			}
		}

		// Custom JS Files
		if ( !empty($this->my_template->get_js()) ) {
			foreach($this->my_template->get_js('header') as $js_file) {
				echo $js_file.PHP_EOL;
			}
		}
	?>
</head>
<style type="text/css">
	h4[aria-expanded="true"]:after{content:"-";}
	h4[aria-expanded="false"]:after{content:"+";}
	li>a.nav-link{background: #e8e8e8;border-bottom: 1px solid #ccc;}
</style>
<body>
	<div id="main">