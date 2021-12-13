<?php
class Api_ec extends CI_Model{
   
    function __construct() {
        parent::__construct();
    }

    function getItemData($itemID,$detail=null){
        $data = $this->Api_common->getDataCustom('*','ec_item','eiSysID = '.$itemID.'');
        if(!$data){
            echo $this->Api_common->setFrontReturnMsg('901','商品資料無效',null);
            exit; 
        }
        if($data[0]['eiStatus']=='售完'){
        }
        if($data[0]['eiDiscountType']=='Percent'){
            $data[0]['discount'] = $data[0]['eiDiscount']*$data[0]['eiPrice'];
        }else if($data[0]['eiDiscountType']=='Discount'){
            $data[0]['discount'] = $data[0]['eiDiscount'];
        }else{
            $data[0]['discount'] = 0;
        }

        $data[0]['shipAmount'] = 0;

        return $data;
    }

    function getAllItemData(){
        $data = $this->Api_common->getDataCustom('eiName,eiSysID,eiStatus,eiSetting,eiImg','ec_item','eiStatus="銷售中" OR eiStatus="預購中"');
        return $data;
    }

    function getOrderData($orderNo){
        $data = $this->Api_common->getDataCustom('*','ec_order','eoOrderNo = "'.$orderNo.'"');
        $data[0]['detail'] = $this->Api_common->getDataCustom('*','ec_order_detail','eodOrderNo = "'.$orderNo.'"');
        if(!$data){
            echo $this->Api_common->setFrontReturnMsg('901','訂單資料無效',null);
            exit; 
        }
        return $data;
    }

    function getOrderARSData($orderNo){
        $data = $this->Api_common->getDataCustom('*','ec_order_ars','eaARSOrderNo = "'.$orderNo.'"');
        $data[0]['detail'] = $this->Api_common->getDataCustom('*','ec_order','eoARSOrderNo = "'.$orderNo.'"');
        if(!$data){
            echo $this->Api_common->setFrontReturnMsg('901','訂單資料無效',null);
            exit; 
        }
        return $data;
    }

    function getOrderDataByEmail($email){
        $data = $this->Api_common->getDataCustom('*','ec_order','eoReceiverEmail = "'.$email.'"');
        if(!$data){
            //echo $this->Api_common->setFrontReturnMsg('901','訂單資料無效',null);
            //exit; 
        }
        return $data;
    }

    function setOrderStatus($status,$orderNo,$ecPay_result=null,$orderData=null,$postData=null){
        if($status=='已付款'){
            $updateData['eoPayAmount'] = $ecPay_result['TradeAmt'];
            $updateData['eoOrderStatus'] = '待出貨';
            $updateData['eoPayStatus'] = '已付款';
            $updateData['eoPayDate'] = date('Y-m-d');
            $updateData['eoPlainShipDate'] = date('Y-m-d');
            if($orderData[0]['eoIsARS']=='Y'){
                $updateData['eoARSDeliverDay'] = date('d');
                //寫入ARS子表
                $updateARS['eaARSStatus'] = '進行中';
                $updateARS['eaARSDeliverDay'] = $updateData['eoARSDeliverDay'];
                $updateARS['eaLastDeliverDate'] = date('Y-m-d');
                $updateARS['eaNextDeliverDate'] = $this->Api_ec->getNextMonthDate(date('Y-m-d'),$orderData[0]['eoARSPeriodType'],$orderData[0]['eoARSFreq']);
                $updateARS['eaUpdateDTime'] = date('Y-m-d H:i:s');
                $this->db->where('eaARSOrderNo', $orderNo);
                $this->db->update('ec_order_ars', $updateARS);
            }
            $this->db->where('eoOrderNo', $orderNo);
            $this->db->update('ec_order', $updateData);
            $postData['orderNo'] = $this->Api_common->stringHash('encrypt',$orderNo);
            $this->sendOrderMail('待出貨',$postData);
        }else if($status=='生成付款隨機碼'){
            $updateData['eoPayRand'] = rand(100,999);
            $this->db->where('eoOrderNo', $orderNo);
            $this->db->update('ec_order', $updateData);
        }else if($status=='確認中'){
            $updateData['eoPayStatus'] = '確認中';
            $this->db->where('eoOrderNo', $orderNo);
            $this->db->update('ec_order', $updateData);
        }
    }

    function ec_pay($detail,$isTest){
        require_once(APPPATH.'libraries/My_Ecpay.php');
        try {
            
            $obj = new ECPay_AllInOne();
            if($isTest=='1'){
                $obj->ServiceURL  = ECPay_URL_Test.'/Cashier/AioCheckOut/V5';  //服務位置
                $obj->HashKey     = ECPay_HashKey_Test ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV_Test ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID_Test;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }else if($isTest=='0'){
                $obj->ServiceURL  = ECPay_URL.'/Cashier/AioCheckOut/V5';  //服務位置
                $obj->HashKey     = ECPay_HashKey ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }
            
            $obj->EncryptType = '1';                                                          //CheckMacValue加密類型，請固定填入1，使用SHA256加密

            $tradeTime = date('Y/m/d H:i:s');
            //基本參數(請依系統規劃自行調整)
            $obj->Send['ReturnURL'] = base_url()."ec/EC_Cart/paymentReturn/".$this->Api_common->stringHash('encrypt',$detail[0]['eoOrderNo'].'_'.$tradeTime);     //綠界後端付款完成通知回傳的網址
            $obj->Send['OrderResultURL'] = base_url()."ec/EC_Cart/chkOrder/".$this->Api_common->stringHash('encrypt',$detail[0]['eoOrderNo']);     //前端跳轉網址
            
            $obj->Send['MerchantTradeNo']   = $detail[0]['eoOrderNo'].'R'.$detail[0]['eoPayRand'];//訂單編號
            $obj->Send['MerchantTradeDate'] = $tradeTime;//交易時間
            $obj->Send['TotalAmount']       = $detail[0]['eoOrderAmount'];//交易金額
            $obj->Send['TradeDesc']         = $detail[0]['eoItemName'].' '.$detail[0]['eoItemType'].'*'.$detail[0]['eoItemQty'].'件';//交易描述
            $obj->Send['ChoosePayment']     = ECPay_PaymentMethod::Credit  ;//付款方式:Credit
            $obj->Send['NeedExtraPaidInfo'] = 'Y';

            //訂單的商品資料
            array_push($obj->Send['Items'], 
                array('Name' => $detail[0]['eoItemName'].' '.$detail[0]['eoItemType'].'. 共 ', 
                    'Price' => (int)$detail[0]['eoItemPrice'], 
                    'Currency' => "元", 
                    'Quantity' => (int) $detail[0]['eoItemQty'], 
                    'URL' => base_url()."ec/EC_Order?itemID=".$detail[0]['eoSysID']));
            if($detail[0]['eoOrderDiscount']){
                array_push($obj->Send['Items'], 
                array('Name' => '折扣', 
                    'Price' => (int)$detail[0]['eoOrderDiscount'], 
                    'Currency' => "元", 
                    'Quantity' => 1, 
                    'URL' => ''));
            }
            if($detail[0]['eoOrderShipAmount']){
                array_push($obj->Send['Items'], 
                array('Name' => '運費', 
                    'Price' => (int)$detail[0]['eoOrderShipAmount'], 
                    'Currency' => "元", 
                    'Quantity' => 1, 
                    'URL' => ''));
            }
            //定期配訂單產生定額訂單
            if($detail[0]['eoIsARS']=='Y'){
                $obj->SendExtend['PeriodAmount'] = $detail[0]['eoOrderAmount'];//每次授權金額
                $obj->SendExtend['PeriodType'] = $detail[0]['eoARSPeriodType'];//周期
                $obj->SendExtend['Frequency'] = $detail[0]['eoARSFreq'];//頻率
                $obj->SendExtend['ExecTimes'] = $detail[0]['eoARSPeriodsTotal'];//次數
                $obj->SendExtend['PeriodReturnURL'] = base_url()."ec/EC_Cart/paymentReturn/".$this->Api_common->stringHash('encrypt',$detail[0]['eoOrderNo'].'_'.$tradeTime) ;//每次綠界授權後觸發更新連結               
            }
            

            # 電子發票參數
            /*
            $obj->Send['InvoiceMark'] = ECPay_InvoiceState::Yes;
            $obj->SendExtend['RelateNumber'] = "Test".time();
            $obj->SendExtend['CustomerEmail'] = 'test@ecpay.com.tw';
            $obj->SendExtend['CustomerPhone'] = '0911222333';
            $obj->SendExtend['TaxType'] = ECPay_TaxType::Dutiable;
            $obj->SendExtend['CustomerAddr'] = '台北市南港區三重路19-2號5樓D棟';
            $obj->SendExtend['InvoiceItems'] = array();
            // 將商品加入電子發票商品列表陣列
            foreach ($obj->Send['Items'] as $info)
            {
                array_push($obj->SendExtend['InvoiceItems'],array('Name' => $info['Name'],'Count' =>
                    $info['Quantity'],'Word' => '個','Price' => $info['Price'],'TaxType' => ECPay_TaxType::Dutiable));
            }
            $obj->SendExtend['InvoiceRemark'] = '測試發票備註';
            $obj->SendExtend['DelayDay'] = '0';
            $obj->SendExtend['InvType'] = ECPay_InvType::General;
            */


            //產生訂單(auto submit至ECPay)
            $obj->CheckOut();
          

        
        } catch (Exception $e) {
            echo $e->getMessage();
        } 
    }

    function ec_query($detail,$isTest){
        require_once(APPPATH.'libraries/My_Ecpay.php');
        try {
            $obj = new ECPay_AllInOne();
            if($isTest=='1'){
                $obj->ServiceURL  = ECPay_URL_Test.'/Cashier/QueryTradeInfo/V5';  //服務位置
                $obj->HashKey     = ECPay_HashKey_Test ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV_Test ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID_Test;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }else if($isTest=='0'){
                $obj->ServiceURL  = ECPay_URL.'/Cashier/QueryTradeInfo/V5';  //服務位置
                $obj->HashKey     = ECPay_HashKey ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }                                                 //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $obj->EncryptType = '1';                                                            //CheckMacValue加密類型，請固定填入1，使用SHA256加密

            //基本參數(請依系統規劃自行調整)
            $obj->Query['MerchantTradeNo'] = $detail[0]['eoOrderNo'].'R'.$detail[0]['eoPayRand'];
            $obj->Query['TimeStamp']       = time() ;

            //查詢訂單
            $info = $obj->QueryTradeInfo();

            //顯示訂單資訊
            return $info;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function ec_query2($detail,$isTest){
        
        require_once(APPPATH.'libraries/My_Ecpay.php');
        try {
            $obj = new ECPay_AllInOne();
            if($isTest=='1'){
                $obj->ServiceURL  = ECPay_URL_Test.'/CreditDetail/QueryTrade/V2';  //服務位置
                $obj->HashKey     = ECPay_HashKey_Test ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV_Test ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID_Test;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
                $obj->Trade['CreditCheckCode']  = ECPay_CreditCheckCode_Test;
            }else if($isTest=='0'){
                $obj->ServiceURL  = ECPay_URL.'/CreditDetail/QueryTrade/V2';  //服務位置
                $obj->HashKey     = ECPay_HashKey ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
                $obj->Trade['CreditCheckCode']  = ECPay_CreditCheckCode;
            }                                                
            $obj->EncryptType = '1';    //CheckMacValue加密類型，請固定填入1，使用SHA256加密

            //基本參數(請依系統規劃自行調整)
            //$obj->Trade['CreditRefundId'] = $detail[0]['eoOrderNo'].'R'.$detail[0]['eoPayRand'];
            $obj->Trade['CreditRefundId'] = $detail['gwsr'];//授權單號
            $obj->Trade['CreditAmount'] = $detail['TradeAmt'];
            //$obj->Trade['TimeStamp']       = time() ;
            //查詢訂單
            //$this->Api_common->dataDump($obj);
            $info = $obj->QueryTrade();
            //顯示訂單資訊
            return $info;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function ec_query_period($detail,$isTest){
        require_once(APPPATH.'libraries/My_Ecpay.php');
        try {
            $obj = new ECPay_AllInOne();
            if($isTest=='1'){
                $obj->ServiceURL  = ECPay_URL_Test.'/Cashier/QueryCreditCardPeriodInfo';  //服務位置
                $obj->HashKey     = ECPay_HashKey_Test ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV_Test ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID_Test;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }else if($isTest=='0'){
                $obj->ServiceURL  = ECPay_URL.'/Cashier/QueryCreditCardPeriodInfo';  //服務位置
                $obj->HashKey     = ECPay_HashKey ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
                $obj->HashIV      = ECPay_HashIV ;//測試用HashIV，請自行帶入ECPay提供的HashIV
                $obj->MerchantID  = ECPay_MerchantID;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
            }                                                 //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $obj->EncryptType = '1';                                                            //CheckMacValue加密類型，請固定填入1，使用SHA256加密

            //基本參數(請依系統規劃自行調整)
            $obj->Query['MerchantTradeNo'] = $detail[0]['eoOrderNo'].'R'.$detail[0]['eoPayRand'];
            $obj->Query['TimeStamp']       = time() ;

            //查詢訂單
            $info = $obj->QueryPeriodCreditCardTradeInfo();

            //顯示訂單資訊
            return $info;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    function reCaptchaChk($postData){
        $sendData['secret'] = RECAPTCHA_SERVER;
        $sendData['response'] = $postData['token'];
        $capResult = json_decode($this->Api_common->getCurl('https://www.google.com/recaptcha/api/siteverify', $sendData),true);
        if($capResult['score']>0.5||$capResult['success']==true){
            return true;
        }else{
            echo $this->Api_common->setFrontReturnMsg('401','reCaptcha score too low',null);
            exit; 
        }
    } 

    function sendOrderMail($type,$postData){
        $orderNo = $this->Api_common->stringHash('decrypt',$postData['orderNo']);
        $orderData = $this->Api_ec->getOrderData($orderNo);
        if($type=='待付款'){
            $orderTable[0]['訂單編號/狀態'] = $orderData[0]['eoOrderNo'].'<br><span style="color:red;">'.$orderData[0]['eoOrderStatus'].'</span><br><a href="'.base_url().'ec/EC_Cart/payOrder?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">前往付款</a>';
        }else if($type=='待出貨'){
            $orderTable[0]['訂單編號/狀態'] = $orderData[0]['eoOrderNo'].'<br><span style="color:blue;">'.$orderData[0]['eoOrderStatus'].'</span><br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">查詢最新狀態</a>';
        }else if($type=='ARS待出貨'){
            $orderTable[0]['訂單編號/狀態'] = $orderData[0]['eoOrderNo'].'<br><span style="color:blue;">'.$orderData[0]['eoOrderStatus'].'</span><br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">查詢最新狀態</a>';
        }else if($type=='已出貨'){
            $orderTable[0]['訂單編號/狀態'] = $orderData[0]['eoOrderNo'].'<br><span style="color:blue;">'.$orderData[0]['eoOrderStatus'].'</span><br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">查詢最新狀態</a><br>'.$orderData[0]['eoDeliverName'].'-'.$orderData[0]['eoDeliverCode'];
        }else if($type=='已取消'||$type=='已退貨'||$type=='通知'){
            $orderTable[0]['訂單編號/狀態'] = $orderData[0]['eoOrderNo'].'<br><span style="color:blue;">'.$orderData[0]['eoOrderStatus'].'</span><br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">查詢狀態</a>';
        }
        $orderTable[0]['商品品項/件'] = $orderData[0]['eoItemName'].'*'.$orderData[0]['eoItemQty'].'件';
        $orderTable[0]['單價'] = $orderData[0]['eoItemPrice'];
        $orderTable[0]['折扣'] = $orderData[0]['eoOrderDiscount'];
        $orderTable[0]['訂單總金額'] = number_format($orderData[0]['eoOrderAmount']);
        $orderTable[0]['收貨/發票資訊'] = $orderData[0]['eoReceiverName'].'<br>'.$orderData[0]['eoReceiverPhone'].'<br>'.$orderData[0]['eoReceiverPostCode'].' '.$orderData[0]['eoReceiverAddr'].'<br>'.$orderData[0]['eoMemberNote'].'<br>'.$orderData[0]['eoInvoiceMeta'].' '.$orderData[0]['eoInvoiceComNo'].$orderData[0]['eoInvoiceCom'].'<br>'.$orderData[0]['eoInvoiceAddr'];
        if($orderData[0]['eoARSOrderNo']){
            $arsItem = '定期配送';
            $orderTable[0]['商品品項/件'] .= $orderTable[0]['商品品項/件'].'<br>第 '.$orderData[0]['eoARSPeriods'].' 期<br>共 '.$orderData[0]['eoARSPeriodsTotal'].' 期';
        }

        $detail['title'] = array('訂單編號/狀態','商品品項/件','訂單總金額','收貨/發票資訊');
        $detail['tableStyle'] = 'border:1px solid #ccc';
        $detail['fontSize'] = 10;
        $detail['allBorder'] = $detail['title'];
        $detail['alignLeft'] = array('收貨資訊');
        $orderTable = $this->Api_table_generate->drawTable($orderTable,$detail,$data);

        $logoURL = EMAIL_IMAGE;
        $brandName = BRAND_NAME;
        $brandColor = '#008ea6';

        $serviceMeta = SERVICE_META.'<br>再次感謝，並期盼您的再次光臨。';

        if(mb_strlen($orderData[0]['eoReceiverName'])==3){
            $name = mb_substr($orderData[0]['eoReceiverName'], 1,2);
        }else{
            $name = $orderData[0]['eoReceiverName'];
        }

        if($type=='待付款'){//訂單確認
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>我們已經收到您所購買的以下訂單的訂購內容。<br>'.$orderTable.'<br>【請注意下列事項】
                <span style="color:red">未付款訂單系統將統一在訂購當天起算的三天後直接取消訂單</span><br>
                本次消費【扣款成功】及【倉庫出貨】時都將另行email/SMS通知。<br>您的定期配【第二期扣款】及【第二期出貨】發生之前，也都另行email/SMS通知。請密切注意您的信箱或手機簡訊，確保您的權益。';
            $subject = '['.$brandName.' '.$arsItem.'] 訂單確認通知';
            $h2 = '感謝您訂購 '.$brandName.$arsItem.'商品!';
        }else if($type=='待出貨'){
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>訂單於 '.date('Y-m-d').' 已扣款成功，我們將開始準備配送<br>'.$orderTable.'';
            $subject = '['.$brandName.' '.$arsItem.'] 訂單扣款成功通知';
            $h2 = '確認已付款! 我們會盡快為您出貨';
        }else if($type=='ARS待出貨'){//綠界Return呼叫 訂單已付款
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>訂單於 '.date('Y-m-d').' 已扣款成功，我們將開始準備配送<br>'.$orderTable.'';
            $subject = '['.$brandName.' '.$arsItem.'] 訂單扣款成功通知';
            $h2 = '確認已付款! 我們會盡快為您出貨';
            //簡訊
            $smsData['emSendDevice'] = 'sms';
            $smsData['emSource'] = BRAND_NAME;
            $smsData['emReceiver'] = $orderData[0]['eoReceiverPhone'];
            $smsData['emSubject'] = '感謝您訂購'.$brandName.$arsItem.'商品，訂單已確認付款，商品將開始準備配送，如有訂單相關問題歡迎來電客服專線詢問 0809-091518';
            $smsData['emContent'] = null;
            $smsData['emStatus'] = '待發送';
            $smsData['emSendType'] = 'ARS待出貨';
            $smsData['emOrderNo'] = $orderNo;
            $this->db->insert('ec_mail', $smsData);
        }else if($type=='ARS提醒配送'){//SMS_ARS-10呼叫 即將配送
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>第 '.$postData['nowPeriods'].' 期商品即將於 '.$postData['nextDeliverDate'].' 為您配送。<br>(遇例假日或國定假日，將順延至次一上班日配送)<br>';
            $subject = '['.$brandName.' 定期配] 第 '.$postData['nowPeriods'].' 期訂單即將於 '.$postData['nextDeliverDate'].' 配送';
            $h2 = '定期配商品即將配送通知';
        }else if($type=='ARS提醒扣款'){//SMS_ARS-1呼叫 即將扣款
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>第 '.$postData['nowPeriods'].' 期商品即將於 '.$postData['nextDeliverDate'].' 進行扣款並準備配送。<br>(遇例假日或國定假日，將順延至次一上班日配送)<br>';
            $subject = '['.$brandName.' 定期配] 第 '.$postData['nowPeriods'].' 期訂單即將於 '.$postData['nextDeliverDate'].' 扣款並開始配送';
            $h2 = '定期配商品即將扣款通知';
        }else if($type=='已出貨'){//匯入物流回應檔時呼叫
            $mailContent = $name.' 您好，<br>非常感謝您訂購 '.$brandName.$arsItem.'商品 '.$orderData[0]['eoItemName'].'<br>您所訂購的商品正在配送中，將盡快配送到您指定地點<br>'.$orderTable.'';
            $subject = '['.$brandName.' '.$arsItem.'] 倉庫出貨通知';
            $h2 = '感謝您! 您的商品已出貨';
            //簡訊
            $smsData['emSendDevice'] = 'sms';
            $smsData['emSource'] = BRAND_NAME;
            $smsData['emReceiver'] = $orderData[0]['eoReceiverPhone'];
            $smsData['emSubject'] = '感謝您購買'.$brandName.$arsItem.'商品'.$orderData[0]['eoItemName'].'，您所訂購的商品正在配送中，如有訂單相關問題歡迎來電客服專線詢問 0809-091518';
            $smsData['emContent'] = null;
            $smsData['emStatus'] = '待發送';
            $smsData['emSendType'] = '已出貨';
            $smsData['emOrderNo'] = $orderNo;
            $this->db->insert('ec_mail', $smsData);
        }else if($type=='已取消'){
            $mailContent = '您的訂單已取消，明細如下：<br>'.$orderTable.'<br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">前往查詢訂單狀態</a><br><br>';
            $subject = '您訂購的 '.$brandName.' 產品訂單已取消';
            $h2 = '您的訂單已取消';
        }else if($type=='已退貨'){
            $mailContent = '您的訂單已退貨完成，明細如下：<br>'.$orderTable.'<br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">前往查詢訂單狀態</a><br><br>';
            $subject = '您訂購的 '.$brandName.' 產品訂單已退貨完成';
            $h2 = '您的訂單已退貨完成';
        }else if($type=='通知'){
            $mailContent = '您的訂單有新通知！ <a href="'.base_url().'ec/EC_Cart/finish?require=Y&orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">請按此連結查看通知</a><hr>您的訂購明細如下：<br>'.$orderTable.'<br><a href="'.base_url().'ec/EC_Cart/status?orderNo='.$this->Api_common->stringHash('encrypt',$orderNo).'">前往查詢訂單狀態</a><br><br>';
            $subject = '您訂購的 '.$brandName.' 產品訂單有新通知';
            $h2 = '您的訂單有新通知！';
        }

        if($orderNo){
            $subject .= ' ( 訂單編號 '.$orderNo.' )';
        }
        
        $mailContent = '<html><body><div style="width: 100%;margin: 20px auto;max-width: 800px;border: 1px solid #4e96a2;padding: 50px;border-radius: 5px;"><div><img height="40px" src="'.$logoURL.'"><hr><h2 style="color: '.$brandColor.';padding: 10px 0px;">'.$h2.'</h2>'.$mailContent.'</div>'.$serviceMeta.'<br><br><hr><center>'.FOOT_META.'</center></div></body></html>';

        //列入排程
        $insertData['emSendDevice'] = 'email';
        $insertData['emReceiver'] = $orderData[0]['eoReceiverEmail'];
        $insertData['emSubject'] = $subject;
        $insertData['emContent'] = $mailContent;
        $insertData['emStatus'] = '待發送';
        $insertData['emSendType'] = $type;
        $insertData['emOrderNo'] = $orderNo;
        
        $this->db->insert('ec_mail', $insertData);
        /*
        //寄信
        $this->load->library('My_SendMail');
        //寄給表單填寫人
        $data = array(
                'recipient'=>array($orderData[0]['eoReceiverEmail']),
                'cc'=>'', 
                'subject' => $subject, 
                'content' => $mailContent,
                'sender'=>MAIL_CONFIG['senderName']); 
        $this->my_sendmail->sendOut($data);

        //寄給內部
        if(preg_match('/待出貨|已出貨|已退貨|通知/', $type)&&!preg_match('/TEST/', $orderData[0]['eoItemName'])){
            $resData = $this->Api_common->getSysConfig('ecSender');
            $receiveEmp = explode(';', $resData['scValue1']);
            $data = array(
                'recipient'=>$receiveEmp,
                'cc'=>'', 
                'subject' => $subject, 
                'content' => $mailContent,'sender'=>MAIL_CONFIG['senderName']); 
            $this->my_sendmail->sendOut($data);
        }*/

    }

    function RequireLoad(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $orderNo = $this->Api_common->stringHash('decrypt',$postData['orderNo']);
        $user_detail=$this->session->all_userdata();

        $retData['msgData'] = array();
        $retData['msgData'][0]['text'] = '您好：感謝您訂購本公司商品，如您有任何的需求，請隨時透過文字訊息與我們聯繫';
        $retData['msgData'][0]['time'] = '';
        $retData['msgData'][0]['type'] = 'return';
        $retData['msgData'][0]['author'] = '客服人員';

        $data = $this->Api_common->getDataCustom('*','ec_require','erOrderNo = "'.$orderNo.'"');

        foreach ($data as $key => $value) {
            $dt['text'] = $data[$key]['erText'];
            $dt['time'] = date('m-d H:i',strtotime($data[$key]['erMsgDTime']));
            $dt['type'] = $data[$key]['erType'];
            if($data[$key]['erType']=='return'){
                $dt['author'] = '客服人員 '.$data[$key]['erReturnEmpName'];
                if($user_detail["account"]&&$data[$key]['erIsRead']=='N'){
                    $dt['isNotifiy'] = 'N';
                }
            }else{
                $dt['author'] = '';
            }

            array_push($retData['msgData'],$dt);
        }

        return $retData;
    }

    function RequireSubmit(){
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $orderNo = $this->Api_common->stringHash('decrypt',$postData['orderNo']);
        if(!$postData['textMsg']){
            echo $this->Api_common->setFrontReturnMsg('901','請輸入訊息',null);exit; 
        }

        $insertData['erOrderNo'] = $orderNo;
        $insertData['erType'] = 'require';
        $insertData['erText'] = $postData['textMsg'];
        $insertData['erMsgDTime'] = date('Y-m-d H:i:s');
        $this->db->insert('ec_require', $insertData);
    }

    function RequireReturnSubmit(){
        $user_detail=$this->session->all_userdata();
        $postData = $this->input->post();
        $postData = $this->Api_common->cleanPostData($postData);
        $orderNo = $this->Api_common->stringHash('decrypt',$postData['orderNo']);
        if(!$postData['textMsg']){
            echo $this->Api_common->setFrontReturnMsg('901','請輸入訊息',null);exit; 
        }

        $insertData['erOrderNo'] = $orderNo;
        $insertData['erType'] = 'return';
        $insertData['erReturnEmpName'] = $user_detail["account"];
        $insertData['erText'] = $postData['textMsg'];
        $insertData['erMsgDTime'] = date('Y-m-d H:i:s');
        $this->db->insert('ec_require', $insertData);

        
    }

    function chkNextNum($table,$field,$offset,$where=null,$order_by=null){
        $this->db->select($field);
        $this->db->from($table);
        if($where){
            $this->db->where($where);
        }
        if($order_by){
            $this->db->order_by($order_by,'desc');
            $this->db->limit(1);
        }        
        $query = $this->db->get();
        if($query){
            foreach ($query->result_array() as $row){
                $id = substr($row[$field], $offset)+1;
            }   
        }else{
            return 1;
        }
        if(!$id){
            $id = 1;
        }
        
        return $id;
    }

    function rebuildInventory(){
        $inventoryChk = $this->Api_common->getDataCustom('eiItemNo,sum(eiItemQty) as qty','ec_inventory','1=1',null,['group_by'=>'eiItemNo']);
        foreach ($inventoryChk as $key => $value) {
            $this->Api_common->cache('save','inventory_'.$value['eiItemNo'],$value);
        }
    }

    function generateMac($hash=null){
        $str = $this->Api_common->stringHash('decrypt',$hash);
        if(!$str){exit;}
        $orderNo = explode('_', $str);
        $orderData = $this->Api_ec->getOrderData($orderNo[0]);
        //$orderData = $this->Api_ec->getOrderData('JP20210414YM0001');
        $isTest = '1';
        if($isTest=='1'){
            $ServiceURL  = ECPay_URL_Test.'/Cashier/AioCheckOut/V5';  //服務位置
            $HashKey     = ECPay_HashKey_Test ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
            $HashIV      = ECPay_HashIV_Test ;//測試用HashIV，請自行帶入ECPay提供的HashIV
            $MerchantID  = ECPay_MerchantID_Test;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
        }else if($isTest=='0'){
            $ServiceURL  = ECPay_URL.'/Cashier/AioCheckOut/V5';  //服務位置
            $HashKey     = ECPay_HashKey ;//測試用Hashkey，請自行帶入ECPay提供的HashKey
            $HashIV      = ECPay_HashIV ;//測試用HashIV，請自行帶入ECPay提供的HashIV
            $MerchantID  = ECPay_MerchantID;//測試用MerchantID，請自行帶入ECPay提供的MerchantID
        }
        
        $domain = base_url();
        $str = 'BindingCard=&ChoosePayment=Credit&ChooseSubPayment=&ClientBackURL=&CreditInstallment=&CustomField1=&CustomField2=&CustomField3=&CustomField4=&DeviceSource=&EncryptType=1&ExecTimes='.$orderData[0]['eoARSPeriodsTotal'].'&Frequency='.$orderData[0]['eoARSFreq'].'&HoldTradeAMT=0&IgnorePayment=&InstallmentAmount=0&InvoiceMark=&ItemName='.$orderData[0]['eoItemName'].' '.$orderData[0]['eoItemType'].'. 共 '.(int)$orderData[0]['eoOrderAmount'].' 元 x '.(int) $orderData[0]['eoItemQty'].'&ItemURL='.$domain.'ec/EC_Order?itemID='.$orderData[0]['eoSysID'].'&Language=&MerchantID='.$MerchantID.'&MerchantMemberID=&MerchantTradeDate='.$orderNo[1].'&MerchantTradeNo='.$orderNo[0].'R'.$orderData[0]['eoPayRand'].'&NeedExtraPaidInfo=Y&OrderResultURL='.$domain.'ec/EC_Cart/chkOrder/'.$this->Api_common->stringHash('encrypt',$orderNo[0]).'&PaymentType=aio&PeriodAmount='.$orderData[0]['eoOrderAmount'].'&PeriodReturnURL='.$domain.'ec/EC_Cart/paymentReturn/'.$hash.'&PeriodType='.$orderData[0]['eoARSPeriodType'].'&Redeem=&Remark=&ReturnURL='.$domain.'ec/EC_Cart/paymentReturn/'.$hash.'&StoreID=&TotalAmount='.$orderData[0]['eoOrderAmount'].'&TradeDesc='.$orderData[0]['eoItemName'].' '.$orderData[0]['eoItemType'].'*'.$orderData[0]['eoItemQty'].'件'.'&UnionPay=';
        
        $ary = explode('&', $str);
        sort($ary);
        foreach ($ary as $key => $value) {
            if(!$newStr){
                $newStr = $value;
            }else{
                $newStr .= '&'.$value;
            }
        }
        $sMacValue = strtolower(urlencode('HashKey='.$HashKey.'&'.$newStr.'&HashIV='.$HashIV));
        // 取代為與 dotNet 相符的字元
        $sMacValue = str_replace('%2d', '-', $sMacValue);
        $sMacValue = str_replace('%5f', '_', $sMacValue);
        $sMacValue = str_replace('%2e', '.', $sMacValue);
        $sMacValue = str_replace('%21', '!', $sMacValue);
        $sMacValue = str_replace('%2a', '*', $sMacValue);
        $sMacValue = str_replace('%28', '(', $sMacValue);
        $sMacValue = str_replace('%29', ')', $sMacValue);

        $sMacValue = hash('sha256', $sMacValue);
        $sMacValue = strtoupper($sMacValue);

        return $sMacValue;
    }

    function getNextMonthDate($buyDate,$type,$freq){
        if($type=='M'){
            $setFreq = 28*$freq;
            $nextYear = date('Y',strtotime('+'.$setFreq.' days',strtotime($buyDate)));
            $nextMonth = date('m',strtotime('+'.$setFreq.' days',strtotime($buyDate)));
            $day = date('d',strtotime($buyDate));
            
            if($day>=cal_days_in_month(CAL_GREGORIAN, $nextMonth, $nextYear)){
                return date($nextYear.'-'.$nextMonth.'-t',strtotime($nextYear.'-'.$nextMonth.'-01'));
            }else{
                return date('Y-m-d',strtotime($nextYear.$nextMonth.$day));
            }
        }else if($type=='D'){
            $setFreq = 1*$freq;
            return date('Y-m-d',strtotime('+'.$setFreq.' days',strtotime($buyDate)));
        }
        
    }

    function getARSData($itemID,$itemType,$period,$detail=null){
        $this->load->model('Api_ragic');
        $ragicItemData = json_decode($this->Api_ragic->ragicCurl(RAGIC_DOMAIN.'forms4/4?where=1000326,eq,'.$itemID.'&where=1000336,eq,'.$itemType.'&where=1000327,eq,'.$period.'&limit=0,1', $ckfile),true);

        return $ragicItemData;
    }
}
?>