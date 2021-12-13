<?php

class Manage_file extends Ci_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        exit;
    }

    function downloadFile($hash){
        $hash = $this->Api_common->stringHash('decrypt',$hash);
         $file_name = $hash.".xlsx";
         $file_path = DIR_SITE_FILE."report/".$hash.".xlsx";
         $file_size = filesize($file_path);
         header('Pragma: public');
         header('Expires: 0');
         header('Last-Modified: ' . gmdate('D, d M Y H:i ') . ' GMT');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Cache-Control: private', false);
         header('Content-Type: application/octet-stream');
         header('Content-Length: ' . $file_size);
         header('Content-Disposition: attachment; filename="' . $file_name . '";');
         header('Content-Transfer-Encoding: binary');
         readfile($file_path);
         unlink($file_path);
    }
}
