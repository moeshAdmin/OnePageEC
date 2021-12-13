<?php

class Manage_dashboard extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_dashboard",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $resData = $this->Api_common->getDataCustom('*','ec_order','eoOrderDate BETWEEN "'.$postData['dateFrom'].'" AND "'.$postData['dateTo'].'" AND eoOrderStatus != "已取消" AND eoOrderStatus != "已退貨"');
        foreach ($resData as $key => $value) {
            if($value['eoOrderStatus']=='待付款'){
                $status['status']['待付款'] ++;
            }else if($value['eoOrderStatus']=='待出貨'&&!$value['eoDeliverOutFile']){
                $status['status']['待拋檔'] ++;
            }else if($value['eoOrderStatus']=='待出貨'&&$value['eoDeliverOutFile']&&!$value['eoDeliverReceiveFile']){
                $status['status']['已拋檔'] ++;
            }else if($value['eoDeliverReceiveFile']&&!preg_match('/已取貨|已收貨/', $value['eoShipProcess'])){
                $status['status']['在途中'] ++;
            }
            if(preg_match('/已取貨|已收貨/', $value['eoShipProcess'])){
                $status['status']['已收貨'] ++;
            }
            if($value['eoInvoiceStatus']=='已開立'){
                $status['status']['已開立'] ++;
            }else if($value['eoInvoiceStatus']=='未開立'&&$value['eoOrderStatus']=='已出貨'){
                $status['status']['待開立'] ++;
            }else if($value['eoInvoiceStatus']&&$value['eoInvoiceStatus']!='未開立'){
                $status['status']['開立錯誤'] ++;
            }
            if($value['eoDate']==date('Y-m-d')){
                $status['status']['今日成交額'] += $value['eoOrderAmount'];
                $status['status']['今日成交件']++;
                $status['status']['今日均單價'] = round($status['status']['今日成交額']/$status['status']['今日成交件'],0);
            }
            $status['orderTable'][$value['eoOrderDate']]['日期'] = $value['eoOrderDate'];
            $status['orderTable'][$value['eoOrderDate']]['件數'] ++;
            $status['orderTable'][$value['eoOrderDate']]['成交金額'] += $value['eoOrderAmount'];
            $status['orderSum']['成交金額'] += $value['eoOrderAmount'];
            $status['orderSum']['件數'] ++;
        }
        krsort($status['orderTable']);
        $status['orderSum']['成交金額'] = number_format($status['orderSum']['成交金額']);
        $status['orderSum']['件數'] = number_format($status['orderSum']['件數']);
        $status['status']['今日成交額'] = number_format($status['status']['今日成交額']);
        $status['status']['今日成交件'] = number_format($status['status']['今日成交件']);
        $status['status']['今日均單價'] = number_format($status['status']['今日均單價']);

        $list = ['待付款','待拋檔','已拋檔','在途中','已收貨','已開立','待開立','開立錯誤'];
        foreach ($list as $key => $value) {
            if(!$status['status'][$value]){$status['status'][$value] = 0;}
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$status);
        exit;
    }
}
