<?php

class Manage_notify extends My_Controller {
    function __construct(){
		parent::__construct( strtolower(__CLASS__) );
		$this->load->model('Api_common');
        $this->load->model('Api_ec');
        $this->load->model('Api_table_generate');
        $this->load->model('Api_ragic');
        $this->load->library('My_SendMail');
        //$this->load->model('Users_auth');
    }

    // 主畫面
    function index(){
        exit;
    }

    function invoiceNotify(){
        $invoiceData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicforms12/2', $ckfile),true);
        //$invoiceData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicforms12/20008/610', $ckfile),true);
        
        $num = 0;
        if(!$invoiceData){exit;}

        foreach ($invoiceData as $key => $value) {
            if($num<3){
                if(!$str){
                    $str = $value['供應商名稱'];
                }else{
                    $str .= '、'.$value['供應商名稱'];
                }
            }
            $num++;
            $table[$key]['應付日期'] = $value['應付日期'];
            $table[$key]['供應商名稱'] = $value['供應商名稱'];
            $table[$key]['備註'] = $value['備註'];
            $table[$key]['付款備註'] = $value['付款備註'];
            $table[$key]['品牌'] = $value['品牌'];
            foreach ($value['通知人員'] as $key2 => $value2) {
                $mail[$value2] = $value2;
            }
            foreach ($value['附件'] as $key2 => $value2) {
                $table[$key]['附件'] .= '<li><a target="blank" href="https://ap3.ragic.com/sims/embedPreview.jsp?0=%2Fsims%2Ffile.jsp%3Fa%3DhugePlus%26f%3D'.$value2.'">'.explode('@', $value2)[1].'</a></li>';
            }
            if($table[$key]['附件']){
                $table[$key]['附件'] = '<ul>'.$table[$key]['附件'].'</ul>';
            }
        }


        $msg = '[付款通知] '.date('Y/m/d').' 共有 '.$str.' 等 '.count($invoiceData).' 筆應付發票，請前往查看 https://ap3.ragic.com/hugePlus/ragicforms12/2';
        $result = $this->sendLineNotifiy('ewPieF7K0W0B6frI2mciMfPnYtN6E2K5lNlnd0yOBDu',$msg);

        array_push($mail, 'peter@pro-duction.com.tw');
        
        $detail['title'] = ['應付日期','供應商名稱','品牌','備註','付款備註','附件'];
        $detail['allBorder'] = $detail['title'];
        $detail['alignLeft'] = ['附件'];
        $subject = '[付款通知] '.date('Y/m/d').' 共有 '.$str.' 等 '.count($invoiceData).' 筆應付發票，請前往查看';
        $html .= '[付款通知] '.date('Y/m/d').' 共有 '.$str.' 等 '.count($invoiceData).' 筆應付發票，請前往查看 <a href="https://ap3.ragic.com/hugePlus/ragicforms12/2">https://ap3.ragic.com/hugePlus/ragicforms12/2</a>';
        $html .= $this->Api_table_generate->drawTable($table,$detail,$data);

        $data = array(
                    'recipient'=>$mail,
                    'cc'=>'', 
                    'subject' => $subject, 
                    'content' => $html,
                    'sender'=>MAIL_CONFIG['senderName']); 
        //$this->Api_common->dataDump($invoiceData);
        $this->Api_common->dataDump($data);
        $result = $this->my_sendmail->sendOut($data);
    }

    function retMoneyNotify(){
        $retData = json_decode($this->Api_ragic->ragicCurl('https://ap3.ragic.com/hugePlus/ragicforms12/3', $ckfile),true);
        $num = 0;
        if(!$retData){exit;}

        foreach ($retData as $key => $value) {
            $num++;
            $table[$key]['預計退款日期'] = $value['預計退款日期'];
            $table[$key]['退款金額'] = $value['退款金額'];
            $table[$key]['退款匯款帳號'] = $value['退款匯款帳號'];
            $table[$key]['訂單來源'] = $value['訂單來源'];
        }

        $mail = ['annie@pro-duction.com.tw','sophia@pro-duction.com.tw','doris@pro-duction.com.tw','peter@pro-duction.com.tw'];
        $msg = '[退款通知] '.date('Y/m/d').' 共有 '.count($retData).' 筆退款訂單，請前往查看 https://ap3.ragic.com/hugePlus/ragicforms12/3';
        $result = $this->sendLineNotifiy('ewPieF7K0W0B6frI2mciMfPnYtN6E2K5lNlnd0yOBDu',$msg);

        
        $detail['title'] = ['訂單來源','預計退款日期','退款金額','退款匯款帳號'];
        $detail['allBorder'] = $detail['title'];
        $subject = '[退款通知] '.date('Y/m/d').' 共有 '.$str.' 等 '.count($retData).' 筆退款訂單，請前往查看';
        $html .= '[退款通知] '.date('Y/m/d').' 共有 '.$str.' 等 '.count($retData).' 筆退款訂單，請前往查看 <a href="https://ap3.ragic.com/hugePlus/ragicforms12/3">https://ap3.ragic.com/hugePlus/ragicforms12/3</a>';
        $html .= $this->Api_table_generate->drawTable($table,$detail,$data);

        $data = array(
                    'recipient'=>$mail,
                    'cc'=>'', 
                    'subject' => $subject, 
                    'content' => $html,
                    'sender'=>MAIL_CONFIG['senderName']); 
        $this->Api_common->dataDump($data);
        $result = $this->my_sendmail->sendOut($data);
    }

    private function sendLineNotifiy($token=NULL,$msg=NULL){
        $header[] = 'Authorization: Bearer '.$token;
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        if(!$msg){
          $postData['message'] = '現在可以透過LINE來接收通知了! 通知時間：'.date('Y-m-d H:i:s');
        }else{
          $postData['message'] = $msg;
        }
        $result = json_decode($this->Api_common->getCurl('https://notify-api.line.me/api/notify',$postData,$header),true);
        return $result;
    }

}
