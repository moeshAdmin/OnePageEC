<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class db_frConvert extends Ci_Controller {
	public function __construct() {
		parent::__construct( strtolower(__CLASS__) );
		set_time_limit(0);
		ini_set('memory_limit', '2048M');
		ini_set('display_errors', '0');
		$this->load->model('Api_common');
	}

	function getRagicDataTransOld(){
		$data = $_GET;

		$data['url'] = str_replace(array('^^','^','-'), array('&','?','/'), $data['url']);

		//$data['filter'] = '1000029,eq,2020/01/02';
		$data['filter'] = explode('^', $data['filter']);
		foreach ($data['filter'] as $key => $value) {
			$temp = explode(',', $value);
            if(substr_count($temp[2])>3){
                $qt = $temp[2];
            }else{
                $qt = urlencode($temp[2]);
            }
			$data['query'] .= '&where='.$temp[0].','.$temp[1].','.$qt;
		}

		if($data['limit']){
			$data['limit'] = '&limit='.$data['limit'];
		}

		$url = "https://ap3.ragic.com/hugePlus/".$data['url']."?v=3&api".$data['limit'].$data['query']; 
        
        $load = json_decode($this->Api_common->cache('load','ragic_'.base64_encode($data['url']),null,['fr'=>true]),true)['data'];
        if($load['data']&&$data['loadCache']=='true'){
            echo $load['data'];
            exit;
        }

		$raw = $this->Curl($url, $ckfile);
		$res = json_decode($raw,true);
		$isFirst = false;
		$detail['titleAry'] = array();
		//$this->Api_common->dataDump($res);
		if($data['subtable']){
			$subTablePreg = '/'.$data['subtable'].'/';
			$num = 0;
			foreach ($res as $key => $value) {
				foreach ($res[$key] as $key2 => $value2) {
					if(!$isFirst&&!preg_match('/_/', $key2)){
						array_push($detail['titleAry'], $key2);
					}
					if(!preg_match('/subtable/', $key2)){
						//$ary[$key.$num][$key2] = $res[$key][$key2];
					}
					if(preg_match('/subtable/', $key2)&&preg_match($subTablePreg, $key2)){
						foreach ($res[$key][$key2] as $key3 => $value3) {
							foreach ($res[$key][$key2][$key3] as $key4 => $value4) {
								if(!$isFirst){
									array_push($detail['titleAry'], $key4);	
								}
								$ary[$key.$num][$key4] = $res[$key][$key2][$key3][$key4];
								$parent = $res[$key][$key2][$key3]['_parentRagicId'];
								$ary[$key.$num] = array_merge($ary[$key.$num],$res[$parent]);
							}
							$num++;
						}
					}
					
				}
				$isFirst = true;
			}
			array_push($detail['titleAry'], '_ragicId');	
			$str = $this->convertFRTxt($ary,"two",$detail);
		}else if(!$data['subtable']){
			foreach ($res as $key => $value) {
				foreach ($res[$key] as $key2 => $value2) {
					if(!$isFirst){
						array_push($detail['titleAry'], $key2);
					}
				}			
				$isFirst = true;
			}
			array_push($detail['titleAry'], '_ragicId');	
			$str = $this->convertFRTxt($res,"two",$detail);
		}

		$this->Api_common->cache('save','ragic_'.base64_encode($data['url']),$str);
		echo $str;
	}

    function getRagicDataTrans($data=null){
        if(!$data){
            $data = $_GET;
        }
        $data['url'] = str_replace(array('^^','^','-'), array('&','?','/'), $data['url']);

        $filePath = DIR_SITE_FILE.'cache/ragicLog';
        $fp = fopen($filePath,'a+');
        fwrite($fp,'start-'.date('Y-m-d')."\r\n");
        fclose($fp);
        
        //$data['filter'] = '1000029,eq,2020/01/02';
        if($data['filter']){
            if(strpos($data['filter'],'$')>0){
                $data['filter'] = explode('$', $data['filter']);
            }else if(strpos($data['filter'],'^')>0){
                $data['filter'] = explode('^', $data['filter']);
            }
            foreach ($data['filter'] as $key => $value) {
                $temp = explode(',', $value);
                if(substr_count($temp[2])>3){
                    $qt = $temp[2];
                }else{
                    $qt = urlencode($temp[2]);
                }
                $data['query'] .= '&where='.$temp[0].','.$temp[1].','.$qt;
            }
        }

        $url = "https://ap3.ragic.com/hugePlus/".$data['url']."?v=3&api".$data['query']; 
        //echo $url;

        
        $filePath = DIR_SITE_FILE.'cache/ragicLog';
        $fp = fopen($filePath,'a+');
        fwrite($fp,$url."\r\n");
        fclose($fp);

        $load = json_decode($this->Api_common->cache('load','ragic_'.urlencode($url).$data['subtable'],null,['fr'=>true]),true)['data'];
        if(strlen($load['data'])>10&&$data['loadCache']=='true'&&$load['update']==date('Y-m-d')){
            echo $load['data'];
            exit;
        }else if($data['loadCache']!='true'){
            $this->removeCache();
        }else{
            $this->removeCache();
        }

        $res = $this->CurlBatch($url, $ckfile,0,null);

        $isFirst = false;
        $detail['titleAry'] = array();
        //$this->Api_common->dataDump($res);
        if($data['subtable']){
            $subTablePreg = '/'.$data['subtable'].'/';
            $num = 0;
            foreach ($res as $key => $value) {
                foreach ($res[$key] as $key2 => $value2) {
                    if(!$isFirst&&!preg_match('/_/', $key2)){
                        array_push($detail['titleAry'], $key2);
                    }
                    if(!preg_match('/subtable/', $key2)){
                        //$ary[$key.$num][$key2] = $res[$key][$key2];
                    }
                    if(preg_match('/subtable/', $key2)&&preg_match($subTablePreg, $key2)){
                        foreach ($res[$key][$key2] as $key3 => $value3) {
                            foreach ($res[$key][$key2][$key3] as $key4 => $value4) {
                                if(!$isFirst){
                                    array_push($detail['titleAry'], $key4); 
                                }
                                $ary[$key.$num][$key4] = $res[$key][$key2][$key3][$key4];
                                $parent = $res[$key][$key2][$key3]['_parentRagicId'];
                                $ary[$key.$num] = array_merge($ary[$key.$num],$res[$parent]);
                            }
                            $num++;
                        }
                    }
                }
                if($value['????????????']){
                    $items = explode(';', $value['????????????']);
                    foreach ($items as $key5 => $value5) {
                        $temp = explode('#',$value5);
                        $res[$key][$temp[0].'????????????'] += $temp[2];
                        $detail['titleAry'][$temp[0].'????????????'] = $temp[0].'????????????';
                    }
                }
                $isFirst = true;
            }
            //$this->Api_common->dataDump($detail['titleAry']);
            //$this->Api_common->dataDump($res);exit;
            array_push($detail['titleAry'], '_ragicId');    
            $str = $this->convertFRTxt($ary,"two",$detail);
        }else if(!$data['subtable']){
            foreach ($res as $key => $value) {
                foreach ($res[$key] as $key2 => $value2) {
                    if(!$isFirst){
                        array_push($detail['titleAry'], $key2);
                    }
                }
                if($value['????????????']){
                    $items = explode(';', $value['????????????']);
                    foreach ($items as $key5 => $value5) {
                        $temp = explode('#',$value5);
                        $res[$key][$temp[0].'????????????'] += $temp[2];
                        $detail['titleAry'][$temp[0].'????????????'] = $temp[0].'????????????';
                    }
                }

                //????????????????????????
                if($value['????????????']=='N'){
                    $skey = $value['??????????????????'].$value['????????????'].$value['???????????????'];
                    if($value['??????????????????']&&$data['url']=='forms/2'&&$multiple[$skey]&&!preg_match('/??????/', $value['????????????'])){
                        $res[$key]['????????????'] = 'Y';
                    }
                    $multiple[$skey] = 'Y';
                }
                $isFirst = true;
            }
            array_push($detail['titleAry'], '????????????');   
            array_push($detail['titleAry'], '_ragicId');    
            $str = $this->convertFRTxt($res,"two",$detail);
        }

        $this->Api_common->cache('save','ragic_'.urlencode($url).$data['subtable'],$str);
        echo $str;
    }

    function getOrderTrueItem(){
        $this->load->model('Api_ragic');
        if(!$_GET['date_from']||!$_GET['date_to']){exit;}
        $data = $_GET;
        if(!$data['source']){
            $data['source'] = '??????';
        }
        $filePath = DIR_SITE_FILE.'cache/ragicLog';
        $fp = fopen($filePath,'a+');
        fwrite($fp,'start-'.date('Y-m-d')."\r\n");
        fclose($fp);

        $date_from = date('Y-m-d',strtotime($data['date_from']));
        $date_to = date('Y-m-d',strtotime($data['date_to']));

        $url = 'https://ap3.ragic.com/hugePlus/forms/2?where=1000029,gte,'.$date_from.'&where=1000029,lte,'.$date_to.'&where=1000027,eq,'.$data['source'].'&limit=0,100000'; 

        $filePath = DIR_SITE_FILE.'cache/ragicLog';
        $fp = fopen($filePath,'a+');
        fwrite($fp,$url."\r\n");
        fclose($fp);
        
        $load = json_decode($this->Api_common->cache('load','ragic_'.urlencode($url).$data['source'],null,['fr'=>true]),true)['data'];
        if(strlen($load['data'])>10&&$data['loadCache']=='true'&&$load['update']==date('Y-m-d')){
            echo $load['data'];
            exit;
        }
        //echo $url;exit();
        $orderData = json_decode($this->Api_ragic->ragicCurl($url, $ckfile),true);
        foreach ($orderData as $key => $value) {
            foreach ($value['_subtable_1000061'] as $key2 => $value2) {
                if($value2['??????']>0){
                    $itemTotalAmount[$key] += $value2['??????'];
                }
            }
            if($value['????????????']){
                $items = explode(';', $value['????????????']);

                foreach ($items as $key5 => $value5) {
                    $temp = explode('#',$value5);
                    //echo $value['??????????????????'].'---'.$temp[0].'---'.$temp[3].'<br>';
                    if($temp[2]>0){
                        $retData[$key.$key5]['??????????????????'] = $value['??????????????????'];
                        $retData[$key.$key5]['????????????'] = $value['????????????'];
                        $retData[$key.$key5]['????????????'] = date('Ym',strtotime($value['????????????']));
                        $retData[$key.$key5]['????????????'] = $value['????????????'];
                        $retData[$key.$key5]['????????????'] = $value['????????????'];
                        $retData[$key.$key5]['??????'] = $temp[0];
                        $retData[$key.$key5]['??????'] = $temp[1];
                        $retData[$key.$key5]['??????'] += $temp[2];
                        $retData[$key.$key5]['??????'] += str_replace('$', '', $temp[3]);
                        $retData[$key.$key5]['?????????'] = $value['?????????'];
                        $retData[$key.$key5]['????????????'] = $value['????????????'];
                        $retData[$key.$key5]['????????????'] = $value['????????????'];
                        $retData[$key.$key5]['???????????????'] = $itemTotalAmount[$key];    
                        $retData[$key.$key5]['???????????????'] = $value['???????????????']-$retData[$key.$key5]['???????????????'];                 
                        $trueItemTotalAmount[$key] += $retData[$key.$key5]['??????'];
                    }
                }
                foreach ($items as $key5 => $value5) {
                    if($retData[$key.$key5]['??????????????????']){
                        $retData[$key.$key5]['?????????????????????'] = $trueItemTotalAmount[$key];
                        if($retData[$key.$key5]['?????????????????????']==$retData[$key.$key5]['???????????????']){
                            $retData[$key.$key5]['????????????????????????'] = 'Y';
                        }else{
                            $retData[$key.$key5]['????????????????????????'] = 'N';
                        }
                        
                    }
                    
                }
            }
        }
        foreach ($retData as $key => $value) {
            if(!$value['??????????????????']){continue;}
            $retData[$key]['????????????'] = $value['??????']+round(($value['???????????????']/$value['?????????????????????']*$value['??????']),0);
        }
        //$this->Api_common->dataDump($retData);
        $detail['titleAry'] = ['??????????????????','????????????','????????????','????????????','????????????','??????','??????','??????','??????','?????????','????????????','???????????????','????????????','???????????????','?????????????????????','????????????????????????','????????????'];
        $str = $this->convertFRTxt($retData,"two",$detail);
        $this->Api_common->cache('save','ragic_'.urlencode($url).$data['source'].$data['subtable'],$str);
        echo $str;
    }

    private function CurlBatch($url, $ckfile,$cycle,$ret=null){
        $num = 1000;
        $data['limit'] = '&limit='.$num*$cycle.','.$num;
        $load = json_decode($this->Api_common->cache('load','curl_'.urlencode($url.$data['limit']),null,['fr'=>true]),true)['data'];
        if($load){
            $raw2 = json_decode($load['data'],true);
        }else{
            $raw = $this->Curl($url.$data['limit'], $ckfile);
            $this->Api_common->cache('save','curl_'.urlencode($url.$data['limit']),$raw);
            $raw2 = json_decode($raw,true);
        }

        if(count($raw2)==$num){
            foreach ($raw2 as $key => $value) {
                $ret[$key] = $value;
            }
            //echo count($ret)."<br>\r\n";
            if(strpos($url, ',&')>0){
                return $ret;
            }else{
                //echo $url.'--->Cycle:'.$cycle."<br>\r\n".PHP_EOL;
                $cycle++;
                return $this->CurlBatch($url, $ckfile,$cycle,$ret);
            }
        }else{
            //echo count($ret)."<br>\r\n";
            //echo "--->Cycle:Final<br>\r\n".PHP_EOL;
            foreach ($raw2 as $key => $value) {
                $ret[$key] = $value;
            }
            return $ret;
        }

    }

    private function convertFRTxt($json,$mode,$detail){

        if($mode=="two"){
            $isFirst = 1;
            foreach ($json as $key => $value) {
                foreach ($detail['titleAry'] as $key2 => $fieldName) {
                    if(preg_match('/_/',$fieldName)&&$fieldName!="_ragicId"){continue;}
                    //??????
                    if($isFirst==1){
                        $head .= $fieldName."||";               
                    }
                    if(in_array($fieldName, $detail['titleAry'])){
                        $body .= str_replace("\r\n", "", $json[$key][$fieldName])."||";
                    }
                }
                /*
                foreach ($json[$key] as $fieldName => $fieldValue) {
                    if(preg_match('/_/',$fieldName)&&$fieldName!="_ragicId"){continue;}
                    //??????
                    if($isFirst==1&&in_array($fieldName, $detail['titleAry'])){
                        $head .= $fieldName."||";               
                    }
                    if(in_array($fieldName, $detail['titleAry'])){
                        $body .= str_replace("\r\n", "", $fieldValue)."||";
                    }
                }*/
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
    private function Curl($url, $ckfile, $PostData=""){
        
        $agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER, false);
        $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic UGRJdEN1eFhEVk5PWHBPZ0JYeEMwQ21RS1dpYkNTTHM2dmU1RS9iR1pEKzlDdmlzRWpLYjdtY1NkQXAveTZuSA=='
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if($PostData != ""){
            curl_setopt($ch, CURLOPT_POST, 1);               //submit data in POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
        }
        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            echo curl_errno($ch).":".str_replace("'","",curl_error($ch));
        }
        
        while ( !$Output ) {
        ???usleep(1000);
        }
        curl_close($ch);

        return($Output);


        
    }

    private function removeCache(){
        $this->load->helper('file');
        $fileAry = get_dir_file_info(APPPATH.'files/cache');
        foreach ($fileAry as $fileName => $value) {            
            if(preg_match('/curl_|ragic_/si', $fileName)){
                unlink(APPPATH.'files/cache/'.$fileName);
            }            
        }
    }

    //?????????????????????FR??????
    function getFullRagicOrder(){
        $startTime = date('Y-m-d H:i:s');
        $this->load->model('Api_ragic');

        $data['url'] = 'forms/2';
        $data['subtable'] = '1000061';
        $data['loadCache'] = 'false';
        $res = $this->getRagicDataTrans($data);

        $this->Api_ragic->saveLog('FR???????????????',0,0,$startTime);
    }

    function cptConvert(){
    	$data = $_GET;

		//????????????
		$url = "http://localhost:8075/webroot/decision/login/cross/domain?fine_username=tti_ep&fine_password=".urlencode('n&p$V3_D')."&validity=-1&callback=";
		$ckfile = tempnam(DIR_SITE_FILE, "CURLCOOKIE.txt");
		$output = $this->Curl($url,$ckfile);

		//??????FR
		$data['reportlet'] = 'hpec/??????????????????.cpt';
		$reportlet = urlencode($data['reportlet']);
		$url = 'http://localhost:8075/webroot/decision/view/report?op=export&viewlet='.urlencode($reportlet).'&format=text';
		$output = $this->Curl($url,$ckfile);
		$output = mb_convert_encoding($output, "UTF-8", "BIG5");
		
		echo str_replace('	', '||', $output);
		exit;
	}	

    function getCalender($date_from=null,$date_to=null,$type=null,$mode=null){
        if(!$date_from){
            $date_from = date('Y/m/d',strtotime($_GET['date_from']));
            $date_to = date('Y/m/d',strtotime($_GET['date_to']));
            $type = $_GET['type'];
        }
        $count = 1;
        if($type=="month"){
            $keyType = 'Y/m';
        }else if($type=="month2"){
            $keyType = 'Ym';
        }else if($type=="year"){
            $keyType = 'Y';
        }else if(strlen($type)>0){
            $keyType = $_GET['type'];
        }
        $week = 1;
        for($i=0;$i<$count;$i++){
            $date = date('Y/m/d',strtotime($date_from)+86400*$i);
            $targetDate = date($keyType,strtotime($date));
            $dateAry[$targetDate]['??????'] = $targetDate;
            
            if(count($holidayAry[$targetDate])>0){
                $dateAry[$targetDate]['????????????'] = $holidayAry[$targetDate]['????????????'];
                $dateAry[$targetDate]['??????'] = $holidayAry[$targetDate]['??????'];
                $dateAry[$targetDate]['??????'] = $holidayAry[$targetDate]['??????'];
            }else{
                $dateAry[$targetDate]['????????????'] = 'N';
                $dateAry[$targetDate]['??????'] = '';
                $dateAry[$targetDate]['??????'] = '';
            }
            $dateAry[$targetDate]['??????'] = date('W',strtotime($targetDate));
            //$dateAry[$targetDate]['??????2'] = floor(date('d',strtotime($targetDate))/7)+1;
            
            if(date('w',strtotime($targetDate))==0){
                $week++;
            }
            $dateAry[$targetDate]['??????2'] = $week;

            $dateAry[$targetDate]['??????'] = date('w',strtotime($targetDate));
            $dateAry[$targetDate]['???'] = date('Y',strtotime($date));
            $dateAry[$targetDate]['???'] = date('m',strtotime($date));
            $dateAry[$targetDate]['???'] = date('d',strtotime($date));
            if(strtotime($date)<strtotime($date_to)){
                $count++;
            }           
        }
        if(!$mode){
            $detail['titleAry'] = array('??????','????????????','??????','??????','??????','??????2','??????','???','???','???');   
            echo $this->convertFRTxt($dateAry,"two",$detail);
        }else{
            return $dateAry;
        }
        
    }
}
