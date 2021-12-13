<?php
class Api_ragic extends CI_Model{
    function __construct() {
        parent::__construct();
    }    

    function transSqlDataToRagicField($mode,$orderInsertData,$value,$detail){
        if($detail['siteName']){
            $siteName = $detail['siteName'];
        }else{
            $siteName = SITE_NAME;
        }
        if($detail['sourceName']){
            $sourceName = $detail['sourceName'];
        }else{
            $sourceName = '官方商店';
        }
        //訂單狀態
        $orderInsertData['1000026'] = $value['eoOrderStatus'];//訂單狀態
        $orderInsertData['1000027'] = $siteName;//來源名稱
        $orderInsertData['1000413'] = $sourceName;//來源通路
        $orderInsertData['1000028'] = $value['eoOrderNo'];//來源訂單單號
        $orderInsertData['1000369'] = $siteName.'-'.$value['eoOrderNo'];//訂單KEY
        $orderInsertData['1000029'] = $value['eoOrderDate'];//訂單日期

        //ARS
        $orderInsertData['1000125'] = $value['eoIsARS'];//是否ARS
        $orderInsertData['1000126'] = $value['eoARSDeliverDay'];//ARS配送日
        $orderInsertData['1000127'] = $value['eoARSPeriods'];//ARS第幾期
        $orderInsertData['1000128'] = $value['eoARSPeriodsTotal'];//ARS總期數
        $orderInsertData['1000129'] = $value['eoARSOrderNo'];//ARS合約單號

        //付款相關
        $orderInsertData['1000031'] = $value['eoPayType'];//付款方式
        $orderInsertData['1000119'] = $value['eoPayStatus'];//付款狀態
        $orderInsertData['1000031'] = $value['eoPayType'];//付款方式
        $orderInsertData['1000120'] = $value['eoPayDate'];//付款日期

        //發票相關
        $orderInsertData['1000121'] = $value['eoInvoiceTime'];//載具類型
        $orderInsertData['1000034'] = $value['eoInvoiceNo'];//發票號碼
        $orderInsertData['1000035'] = $value['eoInvoiceComNo'];//統一編號
        $orderInsertData['1000036'] = $value['eoInvoiceCom'];//發票抬頭
        $orderInsertData['1000032'] = $value['eoInvoiceType'];//載具類型
        

        //物流相關
        $orderInsertData['1000033'] = $value['eoIsReturn'];//是否退貨
        $orderInsertData['1000041'] = $value['eoDeliverName'];//出貨方式
        $orderInsertData['1000062'] = $value['eoShipProcess'];//出貨狀態
        $orderInsertData['1000156'] = $value['eoDeliverCvsID'];//門市ID
        $orderInsertData['1000157'] = $value['eoDeliverCvsName'];//門市名稱
        $orderInsertData['1000230'] = $value['eoDeliverCode'];//物流單號
        $orderInsertData['1000356'] = $value['eoShipProcess3Time'];//客戶取件日期
        $orderInsertData['1000357'] = $value['eoShipProcess2Time'];//出貨日期

        //如果訂單為退貨，針對訂購次統計欄位改寫
        if($value['eoIsReturn']=='Y'){
            $orderInsertData['1000158'] = 'N';//是否首購
            $orderInsertData['1000401'] = '';
            $orderInsertData['1000402'] = '';
            $orderInsertData['1000403'] = date('Y-m-d');//系統退貨日
        }

        //運費
        if($value['eoOrderShipAmount']>0){
            $orderInsertData['1000074'] = $value['eoOrderShipAmount'];
        }else{
            $orderInsertData['1000074'] = 0;
        }

        //折扣
        if($value['eoOrderDiscount']>0){
            $orderInsertData['1000073'] = $value['eoOrderDiscount'];
        }else{
            $orderInsertData['1000073'] = 0;
        }
         $orderInsertData['1000075'] = $value['eoOrderAmount'];

        //訂單客戶資料
        $orderInsertData['1000030'] = $value['eoOrderNo'];//訂單單號
        
        $orderInsertData['1000037'] = $value['eoReceiverName'];//姓名
        $orderInsertData['1000038'] = $value['eoReceiverPhone'];//電話
        $orderInsertData['1000039'] = $value['eoReceiverEmail'];//信箱

        $orderInsertData['1000077'] = $value['eoReceiverPostCode'];//郵遞區號
        $orderInsertData['1000040'] = $value['eoReceiverAddr'];//地址

        $orderInsertData['1000154'] = $value['eoMemberNote'];//客戶下單備註
        $orderInsertData['1000155'] = $value['eoInnerNote'];//店家備註

        //行銷活動
        $orderInsertData['1000142'] = $value['eoUtmCamp'];//行銷活動
        $orderInsertData['1000143'] = $value['eoUtmContent'];//折價券
        //新增列與更新列mark切換
        if($mode=='insert'){
            $mark = '-';
        }else if($mode=='update'){
            $mark = '';
        }
        //無須商品匯入
        if($detail['ignoreItem']=='Y'){
            return $orderInsertData;
        }

        //商品相關
        if($detail['multiItem']=='Y'){

        }else{
            $detail['itemData'][1] = $value;
        }
        $i = 1;
        foreach ($detail['itemData'] as $key => $value2) {
            if($mode=='insert'){
                $orderInsertData['1000043_'.$mark.$i] = $value2['eoItemNo'];//商品編號
                $orderInsertData['1000044_'.$mark.$i] = $value2['eoItemName'];//商品名稱
                $orderInsertData['1000045_'.$mark.$i] = $value2['eoItemSKU'];//商品規格編號
                $orderInsertData['1000046_'.$mark.$i] = $value2['eoItemType'];//商品規格
                $orderInsertData['1000089_'.$mark.$i] = $siteName;//來源名稱(子)
                $orderInsertData['1000090_'.$mark.$i] = $value2['eoOrderNo'];//來源編號(子)
                $orderInsertData['1000084_'.$mark.$i] = $value2['eoOrderDate'];//購買日期(子)            
                $orderInsertData['1000071_'.$mark.$i] = $value2['eoItemPrice'];//商品金額
                $orderInsertData['1000047_'.$mark.$i] = $value2['eoItemQty'];//數量
                $orderInsertData['1000048_'.$mark.$i] = $value2['eoItemSubTotal'];//小計

            }
            
            if($value2['eoOrderShipAmount']>0&&$i==1){
                $orderInsertData['1000049_'.$mark.$i] = $value2['eoOrderShipAmount'];//運費
            }else{
                $orderInsertData['1000049_'.$mark.$i] = 0;//運費
            }

            if($value2['eoOrderDiscount']>0&&$i==1){
                $orderInsertData['1000063_'.$mark.$i] = $value['eoOrderDiscount'];//折扣金額
                $orderInsertData['1000064_'.$mark.$i] = $value['eoUtmContent'];//折扣類型
            }else{
                $orderInsertData['1000063_'.$mark.$i] = 0;//折扣金額
                $orderInsertData['1000064_'.$mark.$i] = "";//折扣類型
            }

            $orderInsertData['1000088_'.$mark.$i] = $orderInsertData['1000023'];//會員流水編號(子)
            $orderInsertData['1000051_'.$mark.$i] = $value2['eoReceiverName'];//收貨人
            $orderInsertData['1000052_'.$mark.$i] = $value2['eoReceiverPhone'];//收貨人電話

            $orderInsertData['1000053_'.$mark.$i] = $value2['eoReceiverPostCode'];//郵遞區號
            $orderInsertData['1000054_'.$mark.$i] = $value2['eoReceiverAddr'];//收貨人地址
            $orderInsertData['1000078_'.$mark.$i] = $value2['eoReceiverEmail'];//收貨人信箱
            $orderInsertData['1000055_'.$mark.$i] = $value2['eoDeliverCode'];//物流單號
            $orderInsertData['1000079_'.$mark.$i] = $value2['eoDeliverName'];//出貨方式
            $orderInsertData['1000080_'.$mark.$i] = $value2['eoShipProcess'];//出貨狀態
            $orderInsertData['1000081_'.$mark.$i] = $value2['eoIsReturn'];//是否退貨
            if($value['eoDeliverCvsID']){
                $orderInsertData['1000056_'.$mark.$i] = $value2['eoDeliverCvsID'];//門市代號
                $orderInsertData['1000057_'.$mark.$i] = $value2['eoDeliverCvsName'];//門市名稱
                $orderInsertData['1000058_'.$mark.$i] = $value2['eoReceiverAddr'];//門市地址
            }

            $orderInsertData['1000122_'.$mark.$i] = $value2['eoShipProcess1Time'];//拋檔日期
            $orderInsertData['1000123_'.$mark.$i] = $value2['eoShipProcess2Time'];//倉庫出貨日期
            $orderInsertData['1000124_'.$mark.$i] = $value2['eoShipProcess3Time'];//客戶取件日期
            $i++;
        }

        return $orderInsertData;

    }


    function syncToRagic($formID,$insertData,$setParam=null){
        $str = '';
        foreach ($insertData as $fieldID => $fieldValue) {
            if(!$fieldValue){unset($insertData[$fieldID]);continue;}
            $str .= '&'.$fieldID.'='.urlencode($fieldValue);                
        }
        if($setParam['doDefaultValue']){
            $setStr .= '&doDefaultValue=true';
        }
        if($setParam['doFormula']){
            $setStr .= '&doFormula=true';
        }
        if($setParam['doLinkLoad']){
            $setStr .= '&doLinkLoad=true';
        }
        
        $url = RAGIC_DOMAIN.$formID."?v=3&api".$setStr;  
        $raw = $this->ragicCurl($url, $ckfile,$insertData);
        $returnData = json_decode($raw,true);
        //$this->Api_common->dataDump($insertData);
        //$this->Api_common->dataDump($returnData);exit;
        if($returnData['status']=='SUCCESS'){
            return $returnData;
        }else{
            return false;
        }
        
    }

    function getRagicFullText($searchForm,$searchValue,$searchMax){
        $url = RAGIC_DOMAIN.$searchForm."?fts=".urlencode($searchValue)."&subtables=0&listing=true";  
        $raw = $this->ragicCurl($url, $ckfile);
        $res = json_decode($raw,true);
        $num = 0;
        foreach ($res as $key => $value) {
            $ary[$num] = $res[$key];
            $num++;
        }

        return($ary);
    }

    function ragicCurl($url, $ckfile, $PostData=""){
        $agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER, false);
        if($PostData){
            $headers = array(
                'Authorization: Basic '.RAGIC_TOKEN
            );
            curl_setopt($ch, CURLOPT_POST, 1);               //submit data in POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
        }else{
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic '.RAGIC_TOKEN
            );
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            echo curl_errno($ch).":".str_replace("'","",curl_error($ch));
        }
        curl_close($ch);
        while ( !$Output ) {
            echo "wait...".PHP_EOL;
            sleep(3);
            $this->ragicCurl($url, $ckfile, $PostData);
        }
        

        return($Output);

    }

    function saveLog($jobName,$jobNum,$finishNum,$startTime){
        $insertData['1000313'] = $startTime;
        $insertData['1000314'] = $jobName;
        $insertData['1000315'] = $jobNum;
        $insertData['1000318'] = $finishNum;
        $returnData = $this->syncToRagic('forms4/3',$insertData,['doDefaultValue'=>true,'doFormula'=>true]);
    }
}
?>