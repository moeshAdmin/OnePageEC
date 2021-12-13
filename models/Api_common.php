<?php
class Api_common extends CI_Model{
    function __construct() {
        parent::__construct();
        date_default_timezone_set("Asia/Taipei");
    }

    function getDataCustom($select,$from,$where,$order=null,$custom=null){
        $this->db->select($select);
        $this->db->from($from);
        if($where!='all'){
            $this->db->where($where);
        }
        if($order){
            $this->db->order_by($order);
        }
        if($custom['limit']){
            $this->db->limit($custom['limit']);
        }
        if($custom['group_by']){
            $this->db->group_by($custom['group_by']);
        }
        $query = $this->db->get();
        $return = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                if($custom['manage']!='Y'&&preg_match('/_ignore/', $row['ccName'.LANG].$row['caTitle'.LANG])){continue;}
                if($custom['manage']!='Y'&&$row['caType'.LANG]=="private"){continue;}
                array_push($return, $row);
            }
        }
        return $return;
    }

    function getDataJoin($select,$from,$where,$detail){
        $this->db->select($select);
        $this->db->from($from);
        $this->db->where($where);
        if($detail['join']){
            foreach ($detail['join'] as $key => $value) {
                $this->db->join($detail['join'][$key],$detail['joinwhere'][$key]);
            }
        }
        if($detail['order']){
            $this->db->order_by($detail['order']);
        }
        if($detail['limit']){
            $this->db->limit($detail['limit']);
        }
        if($detail['groupby']){
            $this->db->group_by($detail['groupby']);
        }
        $query = $this->db->get();
        $return = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                array_push($return, $row);
            }
        }
        return $return;
    }

    function getDataInCustom($select,$from,$where,$ary,$custom,$type,$detail=null){
        $this->db->select($select);
        $this->db->from($from);
        
        if($type=="in"){
            foreach ($ary as $key => $value) {
                if($value==""){unset($ary[$key]);}
            }
            if(count($ary)>0){}else{return;}
            $this->db->where_in($where,$ary);
        }else if($type=="in_or"&&is_array($where)){
            foreach ($ary as $key => $value) {
                if($value==""){unset($ary[$key]);}
            }
            if(count($ary)>0){}else{return;}
            foreach ($where as $key => $value) {
                $this->db->or_where_in($where[$key],$ary[$key]);
            }
            
        }else if($type=="regex"){
            foreach ($ary as $key => $value) {
                if($value==""){unset($ary[$key]);}
            }
            if(count($ary)>0){}else{return;}
            $value = str_replace(';', '|', $this->setArrayToList($ary));
            $this->db->where($where." REGEXP '".$value."'");
        }
        if($custom!='none'){
            if(preg_match('/_OR_/', $custom)){
                $this->db->or_where(str_replace('_OR_', '',$custom));
            }else if(preg_match('/DESC|ASC/', $custom)){
                $this->db->order_by($custom);
            }else{
                $this->db->where($custom);
            }
        }
        $query = $this->db->get();
        $return = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                if($detail['manage']!='Y'&&preg_match('/_ignore/', $row['ccName'.LANG].$row['caTitle'.LANG])){continue;}
                if($detail['manage']!='Y'&&$row['caType'.LANG]=="private"){continue;}
                array_push($return, $row);
            }
        }
        return $return;
    }
    
    function getSysConfig($type){
        $this->db->select('scName,scValue1,scValue2,scValue3,scValue4,scValue5');
        $this->db->from('sys_config');
        $this->db->where('scName',$type);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                $return = $row;
            }
        }
        return $return;
    }

    function getSysKey($type){
        $this->db->select('skName,skAccount,skPassword,skNote');
        $this->db->from('sys_key');
        $this->db->where('skName',$type);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row){
                $return = $row;
            }
        }
        return $return;
    }  

    function dataDump($data,$set=null){//dump指令
        if(!$set){
            echo '<pre>'.var_export($data,true).'</pre>';
        }else{
            return '<pre>'.var_export($data,true).'</pre>';
        }
    }

    function stringHashTest($value){//hash加密解密測試
        $value = $this->Api_common->stringHash('encrypt',$value);
        echo '加密:'.$value.'--->解密:';
        echo $this->Api_common->stringHash('decrypt',$value);
    }

    function stringHash($action, $string) {//hash加密解密
        $output = false;

        $skey = $this->getSysConfig('secretKey');
        
        $encrypt_method = "AES-256-CBC";
        $secret_key = $skey['scValue1'];
        $secret_iv = $skey['scValue2'];
     
        // hash
        $key = hash('sha256', $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
     
        if( $action == 'encrypt' ) {
            $string = base64_encode($string);
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            $output = base64_decode($output);
        }
        $output = str_replace('=', '', $output);
        return $output;
    }

    function serverHash($action,$string) {//hash加密解密
        $encrypt_method = "AES-256-CBC";

        //$skey = $this->getSysConfig('secretKey');
        
        $encrypt_method = "AES-256-CBC";
        //$secret_key = $skey['scValue1'];
        //$secret_iv = $skey['scValue2'];
        $secret_key = SERVER_SECRET_KEY;
        $secret_iv = SERVER_SECRET_IV;
        
        // hash
        $key = hash('sha256', $secret_key);        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
     
        if( $action == 'encrypt' ) {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        $output = str_replace('=', '', $output);
        return $output;
    }

    function jsonHash($action,$str){
        $data = "vWivFURIj8pSyIqQXBkjlg==";
        $key = MD5('&s45ASgj767Hs');
        $iv = '%^ashgSDFHrtd*dfgER';
        if( $action == 'encrypt' ) {
            $output = openssl_encrypt($str, "AES-256-CBC", $key, 0, $iv);
            $output = base64_encode($output);
        }
        else if( $action == 'decrypt' ){
            $output = openssl_decrypt(base64_decode($str), "AES-256-CBC", $key, 0, $iv);
        }
        return $output;
    }

    function setFrontReturnMsg($errorCode,$extMsg,$data){
        switch ($errorCode) {
            case '200':
                $json['status'] = 'SUCCESS';
                $json['code'] = '200';
                $json['msg'] = ''.$extMsg;
                break;
            case '401':
                $json['status'] = 'ERROR';
                $json['code'] = '401';
                $json['msg'] = ''.$extMsg;
                break;
            case '901':
                $json['status'] = 'ERROR';
                $json['code'] = '901';
                $json['msg'] = ''.$extMsg;
                break;
            case '902':
                $json['status'] = 'ERROR';
                $json['code'] = '902';
                $json['msg'] = '查無結果！'.$extMsg;
                break;
            case '903':
                $json['status'] = 'ERROR';
                $json['code'] = '903';
                $json['msg'] = '資料庫錯誤！'.$extMsg;
                break;
            case '904':
                $json['status'] = 'CHK';
                $json['code'] = '904';
                $json['msg'] = '檢查結果:'.$extMsg;
                break;
        }     
        $json['data'] = $data;  
        $json['showMsg'] = $json['status']." ".$json['code']."：".$json['msg'];

        if($json['code']!='200'){
            $this->load->library('user_agent');
            $user_detail=$this->session->all_userdata();
            $this->saveData(DIR_SITE_FILE."temp/access_log/access_log_".$user_detail['empID'].".txt","a+",$errorCode."--->".date('Y-m-d H:i:s')."----->return--->".$user_detail['now_page']."---->".$errorCode."---->".$this->input->ip_address().'--->'.$_SERVER['HTTP_X_REAL_IP']."--->".$this->agent->browser().$this->agent->version()."----->".$json['msg']."\r\n");
        }
        
        if($_SERVER['argv']&&$_SERVER['argv'][6]==BACKEND_KEY){
            echo $json['showMsg'].PHP_EOL;
            if($json['code']!='200'){
                $data = array('recipient'=>[SYS_MAILER],'cc'=>'', 'subject' => mb_convert_encoding('自動作業發生異常', "UTF-8","auto"), 'content' => $json['showMsg'],'sender'=>'Service');   
                $this->my_sendmail->sendOut($data);
            }
        }else{
            return json_encode($json,true);
        }        
    }

    function setArrayToList($ary){
        foreach ($ary as $key => $value) {
            if(!$data){
                $data = $value;
            }else{
                $data .= ';'.$value;
            }
        }
        return $data;
    }

    function cleanPostData($postData){

        foreach ($postData as $key => $value) {
            $postData[$key] = $this->security->xss_clean($postData[$key]);
            $postData[$key] = str_replace(["'",'"',';','%','--'],'', $postData[$key]);
            if(is_array($postData[$key])){
                foreach ($postData[$key] as $key2 => $value2) {
                    $postData[$key][$key2] = str_replace(array("'",'"',';'),'', $postData[$key][$key2]);
                    $postData[$key][$key2] = strip_tags(htmlentities(trim($value2)));
                }
            }else{
                if($value=='null'){$postData[$key]='';}
                $postData[$key] = strip_tags(htmlentities(trim($postData[$key])));
            }
        }
        return $postData;
    }

    function cleanPostNull($postData){
        foreach ($postData as $key => $value) {
            if(is_array($postData[$key])){
                foreach ($postData[$key] as $key2 => $value2) {
                    $postData[$key][$key2] = str_replace(array('null'), '', trim($value2));
                }
            }else{
                $postData[$key] = str_replace(array('null'), '', trim($value));
            }
        }
        return $postData;
    }

    function getCurl($url,$postData,$header=null,$ckfile=null){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko");
        curl_setopt($ch, CURLOPT_URL, $url);       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if($postData&&$header&&!preg_match('/json/', $header[0])){
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($postData, null, '&'));
        }else if($postData){
            curl_setopt($ch, CURLOPT_POST, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        }
        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            //echo curl_errno($ch).":".str_replace("'","",curl_error($ch));
        }
        curl_close($ch);
        if($postData['html']){
            return $Output;
        }else{
            $Output = str_replace(array("\r","\n"),"", strip_tags($Output));
            return $Output;
        }
    }

    function saveJSON($filePos,$writeType,$contentAry) {

        $fp = fopen($filePos,$writeType);
        if(!$fp){
            echo "System Error";
            exit();
        }else{
            $content = json_encode($contentAry);
            fwrite($fp,$content);
            fclose($fp);
        }
    }

    function basicCurl($url, $ckfile, $PostData=""){
        
        $agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($PostData['refer']){
            curl_setopt($ch, CURLOPT_REFERER, $PostData['refer']);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if($PostData != ""){
            curl_setopt($ch, CURLOPT_POST, 1);               //submit data in POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($PostData, null, '&'));
        }
        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            return('ERROR'.curl_errno($ch).":".str_replace("'","",curl_error($ch)));
        }

        curl_close($ch);

        return($Output);        
    }

    function sendMail($data){
        $this->load->library('MY_SendMail');

        $result = $this->my_sendmail->sendOut($data);
        if(preg_match('/Success/', $result)){
            return '200';
        }else{
            return '999';
        }
    }

    function saveData($filePos,$writeType,$content) {
        if($_SERVER['argv']&&$_SERVER['argv'][6]==BACKEND_KEY){
            echo $content.PHP_EOL;
        }
        $fp = fopen($filePos,$writeType);
        if(!$fp){
        }else{
            fwrite($fp,$content);
            fclose($fp);
        }
    }

    function chkBrowser(){
        $this->load->library('user_agent');
        if($this->agent->browser()=="Internet Explorer"&&$this->agent->version()<=9.0){
            redirect(base_url().'auth/users/basic?url='.str_replace(SUB_SITE_PATH.'/', '', $_SERVER['REQUEST_URI']));
            exit();
        }
    }

    function chkBlockIP(){
        if($_SERVER['HTTP_X_REAL_IP']){
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $ip = $this->input->ip_address();
        } 
        $chk = $this->Api_common->getDataIsExist('sbBlockIP,sbBlockReason','sys_block','sbBlockIP = "'.$ip.'" AND sbBlockTime > "'.date('Y-m-d H:i:s',strtotime('-'.IP_BLOCK_HOUR.' hours')).'"');
        if($chk['row']>0){
            echo 'IP address has been blocked for security reasons, Please contact IT.';
            exit;
        }
    }

    function chkBlockTime(){
        if($_SERVER['HTTP_X_REAL_IP']){
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $ip = $this->input->ip_address();
        } 
        $chk = $this->Api_common->getDataIsExist('sbBlockIP,sbBlockTime','sys_block','sbBlockIP = "'.$ip.'"');
        return $chk['row'];
    }

    function insertIPBlock($reason){
        $insertData['sbBlockTime'] = date('Y-m-d H:i:s');
        if($_SERVER['HTTP_X_REAL_IP']){
            $insertData['sbBlockIP'] = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $insertData['sbBlockIP'] = $this->input->ip_address();
        }        
        $insertData['sbBlockReason'] = $reason;
        $insertData['sbBlockDate'] = date('Y-m-d');
        $this->db->insert('sys_block', $insertData); 

        return $this->chkBlockTime();
    }

    function getDataIsExist($select,$from,$where){//判斷資料應新增或更新
        $this->db->select($select);
        $this->db->from($from);
        $this->db->where($where);
        $query = $this->db->get();
        $ary['data'] = array();
        if ($query->num_rows() > 0) {
            $ary['row'] = $query->num_rows();
            $ary['mode'] = 'update';
            foreach ($query->result_array() as $row){
                array_push($ary['data'], $row);
            }
        }else{
            $ary['row'] = 0;
            $ary['mode'] = 'insert';
        }
        return $ary;
    }  

    function getCookie($name){
        $this->load->helper('cookie');
        return $this->input->cookie($name);
    }

    function buildTree($elements, $parentId = 0,$num = 0) {
        $branch = array();
        $num++;
        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = $this->buildTree($elements, $element['id'],$num);
                if ($children) {
                    $element['children'] = $children;
                }
                $element['nest'] = $num;
                if(!isset($branch)){
                    $branch = array();
                }
                array_push($branch, $element);
                //unset($elements[$element['id']]);
            }
        }
        return $branch;
    }

    function treeToAry($res,$treeData,$nest,$num){
        $num++;
        foreach ($treeData as $key => $value) {
            $item['id'] = $treeData[$key]['id'];
            $item['name'] = ''.$nest.'└'.$treeData[$key]['name'];
            array_push($res, $item);
            if($treeData[$key]['children']){
                $res = $this->treeToAry($res,$treeData[$key]['children'],$nest.'　  ',$num);
            }
        }
        return $res;
    }

    function loadCateTree(){
        $resData = $this->Api_common->getDataCustom('*','cms_categorys','ccIsDel = "N"',null,array('manage'=>'Y'));
        foreach ($resData as $key => $value) {
            $element[$key]['id'] = $resData[$key]['ccSysID'];
            $element[$key]['parent'] = (int)$resData[$key]['ccParent'];
            $element[$key]['hash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['ccSysID']);
            if($resData[$key]['ccName'.LANG]){
                $element[$key]['name'] = $element[$key]['id'].'_'.$resData[$key]['ccName'.LANG];
            }else{
                $element[$key]['name'] = $element[$key]['id'].'_'.$resData[$key]['ccName'.'EN'].'(EN)';
            }
            $parentData[$resData[$key]['ccSysID']]['name'] = $resData[$key]['ccName'.LANG];
        }
        foreach ($element as $key => $value) {
            $element[$key]['parentName'] = $parentData[$element[$key]['parent']]['name'];
        }
        $treeData = $this->Api_common->buildTree($element,0);
        $retData['parentOptionData']['cate'] = array();
        $retData['parentOptionData']['cate'] = $this->Api_common->treeToAry($retData['parentOptionData']['cate'],$treeData,'　',0);

        $retData['name'] = 'root';
        $retData['id'] = '';
        $retData['hash'] = '';
        $retData['children'] = $treeData;
        return $retData;
    }

    function cache($type,$fileName,$data=null,$detail=null){
        if($_GET['cache']=='false'){return;}
        if(!LANG){$lang = 'TW';}else{$lang = LANG;}
        $url = str_replace(array('https://','http://',':','/'), '', base_url());
        if($type=="load"){
            $loadData = file_get_contents(DIR_SITE_FILE."cache/".$fileName.".json","w+");
            $loadData = json_decode($loadData,true);
            if($loadData['update']){
                if($detail['fr']){
                    return $this->Api_common->setFrontReturnMsg('200','',$loadData);
                }else if($detail['return']){
                    return $this->Api_common->setFrontReturnMsg('200','',$loadData['data']);
                }else{
                    echo $this->Api_common->setFrontReturnMsg('200','',$loadData['data']);
                    exit;
                }                
            }
        }else if($type=="save"){
            $this->Api_common->saveJSON(DIR_SITE_FILE.'cache/'.$fileName.'.json','w+',array('data'=>$data,'update'=>date('Y-m-d')));
        }else if($type=="titleSave"){
            $this->Api_common->saveJSON(DIR_SITE_FILE.'cache/'.$fileName.'.json','w+',array('data'=>$data,'update'=>date('Y-m-d')));
        }else if($type=="titleCache"){
            $loadData = file_get_contents(DIR_SITE_FILE."cache/".$fileName.".json","w+");
            $loadData = json_decode($loadData,true);
            return $loadData;
        }
    }

    function browserLog($user_detail,$nowPage){
        $this->load->library('user_agent');
        $preg = '/'.BLOCK_URL.'/';
        $lock = false;

        //連結分析
        $path_info = explode('?', $_SERVER['REQUEST_URI']);
        $urlData = explode('/', $path_info[0]);
        foreach ($urlData as $key => $value) {
            //大於十個字>視為hash
            if($key<1){continue;}
            if(strlen($value)>10&&$this->Api_common->stringHash('decrypt',$value)){
                //大於十個字，且解的開
            }else if(preg_match($preg, strtolower($value))){
                $lock = true;
                $this->insertIPBlock('attack-auto_'.$_SERVER['REQUEST_URI']);
            }
        }

        //針對GET參數分析
        $getData = explode('&', $_SERVER['QUERY_STRING']);
        foreach ($getData as $key => $value) {
            $temp = explode('=', $value);
            if($temp[0]=='fbclid'||$temp[0]=='gclid'||$temp[0]=='code'){continue;}
            if(strlen($temp[1])>10&&$this->Api_common->stringHash('decrypt',$temp[1])&&
               !preg_match($preg, strtolower($temp[0]))){
                //如果value>10個字元且value解開
                //且key檢查過關
            }else if(
                preg_match($preg, strtolower($temp[0]))||
                preg_match($preg, strtolower($temp[1]))
                ){
                //key或value需檢查
                $lock = true;
                $this->insertIPBlock('attack-auto_'.$_SERVER['REQUEST_URI']);
            }
        }
        
        /*
        if(preg_match($preg, strtolower($_SERVER['REQUEST_URI']))){
            $lock = true;
            $this->insertIPBlock('attack-auto_'.$_SERVER['REQUEST_URI']);
        }
        
        if(strpos($_SERVER['REQUEST_URI'], '?')>0){
            $payload = explode('?', $_SERVER['REQUEST_URI']);
            $preg = '/'.BLOCK_PREG.'/';
            foreach ($payload as $key => $value) {
                if($key==0){continue;}
                if(preg_match($preg, strtolower($value))){
                    $lock = true;
                }
            }
            if($lock&&BLOCK_PREG){
                $this->insertIPBlock('attack-auto_'.$value);
            }
        }*/
        
        if($lock){
            $bb = $this->agent->browser().$this->agent->version().'--LOCKED';
        }else{
            $bb = $this->agent->browser().$this->agent->version();
        }
        
        $this->saveData(DIR_SITE_FILE."temp/access_log/access_log_".$user_detail['empID'].".txt","a+",$user_detail['empID']."--->".date('Y-m-d H:i:s')."----->".$this->agent->referrer()."--->".$nowPage[3]."---->".$user_detail['account']."---->".$this->input->ip_address().'--->'.$_SERVER['HTTP_X_REAL_IP']."--->".$bb."----->".$_SERVER['REQUEST_URI']."----->".$nowPage[4]."----->".$this->setArrayToList($_POST).$this->setArrayToList($_GET)."\r\n");
    }

    function redirectHttps(){
        if ($_SERVER["HTTPS"] <> "on"&&strpos($_SERVER["SERVER_NAME"], 'tti.tv')>0){ 
            $xredir="https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]; 
            header("Location: ".$xredir); 
        }
    }

    function initLang(){
        if(!$this->Api_common->getCookie('lang')){
            $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if(strtolower($lang[0])=='ja-JP'){
                setcookie('lang', 'JP', time() + (3600 * 4), "/");
            }else{
                setcookie('lang', 'TW', time() + (3600 * 4), "/");
            }
        }
    }

    function initGoogleLD($type,$data,$detail=null){


    }

    function downloadFile($hash){
        $hash = $this->Api_common->stringHash('decrypt',$hash);
         $file_name = $hash.".xlsx";
         $file_path = DIR_SITE_FILE."report/".$hash.".xlsx";
         $file_size = filesize($file_path);
         header('Pragma: public');
         header('Expires: 0');
         header('Last-Modified: ' . gmdate('D, d M Y H:i ') . ' GMT');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Cache-Control: private', false);
         header('Content-Type: application/octet-stream');
         header('Content-Length: ' . $file_size);
         header('Content-Disposition: attachment; filename="' . $file_name . '";');
         header('Content-Transfer-Encoding: binary');
         readfile($file_path);

    }

    function saveReceiveMsg($type,$fileName,$detail=null){
        $str .= '--------------------------------------------------------'."\n";
        if($type=='receive'){
            $str .= $type.$detail['msg'].'-------------'.date('Y-m-d H:i:s')."\n";
            $input = json_decode(file_get_contents('php://input'), true);
            foreach ($_POST as $key => $value) {
                $str .= $key.':'.$value."\n";
            }
            foreach ($_SERVER as $key => $value) {
                $str .= $key.':'.$value."\n";
            }
            foreach ($input as $key => $value) {
                $str .= $key.':'.$value."\n";
            }
            $str .= $this->Api_common->dataDump($input,'return');
        }else{
            $str .= '--------------------------------------------------------'."\n";
            $str .= $type.$detail['msg']."\n";
            $str .= '--------------------------------------------------------'."\n";
        }

        

        $this->Api_common->saveData(DIR_SITE_FILE.'temp/'.$fileName.'.txt','a+',$str);
    }

    function frCurl($url, $ckfile, $PostData=""){
        
        $agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) like Gecko";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_HEADER, false);
        $headers = array(
                'Content-Type:application/json'
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30*1000*60);
        if($PostData != ""){
            curl_setopt($ch, CURLOPT_POST, 1);               //submit data in POST method
            curl_setopt($ch, CURLOPT_POSTFIELDS, $PostData);
        }
        $Output = curl_exec($ch);
        if(curl_errno($ch) != 0){
            echo curl_errno($ch).":".str_replace("'","",curl_error($ch));
            exit;
        }

        curl_close($ch);

        return($Output);        
    }
}
?>