<?php

class Manage_sms_ui extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_ragic');
        $this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        $this->load_MyView("/manage/manage_sms_ui",$data); // 陣列資料 data 與 View Rendering
    }

    function getSMSSendHistory(){
        $custom['group_by'] = 'emContent,emStatus';
        $resData = $this->Api_common->getDataCustom('emCreateDTime,emContent,emStatus,emSendType,emSendTime,count(emSysID) as count','ec_mail','emSendType LIKE "%七日關懷%" AND emSendDevice = "sms" AND emCreateDTime >= "'.date('Y-m-d',strtotime(date('Y-m-d'))-86400*3).'"','emCreateDTime DESC',$custom);
        echo $this->Api_common->setFrontReturnMsg('200','',$resData);exit;
    }

    //從Ragic取得配送SMS名單
    function getSMSData($cate=null,$type=null){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        if(!$cate&&!$type){
            $cate = $_POST['cate'];
            $type = $_POST['type'];
        }
        if(!$cate||!$type){
            echo $this->Api_common->setFrontReturnMsg('901','請選擇項目',null);exit;
        }
        if($cate=='hey_care_7'){
            $cateName = '到貨七日關懷_黑松';
            $setDate = date('Y/m/d',(strtotime(date('Y-m-d'))-86400*7));
            $resData = $this->Api_common->getDataCustom('*','ec_mail','emSendDevice = "sms" AND emSendType = "'.$cateName.'" AND emContent = "'.$cateName.'_'.$setDate.'"');
            if(!$resData){
                $smsData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000033,eq,N&where=1000027,eq,黑松&where=1000356,eq,'.date('Y/m/d',(strtotime(date('Y-m-d'))-86400*7)).'', $ckfile),true);
            }
        }else if($cate=='wp_care_7'){
            $cateName = '到貨七日關懷_好菌家';
            $setDate = date('Y/m/d',(strtotime(date('Y-m-d'))-86400*7));
            $resData = $this->Api_common->getDataCustom('*','ec_mail','emSendDevice = "sms" AND emSendType = "'.$cateName.'" AND emContent = "'.$cateName.'_'.$setDate.'"');
            if(!$resData){
                $smsData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000033,eq,N&where=1000027,eq,好菌家&where=1000357,eq,'.date('Y/m/d',(strtotime(date('Y-m-d'))-86400*7)).'', $ckfile),true);
            }
        }

        if($resData){
            echo $this->Api_common->setFrontReturnMsg('901','簡訊已發送過!',null);exit;
        }

        if(!$smsData){
            echo $this->Api_common->setFrontReturnMsg('901','指定範圍內無資料!',null);exit;
        }
        
        foreach ($smsData as $key => $value) {
            if(mb_strlen($value['收貨人姓名'])==3){
                $name = mb_substr($value['收貨人姓名'], 1,2);
            }else if(mb_strlen($value['收貨人姓名'])==2){
                $name = $value['收貨人姓名'];
            }else{
                $name = '您';
            }
            $phone = $value['收貨人電話'];
            $table[$phone]['name'] = $name;
            $table[$phone]['phone'] = $value['收貨人電話'];
            $table[$phone]['orderNo'] = $value['訂單流水編號'];
            if($value['收貨日期']){
                $table[$phone]['receiveDate'] = $value['收貨日期'];
            }else{
                $table[$phone]['receiveDate'] = $value['出貨日期'];
            }
            $table[$phone]['source'] = $value['來源名稱'];
            if($value['來源名稱']=='好菌家'){
                if(preg_match('/益生菌/', $value['購買商品'].$value['正貨商品'])){
                    $table[$phone]['text'] = $value['來源名稱'].'感謝您的支持! 提醒您,益生菌屬於食品, 要持續吃才能發揮最好效果喔！歡迎加入Line官方帳號 lin.ee/AlfrEPu';
                }else if(preg_match('/優格粉/', $value['購買商品'].$value['正貨商品'])){
                    $table[$phone]['text'] = $value['來源名稱'].'感謝您的支持! 第一次製作優格，建議參考官網產品頁下方影片，有多種方法的教學喔！lihi1.com/OReem';
                }
            }else if($value['來源名稱']=='黑松'){
                $table[$phone]['text'] = $value['來源名稱'].'感謝您的支持! 專家提醒您,保健要持續做, 好產品要繼續吃才能發揮最好功效! 歡迎加入Line官方帳號 lihi1.com/3EkFu';
            }
            $table[$phone]['num'] = mb_strlen($table[$phone]['text']);
            
        }

        if($type=='preview'){
            echo $this->Api_common->setFrontReturnMsg('200','',$table);exit;
        }else if($type=='send'){
            foreach ($table as $key => $value) {
                $insertData[$key]['emSendDevice'] = 'sms';
                $insertData[$key]['emSource'] = $table[$key]['source'];
                $insertData[$key]['emReceiver'] = $table[$key]['phone'];
                $insertData[$key]['emSubject'] = $table[$key]['text'];
                $insertData[$key]['emContent'] = $cateName.'_'.$setDate;
                $insertData[$key]['emStatus'] = '待發送';
                $insertData[$key]['emSendType'] = $cateName;
                $insertData[$key]['emOrderNo'] = $table[$key]['orderNo'];
                $insertData[$key]['emCreateDTime'] = date('Y-m-d H:i:s');
            }
            $this->db->insert_batch('ec_mail', $insertData);
            echo $this->Api_common->setFrontReturnMsg('200','預約發送完成',null);exit;
        }

        
    }
}
