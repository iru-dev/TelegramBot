<?php
/*
   добавление вервера
https://vk.com/dev/callback_api
*/

class Vk_api extends CI_Model
{





 function version_vk_api() {
   return '5.103';
 }


 function get_friendss(){
   $request_params = array(
               'user_id' => '39262861',
               'access_token' => 'edf3ceb9098d0c22677dd2319a65e13ee2f4eb6246519fa3f415791dc1115a22852c7cd620759ecec8c36',
               'v' => '5.103');

   $get_params = http_build_query($request_params);
   $q = file_get_contents('https://api.vk.com/method/friends.get?' . $get_params);
   $friends=json_decode($q);
   $data = $friends->response->items;
   $arr=null;
   $message="Новые друзья: \r\n";

 //  print_r($data);
   foreach ($data as $row) {
     $this->db->where("uid", $row);
     $query = $this->db->get('t_ttt');
       if($query->num_rows() == 0){

         $data = array('uid'=>$row);
         $this->db->insert('t_ttt', $data);

         $arr[]= $row;
       }
   }
   if($arr!==null){
     foreach ($arr as $row2) {
       $message.='https://vk.com/id'.$row2." \r\n";
       // code...
     }
   //  print_r($arr

   }
     return $message;
 }





  function getAlbom($id_albom){

  }
 function getAlbums($user_id, $count=20, $offset=0,$all=FALSE){
   $token='d8ea52aa33ac6977f2eed32f11a1cbe0348cde51f594a080975d798b148fcd736c4d5f532c480fdd329c6';

     $request_params = array(
                 'owner_id' => $user_id,
                 'access_token' => $token,
                 'count' => $count,
                 'offset'=>$offset,
                 'v' => $this->version_vk_api());

     $get_params = http_build_query($request_params);

     $q = file_get_contents('https://api.vk.com/method/photos.getAll?' . $get_params);
     $q = json_decode($q);
     return $q;
 }

 function postToGroup($file, $comment=null, $group_id=53662762){
   $request_params = array(
               'group_id' => $group_id,
               'access_token' => $this->getToken($group_id),
               'v' => $this->version_vk_api());

   $get_params = http_build_query($request_params);

   $q = file_get_contents('https://api.vk.com/method/docs.getWallUploadServer?' . $get_params);
   $q = json_decode($q);

   echo "<pre>";
   //echo $this->getToken($group_id)."<br>";
   print_r($q);
   echo "<br>";
   echo $file;
   echo "<br>";

   //$file = new CURLFile(realpath($file));

  //  $q->response->upload_url

  $post_fields = array(
        'photo' => curl_file_create($file, 'image/png','testpic')
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Content-Type:multipart/form-data"
  ));
  curl_setopt($ch, CURLOPT_URL, $q->response->upload_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
  $response = curl_exec($ch);
   $json = json_decode($response, true);
   print_r($json);
   print_r($post_fields);
  }

 function getListChat(){
   $request_params = array(
               'count' =>  200,
               'access_token' => "2be561e8c344b681d42b0998e69c6d4564f855dafdf0f5f0b5cb842b7bf9da0dc6ac5df224479dcb3abcf",
               'v' => $this->version_vk_api());

   $get_params = http_build_query($request_params);

   $q = file_get_contents('https://api.vk.com/method/messages.getConversations?' . $get_params);
   $q = json_decode($q);
   return $q;
 }

 function getChat($chat_id){
   $request_params = array(
               'chat_id' =>  $chat_id,
               'access_token' => $this->getTokenBot($uid),
               'v' => $this->version_vk_api());

   $get_params = http_build_query($request_params);

   $q = file_get_contents('https://api.vk.com/method/messages.getChat?' . $get_params);
   $q = json_decode($q);
   return $q;
 }
 function getSuggestions($uid){
   $request_params = array(
               'count' =>  100,
               'access_token' => $this->getTokenBot($uid),
               'v' => $this->version_vk_api());

   $get_params = http_build_query($request_params);

   $q = file_get_contents('https://api.vk.com/method/friends.getSuggestions?' . $get_params);
   $q = json_decode($q);
   return $q;
 }

  function confirmation($group_id){
   $this->db->where('group_id', $group_id);
   $return = $this->db->get('tb_bot_config');
   return $return->result()[0]->confirmation_token;
  }

function getConversations($group_id){
    $request_params = array(
                'group_id' => $group_id,
                'count' =>  200,
                'filter' => 'unanswered',
                'extended' => 1,
                'access_token' => $this->getToken($group_id),
                'v' => $this->version_vk_api());

    $get_params = http_build_query($request_params);

    $q = file_get_contents('https://api.vk.com/method/messages.getConversations?' . $get_params);
    $q = json_decode($q);

    foreach ($q->response->items as $row) {

      $row->last_message->text;
      $message = $this->find_response_message($row->last_message->text);
      $q = $this->sendMessage($row->last_message->from_id, $group_id, $message, $row->last_message->id);
    }

    return $q;

}


 function getToken($group_id = null){
   $this->db->where('group_id', $group_id);
   $return = $this->db->get('tb_bot_config');
   return $return->result()[0]->token_group;
 }

 function getTokenBot($uid = null){
   $this->db->where('uid', $uid);
   $return = $this->db->get('tb_bot');
   return $return->result()[0]->access_token;
 }

 function to_log($text){
   $file = '/var/log/vk_bot.log';
   $date = date("Y-m-d H:i:s");
   file_put_contents($file,  json_encode($text)."\r\n", FILE_APPEND | LOCK_EX);
 }

function countMessage($user_id = 0){
  $this->db->where('user_id', $user_id);
  $query = $this->db->get('vk_user_send');
  return $query->result()[0]->count;
}
  function message_new($obj){

    $ban_id = array(
      '-141243966',
      '-136394672',
      '-119542890');

  if (!in_array($obj->object->message->from_id, $ban_id) && $obj->object->message->from_id > 1){



        if ($this->isMember($obj->object->message->from_id, $obj->group_id) or $this->countMessage($obj->object->message->from_id) < 10){

          $message = $this->find_response_message($obj->object->message->text);
          $this->sendMessage($obj->object->message->from_id, $obj->group_id, $message);

        }else{
          $mmm = "А не подписаться ли тебе на группу?\r\nТакже у нас есть Telegram вервия\r\nhttps://t.me/dvasanbot\r\nПодписывайся, уж там без регистрациий и sms";
          $this->sendMessage($obj->object->message->from_id, $obj->group_id, $mmm);

        }
      }else{
        $request_params = array(
                    'group_id' => $obj->group_id,
                    'owner_id' => $obj->object->message->from_id,
                    'reason' => 1, //spam
                    'access_token' => $this->getToken($obj->group_id),
                    'v' => $this->version_vk_api());

        $get_params = http_build_query($request_params);

      #  $q = file_get_contents('https://api.vk.com/method/groups.ban?' . $get_params);
      #  $q = json_decode($q);
      #  $this->to_log($q);
      }
  }

  function find_response_message($message){

    $message = urldecode($message);
    $message = str_replace('/', ' ', $message);
    $message = str_replace('\'', ' ', $message);
    $message = str_replace('"', ' ', $message);
    $message = str_replace('\\', ' ', $message);
    $message = str_replace('?', ' ', $message);
    $message = str_replace('.', ' ', $message);
    $message = str_replace(',', ' ', $message);
    $message = preg_replace("/\s{2,}/", ' ', $message);

    $this->db->where('MATCH (message) AGAINST ("' . $message .
                  '" IN NATURAL LANGUAGE MODE) > 0', null, false);
    $this->db->limit(1);
    $this->db->order_by('id', 'RANDOM');
    $query = $this->db->get('tb_bot_history');



    if ($query->num_rows() > 0) {
      return $query->result()[0]->answer;
    } else {
      return 'Даже не знаю что сказать';
    }
  }
  function isMember($user_id, $group_id){
    $request_params = array(
                'group_id' => $group_id,
                'user_id' => $user_id,
                'access_token' => $this->getToken($group_id),
                'v' => $this->version_vk_api());

    $get_params = http_build_query($request_params);

    $q = file_get_contents('https://api.vk.com/method/groups.isMember?' . $get_params);
    $q = json_decode($q);



    if (isset($q->response)){
      return $q->response;
    }else{
      return 0;
    }

  }

 function getUserInfo($group_id, $user_id){
   $request_params = array(
               'fields' => 'sex, bdate, city, country',
               'user_ids' => $user_id,
               'access_token' => $this->getToken($group_id),
               'v' => $this->version_vk_api());

   $get_params = http_build_query($request_params);
   $q = file_get_contents('https://api.vk.com/method/users.get?' . $get_params);
   $q = json_decode($q);
   return $q;
 }

 function sendMessage($user_id, $group_id, $text, $message_ids=null){

    if (strpos($text, '%time%') != false) {
            $text = str_ireplace("%time%", date("H:i"), $text);
        }

  /*
    if (strpos($text, '%username%') != false or strpos($text, '%USERNAME%') != false) {


          $user = json_decode($this->getUserInfo($user_id));
          $text = str_ireplace("%username%", $user->response[0]->first_name, $text);

      }
*/

      $date = date("Y-m-d H:i:s");
      $timestamp = strtotime($date);
      $random_id = $user_id.$timestamp;
//echo $random_id;
      $request_params = array(
                  'message' => $text,
                  'user_id' => $user_id,
                  'access_token' => $this->getToken($group_id),
                  'random_id' => $random_id,
                  'v' => $this->version_vk_api());

      $get_params = http_build_query($request_params);
      $q = file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
      $q = json_decode($q);
    //  print_r($q);

    if (isset($q->error)){
        switch ($q->error->error_code){
          case '901':
            $q = $this->deleteConversation($user_id, $group_id);
        //    $q=$this->markAsRead($message_ids, $user_id, $this->getToken($group_id));
          break;
    }
  }
 $this->db->where('user_id', $user_id);
 $query = $this->db->get('vk_user_send');
 if($query->num_rows()==0){
   $data = array('user_id'=>$user_id,'count'=>1);
   $this->db->insert('vk_user_send',$data);
 }else{
   $count = $query->result();
   $count = $count[0]->count;
   $count++;
   $this->db->where('user_id',$user_id);
   $data = array('count'=>$count);
   $this->db->update('vk_user_send',$data);
 }

    return $q;
  }
  function markAsRead($start_message_id, $user_id, $group_id){
    $request_params = array(
                'user_id' => $user_id,
                'access_token' => $this->getToken($group_id),
                'start_message_id'=> $start_message_id,
                'v' => $this->version_vk_api());

    $get_params = http_build_query($request_params);
    $q = file_get_contents('https://api.vk.com/method/messages.markAsRead?' . $get_params);
    $q = json_decode($q);

    return $q;

  }
  function addSerer($secret_key){

      $request_params = array(
                  'url' => 'http://api.dcserver.ru/Callback',
                  'title' => 'api from bot',
                  'secret_key' => $secret_key,
                  'v' => $this->version_vk_api());

      $get_params = http_build_query($request_params);
      $q = file_get_contents('https://api.vk.com/method/groups.addCallbackServer?' . $get_params);
      $q = json_decode($q);
      return $q;
      // request return server_id (int)

  }
  function setupSerer($group_id, $server_id, $secret_key){

      $request_params = array(
                  'group_id' => $group_id,
                  'server_id' => $server_id,
                  'message_new' => 1,
                  'message_reply' => 1,
                  'photo_new' => 1,
                  'wall_post_new' => 1,
                  'group_join' => 1,
                  'secret_key' => $secret_key,
                  'api_version' => $this->version_vk_api(),
                  'v' => $this->version_vk_api());

      $get_params = http_build_query($request_params);
      $q = file_get_contents('https://api.vk.com/method/groups.addCallbackServer?' . $get_params);
      $q = json_decode($q);
      return $q;
      // request return server_id (int)

  }
  function deleteConversation($user_id, $group_id){
    $request_params = array(
                'group_id' => $group_id,
                'user_id' => $user_id,
                'access_token' => $this->getToken($group_id),
                'v' => $this->version_vk_api());
    $get_params = http_build_query($request_params);
    $q = file_get_contents('https://api.vk.com/method/messages.deleteConversation?' . $get_params);
    $q = json_decode($q);
    return $q;
  }

  function getNews($user_id=null){
    $this->db->where('enable', 1);
    if ($user_id!==null){
      $this->db->where('uid', $user_id);
    }
    $query = $this->db->get('vk_bot');

    foreach ($query->result() as $row) {
      $request_params = array(
                  'user_id' => $row->uid,
                  'access_token' => $row->access_token,
                  'filters'=>"post",
                  'source_ids'=>'-46535426',
                  'v' => $this->version_vk_api());

      $get_params = http_build_query($request_params);
      $q = file_get_contents('https://api.vk.com/method/newsfeed.get?' . $get_params);
      $q = json_decode($q);
      return $q;
    }
  }
}
//{"error":{"error_code":100,"error_msg":"One of the parameters specified was missing or invalid: random_id is a required parameter","request_params":[{"key":"user_id","value":"530675"},{"key":"v","value":"5.103"},{"key":"method","value":"messages.send"},{"key":"oa
?>
