<?php


class Tg_model extends CI_Model
{
  function __construct(){
		parent::__construct();
  }

  function WriteLog($string){

    $file = './telegram.txt';
    $date = date("Y-m-d H:i:s");
    file_put_contents($file,  "[".$date."] ". $string."\r\n", FILE_APPEND | LOCK_EX);
  }

  function editMessageReplyMarkup($chat_id = null, $message_id = null){

    $bot_url = "https://api.telegram.org/bot" .$this->config->item('telegram_bot_token'). "/";
    $url = $bot_url . "editMessageReplyMarkup";

    $post_fields = array(
        'chat_id' => $chat_id,
        'message_id' => $message_id
        );


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);

    return $output;

  }
}
