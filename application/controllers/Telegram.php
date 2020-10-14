<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Telegram extends CI_Controller
{
  public function __construct()
  {
      parent::__construct();
    //  $this->load->model('Telegram');
    //  $this->load->model('MyCity');
      // 956860444:AAFUCkztT55fZq6vladla3Fsx_pTGAKxKM0
      //curl -F "url=https://api.dcserver.ru/telegram/webhook" -F "certificate=@/etc/nginx/ssl/api.dcserver.ru.crt" "https://api.telegram.org/bot956860444:AAFUCkztT55fZq6vladla3Fsx_pTGAKxKM0/setwebhook"

  }
  function voteBot(){

    $obj = file_get_contents("php://input");
    $obj = json_decode($obj, FALSE);

    $botToken = "1101742196:AAFIFlU1NMQSsfKG-0nkOJnCBxlci1r8YuQ";

    $file = '/var/log/telegram_bot/telegram_Votebot.txt';
    file_put_contents($file,  json_encode($obj)."\r\n", FILE_APPEND | LOCK_EX);


    $this->db->where('chat_id', $obj->message->chat->id);
    $query=$this->db->get('tb_telegam_user');

    if($query->num_rows() == 0){
      $data = array('chat_id'=>$obj->message->chat->id,
                    'first_name'=>$obj->message->chat->first_name,
                    'username'=>$obj->message->chat->username
                    );
      $this->db->insert('tb_telegam_user', $data);
    }

    if (isset($obj->callback_query)){

      $this->db->where('chat_id', $obj->message->chat->id);
      $query = $this->db->get('tb_vote_vibori');

      if($query->num_rows() == 0){
        $data = array(
          'vote' => $obj->data,
          'chat_id' => $obj->message->chat->id
        );
        $this->db->insert('tb_vote_vibori',$data);
      }
    }

//    if (isset($obj->message->entities) &&  $obj->message->entities->type == 'bot_command'){
    if (isset($obj->message->entities) ){
  //file_put_contents($file,  json_encode("ok")."\r\n", FILE_APPEND | LOCK_EX);

  $bot_url = "https://api.telegram.org/bot" .$botToken. "/";


        switch ($obj->message->text){
          case '/start':
          $message = "Приветствие";

          break;
          case '/vote':
          //проверяем не голосовал ли данный пользователь
          //проводим Голосование
          $inline_button1 = array("text"=>"За","callback_data"=>"1");
          $inline_button2 = array("text"=>"Против","callback_data"=>'0');
        //  $inline_button3 = array("text"=>"Не пойду на выборы","callback_data"=>'no_vote');
          $inline_keyboard = [[$inline_button1, $inline_button2]];

          $keyboard = array(
            "inline_keyboard"=>$inline_keyboard,
            "remove_keyboard" => true,
            "selective" => true
           );
           $replyMarkup = json_encode($keyboard);



           $post_fields = array(
               'chat_id' => $obj->message->chat->id,
               'text' => "Поправки в конституцию РФ",
               'reply_markup'=> $replyMarkup
             );
        //   file_get_contents( $bot_url. 'sendMessage?' . urlencode($post_fields));

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.telegram.org/bot' . $botToken . '/sendMessage');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            $output = curl_exec($ch);

            $output = json_decode($output);

            file_put_contents($file,  json_encode($output)."\r\n", FILE_APPEND | LOCK_EX);





          //пишем результат в DB
          $message = "Голосование";

          break;
          case '/stats':
          $this->db->where('vote',1);
          $query=$this->db->get('tb_vote_vibori');
          $za = $query->num_rows();

          $this->db->where('vote',0);
          $query=$this->db->get('tb_vote_vibori');
          $protiv = $query->num_rows();

          $message = "Статистика:\r\nЗа: $za\r\nПротив: $protiv\r\n";
            //проверяем не голосовал ли данный пользователь

            $tbot = file_get_contents("https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=".$obj->message->chat->id."&text=".urlencode($message));
            //Если нашли ошибку отправляем  сообщение в телеграмм

          $message = "Статистику могут видеть только проголосовавшие граждане";
          break;

        default:
        echo 'ok';
        break;
        }
    }






  }

  function webhook(){

    $obj = file_get_contents("php://input");
    $obj = json_decode($obj, FALSE);



    $file = '/var/log/telegram_bot/telegram_bot.txt';
    $date = date("Y-m-d H:i:s");
//    file_put_contents($file,  "[".$date."] ". json_encode($obj)."\r\n", FILE_APPEND | LOCK_EX);
    file_put_contents($file,  json_encode($obj)."\r\n", FILE_APPEND | LOCK_EX);


    $this->db->where('chat_id', $obj->message->chat->id);
    $query=$this->db->get('tb_telegam_user');

    if($query->num_rows() == 0){
      $data = array('chat_id'=>$obj->message->chat->id,
                    'first_name'=>$obj->message->chat->first_name,
                    'username'=>$obj->message->chat->username
                    );
      $this->db->insert('tb_telegam_user', $data);
    }

  //  $this->db->where('file_id', $obj->photo[0]->file_id);
  //  $query=$this->db->get('tb_city_photo');
    //if($query->num_rows() == 0){

  if (isset($obj->message->photo)){

     $data=array('chat_id'=>$obj->message->chat->id,
                 'file_id'=>$obj->message->photo[0]->file_id,
                 'file_unique_id'=>$obj->message->photo[0]->file_unique_id,
                 'message_id'=>$obj->message->message_id
                 );
     $this->db->insert('tb_city_photo', $data);

     $inline_button1 = array("text"=>"Да","callback_data"=>"yes_spb");
     $inline_button2 = array("text"=>"Дет","callback_data"=>'no_spb');
     $inline_keyboard = [[$inline_button1, $inline_button2]];

     $keyboard = array(
       "inline_keyboard"=>$inline_keyboard,
       "remove_keyboard" => true
     //  "selective" => true
      );
     $replyMarkup = json_encode($keyboard);

     $botToken="956860444:AAFUCkztT55fZq6vladla3Fsx_pTGAKxKM0";
     $chat_id = $obj->message->chat->id;



    // file_get_contents($GLOBALS['api'] . '/sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message) . '&reply_markup=' . $replyMarkup);
  //   file_get_contents($bot_url. 'sendMessage?chat_id=' . $chat_id . '&reply_markup=' . $replyMarkup);


     }
 }
}
