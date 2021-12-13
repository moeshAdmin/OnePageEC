<?php

class Manage_crontab extends My_Controller {
    function __construct(){
        parent::__construct( strtolower(__CLASS__) );
        $this->load->model('Api_common');
        $this->load->model('Api_invoice');
        $this->load->model('Api_ragic');
        //$this->load->model('Users_auth');
        ini_set('memory_limit','2048M');
        define('ACCESS_KEY','79b741db8249f6d9f3daaa8b387fda8a');
        define('DOMAIN','jp-labo.com');
        define('CSR','MIICtzCCAZ8CAQAwcjELMAkGA1UEBhMCVFcxFDASBgNVBAMMC2pwLWxhYm8uY29t
MRQwEgYDVQQHDAtUYWlwZWkgQ2l0eTEQMA4GA1UECgwHanAtbGFibzEPMA0GA1UE
CAwGVGFpd2FuMRQwEgYDVQQLDAtqcC1sYWJvLmNvbTCCASIwDQYJKoZIhvcNAQEB
BQADggEPADCCAQoCggEBANbNWsW6828747PZu7gR3vbAvUPCrISMjxJiWNYspiNG
T77shRh3P1u/hbnk60s2mLEYRuWmlSk7lOxBpkgnzm40e/MJcQeqfPK3hw5PuK14
Sdk81pn6oOuyr0xSzrkvl+92r0eRwAbBSXToeH3OUSEyC4IE0wIzvhmx8iFJWkiV
ifwOSYWyIdt6ahE5x4mjo9UEYC8DUbk/LW8oODFBxna7UZCErgox6FtLyuW4H+Hl
NU7h2TbT1KHUrm2w4UhJzRDymz2LjXN3ODbLHgf4wk67LfhsB5eUmEyPWa93bHyz
kK1xyvCDQW7z52Ir2QDFBWio0LuEZ2he0aI1JK7NspUCAwEAAaAAMA0GCSqGSIb3
DQEBCwUAA4IBAQAON4FvOZGjAYaHeVTKLzlbt165V3tCgtdryr5K0SYvwkpsR+kI
VEJUzgxdohuBqkzn5qgkHh2yfGA9iViqzsIXcOJY8cS4tyRRrcMnek9nXEvOXxdh
EkdKLJANIwC927/R0UnT8Ox38ERTilwE3CgmlTJG6LxVm/osqnBwpZ6XlDDqgqlV
eV44OUfAY6VjUXGLSfYVDdN/tKR59HgfpsSs5c3I3+GzNKhFdnZpUyr/76Sh0qzn
MlSXTI+B9lHtt1wfshzkTGc/w61QzI/t0IpoSDbZe2MOTdcl2Zx81vEEyI/ODoED
b68qaYbvctPXNNuEzErCJlelXjjL6ODbWy0U');
    }

    // 主畫面
    function index(){
        $user_detail=$this->session->all_userdata();
    }

    //整批發送待發送信件
    function runSendMail(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->library('My_SendMail');
        $resData = $this->Api_common->getDataCustom('*','ec_mail','emStatus = "待發送" AND emSendDevice = "email"');
        //被撈出來的資料都先註記為已發送
        $this->db->where('emStatus', '待發送');
        $this->db->where('emSendDevice', 'email');
        $this->db->update('ec_mail', ['emStatus'=>'已發送']);
        $jobNum = count($resData);
        $finishNum = 0;
        foreach ($resData as $key => $value) {
            //寄給購買人
            $data = array(
                    'recipient'=>array($resData[$key]['emReceiver']),
                    'cc'=>'', 
                    'subject' => $resData[$key]['emSubject'], 
                    'content' => $resData[$key]['emContent'],
                    'sender'=>MAIL_CONFIG['senderName']); 
            $result = $this->my_sendmail->sendOut($data);
            //再次註記發送時間
            if(preg_match('/Success/', $result)){
                $this->db->where('emSysID', $resData[$key]['emSysID']);
                $this->db->where('emSendDevice', 'email');
                $this->db->update('ec_mail', ['emStatus'=>'已發送','emSendTime'=>date('Y-m-d H:i:s')]);
                $finishNum++;
            }
            sleep(1);
        }
        if($finishNum>0){
            $this->Api_ragic->saveLog('整批發送信件',$jobNum,$finishNum,$startTime);
        }
        
    }

    //整批發送待發送信件
    function runSendSMS(){
        $this->load->library('My_SendMail');
        $startTime = date('Y-m-d H:i:s');
        $resData = $this->Api_common->getDataCustom('*','ec_mail','emStatus = "待發送" AND emSendDevice = "sms"');
        //被撈出來的資料都先註記為已發送
        $this->db->where('emStatus', '待發送');
        $this->db->where('emSendDevice', 'sms');
        $this->db->update('ec_mail', ['emStatus'=>'已發送']);
        $jobNum = count($resData);
        $finishNum = 0;

        $url = SMS_API_URL;
        $postData['username'] = SMS_API_USERNAME;
        $postData['password'] = SMS_API_PASSWORD;
        $postData['method'] = '1';
        foreach ($resData as $key => $value) {
            if(!preg_match('/^09/', $value['emReceiver'])){continue;}
            if($value['emSource']){
                if(preg_match('/黑松/', $value['emSource'])){
                    $postData['username'] = SMS_BATCH_USERNAME_HEY;
                    $postData['password'] = SMS_BATCH_PASSWORD_HEY;
                }else if(preg_match('/日研/', $value['emSource'])){
                    $postData['username'] = SMS_BATCH_USERNAME_JP;
                    $postData['password'] = SMS_BATCH_PASSWORD_JP;
                }else if(preg_match('/好菌家/', $value['emSource'])){
                    $postData['username'] = SMS_BATCH_USERNAME_PRO;
                    $postData['password'] = SMS_BATCH_PASSWORD_PRO;
                }
            }
            $postData['sms_msg'] = $resData[$key]['emSubject'];
            $postData['phone'] = $resData[$key]['emReceiver'];

            $result = json_decode($this->Api_common->basicCurl($url, $ckfile, $postData),true);

            //再次註記發送時間
            if($result['error_code']=='000'){
                $this->db->where('emSysID', $resData[$key]['emSysID']);
                $this->db->where('emSendDevice', 'sms');
                $this->db->update('ec_mail', ['emStatus'=>'已發送','emSendTime'=>date('Y-m-d H:i:s')]);
                $finishNum++;
            }else{
                $this->db->where('emSysID', $resData[$key]['emSysID']);
                $this->db->where('emSendDevice', 'sms');
                $this->db->update('ec_mail', ['emStatus'=>'待發送']);
                $finishNum++;
                $data = array('recipient'=>['peter@pro-duction.com.tw'],'cc'=>'', 'subject' => mb_convert_encoding('簡訊發送失敗', "UTF-8","auto"), 'content' => $result['error_code'],'sender'=>'Service');   
                $this->my_sendmail->sendOut($data);
            }
            sleep(1);
        }
        if($finishNum>0){
            $this->Api_ragic->saveLog('整批發送SMS',$jobNum,$finishNum,$startTime);
        }
        
    }

    //定時移除session
    function removeSessionFile(){
        $this->load->helper('file');
        $fileAry = get_dir_file_info(APPPATH.'files/temp');
        foreach ($fileAry as $fileName => $value) {            
            if(preg_match('/cart_session/si', $fileName)){
                unlink(APPPATH.'files/temp/'.$fileName);
            }            
        }
    }

    //定時移除session
    function removeCache(){
        $this->load->helper('file');
        $fileAry = get_dir_file_info(APPPATH.'files/cache');
        foreach ($fileAry as $fileName => $value) {            
            if(preg_match('/curl|ragic/si', $fileName)){
                unlink(APPPATH.'files/cache/'.$fileName);
            }            
        }
    }

    //整批開立發票
    function confirmInvoice(){
        $this->Api_invoice->confirmInvoice();
    }

    //訂單資料同步
    function syncOrderDataToRagic(){
        $startTime = date('Y-m-d H:i:s');
        $data['date_from'] = date('Y-m-d 00:00:00',strtotime(date('Y-m-d'))-86400*1);
        $data['date_to'] = date('Y-m-d').' 23:59:59';

        //載入訂單主檔
        $orderData = $this->Api_common->getDataCustom('*','ec_order','eoUpdateDTime BETWEEN "'.$data['date_from'].'" AND "'.$data['date_to'].'"','eoUpdateDTime DESC');
        foreach ($orderData as $key => $value) {
            //列出所有會員查詢，確保會員編號不缺漏
            $phone = strip_tags($value['eoReceiverPhone']);
            $orderNo = strip_tags($value['eoOrderNo']);
            $phoneAry[$phone] = $value;
            $orderAry[$orderNo] = $value;
            $orderList[$key] = $orderNo;
        }
        //載入訂單子檔
        $orderDetailData = $this->Api_common->getDataInCustom('*','ec_order_detail','eodOrderNo',$orderList,'none','in');
        foreach ($orderDetailData as $key => $value) {
            $orderNo = strip_tags($value['eodOrderNo']);
            $orderAry[$orderNo]['detail'][$key] = $orderAry[$orderNo];
            $orderAry[$orderNo]['detail'][$key]['eoItemNo'] = $value['eodItemNo'];//商品編號
            $orderAry[$orderNo]['detail'][$key]['eoItemName'] = $value['eodItemName'];//商品名稱
            $orderAry[$orderNo]['detail'][$key]['eoItemSKU'] = $value['eodItemSKU'];//商品規格編號
            $orderAry[$orderNo]['detail'][$key]['eoItemType'] = $value['eodItemType'];//商品規格
            $orderAry[$orderNo]['detail'][$key]['eoItemPrice'] = $value['eodItemPrice'];//商品金額
            $orderAry[$orderNo]['detail'][$key]['eoItemQty'] = $value['eodItemQty'];//數量
            $orderAry[$orderNo]['detail'][$key]['eoItemSubTotal'] = $value['eodItemSubTotal'];//小計
            unset($orderAry[$orderNo]['detail'][$key]['detail']);
        }
        
        //彙整電話
        $pnum = 0;
        $skey = 0;
        foreach ($phoneAry as $phone => $value) {
            $searchText[$pnum] .= $phone.' ';
            if($skey%100==0){
                $pnum++;
            }
            $skey++;
        }

        /* 新訂單建檔 */
        //識別會員是否已存在 已存在會員先存起來
        foreach ($searchText as $key2 => $phoneText) {
            $membData = $this->Api_ragic->getRagicFullText('forms/1',$phoneText,10);
            foreach ($membData as $key => $value) {
                //if(!$membData[$key]['信箱']){continue;}
                $phone = $membData[$key]['連絡電話'];
                $membAry[$phone]['ragicId'] =  $membData[$key]['_ragicId'];
                $membAry[$phone]['membID'] =  $membData[$key]['會員流水編號'];
            }
        }

        //彙整訂單
        $pnum = 0;
        $skey = 0;
        foreach ($orderAry as $orderNo => $value) {
            $searchText2[$pnum] .= SITE_NAME.'-'.$orderNo.' ';
            if($skey%50==0){
                $pnum++;
            }
            $skey++;
        }

        foreach ($searchText2 as $key2 => $orderText) {
            $ordData = $this->Api_ragic->getRagicFullText('forms/2',$orderText,10);
            foreach ($ordData as $key => $value) {
                //if(!$membData[$key]['信箱']){continue;}
                $ord = $ordData[$key]['訂單流水編號'];
                $orderAry[$ord]['ragicId'] =  $ordData[$key]['_ragicId'];
                $orderAry[$ord]['membID'] =  $ordData[$key]['訂單流水編號'];
            }
        }

        //先寫入會員 再寫入訂單
        foreach ($orderData as $key => $value) {
            unset($updateMembData);
            unset($updateOrderData);
            unset($orderInsertData);
            //if($value['eoRagicID']){continue;}//如果訂單已經建檔了就跳過 後面更新循環再處理
            if($membAry[$value['eoReceiverPhone']]){
                //既有會員 留下會員編號 等待寫入到訂單
                $orderInsertData['1000023'] = $membAry[$value['eoReceiverPhone']]['membID'];//會員編號
            }else{
                //非會員 寫入資料庫 拿到會員編號
                $insertData['1000002'] = '';
                $insertData['1000003'] = $value['eoReceiverName'];
                $insertData['1000005'] = $value['eoReceiverEmail'];
                $insertData['1000006'] = $value['eoReceiverPhone'];
                $returnData = $this->Api_ragic->syncToRagic('forms/1',$insertData,['doDefaultValue'=>true,'doFormula'=>true]);
                //留下會員編號 等待寫入到訂單
                //更新會員本身的RagicID
                $orderInsertData['1000023'] = $returnData['data']['1000001'];//會員編號
                $updateMembData['emRagicID'] = $returnData['ragicId'];
                //迴圈中建檔的更新到陣列中，供當日重複訂單查詢時不重複建檔
                $phone = $value['eoReceiverPhone'];
                $membAry[$phone]['ragicId'] = $returnData['ragicId'];
                $membAry[$phone]['membID'] = $returnData['data']['1000001'];
            }
            //訂單子表格
            $detail['multiItem'] = 'Y';
            $detail['itemData'] = $orderAry[$value['eoOrderNo']]['detail'];
            $orderInsertData = $this->Api_ragic->transSqlDataToRagicField('insert',$orderInsertData,$value,$detail);
            if(!$orderInsertData['1000023']){echo 'error_1!';
            //echo $value['eoReceiverPhone'];
            //$this->Api_common->dataDump($membAry);
            exit;}

            //更新會員
            if($updateMembData){
                $this->db->where('emPhone', $value['eoReceiverPhone']);
                $this->db->update('ec_member', $updateMembData);
            }

            if(!$orderAry[$value['eoOrderNo']]['ragicId']){
                $returnData = $this->Api_ragic->syncToRagic('forms/2',$orderInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
                $updateOrderData['eoRagicID'] = $returnData['ragicId'];
                //更新訂單
                $this->db->where('eoSysID', $value['eoSysID']);
                $this->db->update('ec_order', $updateOrderData);
                unset($orderData[$key]);
            }
        }


        /* 已存在訂單建檔 */
        foreach ($orderData as $key => $value) {
            unset($updateMembData);
            unset($updateOrderData);
            unset($orderInsertData);

            $orderInsertData['1000023'] = $membAry[$value['eoReceiverPhone']]['membID'];//會員編號
            $ordNo = $value['eoOrderNo'];
            $ragicId = $orderAry[$ordNo]['ragicId'];
            //訂單子表格
            $detail['multiItem'] = 'Y';
            $detail['itemData'] = $orderAry[$ordNo]['detail'];
            $orderInsertData = $this->Api_ragic->transSqlDataToRagicField('update',$orderInsertData,$value,$detail);
            if(!$orderInsertData['1000023']){echo 'error_2!';
            //$this->Api_common->dataDump($membAry);
            exit;}
            
            $returnData = $this->Api_ragic->syncToRagic('forms/2/'.$ragicId,$orderInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
            //$this->Api_common->dataDump($returnData);
            //exit;
            $updateOrderData['eoRagicID'] = $ragicId;
            $updateOrderData['eoUpdateDTime'] = date('Y-m-d H:i:s');
            $updateOrderData['eoUpdateEmpName'] = 'Server';

            //更新訂單同步時間
            $this->db->where('eoOrderNo', $ordNo);
            $this->db->update('ec_order', $updateOrderData);
        }

        $jobNum = count($orderAry);
        $finishNum = count($finishNum);
        if($finishNum>0){
            $this->Api_ragic->saveLog('一頁式訂單建檔',$jobNum,$finishNum,$startTime);
        }
        echo 'done';
    }

    //ARS合約資料同步
    function syncARSDataToRagic(){
        
        $startTime = date('Y-m-d H:i:s');
        $data['date_from'] = date('Y-m-d H:i:s',strtotime(date('Y-m-d').' 00:00:00')-86400*5);
        $data['date_to'] = date('Y-m-d').' 23:59:59';

        $this->load->model('Api_ragic');
        $arsData = $this->Api_common->getDataCustom('*','ec_order_ars','eaUpdateDTime BETWEEN "'.$data['date_from'].'" AND "'.$data['date_to'].'"','eaUpdateDTime DESC');

        foreach ($arsData as $key => $value) {
            $arsInsertData['1000342'] = $value['eaARSOrderNo'];//合約編號
            $arsInsertData['1000343'] = $value['eaARSStatus'];//合約狀態

            $arsInsertData['1000344'] = $value['eaARSPeriodType'];//配送類型
            $arsInsertData['1000345'] = $value['eaARSFreq'];//配送週期
            $arsInsertData['1000346'] = $value['eaARSPeriods'];//已配送期數
            $arsInsertData['1000347'] = $value['eaARSPeriodsTotal'];//應配送期數

            $arsInsertData['1000348'] = $value['eaLastDeliverDate'];//上次配送日
            $arsInsertData['1000349'] = $value['eaNextDeliverDate'];//下次配送日

            if(!$value['eaARSRagicID']){
                //新合約
                $returnData = $this->Api_ragic->syncToRagic('forms/8',$arsInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
                $updateARSData['eaARSRagicID'] = $returnData['ragicId'];
                //更新訂單
                $this->db->where('eaSysID', $value['eaSysID']);
                $this->db->update('ec_order_ars', $updateARSData);
            }else{
                //已存在合約
                $returnData = $this->Api_ragic->syncToRagic('forms/8/'.$value['eaARSRagicID'],$arsInsertData,['doDefaultValue'=>true,'doFormula'=>true,'doLinkLoad'=>true]);
            }
            $finishNum++;
        }

        $jobNum = count($arsData);

        if($finishNum>0){
            $this->Api_ragic->saveLog('ARS合約建檔',$jobNum,$finishNum,$startTime);
        }
        echo 'done';
    }

    function rebuildInventory(){
        $this->load->model('Api_ec');
        $this->Api_ec->rebuildInventory();
    }

    function processSSL($action=null){

        $access_key = ACCESS_KEY;
        $domain = DOMAIN;

        if($action=='create'){
            $url = 'https://api.zerossl.com/certificates?access_key='.$access_key;
            $postData['certificate_domains'] = $domain;
            $postData['certificate_csr'] = CSR;
            $postData['certificate_validity_days'] = '90';
            $result = json_decode($this->Api_common->getCurl($url,$postData),true);
            $this->Api_common->dataDump($result);
        }else if($action=='verify'){
            //查詢domain資料
            $url = 'https://api.zerossl.com/certificates?access_key='.$access_key.'&certificate_status=draft';
            $result = json_decode($this->Api_common->getCurl($url,$postData),true);
            foreach ($result['results'] as $key => $value) {
                $domain = $result['results'][$key]['common_name'];
                $id = $result['results'][$key]['id'];
                $retData = $result['results'][$key]['validation']['other_methods'][$domain];
                $fileName = str_replace('https://'.$domain.'/.well-known/pki-validation/', '', $retData['file_validation_url_https']);
                $filePath = str_replace('application'.DIRECTORY_SEPARATOR, '.well-known'.DIRECTORY_SEPARATOR.'pki-validation'.DIRECTORY_SEPARATOR.$fileName, APPPATH);
                foreach ($retData['file_validation_content'] as $key2 => $value2) {
                    $fileContent .= $value2."\r\n";
                }
                $this->Api_common->saveData($filePath,'w+',$fileContent);
                
                //發送驗證要求
                $result2 = json_decode($this->Api_common->getCurl('https://api.zerossl.com/certificates/'.$id.'/challenges?access_key='.$access_key,['validation_method'=>'HTTPS_CSR_HASH']),true);
                $this->Api_common->dataDump($result2);
            }
        }else if($action=='renew'){
            //查詢domain資料
            $url = 'https://api.zerossl.com/certificates?access_key='.$access_key.'&certificate_status=issued';
            $result = json_decode($this->Api_common->getCurl($url,$postData),true);

            foreach ($result['results'] as $key => $value) {
                $domain = $result['results'][$key]['common_name'];
                $id = $result['results'][$key]['id'];
                $validDay = (strtotime($result['results'][$key]['expires'])-strtotime(date('Y-m-d')))/86400;
                //排除接近過期資料
                if($validDay<80){continue;}

                //備份檔案
                $this->backupSSL();
                
                //下載憑證
                $sslResult = json_decode($this->Api_common->getCurl('https://api.zerossl.com/certificates/'.$id.'/download/return?access_key='.$access_key,null),true);
                $certificate = $sslResult['certificate.crt'];
                $ca_bundle = $sslResult['ca_bundle.crt'];
                $chained = $certificate."\r\n".$ca_bundle;
                $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/certificate.crt','w+',$certificate);
                $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/ca_bundle.crt','w+',$ca_bundle);
                $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/'.$domain.'.chained.crt','w+',$chained);
                
                exec('nginx -s reload', $out);var_dump($out);
                $this->load->library('My_SendMail');
                //寄給表單填寫人
                $data = array(
                        'recipient'=>array(SYS_MAILER),
                        'cc'=>'', 
                        'subject' => $domain.' SSL Renew', 
                        'content' => $out,
                        'sender'=>MAIL_CONFIG['senderName']); 
                $this->my_sendmail->sendOut($data);
            }
        }
    }

    //每日大量執行作業 (本地端限定，GCP不執行)
    function dailyJob(){
        //檢查首購資訊 (以有最後購買日有更新的會員為基礎)
        $this->doCountIsFirstOrder();
        //寫入正貨商品資訊(正貨欄位無資料)(舊版未轉換商品，棄用)
        //$this->doItemAnalysis();
        //寫入正貨商品資訊(正貨欄位無資料)
        $this->doTrueItemDataTrans();
        //購買次統計
        $this->doCountOrderTime('訂單購買次更新');
        $this->doCountOrderTime('退貨重計購買次');
        //執行發票折讓
        $this->doInvoiceRet();
    }

    function setReport($type,$source){
        if($source=='hey'){
            $postData['param']['source'] = urlencode('黑松');
            $postData['recipient'] = ['doris@pro-duction.com.tw','peter@pro-duction.com.tw','shannon@pro-duction.com.tw'];
        }else if($source=='well'){
            $postData['param']['source'] = urlencode('好菌家');
            $postData['recipient'] = ['luci@pro-duction.com.tw','sophia@pro-duction.com.tw','peter@pro-duction.com.tw','shannon@pro-duction.com.tw'];
        }else if($source=='jp'){
            $postData['param']['source'] = urlencode('日研專科');
            $postData['recipient'] = ['doris@pro-duction.com.tw','nina@pro-duction.com.tw','peter@pro-duction.com.tw','dicky0411@gmail.com','shannon@pro-duction.com.tw'];
        }

        //$postData['recipient'] = ['peter@pro-duction.com.tw'];

        if($type=='w'){
            $postData['param']['date_from'] = date('Y-m-01');
            $postData['param']['date_to'] = date('Y-m-t',strtotime(date('Y-m-d')));
            $postData['param']['loadCache'] = 'true';
            $postData['fileName'] = urldecode($postData['param']['source']).'周報表_'.date('Y-m-d');
            $postData['reportlet'] = 'hugePlusFR/周報表.cpt';
            $postData['mailSubject'] = '['.date('Y-m-d').'] '.urldecode($postData['param']['source']).'周報表';
            $this->sendReport($postData);
        }else if($type=='y'){
            $postData['param']['date_from'] = '2020-01-01';
            $postData['param']['date_to'] = date('Y-m-t',strtotime(date('Y-m-d')));
            $postData['param']['loadCache'] = 'true';
            $postData['fileName'] = urldecode($postData['param']['source']).'年報表_至'.$postData['param']['date_to'];
            $postData['reportlet'] = 'hugePlusFR/年報表.cpt';
            $postData['mailSubject'] = '['.date('Y-m-d').'] '.urldecode($postData['param']['source']).'年報表';
            $this->sendReport($postData);
        }else if($type=='last_month_week'){
            $postData['param']['date_from'] = date('Y-m-01',(strtotime(date('Y-m-d'))-86400*5));
            $postData['param']['date_to'] = date('Y-m-t',(strtotime(date('Y-m-d'))-86400*5));
            $postData['param']['loadCache'] = 'true';
            $postData['fileName'] = urldecode($postData['param']['source']).'結算周報表_'.date('Y-m',strtotime($postData['param']['date_from']));
            $postData['reportlet'] = 'hugePlusFR/周報表.cpt';
            $postData['mailSubject'] = '['.date('Y-m',strtotime($postData['param']['date_from'])).'] '.urldecode($postData['param']['source']).'結算周報表';
            $this->sendReport($postData);
        }else if($type=='last_month_year'){
            $postData['param']['date_from'] = '2020-01-01';
            $postData['param']['date_to'] = date('Y-m-t',(strtotime(date('Y-m-d'))-86400*5));
            $postData['param']['loadCache'] = 'true';
            $postData['fileName'] = urldecode($postData['param']['source']).'結算年報表_'.date('Y-m',strtotime($postData['param']['date_from']));
            $postData['reportlet'] = 'hugePlusFR/年報表.cpt';
            $postData['mailSubject'] = '['.date('Y-m',strtotime($postData['param']['date_from'])).'] '.urldecode($postData['param']['source']).'結算年報表';
            $this->sendReport($postData);
        }
        //$this->Api_common->dataDump($postData);
        
        echo 'done';  
    }

    private function sendReport($postData){
        $this->load->library('My_SendMail');
        $this->load->model('Api_finereport');

        $postData['type'] = 'excel';

        $output = $this->Api_finereport->cptConvert($postData);

        $filePath = DIR_SITE_FILE.'cache/'.$postData['fileName'].'.xlsx';
        $fp = fopen($filePath,'w+');
        fwrite($fp,$output);
        fclose($fp);

        $imguri = base64_encode($output);


        $data = array('recipient'=>$postData['recipient'],'cc'=>'', 'subject' => mb_convert_encoding($postData['mailSubject'], "UTF-8","auto"), 'content' => $mailContent,'sender'=>'Service','attachment'=>$filePath);   
        $this->my_sendmail->sendOut($data);
    
    } 

    private function backupSSL(){
        $this->load->helper('file');
        $domain = DOMAIN;
        $certificate = read_file(DIR_SITE_FILE.'ssl/'.$domain.'/certificate.crt');
        $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/certificate.crt_'.date('Ymd'),'w+',$certificate);
        $ca_bundle = read_file(DIR_SITE_FILE.'ssl/'.$domain.'/ca_bundle.crt');
        $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/ca_bundle.crt_'.date('Ymd'),'w+',$ca_bundle);
        $chained = read_file(DIR_SITE_FILE.'ssl/'.$domain.'/'.$domain.'.chained.crt');
        $this->Api_common->saveData(DIR_SITE_FILE.'ssl/'.$domain.'/'.$domain.'.chained.crt_'.date('Ymd'),'w+',$chained);
    }

    //如果訂單為首購且已退貨
    private function doRewriteReturn(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->model('Api_ragic');
        $result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000033,eq,Y&where=1000158,eq,Y'.'&limit=0,10000', $ckfile),true);
        $finishNum = 0;
        foreach ($result as $key => $value) {
            if($value['是否首購']=='Y'&&$value['是否退貨']=='Y'){
                $insertData['1000158'] = 'N';
                $insertData['1000401'] = '';
                $insertData['1000402'] = '';
                $returnData = $this->Api_ragic->syncToRagic('forms/2/'.$value['_ragicId'],$insertData);
                $finishNum++;
            }
        }

        $jobNum = count($result);
        $this->Api_ragic->saveLog('解除已退貨首購訂單',$jobNum,$finishNum,$startTime);
    }
    //寫入正貨商品資訊(正貨欄位無資料)
    private function doItemAnalysis(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->model('Api_ragic');
        $itemData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicinventory/20002?where=1000140,eq,Y'.'&limit=0,10000', $ckfile),true);
        foreach ($itemData as $key => $value) {
            if(!$value['關鍵字']){continue;}
            $itemPreg[$value['商品編號']]['關鍵字'] = $value['關鍵字'];
            $itemPreg[$value['商品編號']]['入別'] = $value['入別'];
            $itemPreg[$value['商品編號']]['商品編號'] = $value['商品編號'];
        }
        //$this->Api_common->dataDump($itemPreg);
        $result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000310,eq,&limit=0,100000', $ckfile),true);
        foreach ($result as $key => $value) {
            # code..._subtable_1000061
            foreach ($value['_subtable_1000061'] as $key2 => $value2) {
                if($value2['小計']<200){continue;}
                foreach ($itemPreg as $key3 => $value3) {
                    $preg = '/'.$value3['關鍵字'].'/';
                    $preg2 = '/'.$value3['入別'].'/';
                    $preg3 = '/'.$value3['商品編號'].'/';
                    if($value3['關鍵字']&&$value3['入別']){
                        if(preg_match('/暢酵/', $value2['商品名稱'])&&preg_match($preg3, $value2['商品編號'])&&preg_match($preg, $value2['商品名稱'])&&preg_match($preg2, $value2['商品規格'])){
                            echo $key.'----'.$value2['商品名稱'].'--'.$value3.'--'.$value2['小計']."\r\n".PHP_EOL;
                            $orderData[$key]['正貨商品'] .= $value3['關鍵字'].'#'.$value3['入別'].'#'.$value2['數量'].'$'.$value2['小計'].';';
                        }else if((preg_match($preg, $value2['商品名稱'])&&preg_match($preg2, $value2['商品名稱']))||(preg_match($preg, $value2['商品規格'])&&preg_match($preg2, $value2['商品規格']))){
                            if(!preg_match('/暢酵/', $value2['商品名稱'])){
                                echo $key.'----'.$value2['商品名稱'].'--'.$value3.'--'.$value2['小計']."\r\n".PHP_EOL;
                                $orderData[$key]['正貨商品'] .= $value3['關鍵字'].'#'.$value3['入別'].'#'.$value2['數量'].'$'.$value2['小計'].';';
                            }
                            
                        }
                    }else if($value3['關鍵字']){
                        if(preg_match($preg, $value2['商品名稱'])||preg_match($preg, $value2['商品規格'])){
                            echo $key.'----'.$value2['商品名稱'].'--'.$value3.'--'.$value2['小計']."\r\n".PHP_EOL;
                            $orderData[$key]['正貨商品'] .= $value3['關鍵字'].'#'.$value3['入別'].'#'.$value2['數量'].'$'.$value2['小計'].';';
                        }
                    }
                    
                }
            }
        }

        $finishNum = 0;
        foreach ($orderData as $key => $value) {
            $insertData['1000310'] = $value['正貨商品'];
            echo $key.'--'.$value['正貨商品']."\r\n".PHP_EOL;
            $returnData = $this->Api_ragic->syncToRagic('forms/2/'.$key,$insertData);
            $finishNum++;
        }

        $jobNum = count($orderData);
        $this->Api_ragic->saveLog('正貨商品判斷',$jobNum,$finishNum,$startTime);
    }
    //檢查首購資訊 (以有最後購買日有更新的會員為基礎)
    private function doCountIsFirstOrder(){
        $startTime = date('Y-m-d H:i:s');
        $startDate = date('Y-m-d H:i:s',(strtotime(date('Y-m-d H:i:s'))-86400*30));
        $this->load->model('Api_ragic');
        $this->doRewriteReturn();
        $result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/1?where=1000160,gte,'.urlencode($startDate).'&limit=0,10000', $ckfile),true);
        //$result = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/1?where=1000017,eq,Y&limit=0,20000', $ckfile),true);
        //echo 'aaa';
        //exit;
        foreach ($result as $key => $value) {
            foreach ($result[$key]['_subtable_1000060'] as $key2 => $value2) {
                if($value2['來源名稱']=='日研專科'){
                    $pkey = $value2['購買日期'].'_'.substr($value2['訂單編號'], -4);
                }else{
                    $pkey = $value2['購買日期'].'_'.substr($value2['訂單編號'], -6);
                }                
                if($value2['是否退貨']=='N'){
                    $orderAry[$value['會員流水編號']][$value2['來源名稱']][$pkey] = $value2;
                }
                ksort($orderAry[$value['會員流水編號']][$value2['來源名稱']]);
            }
        }
        foreach ($orderAry as $membID => $value) {
            foreach ($orderAry[$membID] as $fromEC => $value2) {
                    $first[$membID.$fromEC] = array_shift($value2);
            }
        }
        $finishNum = 0;
        foreach ($first as $key => $value) {
            if($value['是否首購']!='Y'&&$value['是否退貨']=='N'&&$value['是否ARS']=='N'){
                $insertData['1000158'] = 'Y';
                $returnData = $this->Api_ragic->syncToRagic('forms/2/'.$value['_ragicId'],$insertData);
                echo $value['_ragicId']."\r\n".PHP_EOL;
                $finishNum++;
            }
        }
        
        $jobNum = count($first);
        $this->Api_ragic->saveLog('首購判斷',$jobNum,$finishNum,$startTime);
        echo 'ok';
    }

    private function getItemNumTrans(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->model('Api_ragic');
        
        //正貨商品
        $itemData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicinventory/20002?where=1000140,eq,Y'.'&limit=0,10000', $ckfile),true);
        foreach ($itemData as $key => $value) {
            if(!$value['關鍵字']){continue;}
            $itemPreg[$value['商品編號']]['關鍵字'] = $value['關鍵字'];
            $itemPreg[$value['商品編號']]['入別'] = $value['入別'];
            $itemPreg[$value['商品編號']]['商品編號'] = $value['商品編號'];
            $itemPreg[$value['商品編號']]['數量'] = 1;
            $trueItemList[$value['商品編號']]['正貨商品'][0] = $itemPreg[$value['商品編號']];
        }

        //轉換表
        $transData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicinventory/1?limit=0,10000', $ckfile),true);
        foreach ($transData as $key => $value) {
            if(!$value['商品規格編號']){continue;}
            if($newItemData[$value['商品規格編號']]){continue;}
            $transPreg[$value['商品規格編號']]['對應通路'] = $value['對應通路'];
            $transPreg[$value['商品規格編號']]['商品規格名稱'] = $value['商品規格名稱'];
            $transPreg[$value['商品規格編號']]['商品規格編號'] = $value['商品規格編號'];
            
            foreach ($value['_subtable_1000138'] as $key2 => $value2) {
                if($itemPreg[$value2['對應料號']]['關鍵字']){
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['關鍵字'] = $itemPreg[$value2['對應料號']]['關鍵字'];
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['入別'] = $itemPreg[$value2['對應料號']]['入別'];
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['對應料號'] = $value2['對應料號'];
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['名稱'] = $value2['名稱'];
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['數量'] = $value2['數量'];
                    $transPreg[$value['商品規格編號']]['正貨商品'][$key2]['均攤售價'] = $value2['均攤售價'];
                }
                
                /*
                foreach ($itemPreg as $key3 => $value3) {
                    $preg = '/'.$value3['關鍵字'].'/';
                    $preg2 = '/'.$value3['入別'].'/';
                    $preg3 = '/'.$value3['商品編號'].'/';
                    //echo $value2['對應料號'].'----'.$value3['商品編號'].'<br>';
                    if($value3['關鍵字']&&$value3['入別']){
                        if(preg_match('/暢酵/', $value2['名稱'])&&preg_match($preg3, $value2['對應料號'])&&preg_match($preg, $value2['名稱'])&&preg_match($preg2, $value2['名稱'])){
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3] = $value3;
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['數量'] = $value2['數量'];
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['均攤售價'] = $value2['均攤售價'];
                        }else if($value2['對應料號']==$value3['商品編號']||(preg_match($preg, $value2['名稱'])&&preg_match($preg2, $value2['名稱']))){
                            if(!preg_match('/暢酵/', $value2['名稱'])){
                                $transPreg[$value['商品規格編號']]['正貨商品'][$key3] = $value3;
                                $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['數量'] = $value2['數量'];
                                $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['均攤售價'] = $value2['均攤售價'];
                            }
                        }else if($value2['對應料號']==$value3['商品編號']){
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3] = $value3;
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['數量'] = $value2['數量'];
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['均攤售價'] = $value2['均攤售價'];
                        }
                    }else if($value3['關鍵字']){
                        if($value2['對應料號']==$value3['商品編號']||preg_match($preg, $value2['名稱'])){
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3] = $value3;
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['數量'] = $value2['數量'];
                            $transPreg[$value['商品規格編號']]['正貨商品'][$key3]['均攤售價'] = $value2['均攤售價'];
                        }
                    }
                    
                }*/
            }
            $newItemData[$value['商品規格編號']]['正貨商品'] = $transPreg[$value['商品規格編號']]['正貨商品'];
            $newItemData[$value['商品規格編號']]['對應通路'] = $value['對應通路'];
            $newItemData[$value['商品規格編號']]['商品規格名稱'] = $value['商品規格名稱'];
            $newItemData[$value['商品規格編號']]['商品規格編號'] = $value['商品規格編號'];
        }

        $retData['itemNumTrans'] = $newItemData;
        $retData['trueItemList'] = $trueItemList;
        return $retData;

        
    }
    //寫入正貨商品資訊(正貨欄位無資料)
    function doTrueItemDataTrans(){
        $resData = $this->getItemNumTrans();
        $itemNumTrans = $resData['itemNumTrans'];
        $trueItemList = $resData['trueItemList'];
        //$this->Api_common->dataDump($trueItemList);
        //$this->Api_common->dataDump($itemNumTrans);
        //exit;
        //debug大量更新使用
        //$orderData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,2020/01/01&where=1000029,lte,2020/06/30&limit=0,100000', $ckfile),true);
        //$orderData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms2/3?where=1000029,gte,2020/07/01&where=1000029,lte,2020/12/31&limit=0,100000', $ckfile),true);

        //一般新資料使用
        $orderData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000368,eq,N&limit=0,100000', $ckfile),true);
        
        foreach ($orderData as $key => $value) {
            foreach ($value['_subtable_1000061'] as $key2 => $value2) {             
                $orderDataNew[$key]['原正貨商品'] = $value2['正貨商品'];   
                $orderDataNew[$key]['原商品組'][$key2]['商品名稱'] = $value2['商品名稱'].$value2['商品規格'];
                $orderDataNew[$key]['原商品組'][$key2]['商品名稱'] = $value2['商品名稱'].$value2['商品規格'];
                $orderDataNew[$key]['原商品組'][$key2]['商品規格編號'] = $value2['商品規格編號'];
                $orderDataNew[$key]['原商品組'][$key2]['商品編號'] = $value2['商品編號'];
                $orderDataNew[$key]['原商品組'][$key2]['組數'] = $value2['數量'];

                //轉換表比對
                if($itemNumTrans[$value2['商品規格編號']]){
                    $value3 = $itemNumTrans[$value2['商品規格編號']]['正貨商品'];
                }else if($itemNumTrans[$value2['商品編號']]){
                    $value3 = $itemNumTrans[$value2['商品編號']]['正貨商品'];
                //正貨列表比對
                }else if($trueItemList[$value2['商品規格編號']]){
                    $value3 = $trueItemList[$value2['商品規格編號']]['正貨商品'];
                }else if($trueItemList[$value2['商品編號']]){
                    $value3 = $trueItemList[$value2['商品編號']]['正貨商品'];
                }else{
                    $value3 = '';
                }
                //商品小計
                if($value2['小計']>0){
                    $orderDataNew[$key]['商品總金額'] += $value2['小計'];
                }
                
                if(is_array($value3)){
                    foreach ($value3 as $key4 => $value4) {
                        //$orderDataNew[$key][$key2]['正貨商品'] = $value4['關鍵字'].'#'.$value4['入別'].'#'.($value4['數量']*$value2['數量']).'$'.$value2['小計'].';';
                        $itemKey = $value4['關鍵字'].'#'.$value4['入別'].'#';
                        $orderDataNew[$key]['商品組'][$itemKey]['數量'] += ($value2['數量']*$value4['數量']);
                        if(count($value3)==1){
                            $orderDataNew[$key]['商品組'][$itemKey]['金額'] += ($value2['小計']);
                        }else if($value2['小計']>0){
                            $orderDataNew[$key]['商品組'][$itemKey]['金額'] += ($value2['數量']*$value4['均攤售價']);
                        }else{
                            $orderDataNew[$key]['商品組'][$itemKey]['金額'] += ($value2['小計']);
                        }
                    }
                }else{
                    if($value2['小計']<200){
                        unset($orderDataNew[$key]['原商品組'][$key2]);
                    }else{
                        if(!preg_match('/promotionsN|coupon/', $value2['商品規格編號'].$value2['商品編號'])){
                            $orderDataNew[$key]['原商品組'][$key2]['正貨商品'] = '???';
                            $orderDataNew[$key]['未轉換'] = 'Y';
                            $orderDataNew[$key]['未轉換名稱'] = $value2['商品名稱'].$value2['商品規格'];
                            $orderDataNew[$key]['未轉換ID'] = $value2['_ragicId'];
                            
                            //echo $value2['商品名稱'].'---'.$value2['商品規格編號'].'---'.$value2['商品編號'].'---'.$value2['數量'].'---'.$value2['小計'].'<br>';
                        }
                    }

                }

            }
        }

        //最終整理
        foreach ($orderDataNew as $key => $value) {
            foreach ($value['商品組'] as $itemKey => $value2) {
                $orderDataNew[$key]['正貨商品'] .= $itemKey.$value2['數量'].'#$'.$value2['金額'].';';
                $orderDataNew[$key]['正貨商品總金額'] += $value2['金額'];
            }
            if($orderDataNew[$key]['正貨商品總金額']==$orderDataNew[$key]['商品總金額']){
                $orderDataNew[$key]['正貨商品金額檢核'] = 'Y';
            }else{
                $orderDataNew[$key]['正貨商品金額檢核'] = 'N';
            }
        }
        $finishNum = 0;
        foreach ($orderDataNew as $key => $value) {
            $insertData['1000310'] = $value['正貨商品'];
            $insertData['1000368'] = $value['正貨商品金額檢核'];
            
            echo $key.'--'.$value['正貨商品']."\r\n<br>".PHP_EOL;
            if($value['正貨商品']){
                $returnData = $this->Api_ragic->syncToRagic('forms/2/'.$key,$insertData);
            }
            $finishNum++;
            if($orderDataNew[$key]['未轉換']=='Y'){
                $transAry[$orderDataNew[$key]['未轉換名稱']] = '商品 <a href="https://ap3.ragic.com/hugePlus/forms/4/'.$orderDataNew[$key]['未轉換ID'].'">['.$orderDataNew[$key]['未轉換名稱'].']</a> 未轉換<br>';
                $errorStr .= '訂單:<a href="https://ap3.ragic.com/hugePlus/forms/2/'.$key.'">'.$key.'</a>'.$transAry[$orderDataNew[$key]['未轉換名稱']]; 
            }
        }
        if($transAry){
            foreach ($transAry as $key => $value) {
                $transStr .= $value;
            }
            $errorStr = '未轉換商品<br>'.$transStr.'<br>完整清單<br>'.$errorStr.'<hr>請按下商品名稱進入商品明細，按下右下角 [建立商品轉換] 進行建立商品轉換';
            $this->load->library('My_SendMail');
            $data = array(
                    'recipient'=>['ida@pro-duction.com.tw','sophia@pro-duction.com.tw','doris@pro-duction.com.tw','peter@pro-duction.com.tw'],
                    'cc'=>'', 
                    'subject' => '商品轉換異常', 
                    'content' => $errorStr,
                    'sender'=>MAIL_CONFIG['senderName']); 
            $result = $this->my_sendmail->sendOut($data);
        }
        
            
    }

    function test(){
        echo 'http://localhost:8075/webroot/decision/view/report?op=export&viewlet=hugePlusFR%2F%E5%B9%B4%E5%A0%B1%E8%A1%A8.cpt&format=excel&extype=page&source=%E5%A5%BD%E8%8F%8C%E5%AE%B6&date_from=2020%2F01%2F01&date_to=2021%2F07%2F31&loadCache=true&filter='.urlencode('1000029,gte,2020/01/01^1000029,lte,2021/07/31^1000027,eq,%E5%A5%BD%E8%8F%8C%E5%AE%B6');
    }

    function updateErrorData(){
        $errorData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/2?where=1000069,eq,'.'&limit=0,1000', $ckfile),true);
        foreach ($errorData as $key => $value) {
            //echo $value['訂單流水編號'].'---'.$value['會員流水編號'].'---'.$value['訂購人姓名'].'---'.$value['訂購人信箱'].'----->';
            if($value['收貨人姓名']==$value['訂購人姓名']){
                unset($errorData[$key]);continue;
            }
            if($value['收貨人信箱']=='oilbarrierpro@gmail.com'){
                unset($errorData[$key]);continue;
            }
            if($value['收貨人地址']){
                $ftStr .= $value['收貨人地址'].' ';
            }
        }

        $membData = $this->Api_ragic->getRagicFullText('forms/1',$ftStr,10);
        foreach ($membData as $key => $value) {
            if($value['完整地址']){
                $emailAry[$value['完整地址']]['會員流水編號'] = $value['會員流水編號'];
                $emailAry[$value['完整地址']]['姓名'] = $value['姓名'];
                $emailAry[$value['完整地址']]['完整地址'] = $value['完整地址'];
                $emailAry[$value['完整地址']]['連絡電話'] = $value['連絡電話'];
            }
        }

        foreach ($errorData as $key => $value) {
            echo $value['訂單流水編號'].'---'.$value['會員流水編號'].'---'.$value['收貨人姓名'].$value['收貨人電話'].'---'.$value['收貨人信箱'].'----->';
            if($emailAry[$value['收貨人地址']]){
                echo '比對吻合<br>';
                $setParam['doLinkLoad'] = true;
                $insertData['1000023'] = $emailAry[$value['收貨人地址']]['會員流水編號'];
                $insertData['1000024'] = $emailAry[$value['收貨人地址']]['姓名'];
                $this->Api_common->dataDump($insertData);
                //$returnData = $this->Api_ragic->syncToRagic('forms/2/'.$key,$insertData,$setParam);
            }
            $this->Api_common->dataDump($emailAry[$value['收貨人信箱']]);
        }

        //$this->Api_common->dataDump($errorData);
    }

    function doInvoiceRet(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->model('Api_ret_invoice');
        $this->load->model('Api_ragic');
        $invoiceRetData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/5?where=1000359,eq,Y&where=1000379,eq,N', $ckfile),true);
        $finishNum = 0;
        foreach ($invoiceRetData as $key => $value) {
            if($value['訂單來源']=='日研專科'){
                $ecpayDetail['HashKey'] = 'bBLLXU7046et0TfI';
                $ecpayDetail['HashIV'] = 'CAFO8yKHLSxzSFq1';
                $ecpayDetail['MerchantID'] = '3273562';
                $ecpayDetail['Type'] = 'Normal';
                $orderDetail['InvoiceNo'] = $value['發票號碼'];
                $orderDetail['InvoiceDate'] = $value['訂單日期'];
                $orderDetail['InvoiceRetAmount'] = $value['退款金額'];
                //查詢發票
                $retData = $this->Api_ret_invoice->run('ecpay','query',$ecpayDetail,$orderDetail);
                if($retData['RtnCode']=='1'){
                    //執行折讓
                    $retData2 = $this->Api_ret_invoice->run('ecpay','retInvoice',$ecpayDetail,$orderDetail);
                    if($retData2['RtnCode']=='1'){
                        $insertData['1000379'] = 'Y';
                        $insertData['1000380'] = $retData2['IA_Date'];
                        $insertData['1000381'] = $retData2['IA_Allow_No'];
                        $returnData = $this->Api_ragic->syncToRagic('forms/5/'.$key,$insertData);
                        $finishNum++;
                    }else if($retData2){
                        $errorStr .= '折讓錯誤: 發票號碼:'.$orderDetail['InvoiceNo'].':'.$retData2['RtnMsg'].'<br>';
                    }
                }else if($retData){
                    $errorStr .= '查詢錯誤: 發票號碼:'.$orderDetail['InvoiceNo'].' :'.$retData['RtnMsg'].'<br>';
                }
            }
        }
        echo $errorStr;

        if($errorStr){
            $this->load->library('My_SendMail');
            $data = array(
                    'recipient'=>['peter@pro-duction.com.tw'],
                    'cc'=>'', 
                    'subject' => '發票折讓異常', 
                    'content' => $errorStr,
                    'sender'=>MAIL_CONFIG['senderName']); 
            $result = $this->my_sendmail->sendOut($data);
        }

        $jobNum = count($invoiceRetData);
        $this->Api_ragic->saveLog('執行發票折讓',$jobNum,$finishNum,$startTime);
    }

    function doCountOrderTime($type=null){
        $startTime = date('Y-m-d H:i:s');
        if($type=='退貨重計購買次'){
            $param = '?where=1000404,gte,'.date('Y-m-d',strtotime(date('Y-m-d'))-86400).'&where=1000404,lte,'.date('Y-m-d').'&limit=0,1000';
        }else if($type=='訂單購買次更新'){
            $param = '?where=1000160,gte,'.date('Y-m-d',strtotime(date('Y-m-d'))-86400).'&where=1000160,lte,'.date('Y-m-d').'&limit=0,1000';
        }else if($type=='etc'){
            //$param = '?fts=R011713%20R010571%20R011864%20R011775%20R011420%20R011572%20R010545%20R011660%20R010646%20R010794%20R012406%20R011166%20R010433%20R010971%20R010469%20R011073%20R012392%20R010923';
        }
        if($param){
            $resData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/1'.$param, $ckfile),true);
        }
        $total = count($resData);
        $setCount = 0;
        echo $type.' - '.$total.PHP_EOL;
        foreach ($resData as $id => $value) {
            $itemStr = '';
            foreach ($resData[$id]['_subtable_1000060'] as $subid => $value2) {
                if($value2['是否退貨']=='Y'){continue;}
                $membOrd[$value2['來源名稱']][$value2['購買日期'].'_'.$subid]['id'] = $subid;
                $membOrd[$value2['來源名稱']][$value2['購買日期'].'_'.$subid]['是否首購'] = $value2['是否首購'];
                $membOrd[$value2['來源名稱']][$value2['購買日期'].'_'.$subid]['購買日期'] = $value2['購買日期'];
                ksort($membOrd[$value2['來源名稱']]);
                $itemStr .= $value2['正貨商品'];
            }
            
            foreach ($membOrd as $source => $value3) {
                $num = 1;
                $lastDate = '';
                foreach ($membOrd[$source] as $key2 => $value2) {
                    $membOrd[$source][$key2]['購買次'] = $num;
                    if($value2['是否首購']=='Y'){
                        $membOrd[$source][$key2]['距上次購買'] = 0;
                        $lastDate = $value2['購買日期'];
                    }else{
                        $membOrd[$source][$key2]['距上次購買'] = (int)((strtotime($value2['購買日期'])-strtotime($lastDate))/86400);
                        $lastDate = $value2['購買日期'];
                    }
                    $num++;
                }
            }

            foreach ($membOrd as $source => $value3) {
                foreach ($membOrd[$source] as $key2 => $value2) {
                    $updateData['1000401_'.$value2['id']] = $value2['購買次'];//本次購買
                    $updateData['1000402_'.$value2['id']] = $value2['距上次購買'];//距上次購買
                }
            }
            $updateData['1000414'] = $this->doCountBuyItemTotal($itemStr);
            $result = $this->Api_ragic->syncToRagic('forms/1/'.$id,$updateData,null);
            //$this->Api_common->dataDump($membOrd);
            //$this->Api_common->dataDump($result);
            echo '['.$setCount.'/'.$total.']'.$id.PHP_EOL;
            unset($membOrd);
            unset($updateData);
            $setCount++;
        }

        $jobNum = count($resData);
        $this->Api_ragic->saveLog($type,$jobNum,$setCount,$startTime);
        //$this->Api_common->dataDump($resData);
        
        //$orderInsertData['1000401_'.$mark.$i] = $value2['eoItemType'];//商品規格
        //$orderInsertData['1000402_'.$mark.$i] = $value2['eoItemType'];//商品規格
        //$result = $this->Api_ragic->syncToRagic('forms/1/'.$id,$orderInsertData,null);
    }

    function doCountBuyItemTotal($str){
        //$str = '黑松UCII 龜鹿膠原#30#3#$3009;黑松UCII 龜鹿膠原#30#3#$3009;黑松UCII 龜鹿膠原#30#3#$3009;黑松UCII 龜鹿膠原#30#3#$3009;黑松人蔘精#15#1#$685;黑松UCII 龜鹿膠原#30#3#$3009;黑松UCII 龜鹿膠原#30#3#$3009;L-137植物乳酸菌#30#4#$3136;L-137植物乳酸菌#30#1#$784;L-137植物乳酸菌#30#1#$784;黑松UCII 龜鹿膠原#30#3#$3009;黑松UCII 龜鹿膠原#30#1#$1003;果寡糖順暢粉#15#3#$747;';
        $temp = explode(';',$str);
        foreach ($temp as $key => $value) {
            if(!$value){continue;}
            $temp2 = explode('#', $value);
            $itemData[$temp2[0].'#'.$temp2[1]]['數量'] += (int)$temp2[2];
            $itemData[$temp2[0].'#'.$temp2[1]]['價格'] += (int)str_replace('$', '', $temp2[3]);
        }
        foreach ($itemData as $key => $value) {
            $retStr .= $key.'#'.$value['數量'].'#$'.$value['價格'].';';
        }

        return $retStr;
    }

    function test2(){
        $param = '?where=1000160,gte,'.date('Y-m-d','2021-07-01').'&where=1000160,lte,2021-12-30&limit=0,10000';
        $resData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/forms/1'.$param, $ckfile),true);
        foreach ($resData as $id => $value) {
            $itemStr = '';
            foreach ($resData[$id]['_subtable_1000060'] as $subid => $value2) {
                if($value2['是否退貨']=='Y'){continue;}
                $itemStr .= $value2['正貨商品'];
            }
            $updateData['1000414'] = $this->doCountBuyItemTotal($itemStr);
            $result = $this->Api_ragic->syncToRagic('forms/1/'.$id,$updateData,null);
            echo $id.PHP_EOL;
        }
        //$this->Api_common->dataDump();
        echo date('Y-m-d H:i:s').'done';
    }

}
