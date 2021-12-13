<?php

class Manage_ars extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_invoice');
        $this->load->model('Api_excel');
        $this->load->model('Api_table_generate');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $itemData = $this->Api_common->getDataCustom('eiName,eiSysID,eiStatus','ec_item','all');
        foreach ($itemData as $key => $value) {
            $data['itemData'][$key]['name'] = '['.$value['eiStatus'].'] '.$value['eiName'];
            $data['itemData'][$key]['id'] = $value['eiSysID'];
        }
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_ars",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $arsOrderNo = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSOrderNo = "'.$arsOrderNo.'"');
            $field = array('eaReceiverName','eaReceiverPhone','eaReceiverPostCode','eaReceiverAddr','eaInnerNotes','eaRequestDeliverDate','eaNextDeliverDate');
            foreach ($field as $key => $value) {
               $retData['orderData'][$value] = $resData[0][$value];
            }
            $retData['orderData']['hash'] = $hash;
        }else{
            $postData = $this->input->post();
            $postData = $this->Api_common->cleanPostData($postData);
            if($postData['arsOrderID']){
                $filterKey = ' AND eaARSOrderNo = "'.$this->Api_common->stringHash('decrypt',$postData['arsOrderID']).'"';
            }
            //取得訂單
            $resData = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSStatus="進行中"'.$filterKey);
            foreach ($resData as $key => $value) {
                $ordLst[$value['eaARSOrderNo']] = $value['eaARSOrderNo'];
            }
            //取得訂單子表
            $resData2 = $this->Api_common->getDataInCustom('*','ec_order','eoARSOrderNo',$ordLst,'none','in');
            //$resData2 = $this->Api_common->getDataCustom('*','ec_order_detail','eodPlainShipDate BETWEEN "'.$postData['dateFrom'].'" AND "'.$postData['dateTo'].'"');
            foreach ($resData2 as $key => $value) {
                $resDetail[$value['eoARSOrderNo']][$value['eoOrderNo']] = $value;
            }

            foreach ($resData as $key => $value) {                
                $ordNo = $resData[$key]['eaARSOrderNo'];
                $newData[$ordNo] = $resData[$key];
                $newData[$ordNo]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eoSysID']);
                $newData[$ordNo]['eaARSOrderNoHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eaARSOrderNo']);
                
                $newData[$ordNo]['msg'] = 0;
                $newData[$ordNo]['detail'] =  $resDetail[$ordNo];
            }
            $retData['object'] = $newData;

        }
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function editOrder(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$postData['hash']);
        
        if($postData['eaRequestDeliverDate']){
            if(strtotime($postData['eaNextDeliverDate'])==strtotime(date('Y-m-d'))){
                echo $this->Api_common->setFrontReturnMsg('901','本期訂單即將產生，若需延後本期定期，請直接更改訂單配送日',null);
                exit;
            }
            if(
                strtotime($postData['eaRequestDeliverDate'])<=strtotime(date('Y-m-d'))||
                strtotime($postData['eaRequestDeliverDate'])<=strtotime($postData['eaNextDeliverDate'])
            ){
                echo $this->Api_common->setFrontReturnMsg('901','無法提早配送，日期必須大於預計配送日- '.$postData['eaNextDeliverDate'],null);
                exit;
            }
        }else{
            $postData['eaRequestDeliverDate'] = null;
        }
        unset($postData['hash']);
        unset($postData['eaNextDeliverDate']);
        $submitData = $postData;
        $this->db->where('eaARSOrderNo', $arsOrderNo);
        $this->db->update('ec_order_ars', $submitData);
        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function getARSOrderDetail($hash){
        $this->load->model('Api_ragic');
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$hash);
        $arsOrderNo = 'HA20210610LL0001';
        $result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/8?where=1000342,eq,'.$arsOrderNo.'', $ckfile),true);
        foreach ($result as $key => $value) {
            echo '<script>location.href = "https://ap3.ragic.com/hugePlus/forms/8/'.$key.'";</script>';
            exit;
        }
    }

    function queryARS(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $arsOrderNo = $this->Api_common->stringHash('decrypt',$postData['hash']);
        $arsData = $this->Api_ec->getOrderARSData($arsOrderNo);//取得訂單資料
        $orderData = $arsData[0]['detail'];
        $payType = $this->Api_common->getSysConfig('ecPayType');
        if($payType['scValue1']=='Test'){
            $result = $this->Api_ec->ec_query_period($orderData,'1');
        }else if($payType['scValue1']=='Normal'){
            $result = $this->Api_ec->ec_query_period($orderData,'0');
        }
        echo $this->Api_common->setFrontReturnMsg('200','',$result);
        exit;
    }

}
