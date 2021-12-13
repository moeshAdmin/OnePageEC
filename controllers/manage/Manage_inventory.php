<?php

class Manage_inventory extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->load_MyView("/manage/manage_inventory",$data); // 陣列資料 data 與 View Rendering
    }

    function load($hash=null){
        if($hash){
            $sysID = $this->Api_common->stringHash('decrypt',$hash);
            $resData = $this->Api_common->getDataCustom('*','ec_item','eiSysID = "'.$sysID.'"');
            $retData['name'] = $resData[0]['eiName'];
            $retData['setting'] = $resData[0]['eiSetting'];
            $retData['desc'] = $resData[0]['eiDesc']; 
            $retData['html'] = $resData[0]['eiHtml']; 
            $retData['img'] = $resData[0]['eiImg']; 
            $retData['price'] = $resData[0]['eiPrice']; 
            $retData['status'] = $resData[0]['eiStatus']; 
            $retData['type'] = $resData[0]['eiItemType']; 
            $retData['hash'] = $hash;
        }else{
            //載入庫存
            $inventoryData = $this->Api_common->getDataCustom('eiItemNo,sum(eiItemQty) as qty','ec_inventory','1=1',null,['group_by'=>'eiItemNo']);
            foreach ($inventoryData as $key => $value) {
                $setting[$value['eiItemNo']]['stock'] = $value['qty'];
            }

            $resData = $this->Api_common->getDataCustom('*','ec_item','all');
            foreach ($resData as $key => $value) {
                $resData[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['eiSysID']);
                $resData[$key]['eiSetting'] = json_decode($resData[$key]['eiSetting'],true);
                foreach ($resData[$key]['eiSetting'] as $key2 => $value2) {
                    if($setting[$key2]['stock']){
                        $resData[$key]['eiSetting'][$key2]['stock'] = $setting[$key2]['stock'];
                    }else{
                        $resData[$key]['eiSetting'][$key2]['stock'] = 0;
                    }
                }
                $resData[$key]['eiSetting'] = json_encode($resData[$key]['eiSetting']);
            }
            $retData['object'] = $resData;
        }

        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    }

    function submit(){
        $postData = $this->input->post();
        $user_detail=$this->session->all_userdata();
        $insertInventory['eiItemNo'] = $postData['itemNo'];
        if($postData['action']=='入庫'){
            $insertInventory['eiItemQty'] = (int)abs($postData['itemQty']);
            $insertInventory['eiTicketType'] = '入庫單';
            $insertInventory['eiTicketNo'] = 'SI'.date('YmdHis');
        }else if($postData['action']=='出庫'){
            $insertInventory['eiItemQty'] = (int)(0-$postData['itemQty']);
            $insertInventory['eiTicketType'] = '出庫單';
            $insertInventory['eiTicketNo'] = 'SO'.date('YmdHis');
        }
             
        $insertInventory['eiNote'] = $postData['note'];
        $insertInventory['eiDate'] = date('Y-m-d');
        $insertInventory['eiCreateEmp'] = $user_detail['account'];
        $this->db->insert('ec_inventory', $insertInventory);

        //重建庫存快取
        $this->Api_ec->rebuildInventory();

        echo $this->Api_common->setFrontReturnMsg('200','',null);
        exit;
    }

    function inventoryDetail($itemNo){
        $this->load->model('Api_table_generate');
        $inventoryData = $this->Api_common->getDataCustom('*','ec_inventory','eiItemNo = "'.$itemNo.'"');
        $detail['title'] = array('name','eiItemNo','eiDate','eiTicketNo','eiTicketType','eiItemQty');
        $detail['allBorder'] = $detail['title'];
        echo $this->Api_table_generate->drawTable($inventoryData,$detail,$data);
    }
}
