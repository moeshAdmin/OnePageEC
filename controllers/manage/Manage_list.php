<?php

class Manage_list extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_excel');
        $this->load->model('Api_table_generate');
        $this->load->model('Api_ragic');
        $this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        $this->load_MyView("/manage/manage_list",$data); // 陣列資料 data 與 View Rendering
    }

    function downloadList(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        if($postData['type']=='高質量新客_好菌家'){
            $startDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*30);
            $endDate = date('Y/m/d');
            $listData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,'.$startDate.'&where=1000029,lte,'.$endDate.'&where=1000158,eq,Y&where=1000027,eq,'.urlencode('好菌家'), $ckfile),true);
        }else if($postData['type']=='已購提醒回購_晚安大組_好菌家'){
            $startDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*105);
            $endDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*75);
            $listData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,'.$startDate.'&where=1000029,lte,'.$endDate.'&where=1000153,like,3袋組&where=1000310,like,晚安益生菌#30&where=1000027,eq,'.urlencode('好菌家'), $ckfile),true);
        }else if($postData['type']=='已購提醒回購_晚安小組_好菌家'){
            $startDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*45);
            $endDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*15);
            $listData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,'.$startDate.'&where=1000029,lte,'.$endDate.'&where=1000153,like,3袋組&where=1000310,like,晚安益生菌#30&where=1000027,eq,'.urlencode('好菌家'), $ckfile),true);
            //310正貨商品
            //153購買商品
        }else if($postData['type']=='喚醒沉睡客_好菌家'){
            $startDate = date('Y/m/d',strtotime(date('Y-m-d'))-86400*180);
            $listData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/1?where=1000285,gte,'.$startDate.'&limit=0,100000', $ckfile),true);
        }else if($postData['type']=='區間內有下單兩次記錄的客戶'){
            $startDate = date('Y/m/d',strtotime($postData['date_from']));
            $endDate = date('Y/m/d',strtotime($postData['date_to']));
            $listData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,'.$startDate.'&where=1000029,lte,'.$endDate.'&where=1000027,eq,'.urlencode('好菌家'), $ckfile),true);
            foreach ($listData as $key => $value) {
                $mKey = $value['會員流水編號'];
                $membData[$mKey]['次數']++;
                $membData[$mKey]['訂購人姓名'] = $value['訂購人姓名'];
                $membData[$mKey]['訂購人電話'] = $value['訂購人電話'];
                $membData[$mKey]['訂購人信箱'] = $value['訂購人信箱'];
            }
            unset($listData);
        }

        if($membData){
            $postData['downloadName'] = 'listData_'.$name.'_'.date('Y-m-d',strtotime($postData['date_from'])).'_'.date('Y-m-d',strtotime($postData['date_from']));
            foreach ($membData as $mKey => $value) {
                if($value['次數']==1){
                    unset($membData[$mKey]);
                }
            }
            $this->exportData($membData,$postData['type'],$postData);
        }else if($listData){
            foreach ($listData as $key => $value) {
                if($postData['type']=='喚醒沉睡客_好菌家'){
                    $excelData[$key]['連絡電話'] = $value['連絡電話'];
                    $excelData[$key]['信箱'] = $value['信箱'];
                    $excelData[$key]['好菌家有效購買次'] = $value['好菌家有效購買次'];
                    $excelData[$key]['好菌家首購日'] = $value['好菌家首購日'];
                    $excelData[$key]['好菌家最後購買日'] = $value['好菌家最後購買日'];
                }else{
                    $excelData[$key]['購買日期'] = $value['購買日期'];
                    $excelData[$key]['收貨人電話'] = $value['收貨人電話'];
                    $excelData[$key]['收貨人信箱'] = $value['收貨人信箱'];
                    $excelData[$key]['來源名稱'] = $value['來源名稱'];
                    $excelData[$key]['購買商品'] = $value['購買商品'];
                    $excelData[$key]['正貨商品'] = $value['正貨商品'];
                    $excelData[$key]['訂單總金額'] = $value['訂單總金額'];
                }
            }
            $this->exportData($excelData,$postData['type'],$postData);
        }else{
            echo $this->Api_common->setFrontReturnMsg('901','無符合名單',null);
            exit;
        }
        
        
    }

    private function exportData($resData,$name,$postData){
        $num = 2;
        $title = array();

        foreach ($resData as $key => $value) {
            if($num==2){
                foreach ($resData[$key] as $key2 => $value2) {
                    array_push($title, $key2);
                }
            }
            $excelKey = "A".$num;
            $excelExport[$excelKey] = array();
            array_push($excelExport[$excelKey], $resData[$key]);
            $num++;
        }

        if($postData['downloadName']){
            $detail['downloadName'] = $postData['downloadName'];
        }else{
            $detail['downloadName'] = 'listData_'.$name.'_'.date('Y-m-d');
        }
        
        $detail['sheetName'] = "listData";
        $detail['startLine'] = 2;
        $detail['totalLine'] = count($excelExport);
        $detail['titleLine'] = 1;
        $this->Api_excel->export_Excel($name,$excelExport,false,$title,$detail);

        $hash = $this->Api_common->stringHash('encrypt',$detail['downloadName']);
        $postData['url'][0] = base_url().'manage/manage_file/downloadFile/'.$hash;
        echo $this->Api_common->setFrontReturnMsg('200','',$postData);
        exit;
    }

}
