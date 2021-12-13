<?php

class File_manage extends Ci_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
		$this->load->model('Api_file');
		$this->load->model('Api_common');
        $this->load->helper('download');
        $this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        //$get_data = $this->input->get();
        //$data['type'] = $get_data['type'];
        //$data['hash'] = $get_data['hash'];
        //$this->load_MyView("/file/file_upload",$data,array(true,false,true,false)); // 陣列資料 data 與 View Rendering
    }

    function readFile(){
        exit();
        if ( mb_strlen($_FILES["file"]["name"], 'Big5') != strlen($_FILES["file"]["name"]) ) {
            $_FILES["file"]["name"] = iconv('UTF-8', 'Big5', $_FILES["file"]["name"]);
        }
        $filename = $_FILES["file"]["name"];
            if ( empty($filename) ){
                echo $this->Api_common->setFrontReturnMsg('901','請選擇檔案!',$_FILES);
                exit();
            }else {
                if(move_uploaded_file($_FILES["file"]["tmp_name"], DIR_SITE_FILE ."temp.xlsx")){

                }else{
                    echo $this->Api_common->setFrontReturnMsg('901','寫入失敗!',$_FILES);
                    exit();
                }
            }
        //$raw = $this->readExcel("temp.xlsx");
        echo $this->Api_common->setFrontReturnMsg('200','',$_FILES);
        exit;
    }

    function fileUpload(){
        if(!$this->input->is_ajax_request()){exit;}
        $this->load->library('My_FileUpload');
    }
    
    function fileProcess(){
        $user_detail=$this->session->all_userdata();
        $postData = $this->input->post();
        $detail['postFileInput'] = 'fileName';
        $this->Api_file->fileProcess($user_detail,$postData,$detail);
    }

    function fileDownload($fileHash){
        $temp = explode('_', $fileHash);
        $actHash = $this->Api_common->stringHash('decrypt',$temp[0]);
        $accessTime = $this->Api_common->stringHash('decrypt',$temp[1]);
        $user_detail=$this->session->all_userdata();
        //echo date('Y-m-d H:i:s',$accessTime);
        $this->Api_file->fileDownload($actHash,$user_detail);
        //$this->Api_common->dataDump($user_detail);
    }
    function fileDel($fileHash){
        $temp = explode('_', $fileHash);
        $actHash = $this->Api_common->stringHash('decrypt',$temp[0]);
        $user_detail=$this->session->all_userdata();
        $this->Api_file->fileDel($user_detail,$actHash);
        echo $this->Api_common->setFrontReturnMsg('200','','');
    }
}
