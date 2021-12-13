<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class My_SendMail {
    var $isSet = FALSE;
    var $charSeparation = '|';
    var $senderName = MAIL_CONFIG['senderName'];
    var $senderMail = MAIL_CONFIG['senderMail'];
    var $subject = '';
    var $content = '';
    var $attachment = '';
    var $cc = '';
    var $bcc = '';
    var $mail_config = MAIL_CONFIG;
    function __construct() {
        $this->ci = & get_instance();
        $this->ci->load->library('email');
    }
    public function set_Config($config) {
        $this->mail_config = $config;
        $this->ci->email->initialize($this->mail_config);
    }
    public function set_Type($mailtype = 'html') {
        $this->mail_config['mailtype'] = $mailtype;
        $this->ci->email->initialize($this->mail_config);
    }
    public function set_Subject($subject = '') {
        $this->subject = $subject;
    }
    public function set_Content($content = '') {
        $this->content = $content;
    }
    public function set_CC($cc = '') {
        $this->cc = $cc;
    }
    public function set_BCC($bcc = '') {
        $this->bcc = $bcc;
    }
    public function set_Attach($attachment = '') {
        $this->attachment = $attachment;
    }
    public function set_senderName($name = '') {
        $this->senderName = $name == '' ? $this->senderName : $name;
    }
    public function set_senderMail($address = '') {
        $this->senderMail = $address == '' ? $this->senderMail : $address;
    }
    public function set_Sender($name = '', $address = '') {
        $this->set_senderName($name);
        $this->set_senderMail($address);
    }
    public function send($address) {
        $this->ci->email->from($this->senderMail, $this->senderName);
        $this->ci->email->reply_to($this->senderMail, $this->senderName);
        $this->ci->email->to($address);
        if ($this->cc != '') $this->ci->email->cc($this->cc);
        if ($this->bcc != '') $this->ci->email->bcc($this->bcc);
        if ($this->attachment != '') $this->ci->email->attach($this->attachment);
        $this->ci->email->subject($this->subject);
        $this->ci->email->message($this->content);
        if ($this->ci->email->send()) $result = array('success' => 1, 'failed' => 0);
        else $result = array('success' => 0, 'failed' => 1);
        //echo $this->ci->email->print_debugger();
        return $result;
    }
    public function sendOut($data) {
        $mail = array('to' => $this->senderMail, 'subject' => 'test mail', 'content' => 'TEST!!', 'sender' => $this->senderName, 'replier' => $this->senderName, );
        if (!is_array($data)) $result = $this->send($data);
        else {
            if (array_key_exists('recipient', $data)) $mail['to'] = $data['recipient'];
            if (array_key_exists('subject', $data)) $mail['subject'] = $data['subject'];
            if (array_key_exists('content', $data)) $mail['content'] = $data['content'];
            if (array_key_exists('sender', $data)) $mail['sender'] = $data['sender'];
            if (array_key_exists('replier', $data)) $mail['replier'] = $data['replier'];
            if (array_key_exists('cc', $data)) {$mail['cc'] = $data['cc'];$this->set_CC($mail['cc']);}
            if (array_key_exists('bcc', $data)){$mail['bcc'] = $data['bcc'];$this->set_BCC($mail['bcc']);}
            if (array_key_exists('attachment', $data)){$mail['attachment'] = $data['attachment'];$this->set_Attach($mail['attachment']);}
            $this->set_Type('html');
            $this->set_Sender('=?UTF-8?B?'.base64_encode($mail['sender']).'?=', $this->senderMail);
            $this->set_Subject($mail['subject']);
            $this->set_Content($mail['content']);
            $result = $this->send($mail['to']);
        }
        if ($result['success']) return '1|Success';
        else return '0|Failed';
    }
    public function send_OnebyOne($mail_list, $delay = 0) {
        if (is_array($mail_list)) $list = $mail_list;
        elseif(is_string($mail_list)) $list = explode(",", $mail_list);
        else $list = array();
        $result = array('success' => array(), 'failed' => array());
        foreach($list as $address) {
            if ($delay > 0) sleep($delay);
            if (!empty($address) && filter_var($address, FILTER_VALIDATE_EMAIL)) {
                if ($this->send($address)) {
                    $result['success'][] = $address;
                } else $result['failed'][] = $address;
            }
        }
        return $result;
    }
}