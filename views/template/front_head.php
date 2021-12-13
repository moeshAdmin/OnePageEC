<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->load->helper('cookie');
if($template=='event'||$itemStatus=='隱藏賣場'){
	$setTitle = $title;
	$setSite = $title;
}else{
	if($cate){
		$cate = ' > '.$cate;
	}
	$setTitle = $title.$cate.' - '.SITE_NAME;
	$setSite = SITE_NAME;
}
?>

<html>
<head>

	<title><?php echo $setTitle; ?></title>
	<meta name="description" content="<?php echo $desc; ?>">
	<meta name="google-site-verification" content="Nxj2U_20ehQFIP2rczellm8fN27blK4VdVKMwFKJYKQ" />
	<meta http-equiv="Cache-Control" content="max-age=86400" />
	<meta property="og:title" content="<?php echo $setTitle; ?>"/>
	<meta property="og:description" content="<?php echo $desc; ?>" />
	<meta property="og:image" content="<?php echo SITE_FB_IMAGE; ?>"/>
	<meta property="og:url" content="<?php echo $url; ?>"/>
	<meta property="og:type" content="website"/>
	<meta property="og:site_name" content="<?php echo $setSite; ?>"/>
	<link rel="canonical" href="<?php echo $url; ?>">
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
		echo $googleld;
	?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PXFV7WP');</script>
</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PXFV7WP"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
	<div id="main">