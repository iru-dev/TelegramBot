<?php

class Dialogflow extends CI_Model
{

 function url() {
   return 'https://api.dialogflow.com/v2/';
 }
 function token() {
   return '6fc94e9fdb69474d9ccc8f69c3cde83b';
 }

 function request($message, $sessionId){
   header 'Authorization: Bearer '.$this->token();
   $request_params = array(
               'sessionId' => $sessionId,
               'query' => ,
               'data'=> ,
               'v' => 20150910;

   $get_params = http_build_query($request_params);

   $q = file_get_contents($this->url().'?' . $get_params);
   $q = json_decode($q);
   return $q;
 }

}
