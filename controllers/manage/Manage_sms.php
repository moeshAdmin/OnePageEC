<?php

class Manage_sms extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_table_generate');
        $this->load->model('Api_ragic');
        //$this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        exit;
    }
    //ARS自動關心
    function setArsSMSData(){
        $payBefore10 = date('Y-m-d',strtotime(date('Y-m-d'))+(86400*3));//10;
        $payBefore1 = date('Y-m-d',strtotime(date('Y-m-d'))+(86400*1));
        $startTime = date('Y-m-d H:i:s');
        //$payBefore1 = '2021-05-21';
        //$payBefore10 = '2021-05-21';
        //扣款一天前
        $resData = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSStatus = "進行中" AND eaNextDeliverDate = "'.$payBefore1.'"');
        foreach ($resData as $key => $value) {
            $arsData = $this->Api_ec->getOrderARSData($value['eaARSOrderNo']);
            $orderData = $arsData[0]['detail'];
            $insertData['emSendDevice'] = 'sms';
            $insertData['emSource'] = BRAND_NAME;
            $insertData['emReceiver'] = $orderData[0]['eoReceiverPhone'];
            $insertData['emSubject'] = '感謝您訂購'.BRAND_NAME.'定期配商品，本期訂單將於'.$value['eaNextDeliverDate'].'扣款，如有訂單相關問題歡迎來電客服專線詢問 0809-091518';
            $insertData['emContent'] = null;
            $insertData['emStatus'] = '待發送';
            $insertData['emSendType'] = 'ARS提醒扣款';
            $insertData['emOrderNo'] = null;
            $this->db->insert('ec_mail', $insertData);

            $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$value['eaARSOrderNo']);
            $postData['nextDeliverDate'] = $value['eaNextDeliverDate'];
            $postData['nowPeriods'] = $value['eaARSPeriods']+1;
            
            $this->Api_ec->sendOrderMail('ARS提醒扣款',$postData);
        }

        if($resData>0){
            $this->Api_ragic->saveLog('整批發送SMS-ARS',count($resData),count($resData),$startTime);
        }

        $startTime = date('Y-m-d H:i:s');
        //扣款十天前
        $resData = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSStatus = "進行中" AND eaNextDeliverDate = "'.$payBefore10.'"');
        foreach ($resData as $key => $value) {
            $arsData = $this->Api_ec->getOrderARSData($value['eaARSOrderNo']);
            $orderData = $arsData[0]['detail'];
            $insertData['emSendDevice'] = 'sms';
            $insertData['emSource'] = BRAND_NAME;
            $insertData['emReceiver'] = $orderData[0]['eoReceiverPhone'];
            $insertData['emSubject'] = '感謝您訂購'.BRAND_NAME.'定期配商品，本期商品將於'.$value['eaNextDeliverDate'].'為您配送，如有訂單相關問題歡迎來電客服專線詢問 0809-091518';
            $insertData['emContent'] = null;
            $insertData['emStatus'] = '待發送';
            $insertData['emSendType'] = 'ARS提醒配送';
            $insertData['emOrderNo'] = null;
            $this->db->insert('ec_mail', $insertData);

            $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$value['eaARSOrderNo']);
            $postData['nextDeliverDate'] = $value['eaNextDeliverDate'];
            $postData['nowPeriods'] = $value['eaARSPeriods']+1;
            $this->Api_ec->sendOrderMail('ARS提醒配送',$postData);
        }

        if($resData>0){
            $this->Api_ragic->saveLog('整批發送SMS-ARS',count($resData),count($resData),$startTime);
        }
        $this->Api_ragic->saveLog('整批發送SMS_ARS-Done',0,0,$startTime);
        echo 'done';
    }

}
