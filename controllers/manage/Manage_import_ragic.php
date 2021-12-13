<?php

class Manage_import_ragic extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_excel');
        $this->load->model('Api_ec');
        $this->load->model('Api_table_generate');
        $this->load->model('Users_auth');
        $this->load->model('Api_ragic');
        define(LANG,$this->Api_common->getCookie('lang'));

        set_time_limit(0);
        ini_set('memory_limit','1024M');
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
        $this->removeUploadFile();
        $this->load_MyView("/manage/manage_import_ragic",$data); // 陣列資料 data 與 View Rendering
    }

    function getUploadFile(){
        $resData = $this->Api_common->getDataCustom('fmFileName,fmCreateBy,fmCreateDTime,fmDesc','sys_file','fmFileTag="import_ragic" AND fmCreateDTime>= "'.date('Y-m-d',strtotime('-7 day',strtotime(date('Ymd')))).'"','fmCreateDTime DESC');
        foreach ($resData as $key => $value) {
            $tableData[$key]['file'] = $resData[$key]['fmFileName'];
            $tableData[$key]['upload'] = $resData[$key]['fmCreateBy'];
            $tableData[$key]['time'] = $resData[$key]['fmCreateDTime'];
            $tableData[$key]['desc'] = $resData[$key]['fmDesc'];
            $tableData[$key]['report_url'] = base_url().'rma/rma_report/getRawData/'.$resData[$key]['fmParentHash'].'/'.$this->Api_common->stringHash('encrypt',$resData[$key]['fmFileName']);
        }

        echo $this->Api_common->setFrontReturnMsg('200','',$tableData);
        exit;
    }
    function submit($type,$postData=null){
        $startTime = date('Y-m-d H:i:s');
        $user_detail=$this->session->all_userdata();
        if(!$postData){
            $postData = $this->input->post();
        }
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","啟動程序中...");
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_error_".date('Ymd').".txt","w+","");

        //檢查檔案格式
        foreach ($postData['fileName-ship'] as $key => $fileName) {
            if(strpos($fileName, '.xls')==0&&strpos($fileName, '.csv')==0){
                echo $this->Api_common->setFrontReturnMsg('901','只接受Excel檔或CSV檔',['retHtml'=>'只接受Excel檔或CSV檔']);
                exit;
            }
            $fileAry[$key]['name'] = $fileName;
        }
        if(!$postData['runType']){echo $this->Api_common->setFrontReturnMsg('901','未選擇上傳檔案類型',['retHtml'=>'未選擇上傳檔案類型']);exit;}

        //轉換檔案欄位對應
        $tempRet = $this->getFieldData($postData['runType'],$fileAry);
        $field = $tempRet['field'];
        if(count($tempRet['fileAry'])>0){
            $fileAry = $tempRet['fileAry'];
        }
        
        if(!$fileAry){echo $this->Api_common->setFrontReturnMsg('901','未上傳檔案',['retHtml'=>'未上傳檔案']);exit;}

        $detail['title']['ResFile'] = 'ResFile';
        $fileCount = count($fileAry);
        foreach ($fileAry as $key => $value) {
            //如果沒有預處理檔案，再讀取實際檔案
            if(!$fileAry[$key]['data']){
                $fileAry[$key]['data'] = $this->Api_excel->readExcel(UPLOAD_FILE.$fileAry[$key]['name']);
            }
            
            $tranFieldAry = array();
            if(!$fileAry[$key]['data'][1]['B']&&count($fileAry[$key]['data'])<2){echo $this->Api_common->setFrontReturnMsg('901','無資料可匯入',['retHtml'=>'無資料可匯入']);exit;}

            //找列首判斷欄位位置
            foreach ($field as $fieldName => $sqlName) {
                $excelFieldName = array_search($fieldName, $fileAry[$key]['data'][1]);
                if(!$excelFieldName){continue;}
                $tranFieldAry[$excelFieldName]['fieldName'] = $fieldName;
                $tranFieldAry[$excelFieldName]['sqlName'] = $sqlName;
            }
            //$this->Api_common->dataDump($field);
            if(!$tranFieldAry){echo $this->Api_common->setFrontReturnMsg('901','錯誤的檔案格式',['retHtml'=>'錯誤的檔案格式']);exit;}

            if($type=='preview'){
                $retField = 'fieldName';
            }else if($type=='upload'){
                $retField = 'sqlName';
            }
            //產生預覽資料
            $dataCount = count($fileAry[$key]['data'])-1;
            foreach ($fileAry[$key]['data'] as $key2 => $value2) {
                if($type=='preview'&&$key2>6){break;}
                //if($key2>3){break;}
                if($key2==1){continue;}
                $tableData[$key.'-'.$key2]['ResFile'] = $fileAry[$key]['name'];
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","彙整資料中，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - ".$key2." / ".$dataCount);
                foreach ($fileAry[$key]['data'][$key2] as $excelFieldName => $excelFieldValue) {
                    $fieldName = $tranFieldAry[$excelFieldName][$retField];
                    if(!$fieldName){continue;}
                    if(!$detail['title'][$fieldName]){
                        $detail['title'][$fieldName] = $fieldName;
                    }
                    $excelFieldValue = strip_tags($excelFieldValue);
                    if(preg_match('/shipprocess1time|shipprocess2time|shipprocess3time|時間/', strtolower($fieldName))&&$excelFieldValue){
                        $tableData[$key.'-'.$key2][$fieldName] = date('Y-m-d H:i:s',strtotime($excelFieldValue));
                    }else if(preg_match('/eoorderdiscount/', strtolower($fieldName))&&$excelFieldValue){
                        //折扣累計
                        $tableData[$key.'-'.$key2][$fieldName] += $excelFieldValue;
                    }else if(preg_match('/eoutmcamp/', strtolower($fieldName))&&$excelFieldValue){
                        //活動合併欄位
                        $tableData[$key.'-'.$key2][$fieldName] .= $excelFieldValue;
                    }else if(preg_match('/eoutmcontent/', strtolower($fieldName))&&$excelFieldValue){
                        //折價券合併欄位
                        $tableData[$key.'-'.$key2][$fieldName] .= $excelFieldValue;
                    }else if(preg_match('/date|日期/', strtolower($fieldName))&&$excelFieldValue){
                        $tableData[$key.'-'.$key2][$fieldName] = date('Y-m-d',strtotime($excelFieldValue));
                    }else if($excelFieldValue){
                        $tableData[$key.'-'.$key2][$fieldName] = $excelFieldValue;
                    }
                    //電話欄位修正
                    if(preg_match('/eoreceiverphone/', strtolower($fieldName))&&preg_match('/^\+886/', $excelFieldValue)){
                        $tableData[$key.'-'.$key2][$fieldName] = str_replace('+886', '0', $excelFieldValue);
                    }else if(preg_match('/eoreceiverphone/', strtolower($fieldName))&&preg_match('/^9/', $excelFieldValue)&&strlen( $excelFieldValue)==9){
                        $tableData[$key.'-'.$key2][$fieldName] = '0'.$excelFieldValue;
                    }
                }
            }

            if($type=='preview'){
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","檢查資料中，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - 總筆數:".$dataCount);
                $err = $this->chkField($tableData,$postData);
                if($err){
                    $resultErr .= $fileAry[$key]['name'].' 資料檢查<br><div style="max-height:300px;overflow:auto">'.$err.'</div>';
                }
                $err = '';
            }else if($type=='upload'){
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","寫入資料庫，檔案: [".($key+1)."/".$fileCount."] ".$fileAry[$key]['name']." - 總筆數:".$dataCount);
                $totalDataCount += $dataCount;
                $this->insertDB($tableData,$postData,$fileAry[$key]['brand']);
                $tableData = array();
                $fileLog['fmFileHash'] = substr($this->Api_common->stringHash('encrypt',date('Ymd').rand(10000,99999).$fileAry[$key]['name']), 5,16);
                $fileLog['fmFileName'] = $fileAry[$key]['name'];
                $fileLog['fmFileSize'] = 0;
                $fileLog['fmDesc'] = $postData['runType'].' - 總筆數:'.$dataCount;
                $fileLog['fmIsDone'] = 'Y';
                $fileLog['fmFileTag'] = 'import_ragic';
                $fileLog['fmCreateBy'] = $user_detail['account'];
                $fileLog['fmCreateDTime'] = date('Y-m-d H:i:s');
                $this->db->insert('sys_file', $fileLog); 

                $jobNum = count($first);
                $this->Api_ragic->saveLog('訂單匯入:'.$postData['runType'],$fileCount,$dataCount,$startTime);
            }
            
        }

        //顯示結果表格
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
            if($postData['runType']=='waca_好菌家'||$postData['runType']=='waca_好菌家出貨'){
                $tp = 'WACA (好菌家)';
            }else if($postData['runType']=='cyberbiz_黑松'){
                $tp = 'CyberBiz (黑松)';
            }else if($postData['runType']=='cyberbiz_日研專科'){
                $tp = 'CyberBiz (日研專科)';
            }else if($postData['runType']=='pchome'){
                $tp = 'PCHome';
            }else if($postData['runType']=='momo'){
                $tp = 'MOMO';
            }
            $retData['retHtml'] .= '<span style="color:red">確認上傳 '.$tp.'?</span>';
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","");
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
        }else if($type=='upload'){
            $retData['retHtml'] = '檔案數: '.$fileCount.',總筆數:'.$totalDataCount.'筆';
            echo $this->Api_common->setFrontReturnMsg('200','',$retData);
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","");
            //exit;
        }
        //$retData['retHtml'] = '';
        
        //exit;
    } 

    private function getFieldData($runType,$oldfileAry){
        if($runType=='waca_好菌家'){
           $field = array(
                '訂單編號'=>'eoOrderNo',
                '購買日期'=>'eoOrderDate',
                '商品編號'=>'eoItemNo',
                '品名'=>'eoItemName',
                '多規格名稱一'=>'eoItemType',
                '規格編號'=>'eoItemSKU',
                '訂購商品數量'=>'eoItemQty',
                '小計'=>'eoItemSubTotal',
                '運費'=>'eoOrderShipAmount',
                '總計'=>'eoOrderAmount',
                '[訂單]郵遞區號'=>'eoReceiverPostCode',
                '[訂單]收件人地址'=>'eoReceiverAddr',
                '[訂單]運送方式'=>'eoDeliverName',
                '[訂單]出貨時間'=>'eoShipProcess2Time',
                //'配送狀態'=>'eoShipProcess',
                //'物流單號'=>'eoDeliverCode',
                '[訂單]門市代碼'=>'eoDeliverCvsID',
                '[訂單]門市店名'=>'eoDeliverCvsName',
                '[訂單]門市地址'=>'eoReceiverAddr',
                '付款方式'=>'eoPayType',
                '付款狀態'=>'eoPayStatus',
                '購買人姓名'=>'eoReceiverName',
                '購買人電子信箱'=>'eoReceiverEmail',
                '[訂單]收件人電話'=>'eoReceiverPhone',
                '訂單狀態'=>'eoOrderStatus',
                '發票號碼'=>'eoInvoiceNo',
                '開立日期'=>'eoInvoiceTime',
                '統一編號'=>'eoInvoiceComNo',
                '發票抬頭'=>'eoInvoiceCom',
                '發票類型'=>'eoInvoiceType',
                '載具編號'=>'eoInvoiceMeta',
                //'客戶備註'=>'eoMemberNote',
                '店家訂單備註'=>'eoInnerNote'
                
            ); 
        }else if($runType=='waca_好菌家出貨'){
           $field = array(
                '訂單編號'=>'eoOrderNo',
                '[出貨單]出貨時間'=>'eoShipProcess2Time',
                '[出貨單]物流收貨日期'=>'eoShipProcess3Time',
                '出貨狀態'=>'eoShipProcess',
                '[出貨單]物流單號'=>'eoDeliverCode',
                '店家訂單備註'=>'eoInnerNote',
                '客戶備註'=>'eoMemberNote'
            ); 
        }else if($runType=='cyberbiz_黑松'){
           $field = array(
                '訂單編號'=>'eoOrderNo',
                '時間'=>'eoOrderDate',
                'SKU'=>'eoItemNo',
                '商品名稱'=>'eoItemName',
                '商品款式'=>'eoItemType',
                '數量'=>'eoItemQty',
                '小計'=>'eoItemSubTotal',
                '運費'=>'eoOrderShipAmount',
                '總額'=>'eoOrderAmount',
                '收件人地址'=>'eoReceiverAddr',
                '出貨方式'=>'eoDeliverName',
                '出貨時間'=>'eoShipProcess2Time',
                '收貨時間'=>'eoShipProcess3Time',
                '出貨狀態'=>'eoShipProcess',
                '托運單號'=>'eoDeliverCode',
                '收貨超商代號'=>'eoDeliverCvsID',
                '門市名稱'=>'eoDeliverCvsName',
                '門市地址'=>'eoReceiverAddr??',
                '付款方式'=>'eoPayType',
                '付款狀態'=>'eoPayStatus',
                '購買人名稱'=>'eoReceiverName',
                'Email'=>'eoReceiverEmail',
                '收件人電話'=>'eoReceiverPhone',
                '訂單狀態'=>'eoOrderStatus',
                '發票號碼'=>'eoInvoiceNo',
                '發票開立日期'=>'eoInvoiceTime',
                '統一編號'=>'eoInvoiceComNo',
                '發票抬頭'=>'eoInvoiceCom',
                '發票類型'=>'eoInvoiceType',
                '載具號碼'=>'eoInvoiceMeta',
                '店家備註'=>'eoInnerNote',
                '客戶備註'=>'eoMemberNote',
                '商品廠商'=>'??',

                '任選折扣總金額'=>'eoOrderDiscount',
                '全館活動金額'=>'eoOrderDiscount',
                'VIP折扣'=>'eoOrderDiscount',
                '優惠券金額'=>'eoOrderDiscount',
                '紅利折抵'=>'eoOrderDiscount',
                '第三方折扣金額'=>'eoOrderDiscount',       

                '任選折扣'=>'eoUtmCamp',    
                '全館活動'=>'eoUtmCamp',
                '優惠券名稱'=>'eoUtmContent' 
                
            ); 
        }else if($runType=='cyberbiz_日研專科'){
           $field = array(
                '訂單編號'=>'eoOrderNo',
                '時間'=>'eoOrderDate',
                'SKU'=>'eoItemNo',
                '商品名稱'=>'eoItemName',
                '商品款式'=>'eoItemType',
                '數量'=>'eoItemQty',
                '小計'=>'eoItemSubTotal',
                '運費'=>'eoOrderShipAmount',
                '總額'=>'eoOrderAmount',
                '收件人地址'=>'eoReceiverAddr',
                '出貨方式'=>'eoDeliverName',
                '出貨時間'=>'eoShipProcess2Time',
                '收貨時間'=>'eoShipProcess3Time',
                '出貨狀態'=>'eoShipProcess',
                '托運單號'=>'eoDeliverCode',
                '收貨超商代號'=>'eoDeliverCvsID',
                '門市名稱'=>'eoDeliverCvsName',
                '門市地址'=>'eoReceiverAddr??',
                '付款方式'=>'eoPayType',
                '付款狀態'=>'eoPayStatus',
                '購買人名稱'=>'eoReceiverName',
                'Email'=>'eoReceiverEmail',
                '收件人電話'=>'eoReceiverPhone',
                '訂單狀態'=>'eoOrderStatus',
                '發票號碼'=>'eoInvoiceNo',
                '發票開立日期'=>'eoInvoiceTime',
                '統一編號'=>'eoInvoiceComNo',
                '發票抬頭'=>'eoInvoiceCom',
                '發票類型'=>'eoInvoiceType',
                '載具號碼'=>'eoInvoiceMeta',
                '店家備註'=>'eoInnerNote',
                '客戶備註'=>'eoMemberNote',
                '商品廠商'=>'??',

                '任選折扣總金額'=>'eoOrderDiscount',
                '全館活動金額'=>'eoOrderDiscount',
                'VIP折扣'=>'eoOrderDiscount',
                '優惠券金額'=>'eoOrderDiscount',
                '紅利折抵'=>'eoOrderDiscount',
                '第三方折扣金額'=>'eoOrderDiscount',       

                '任選折扣'=>'eoUtmCamp',    
                '全館活動'=>'eoUtmCamp',
                '優惠券名稱'=>'eoUtmContent' 
                
            ); 
        }else if($runType=='pchome'){
            $brandField = 19;
            $field = array(
                '訂單狀態'=>'eoOrderStatus',
                '出貨狀態'=>'eoShipProcess',
                '訂單編號'=>'eoOrderNo',
                '轉單日期'=>'eoOrderDate',
                '商品ID'=>'eoItemNo',
                '商品名稱'=>'eoItemName',
                '姓名'=>'eoReceiverName',
                '電話'=>'eoReceiverPhone',
                ''=>'eoItemType',
                '實際出貨數量'=>'eoItemQty',
                '成本小計'=>'eoItemSubTotal',
                ''=>'eoOrderShipAmount',
                '訂單總金額'=>'eoOrderAmount',
                '出貨日'=>'eoShipProcess2Time'
            ); 
            foreach ($oldfileAry as $key => $value) {
                //讀取檔案 轉換編碼
                $fp = file_get_contents(UPLOAD_FILE.$oldfileAry[$key]['name']);
                $result = mb_convert_encoding($fp,"utf-8","utf-16");
                if(!preg_match('/PChome/', $result)){
                    echo $this->Api_common->setFrontReturnMsg('901','檔案格式錯誤',null);
                    exit;
                }
                //文字轉陣列
                $temp = explode("\n", $result);
                foreach ($temp as $key2 => $value2) {
                    $temp2 = explode("\t", $value2);
                    $resData[$key2] = $temp2;
                }
                if($resData[1][4]!='訂單編號'||$resData[1][5]!='商品名稱'){
                    echo $this->Api_common->setFrontReturnMsg('901','訂單編號被異動，請檢查檔案格式',null);
                    exit;
                }
                unset($resData[0]);
                //$resData = array_values($resData);
                //變更格式資料 訂單編號/料號/金額統計等
                foreach ($resData as $key2 => $value2) {
                    if($key2<2){continue;}
                    if(!$value2[4]){unset($resData[$key2]);continue;}
                    $brand = '';
                    $resData[$key2][4] = mb_substr($value2[4], 0,strpos($value2[4], '-'));
                    $resData[$key2][13] = str_replace(['"=""','"""','"',' '],'', $value2[13]);
                    if(preg_match('/黑松/', $value2[5])){$brand = '黑松';
                    }else if(preg_match('/好菌家/', $value2[5])){$brand = '好菌家';}
                    if($brand!=''){
                        $orderBrandAry[$resData[$key2][4]] = $brand;
                    }
                    $priceAry[$resData[$key2][4]] += $value2[10];
                }
                //資料整理
                $resData[1][14] = '訂單總金額';
                $resData[1][15] = '姓名';
                $resData[1][16] = '電話';
                $resData[1][17] = '訂單狀態';
                $resData[1][18] = '出貨狀態';
                $resData[1][19] = '品牌';
                foreach ($resData as $key2 => $value2) {
                    if($key2<2){continue;}
                    $resData[$key2][14] = $priceAry[$resData[$key2][4]];
                    $resData[$key2][15] = 'PCHome';
                    $resData[$key2][16] = '0999000000';
                    $resData[$key2][17] = '已結案';
                    $resData[$key2][18] = '已收貨';
                    $resData[$key2][19] = $orderBrandAry[$resData[$key2][4]];
                }
                $fileAry[$key]['name'] = $oldfileAry[$key]['name'];
                $fileAry[$key]['data'] = $resData;
           }

        }else if($runType=='momo'){
            $brandField = 'AA';
            $field = array(
                '訂單狀態'=>'eoOrderStatus',
                '出貨狀態'=>'eoShipProcess',
                '訂單編號'=>'eoOrderNo',
                '訂單成立日'=>'eoOrderDate',
                '品號'=>'eoItemNo',
                '品名'=>'eoItemName',
                '姓名'=>'eoReceiverName',
                '電話'=>'eoReceiverPhone',
                ''=>'eoItemType',
                '數量'=>'eoItemQty',
                '售價(含稅)'=>'eoItemSubTotal',
                ''=>'eoOrderShipAmount',
                '訂單總金額'=>'eoOrderAmount',
                '物流公司'=>'eoDeliverName',
                '實際出貨日'=>'eoShipProcess2Time'
            ); 
            foreach ($oldfileAry as $key => $value) {
                $resData = $this->Api_excel->readExcel(UPLOAD_FILE.$oldfileAry[$key]['name']);
                if($resData[1]['B']!='訂單編號'||$resData[1]['N']!='品名'||$resData[1]['T']!='售價(含稅)'){
                    echo $this->Api_common->setFrontReturnMsg('901','資料格式被異動，請檢查檔案格式',null);
                    exit;
                }
                //變更格式資料 訂單編號/料號/金額統計等
                foreach ($resData as $key2 => $value2) {
                    if($key2<2){continue;}
                    if(!$value2['B']){unset($resData[$key2]);continue;}
                    $brand = '';
                    $resData[$key2]['B'] = mb_substr($value2['B'], 0,strpos($value2['B'], '-'));
                    if(preg_match('/黑松/', $value2['N'])){$brand = '黑松';
                    }else if(preg_match('/好菌家/', $value2['N'])){$brand = '好菌家';}
                    if($brand!=''){
                        $orderBrandAry[$resData[$key2]['B']] = $brand;
                    }
                    $priceAry[$resData[$key2]['B']] += $value2['T'];
                }
                //資料整理
                $resData[1]['V'] = '訂單總金額';
                $resData[1]['W'] = '姓名';
                $resData[1]['X'] = '電話';
                $resData[1]['Y'] = '訂單狀態';
                $resData[1]['Z'] = '出貨狀態';
                $resData[1]['AA'] = '品牌';
                foreach ($resData as $key2 => $value2) {
                    if($key2<2){continue;}
                    $resData[$key2]['V'] = $priceAry[$resData[$key2]['B']];
                    $resData[$key2]['W'] = 'MOMO';
                    $resData[$key2]['X'] = '0998000000';
                    $resData[$key2]['Y'] = '已結案';
                    $resData[$key2]['Z'] = '已收貨';
                    $resData[$key2]['AA'] = $orderBrandAry[$resData[$key2]['B']];
                }
                $fileAry[$key]['name'] = $oldfileAry[$key]['name'];
                $fileAry[$key]['data'] = $resData;
           }
        }
        
        //通路分離
        if(preg_match('/pchome|momo/', $runType)){
            $temp = $fileAry;
            unset($fileAry);
            foreach ($temp as $key => $value) {
                foreach ($value['data'] as $key2 => $value2) {
                    if($key2==1){$titleTR = $value2;continue;}
                    $brand = $value2[$brandField];
                    $fileAry[$brand.$key]['name'] = $brand.$key;
                    $fileAry[$brand.$key]['brand'] = $brand;
                    $fileAry[$brand.$key]['data'][$key2] = $value2;
                }
            }
            foreach ($fileAry as $brand => $value) {
                $fileAry[$brand]['data'][1] = $titleTR;
                ksort($fileAry[$brand]['data']);
            }
        }

        $retData['field'] = $field;
        $retData['fileAry'] = $fileAry;

        return $retData;
    }

    private function chkField($tableData,$postData){
        if($postData['runType']=='waca_好菌家'){
            foreach ($tableData as $key => $value) {
                if(!$value['訂單編號']||!$value['[訂單]收件人電話']||!$value['訂單狀態']){
                    echo $this->Api_common->setFrontReturnMsg('901','檔案格式錯誤',['retHtml'=>'檔案格式錯誤']);
                    exit;
                }
            }
        }else if($postData['runType']=='waca_好菌家出貨'){
            foreach ($tableData as $key => $value) {
                if(!$value['訂單編號']||!$value['出貨狀態']){
                    echo $this->Api_common->setFrontReturnMsg('901','檔案格式錯誤',['retHtml'=>'檔案格式錯誤']);
                    exit;
                }
            }
        }else if($postData['runType']=='cyberbiz_黑松'){
            $sourceChk = false;
            foreach ($tableData as $key => $value) {
                if(!$value['訂單編號']||!$value['收件人電話']||!$value['訂單狀態']||!$value['總額']){
                    echo $this->Api_common->setFrontReturnMsg('901','檔案格式錯誤',['retHtml'=>'檔案格式錯誤']);
                    exit;
                }
                if($value['商品廠商']=='黑松生技'){
                    $sourceChk = true;
                }
            }
            if(!$sourceChk){
                echo $this->Api_common->setFrontReturnMsg('901','請檢查來源廠商是否正確',['retHtml'=>'請檢查來源廠商是否正確-->'.$value['商品廠商']]);
                exit;
            }
        }else if($postData['runType']=='cyberbiz_日研專科'){
            $sourceChk = false;
            foreach ($tableData as $key => $value) {
                if(!$value['訂單編號']||!$value['收件人電話']||!$value['訂單狀態']||!$value['總額']){
                    echo $this->Api_common->setFrontReturnMsg('901','檔案格式錯誤',['retHtml'=>'檔案格式錯誤']);
                    exit;
                }
                if($value['商品廠商']=='日研專科'){
                    $sourceChk = true;
                }
            }
            if(!$sourceChk){
                echo $this->Api_common->setFrontReturnMsg('901','請檢查來源廠商是否正確',['retHtml'=>'請檢查來源廠商是否正確-->'.$value['商品廠商']]);
                exit;
            }
        }
    }

    private function insertDB($resData,$postData,$brand=null){
        $user_detail=$this->session->all_userdata();
        
        foreach ($resData as $key => $value) {
            $orderNo = $value['eoOrderNo'];

            if(preg_match('/cyberbiz/', $postData['runType'])){
                $resData[$key]['eoItemSKU'] = $value['eoItemNo'];
            }

            if(!$insertData[$orderNo]){
                $insertData[$orderNo] = $resData[$key];
            }
            
            //彙整子表格資料
            $itemData[$orderNo]['itemData'][$key] = $resData[$key];

            //彙整行銷資料
            if($value['eoItemNo']=='promotionsN'){
                $insertData[$orderNo]['eoUtmCamp'] = $value['eoItemName'].'_'.$value['eoItemType'];
            }else if($value['eoItemNo']=='coupon'){
                $insertData[$orderNo]['eoUtmContent'] = $value['eoItemName'].'_'.$value['eoItemType'];
            }

            $searchText .= $orderNo.' ';
        }

        if($postData['runType']=='waca_好菌家'){
            $detail['siteName'] = '好菌家';
            $membAry = $this->insertMember($resData,$postData);
        }else if($postData['runType']=='waca_好菌家出貨'){
            $detail['siteName'] = '好菌家';
            $detail['ignoreItem'] = 'Y';
        }else if($postData['runType']=='cyberbiz_黑松'){
            $detail['siteName'] = '黑松';
            $membAry = $this->insertMember($resData,$postData);
        }else if($postData['runType']=='cyberbiz_日研專科'){
            $detail['siteName'] = '日研專科';
            $membAry = $this->insertMember($resData,$postData);
        }else if($postData['runType']=='pchome'){
            $detail['siteName'] = $brand;
            $detail['sourceName'] = 'PCHome';
            $membAry = $this->insertMember($resData,$postData);
        }else if($postData['runType']=='momo'){
            $detail['siteName'] = $brand;
            $detail['sourceName'] = 'MOMO';
            $membAry = $this->insertMember($resData,$postData);
        }

        //彙整訂單
        $pnum = 0;
        $skey = 0;
        foreach ($insertData as $orderNo => $value) {
            $orderText[$pnum] .= $detail['siteName'].'-'.$orderNo.' ';
            if($skey%50==0){
                $pnum++;
            }
            $skey++;
        }

        //$this->Api_common->dataDump($orderText);exit;

        //檢查訂單是否已存在
        foreach ($orderText as $key2 => $value2) {
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","檢查訂單是否已存在");
            $ragicData = $this->Api_ragic->getRagicFullText('forms/2',$value2,20);
            foreach ($ragicData as $key => $value) {
                $orderNo = $ragicData[$key]['訂單流水編號'];
                $ordAry[$orderNo]['ragicId'] =  $ragicData[$key]['_ragicId'];
                $ordAry[$orderNo]['orderNo'] =  $ragicData[$key]['訂單流水編號'];
            }
            
        }
        
        

        $detail['multiItem'] = 'Y';
        $dataCount = count($insertData);
        $num = 1;
        $retData['error'] = 0;
        $retData['success'] = 0;
        foreach ($insertData as $orderNo => $value) {
            $remain = (($dataCount-$num)*0.5/60);
            $minutes = round($remain,0);
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","寫入 Ragic，[".$orderNo."] ".$num."/".$dataCount.' 失敗筆數: '.$retData['error'].'<br>剩餘時間: '.$minutes.'分以內');
            
            unset($orderInsertData);
            //設定子表格資料
            $detail['itemData'] = $itemData[$orderNo]['itemData'];

            //取得電話對應會員編號
            if($membAry[$value['eoReceiverPhone']]['memberNo']){
                $orderInsertData['1000023'] = $membAry[$value['eoReceiverPhone']]['memberNo'];
            }

            //退貨判斷
            if(preg_match('/失敗|取消|退貨/', $value['eoOrderStatus'])||preg_match('/取消|退貨|逾期未取|運送異常/', $value['eoShipProcess'])){
                $value['eoIsReturn'] = 'Y';
            }else{
                $value['eoIsReturn'] = 'N';
            }
            //如果是忽略商品資料的檔案，不做退貨判斷
            if($detail['ignoreItem']=='Y'){
                unset($value['eoIsReturn']);
            }

            //依據訂單狀態分流
            if($ordAry[$orderNo]){
                $mode = 'update';
                $orderInsertData = $this->Api_ragic->transSqlDataToRagicField($mode,$orderInsertData,$value,$detail);
                $result = $this->Api_ragic->syncToRagic('forms/2/'.$ordAry[$orderNo]['ragicId'],$orderInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
            }else{
                $mode = 'insert';
                $orderInsertData = $this->Api_ragic->transSqlDataToRagicField($mode,$orderInsertData,$value,$detail);
                $result = $this->Api_ragic->syncToRagic('forms/2',$orderInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
            }
            if(!$result){
                $retData['error']++;
                if(!$returnData){
                    $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_error_".date('Ymd').".txt","a+",$mode.'_'.$orderNo."\r\n");
                }
            }else{
                $retData['success']++;
            }
            $num++;
            //exit;
        }

        return $retData;
        //exit;
        
    }

    private function insertMember($resData,$postData){
        $user_detail=$this->session->all_userdata();
        foreach ($resData as $key => $value) {
            $phone = strip_tags($value['eoReceiverPhone']);            
            $phoneAry[$phone] = $value;
        }

        //彙整電話
        $pnum = 0;
        $skey = 0;
        foreach ($phoneAry as $phone => $value) {
            $membText[$pnum] .= $phone.' ';
            if(preg_match('/^9/', $phone)&&strlen($phone)==9){
                echo $this->Api_common->setFrontReturnMsg('901','鍵值資料錯誤，請檢查電話欄位(9)',['retHtml'=>'鍵值資料錯誤，請檢查電話欄位']);
                exit;
            }
            if($skey%500==0){
                $pnum++;
            }
            $skey++;
        }

        //檢查字串
        foreach ($membText as $key => $value) {
            if(strpos($value, '+')>0||strpos($value, '.')>0){
                echo $this->Api_common->setFrontReturnMsg('901','鍵值資料錯誤，請檢查電話欄位(+886)',['retHtml'=>'鍵值資料錯誤，請檢查電話欄位']);
                exit;
            }
        }
        
        //檢查會員編號是否已存在
        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","檢查會員是否已存在");
        foreach ($membText as $key => $value) {
            $ragicData = $this->Api_ragic->getRagicFullText('forms/1',$value,10);
            foreach ($ragicData as $key2 => $value2) {
                $phone = $ragicData[$key2]['連絡電話'];
                $membAry[$phone]['ragicId'] =  $ragicData[$key2]['_ragicId'];
                $membAry[$phone]['memberNo'] =  $ragicData[$key2]['會員流水編號'];
                unset($phoneAry[$phone]);
            }
        }

        $count = count($phoneAry);
        $num = 1;
        //不存在的寫入會員
        foreach ($phoneAry as $phone => $value) {            
            $remain = (($count-$num)*0.5/60);
            $minutes = round($remain,0);
            $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","會員資料寫入中...".$num."/".$count.'<br>剩餘時間: '.$minutes.'分以內');
            $insertData['1000002'] = '';
            $insertData['1000003'] = $value['eoReceiverName'];
            $insertData['1000005'] = $value['eoReceiverEmail'];
            $insertData['1000006'] = $value['eoReceiverPhone'];
            $insertData['1000010'] = $value['eoReceiverPostCode'];
            $insertData['1000011'] = $value['eoReceiverAddr'];
            $returnData = $this->Api_ragic->syncToRagic('forms/1',$insertData,['doDefaultValue'=>true,'doFormula'=>true]);
            if(!$returnData){
                $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_error_".date('Ymd').".txt","a+",$value['eoReceiverEmail'].'_'.$value['eoReceiverPhone']."\r\n");
            }
            $membAry[$phone]['ragicId'] =  $returnData['ragicId'];
            $membAry[$phone]['memberNo'] =  $returnData['data']['1000001'];//會員編號
            $num++;
        }

        $this->Api_common->saveData(DIR_SITE_FILE."temp/process_log/".$user_detail['empID']."_Manage_import_ragic.txt","w+","會員檢查完成");
        
        return $membAry;
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

    function autoProcess($source){
        //$postData['fileName-ship']
        if($source=='test'){
            $pathOld = 'D:/python/autoorder/export';
            $pathNew = 'D:/xampp/htdocs/hugePlusEC/uploads';
        }else if($source=='normal'){
            $pathOld = 'C:/tomcat-8075/webapps/webroot/WEB-INF/reportlets/hugePlusFR/order/export';
            $pathNew = 'C:/xampp/htdocs/hugePlusEC/uploads';
        }
        
        $fileAry = get_dir_file_info($pathOld);
        $index = 0;
        //從訂單下載資料夾搬移
        foreach ($fileAry as $fileName => $value) {
            copy($pathOld.'/'.$fileName, $pathNew.'/'.$fileName);
            unlink($pathOld.'/'.$fileName);
        }
        //整理資料
        $fileAry = get_dir_file_info($pathNew);
        foreach ($fileAry as $fileName => $value) {
            if(!preg_match('/xls/', $fileName)){continue;}
            $postData['fileName-ship'][0] = $fileName;
            $index++;
            $excelData = $this->Api_excel->readExcel($pathNew.'/'.$fileName);
            $find = false;
            if(count($excelData)==1){continue;}
            foreach ($excelData as $key => $value) {
                if($key>20){break;}
                if($find){break;}
                foreach ($excelData[$key] as $key => $value2) {
                    if(preg_match('/好菌家/', $value2)||preg_match('/晚安益生菌/', $value2)){
                        echo $fileName.'---'.$value2.'<br>';
                        $postData['runType'] = 'waca_好菌家';
                        $find = true;
                    }else if(preg_match('/黑松生技/', $value2)){
                        echo $fileName.'---'.$value2.'<br>';
                        $postData['runType'] = 'cyberbiz_黑松';
                        $find = true;
                    }else if(preg_match('/日研專科/', $value2)){
                        echo $fileName.'---'.$value2.'<br>';
                        $postData['runType'] = 'cyberbiz_日研專科';
                        $find = true;
                    }
                }
            }
            $this->Api_common->dataDump($postData);
            $result = $this->submit('preview',$postData);
            $result = $this->submit('upload',$postData);
        }
        $this->removeUploadFile();
    }

}
