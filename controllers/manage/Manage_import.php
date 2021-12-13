<?php

class Manage_import extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_excel');
        $this->load->model('Api_ec');
        $this->load->model('Api_table_generate');
        $this->load->model('Users_auth');
        define(LANG,$this->Api_common->getCookie('lang'));
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->removeUploadFile();
        $this->load_MyView("/manage/manage_import",$data); // 陣列資料 data 與 View Rendering
    }

    function getUploadFile(){
        $resData = $this->Api_common->getDataCustom('fmFileName,fmCreateBy,fmCreateDTime','sys_file','fmDesc="fong" AND fmCreateDTime>= "'.date('Y-m-d',strtotime('-7 day',strtotime(date('Ymd')))).'"','fmCreateDTime DESC');
        foreach ($resData as $key => $value) {
            $tableData[$key]['file'] = $resData[$key]['fmFileName'];
            $tableData[$key]['upload'] = $resData[$key]['fmCreateBy'];
            $tableData[$key]['time'] = $resData[$key]['fmCreateDTime'];
            $tableData[$key]['count'] = $resData[$key]['count'];
            $tableData[$key]['report_url'] = base_url().'rma/rma_report/getRawData/'.$resData[$key]['fmParentHash'].'/'.$this->Api_common->stringHash('encrypt',$resData[$key]['fmFileName']);
        }

        echo $this->Api_common->setFrontReturnMsg('200','',$tableData);
        exit;
    }
    function submit($type){
        $user_detail=$this->session->all_userdata();
        $postData = $this->input->post();
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_import.txt","w+","啟動程序中...");
        foreach ($postData['fileName-ship'] as $key => $fileName) {
            if(strpos($fileName, '.xls')==0&&strpos($fileName, '.csv')==0){
                echo $this->Api_common->setFrontReturnMsg('901','只接受Excel檔或CSV檔',['retHtml'=>'只接受Excel檔或CSV檔']);
                exit;
            }
            $fileAry[$key]['name'] = $fileName;
        }
        if(!$postData['runType']){echo $this->Api_common->setFrontReturnMsg('901','未選擇上傳檔案類型',['retHtml'=>'未選擇上傳檔案類型']);exit;}
        $field = $this->getFieldData($postData['runType']);
        
        if(!$fileAry){echo $this->Api_common->setFrontReturnMsg('901','未上傳檔案',['retHtml'=>'未上傳檔案']);exit;}

        $detail['title']['ResFile'] = 'ResFile';
        $fileCount = count($fileAry);
        foreach ($fileAry as $key => $value) {
            //根據檔名讀取檔案
            $fileAry[$key]['data'] = $this->Api_excel->readExcel(UPLOAD_FILE.$fileAry[$key]['name']);
            $tranFieldAry = array();
            if(!$fileAry[$key]['data'][1]['B']){echo $this->Api_common->setFrontReturnMsg('901','無資料可匯入',['retHtml'=>'無資料可匯入']);exit;}
            //找列首判斷欄位位置
            foreach ($field as $fieldName => $sqlName) {
                $excelFieldName = array_search($fieldName, $fileAry[$key]['data'][1]);
                if(!$excelFieldName){continue;}
                $tranFieldAry[$excelFieldName]['fieldName'] = $fieldName;
                $tranFieldAry[$excelFieldName]['sqlName'] = $sqlName;
            }
            if(!$tranFieldAry){echo $this->Api_common->setFrontReturnMsg('901','錯誤的檔案格式',['retHtml'=>'錯誤的檔案格式']);exit;}

            if($type=='preview'){
                $retField = 'fieldName';
            }else if($type=='upload'){
                $retField = 'sqlName';
            }
            //產生預覽資料
            $dataCount = count($fileAry[$key]['data']);
            foreach ($fileAry[$key]['data'] as $key2 => $value2) {
                //if($type=='preview'&&$key2>2){break;}
                //if($key2>3){break;}
                if($key2==1){continue;}
                $tableData[$key.'-'.$key2]['ResFile'] = $fileAry[$key]['name'];
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import.txt","w+","彙整資料中，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - ".$key2." / ".$dataCount);
                foreach ($fileAry[$key]['data'][$key2] as $excelFieldName => $excelFieldValue) {
                    $fieldName = $tranFieldAry[$excelFieldName][$retField];
                    if(!$fieldName){continue;}
                    if(!$detail['title'][$fieldName]){
                        $detail['title'][$fieldName] = $fieldName;
                    }
                    if(preg_match('/shipprocess1time|shipprocess2time|shipprocess3time|時間/', strtolower($fieldName))&&$excelFieldValue){
                        $tableData[$key.'-'.$key2][$fieldName] = date('Y-m-d H:i:s',strtotime($excelFieldValue));
                    }else if(preg_match('/date|日期/', strtolower($fieldName))&&$excelFieldValue){
                        $tableData[$key.'-'.$key2][$fieldName] = date('Y-m-d',strtotime($excelFieldValue));
                    }else{
                        $tableData[$key.'-'.$key2][$fieldName] = $excelFieldValue;
                    }
                }
            }
            if($type=='preview'){
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import.txt","w+","檢查資料中，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - 總筆數:".$dataCount);
                if($postData['runType']=='fong'){
                    $err = $this->chkField($tableData,$postData);
                }
                if($err){
                    $resultErr .= $fileAry[$key]['name'].' 資料檢查<br><div style="max-height:300px;overflow:auto">'.$err.'</div>';
                }
                $err = '';
            }else if($type=='upload'){
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import.txt","w+","寫入資料庫，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - 總筆數:".$dataCount);
                $totalDataCount += $dataCount;
                if($postData['runType']=='fong'){
                    $this->insertDB($tableData,$postData);
                }
                $tableData = array();
                $fileLog['fmFileHash'] = substr($this->Api_common->stringHash('encrypt',date('Ymd').rand(10000,99999).$fileAry[$key]['name']), 5,16);
                $fileLog['fmFileName'] = $fileAry[$key]['name'];
                $fileLog['fmFileSize'] = 0;
                $fileLog['fmDesc'] = $postData['runType'];
                $fileLog['fmIsDone'] = 'Y';
                $fileLog['fmCreateBy'] = $user_detail['account'];
                $fileLog['fmCreateDTime'] = date('Y-m-d H:i:s');
                $this->db->insert('sys_file', $fileLog); 
            }
            
        }
        $num = count($tableData);
        if($type=='preview'){
            $detail['fontSize'] = 10;
            $retData['retHtml'] .= $resultErr;
            //重大錯誤中斷
            if(strpos($resultErr, '(重大)')>0){
                echo $this->Api_common->setFrontReturnMsg('901','有無法忽略錯誤，請確認',$retData);
                exit;
            }
            //產生預覽表格
            if(count($fileAry)>1){
                $retData['retHtml'] .= '<span style="color:red">此次有上傳多個檔案，請確認每個檔案的對應欄位是否正確</span>';
            }else{
                $retData['retHtml'] .= '<span style="color:red">請確認每個檔案的對應欄位是否正確</span>';
            }
            $retData['retHtml'] .= $this->Api_table_generate->drawTable($tableData,$detail,$data);
            if($postData['runType']=='fong'){
                $tp = '[峰潮]出貨回應檔';
            }
            $retData['retHtml'] .= '<span style="color:red">確認上傳 '.$tp.'?</span>';
        }else if($type=='upload'){
            $retData['retHtml'] = '檔案數: '.$fileCount.',總筆數:'.$totalDataCount.'筆';
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import.txt","w+","");
            exit;
        }
        //$retData['retHtml'] = '';
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import.txt","w+","");

        
        echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        exit;
    } 

    private function getFieldData($runType){
        if($runType=='fong'){
           $field = array(
            '訂單編號'=>'eoOrderNo',
            '收件人'=>'eoReceiverName',
            '電話'=>'eoReceiverPhone',            
            '出貨時間'=>'eoShipProcess2Time',
            '物流類別'=>'eoDeliverName',
            '追蹤碼'=>'eoDeliverCode',
            '物流狀態'=>'eoShipProcess',
            '取貨時間'=>'eoShipProcess3Time'
            ); 
        }else if($runType=='custRma'){
            $field = array(
            ); 
        }else if($runType=='rmaResult'){
            $field = array(
            ); 
        }

        return $field;
    }

    private function chkField(){

    }

    private function insertDB($insertData,$postData){
        $ordAry = array();
        foreach ($insertData as $key => $value) {
            array_push($ordAry, str_replace("'", '', $insertData[$key]['eoOrderNo']));
        }

        $resData = $this->Api_common->getDataInCustom('eoOrderNo,eoReceiverName,eoReceiverPhone','ec_order','eoOrderNo',$ordAry,'none','in');
        foreach ($resData as $key => $value) {
            $ordNo = $resData[$key]['eoOrderNo'];
            $ordData[$ordNo]['eoOrderNo'] = $resData[$key]['eoOrderNo'];
            $ordData[$ordNo]['eoReceiverName'] = $resData[$key]['eoReceiverName'];
            $ordData[$ordNo]['eoReceiverPhone'] = $resData[$key]['eoReceiverPhone'];
        }

        $user_detail=$this->session->all_userdata();
        foreach ($insertData as $key => $value) {
            $ordNo = $insertData[$key]['eoOrderNo'];
            if(!$ordData[$ordNo]){
                //$err .= '訂單資料不存在: '.$ordNo.'<br>';
                unset($insertData[$key]);
                continue;
            }
            if($ordData[$ordNo]['eoReceiverName']!=$insertData[$key]['eoReceiverName']||
                $ordData[$ordNo]['eoReceiverPhone']!=$insertData[$key]['eoReceiverPhone']){
                //$err .= '訂單資料比對異常(收件人/電話不符): '.$ordNo.'<br>';
            }
            if($insertData[$key]['eoShipProcess']=='已出貨'||$insertData[$key]['eoShipProcess']=='已取貨'||$insertData[$key]['eoShipProcess2Time']){
                if(!preg_match('/異常|退貨/', $insertData[$key]['eoShipProcess'])){
                    $insertData[$key]['eoOrderStatus'] = '已出貨';
                }
            }
            $insertData[$key]['eoDeliverReceiveFile'] = $insertData[$key]['ResFile'];
            $insertData[$key]['eoUpdateDTime'] = date('Y-m-d H:i:s');
            $insertData[$key]['eoUpdateEmpName'] = $user_detail['account'];
            unset($insertData[$key]['ResFile']);
            unset($insertData[$key]['eoReceiverName']);
            unset($insertData[$key]['eoReceiverPhone']);
            $ordAry[$ordNo] = $ordNo;
        }
        if(!$insertData){
            $retData['retHtml'] = '無資料可寫入';
            echo $this->Api_common->setFrontReturnMsg('901','資料出現異常，請與 IT 聯繫',$retData);
            exit;
        }
        if($err){
            $retData['retHtml'] = '資料異常，資料未寫入成功，請確認:<br>'.$err;
            echo $this->Api_common->setFrontReturnMsg('901','資料出現異常，請與 IT 聯繫',$retData);
            exit;
        }

        $this->db->update_batch('ec_order', $insertData, 'eoOrderNo');

        //發信通知，但排除已發送過
        $resData = $this->Api_common->getDataInCustom('emOrderNo','ec_mail','emOrderNo',$ordAry,'emSendType = "已出貨"','in');
        foreach ($resData as $key => $value) {
            $doneSendBefore[$value['emOrderNo']] = $value['emOrderNo'];
        }
        foreach ($insertData as $key => $value) {
            if($value['eoOrderStatus']=='已出貨'){
                if(!$doneSendBefore[$value['eoOrderNo']]){
                    $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$value['eoOrderNo']);
                    $this->Api_ec->sendOrderMail('已出貨',$postData);
                    $doneSendBefore[$value['eoOrderNo']] = $value['eoOrderNo'];
                }
            }
        }
    }

    private function removeUploadFile(){
        $this->load->helper('file');
        $fileAry = get_dir_file_info('./uploads');
        foreach ($fileAry as $fileName => $value) {
            if($fileName=='.htaccess'){continue;}
            unlink('./uploads/'.$fileName);
        }
        $fileAry = get_dir_file_info(APPPATH.'/files/report');
        foreach ($fileAry as $fileName => $value) {
            if($fileName=='.htaccess'){continue;}
            unlink('./uploads/'.$fileName);
        }
    }
}
