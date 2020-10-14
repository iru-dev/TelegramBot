<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class TelBotVasyan extends CI_Controller
{
  public function __construct()
  {
      parent::__construct();
    //  $this->load->model('Telegram');
    //  $this->load->model('MyCity');
      // 1193359664:AAEzpVmmRyvizIz_rTxWBhqLgvYJVihPLMc
      //curl -F "url=https://api.dcserver.ru/TelBotVasyan/webhook" -F "certificate=@/etc/nginx/ssl/api.dcserver.ru.crt" "https://api.telegram.org/bot1193359664:AAEzpVmmRyvizIz_rTxWBhqLgvYJVihPLMc/setwebhook"

  }
  function index(){
    echo '<a href="https://oauth.vk.com/authorize?client_id=7336374&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=wall,friends,post,notify,groups,photos,offline&response_type=token&v=5.52">vk auth</a>';



  }
function ff(){
  $this->to_send_admin($this->Vk_api->get_friendss());
}

  function complite($BUILD_ID=0){
      $this->to_send_admin("Проект залит на сервер\r\nBuildID:\r\n".$BUILD_ID);
  }

  function getNews(){

      $request_params = array(
                  'user_id' => '530675',
                  'access_token' => 'e5ca3d0198d3c83659bdec4c5796dda122657c969d7685a7c77653e861e9e8cf7194986ce7805b04dd6a4',
                  'filters'=> "post",
                  'source_ids'=> '-46535426',
                  'v' => '5.103');

      $get_params = http_build_query($request_params);
      $q = file_get_contents('https://api.vk.com/method/newsfeed.get?' . $get_params);

    $news = json_decode($q);



    if (isset($news->error)){
      print_r($news);
        $text = $news->error->error_msg;
        $text.= " from ".$news->error->request_params[0]->value;
        $this->to_send_admin($text);
    }
    else{


    $PCREpattern  =  '/\r\n|\r|\n/u';

    foreach ($news->response->items as $row) {



        if(isset($row->text) && $row->text!==null && $row->text!=="") {

        $text = preg_replace($PCREpattern, ' ',  $row->text);
        $text = str_replace('гифки', '', $text);
        $text = str_replace('занимательные и забавные # приколы юмор', '', $text);
        $text = str_replace('  ', ' ', $text);

      }else{
        $text="";
      }


            foreach ($row->attachments as $row) {
              if(isset($row->doc) && $row->doc->ext == 'gif'){
                $url = $row->doc->url;

            }
          }



          $file = parse_url($url);
          $file = str_replace("/doc", "", $file['path']);

          $this->db->where("file", $file);
          $query = $this->db->get('tb_files_gifs');

          if($query->num_rows() == 0){
            $uploaddir = '/usr/www/api.dcserver.ru/public_html/files/';
            $upload = $uploaddir.$file.".gif";
            file_put_contents($upload, file_get_contents($url));

          if ($file!=null && $file!=""){
            $data = array('file'=>$file,'comment'=>$text);
            $this->db->insert('tb_files_gifs', $data);
          }
        }
          //$get
          //insert_db

    }

    }
  }



  function sendGifs(){
    $botToken="1193359664:AAEzpVmmRyvizIz_rTxWBhqLgvYJVihPLMc";

    $this->db->where('bot', "TelBotVasyan");
    $userquery = $this->db->get('tb_telegam_user');
//    echo "<pre>";
    //print_r($userquery->result());

// https://apps.timwhitlock.info/emoji/tables/unicode
// \xF0\x9F\x91\x8E down
// \xF0\x9F\x91\x8D up

    $inline_button1 = array("text"=>"\xE2\x9D\xA4", "callback_data"=>"allow");
    $inline_button2 = array("text"=>"\xF0\x9F\x91\x8E", "callback_data"=>'deny');
    $inline_keyboard = [[$inline_button1, $inline_button2]];

    $keyboard = array(
      "inline_keyboard"=>$inline_keyboard,
      "remove_keyboard" => true
    //  "selective" => true
    );
    $replyMarkup = json_encode($keyboard);


    $this->db->where('send', 0);
    $this->db->where('file!=', NULL);
    $this->db->where('file!=', "");
    $this->db->order_by("id", "random");
    $this->db->limit(1);
    $query = $this->db->get('tb_files_gifs');

    //print_r($query->result());
    if($query->num_rows() < 2){
      $this->getNews();
    }

    foreach ($query->result() as $row) {
    $file='/usr/www/api.dcserver.ru/public_html/files/'.$row->file.".gif";

    //$this->Vk_api->postToGroup($file, $row->comment, 53662762);

      foreach($userquery->result() as $t_row) {

          $bot_url = "https://api.telegram.org/bot".$botToken."/";
          $url = $bot_url . "sendVideo?chat_id=" . $t_row->chat_id ;

          $post_fields = array(
              'chat_id' => $t_row->chat_id,
              'caption' => $row->comment,
              'reply_markup'=> $replyMarkup,
              'video' => new CURLFile(realpath($file))
          );

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Content-Type:multipart/form-data"
          ));
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
          $output = curl_exec($ch);

          $output = json_decode($output);

      //    print_r($output);

          $file_unique_id = $output->result->video->file_unique_id;

          $data = array (
            'file_unique_id' => $file_unique_id,
            'send' => 1);

            $this->db->where('id', $row->id);
            $this->db->update('tb_files_gifs', $data);

        }

    }

  }
function test2(){
  $file = '/usr/www/api.dcserver.ru/public_html/files/395332977_551079300.gif';

  $this->Vk_api->postToGroup($file, "test gif", 53662762);
}
  function to_log($text){
    $file = '/var/log/telegram_bot/telegram_bot.txt';
    $date = date("Y-m-d H:i:s");
    file_put_contents($file,  json_encode($text)."\r\n", FILE_APPEND | LOCK_EX);
  }


  function to_send_admin($text){
        $token = "1193359664:AAEzpVmmRyvizIz_rTxWBhqLgvYJVihPLMc";
      file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?chat_id=238538484&text=".urlencode($text));
  }
  function webhook(){
    $token = "1193359664:AAEzpVmmRyvizIz_rTxWBhqLgvYJVihPLMc";
    $obj = file_get_contents("php://input");
    $obj = json_decode($obj, FALSE);

    $this->to_log($obj);

if(isset($obj->message->entities)){
 //&& $obj->message->entities->type == "bot_command"


switch ($obj->message->text) {
    case "/start":

    $last_name = null;
    $first_name = null;
    $username= null;
    if (isset($obj->message->chat->last_name)){
     $last_name = $obj->message->chat->last_name;
    }
    if (isset($obj->message->chat->first_name)){
     $first_name = $obj->message->chat->first_name;
    }
    if (isset($obj->message->chat->username)){
     $username = $obj->message->chat->username;
    }

    $this->db->where('chat_id', $obj->message->chat->id);
    $this->db->where('bot', "TelBotVasyan");
    $query = $this->db->get('tb_telegam_user');
    if($query->num_rows() == 0){
      $data = array('chat_id' => $obj->message->chat->id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'username'=> $username,
                    'bot' => "TelBotVasyan"
                    );

      $this->db->insert('tb_telegam_user', $data);
      $this->to_send_admin("Новый пользователь: \r\n".
                            $obj->message->chat->id."\r\n".
                            $first_name."\r\n".
                            $last_name."\r\n".
                            $username);
      $this->db->where('bot', "TelBotVasyan");
      $query=$this->db->get("tb_telegam_user");
      $this->to_send_admin("Итого: ".$query->num_rows());

    }else{
      $data = array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'username'=> $username,
                    'bot' => "TelBotVasyan"
                    );
      $this->db->where('chat_id', $obj->message->chat->id);
      $this->db->update('tb_telegam_user', $data);
    }

        break;
    case "/fr":
         $this->to_send_admin($this->Vk_api->get_friendss());
       break;
    case "/admin":
        $this->db->where('bot', "TelBotVasyan");
        $query=$this->db->get("tb_telegam_user");
        $this->to_send_admin("Пользователей: ".$query->num_rows());
        break;
    case "/read":
      $this->to_send_admin("Перечитываю сообщения");
      $this->Vk_api->getConversations(53662762);
      break;

    default:

     }

  }else{




    if (isset($obj->callback_query)){


      $file_unique_id =  $obj->callback_query->message->video->file_unique_id;

      if($obj->callback_query->data == 'allow'){


        $this->db->where('file_unique_id', $file_unique_id);
        $query = $this->db->get('tb_files_gifs');
        $vote = $query->result()[0]->vote;

        $data = array ('vote' => $vote+1);
        $this->db->where('file_unique_id', $file_unique_id);
        $this->db->update('tb_files_gifs', $data);
      }
      if($obj->callback_query->data == 'deny'){


        $this->db->where('file_unique_id',$file_unique_id);
        $query = $this->db->get('tb_files_gifs');
        $vote = $query->result()[0]->un_vote;

        $data = array ('un_vote' => $vote+1);
        $this->db->where('file_unique_id', $file_unique_id);
        $this->db->update('tb_files_gifs', $data);
      }

      $bot_url = "https://api.telegram.org/bot" .$token. "/";
      $url = $bot_url . "editMessageReplyMarkup";

      $post_fields = array(
          'chat_id' => $obj->callback_query->message->chat->id,
          'message_id' => $obj->callback_query->message->message_id
          );


      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
      $output = curl_exec($ch);

      //$this->to_log($output);

    }else{

      $mess = $this->Vk_api->find_response_message($obj->message->text);
      file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$obj->message->chat->id."&text=".urlencode($mess));
    }
    }
  }

  function test($mess){
    if($mess!=null){
      echo $this->Vk_api->find_response_message($mess);
    }
  echo "test ok";
  }

}
