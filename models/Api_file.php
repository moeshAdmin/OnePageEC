<?php
class Api_file extends CI_Model{
    function __construct() {
        parent::__construct();
    }    

    function fileProcess($user_detail,$postData,$detail){
        foreach ($postData[$detail['postFileInput']] as $key => $fileName) {
            if(file_exists(UPLOAD_FILE.$fileName)){
                //資料夾不存在->建立
                if(!is_dir(DIR_SITE_FILE."user_files/".$user_detail['account'])){
                    mkdir(DIR_SITE_FILE."user_files/".$user_detail['account'], 0777);
                }
                $fileHash = substr($this->Api_common->stringHash('encrypt',MD5(rand(0,999999).$fileName)), 0,16);
                //搬移檔案
                if(copy(UPLOAD_FILE.$fileName, DIR_SITE_FILE."user_files/".$user_detail['account'].'/'.$fileHash)){
                    $insertData[$key]['fmParentHash'] = $detail['parentHash'];
                    $insertData[$key]['fmFileHash'] = $fileHash;
                    $insertData[$key]['fmSubNo'] = $detail['subNo'];
                    $insertData[$key]['fmFileName'] = $fileName;
                    $insertData[$key]['fmFileSize'] = filesize(UPLOAD_FILE.$fileName);
                    if($postData['fileTag']){
                        $insertData[$key]['fmFileTag'] = $postData['fileTag'][$key];
                    }else{
                        $insertData[$key]['fmFileTag'] = $this->Api_common->setArrayToList($postData[$detail['postFileTag'].$postData['randName'][$key]]);
                    }
                    if($detail['fileDesc']){
                        $insertData[$key]['fmDesc'] = $detail['fileDesc'];
                    }
                    $insertData[$key]['fmCreateBy'] = $user_detail['account'];
                    $insertData[$key]['fmCreateDTime'] = date('Y-m-d H:i:s');
                }else{
                    $errorLog[$key]['fileName'] = $fileName;
                }
                //刪除檔案
                unlink(UPLOAD_FILE.$fileName);
            }
        }
        
        if(count($insertData)>0){
            $this->db->insert_on_duplicate_update_batch('sys_file',$insertData);
        }
        return $errorLog;
    }

    function fileDownload($fileHash,$user_detail){
        $downDetail = $this->Api_common->getDataCustom('fmFileHash,fmParentHash,fmCreateBy,fmFileName,fmFileTag','sys_file','fmFileHash = "'.$fileHash.'"');
        $gpHash = explode(';', $downDetail[0]['fmAllowGroup']);
        $fmParentHash = $downDetail[0]['fmParentHash'];
        $canAccess = 'N';
        foreach ($gpHash as $key => $value) {
            if(!$value){continue;}
            if($user_detail['groupConfig'][$value]||
                $user_detail['groupConfigCC'][$value]||
                $user_detail['groupsFollow'][$value]||$value=='all'||
                preg_match('/testReport_/', $downDetail[0]['fmFileTag'])){
                $canAccess = 'Y';
            }
        }

        if($user_detail['actor']=='系統管理者'){
            $canAccess = 'Y';
        }
        
        if($canAccess=='N'){
            $this->load->model('Api_form_generate');
            if($this->Api_form_generate->chkAuth($fmParentHash,'',array('type'=>'return','ignoreStatus'=>true))){

            }else{
                echo '<script>alert("非允許對象");</script>';
                exit;
            }
        }
        if($downDetail[0]['fmCreateBy']&&$downDetail[0]['fmFileHash']){
            $data = file_get_contents(DIR_SITE_FILE."user_files/".$downDetail[0]['fmCreateBy'].'/'.$downDetail[0]['fmFileHash']); // Read the file's contents
            $detail['file_name'] = $downDetail[0]['fmFileName'];
            $detail['file_path'] = DIR_SITE_FILE."user_files/".$downDetail[0]['fmCreateBy'].'/'.$downDetail[0]['fmFileHash'];
            //base64顯示模式，預備未來使用
            //$imguri = base64_encode($data);
            //echo '<img src="data:image/png;base64,'.$imguri.'">';
            $this->Api_file->setFileDownload($detail);
        }else{
            echo '<script>alert("檔案不存在");</script>';
            exit;
        }
    }
    
    private function setFileDownload($detail){
        $file_name = $detail['file_name'];
         $file_path = $detail['file_path'];
         $file_size = filesize($file_path);
         header('Content-Length: ' . $file_size);
         $repName = str_replace(array("(",")"," ","%","&","#","*","@","!","?"), '_', $file_name);
        //if(strpos($_SERVER['REQUEST_URI'], $repName)<1){
            //header("Location: ".base_url().str_replace('/ep/', '', $_SERVER['REQUEST_URI']).'/'.$repName);
        //}
         if(preg_match("/\.pdf$|\.txt$|\.html$|\.htm$/", $file_name)){
            if(preg_match("/\.txt$/", $file_name)){
                header('Content-Type: text/plain');
            }else if(preg_match("/\.pdf$/", $file_name)){
                header('Content-Type: application/pdf');
            }else if(preg_match("/\.html$|\.htm$/", $file_name)){
                header('Content-Type: text/html');
            }
            header('Content-Disposition:filename="' . $file_name . '"');
         }else{
            header('Content-Type: application/octet-stream');
            header('Content-Disposition:attachment;filename="' . $file_name . '"');
         }
         readfile($file_path);
    }

    function fileDel($user_detail,$fileHash){
        $this->db->delete('sys_file', array('fmFileHash' => $fileHash,'fmCreateBy'=>$user_detail['account'])); 
        unlink(DIR_SITE_FILE."user_files/".$user_detail['account'].'/'.$fileHash);
    }

    function getFormGenFiles($hashCode,$subNo,$fileTag,$detail){
        if(preg_match('/Comments/', $detail['formType'])){
            $resData = $this->Api_common->getDataCustom('fmFileHash,fmFileName,fmFileTag,fmCreateDTime,fmCreateBy','sys_file','fmParentHash = "'.$hashCode.'" AND fmSubNo="'.$subNo.'" AND fmFileTag="'.$fileTag.'" AND fmDesc="pmsID_'.$detail['pmsID'].'"');
        }else{
            $resData = $this->Api_common->getDataCustom('fmFileHash,fmFileName,fmFileTag,fmCreateDTime,fmCreateBy','sys_file','fmParentHash = "'.$hashCode.'" AND fmSubNo="'.$subNo.'" AND fmFileTag="'.$fileTag.'"','fmSysID ASC');
        }
        foreach ($resData as $key => $value) {
            $retData[$key]['FileHash'] = $this->Api_common->stringHash('encrypt',$resData[$key]['fmFileHash']).'_'.$this->Api_common->stringHash('encrypt',strtotime('+1 day',strtotime(date('Y-m-d H:i:s'))));
            $retData[$key]['FileName'] = $resData[$key]['fmFileName'];
            $retData[$key]['FileTag'] = $resData[$key]['fmFileTag'];
            $retData[$key]['FileDate'] = date('Y-m-d',strtotime($resData[$key]['fmCreateDTime']));
            $retData[$key]['UploadBy'] = $resData[$key]['fmCreateBy'];
        }

        return $retData;
    }

    function getFileTable($sql){
        $fileData = $this->Api_common->getDataCustom('fmFileHash,fmFileName,fmCreateBy,fmCreateDTime','sys_file',$sql);
        foreach ($fileData as $key => $value) {
            $fileContent[$key]['下載'] = '<a href="https:'.str_replace('https:', '', base_url()).'file/file_manage/fileDownload/'.$this->Api_common->stringHash('encrypt',$fileData[$key]['fmFileHash']).'">下載</a>';
            $fileContent[$key]['檔案'] = $fileData[$key]['fmFileName'];
            $fileContent[$key]['上傳人員'] = $fileData[$key]['fmCreateBy'];
            $fileContent[$key]['日期'] = date('Y-m-d',strtotime($fileData[$key]['fmCreateDTime']));
        }
        $detail['fontSize'] = 10;
        $detail['title'] = array('檔案','日期','上傳人員','下載');
        $detail['alignLeft'] = $detail['title'];
        $detail['allBorder'] = $detail['title'];
        if($fileContent){
            $retData['html'] = $this->Api_table_generate->drawTable($fileContent,$detail,'');
        }

        return $retData;
    }
}
?>