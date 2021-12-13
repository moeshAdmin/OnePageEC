<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use pimax\FbBotApp;
use pimax\Messages\ImageMessage;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\MessageElement;
use pimax\Messages\StructuredMessage;
Class Facebook_bot
{
   
   public static $sender_id;
   public static $helper;
   
   public function __construct($params = [])
   {
      $ci = &get_instance();
      $config = $ci->config;
      
      // Load config
      $config->load('facebook_bot');
      
      $verify_token = $config->item('verify_token');
      $access_token = $config->item('access_token');
      
      self::$helper = new FbBotApp($access_token);
      
      if ($ci->input->get_post('hub_mode') === 'subscribe' &&
         $ci->input->get_post('hub_verify_token') === $verify_token
      ) {
         die($ci->input->get_post('hub_challenge'));
      }
   }
   
   public static function setSenderId($sender_id)
   {
      self::$sender_id = $sender_id;
   }
   
   public static function getMessage($text)
   {
      return new Message(self::$sender_id, $text);
   }
   
   public static function getImageMessage($url)
   {
      return new ImageMessage(self::$sender_id, $url);
   }
   
   public static function getStructuredMessage($type, $array)
   {
      switch ($type) {
         case 'TYPE_BUTTON':
            $casted_type = StructuredMessage::TYPE_BUTTON;
            break;
         default:
            $casted_type = StructuredMessage::TYPE_GENERIC;
      }
      
      return new StructuredMessage(self::$sender_id, $casted_type, $array);
   }
   
   public static function getMessageButton($type, $title, $url = '')
   {
      switch ($type) {
         case 'TYPE_WEB':
            $casted_type = MessageButton::TYPE_WEB;
            break;
         default:
            $casted_type = MessageButton::TYPE_POSTBACK;
      }
      
      return new MessageButton($casted_type, $title, $url);
   }
   
   public static function getMessageElement($title, $subtitle, $image_url = '', $buttons = [])
   {
      return new MessageElement($title, $subtitle, $image_url, $buttons);
   }
   
   public static function send($message)
   {
      self::$helper->send($message);
   }
}