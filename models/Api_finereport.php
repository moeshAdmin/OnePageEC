<?php
class Api_finereport extends CI_Model{
    function __construct() {
        parent::__construct();
    }    
    function fr_login($domain){
        $url = $domain."/login/cross/domain?fine_username=".FR_ACCOUNT."&fine_password=".FR_PASS."&validity=-1&callback=";
        $output = $this->Api_common->frCurl($url,DIR_SITE_FILE.'CURLCOOKIE.txt');
        return $output;        
    }

    function cptConvert($postData){
        $reportlet = $postData['reportlet'];
        $type = $postData['type'];
        $domain = FR_DOMAIN.'/webroot/decision';
        $login = 0;

        //登入
        while ($login<3) {
            $result = $this->fr_login($domain);
            if(strpos($result, '"status":"success"')>0){
                $login = 99;
            }else{
                sleep(3);
                $login++;
            }
        }
        
        if($login!=99){
            return 'error';
        }
        
        //格式判斷
        if($type=='pic'){
            $format = 'format=image&extype=PNG';
        }else if($type=='excel'){
            $format = 'format=excel&extype=page';
        }else if($type=='pdf'){
            $format = 'format=pdf';
        }else if($type=='test'){
            $format = 'ref_t=design&op=view';
        }else{
            $format = 'format=text';
        }
        //參數串接
        foreach ($postData['param'] as $key => $value) {
            if(str_replace('%', '', $value)!=$value){
                $param .= '&'.$key.'='.urlencode($value);
            }else{
                $param .= '&'.$key.'='.$value;
            }
        }
        
        $url = $domain.'/view/report?op=export&viewlet='.urlencode($reportlet).'&'.$format.$param;

        if($type=='excel'){
            //excel直接引導下載
            //return '<script>window.location.href = "'.$url.'";</script>';exit;
        }
        //其餘擷取後顯示
        $output = $this->Api_common->frCurl($url,DIR_SITE_FILE.'CURLCOOKIE.txt');

        //如果遇到錯誤重試
        if(strpos($output, 'Sorry, an error occurs.')>0){
            sleep(3);
            $postData['retry']++;
            if($postData['retry']<3){
                return $this->cptConvert($postData);
            }else{
                return '<script>alert("fr_error");</script>fr_error';exit;
            }
        }
        if($type=='pic'){
            $imguri = base64_encode($output);
            return '<img style="width:100%" src="data:image/png;base64,'.$imguri.'">';
        }else{
            if($type=='pdf'){
                header('Content-Type: application/pdf');
                header("Content-Disposition:filename=".$postData['fileName'].".pdf");
                readfile("original.pdf");
            }else if($type=='excel'){
                //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                //header("Content-Disposition:filename=".$postData['fileName'].".xlsx");
                //readfile("original.xlsx");
            }
            return $output;
        }
        exit;
    }  

    function convertFRTxt($json,$mode,$detail){

        if($mode=="two"){
            $isFirst = 1;
            foreach ($json as $key => $value) {
                foreach ($json[$key] as $fieldName => $fieldValue) {
                    if(preg_match('/_/',$fieldName)&&$fieldName!="_ragicId"){continue;}
                    //表頭
                    if($isFirst==1&&in_array($fieldName, $detail['titleAry'])){
                        $head .= $fieldName."||";               
                    }
                    if(in_array($fieldName, $detail['titleAry'])){
                        $body .= str_replace("\r\n", "", $fieldValue)."||";
                    }
                    
                }
                if($body){
                    $body .= "\r\n";
                }
                $isFirst=0;
            }
            return $head."\r\n".$body;
        }else if($mode=="three"){
            foreach ($detail['titleAry'] as $key => $value) {
                $head .= $value."||";
            }
            foreach ($json as $key => $value) {
                foreach ($json[$key] as $key2 => $value2) {
                    foreach ($detail['titleAry'] as $key3 => $title) {
                        if(!$json[$key][$key2][$title]){
                            $json[$key][$key2][$title] = '0';
                        }
                        $body .= $json[$key][$key2][$title]."||";
                    }
                    $body .= "\r\n";
                }
            }
            return $head."\r\n".$body;
        }

    }

}
?>