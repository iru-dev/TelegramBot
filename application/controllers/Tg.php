<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Tg extends CI_Controller
{
  public function __construct()
  {
      parent::__construct();
      $this->load->model('Telegram');
  }
  function index(){


    if(isset($_POST["submit"])) {
      print_r($_POST);
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);

        $uploaddir = "/usr/www/api.dcserver.ru/public_html/photo";


        $file = $_FILES["fileToUpload"]["tmp_name"];
        $md5 = md5_file($file);
        $name = explode(".", $_FILES["fileToUpload"]["name"]);
        $name = $md5.".".array_pop($name);

        move_uploaded_file($file, $uploaddir."/". $name);

        $inline_button1 = array("text"=>"Свой","callback_data"=>"allow");
        $inline_button2 = array("text"=>"Чужой","callback_data"=>'deny');
        $inline_keyboard = [[$inline_button1, $inline_button2]];

        $keyboard = array(
          "inline_keyboard"=>$inline_keyboard,
          "remove_keyboard" => true
        //  "selective" => true
        );
        $replyMarkup = json_encode($keyboard);

          $botToken="978186325:AAES6DojwRTCrd81GJAJ-83zQRw65oh61Uw";
          $chat_id = "238538484";

          $bot_url = "https://api.telegram.org/bot" .$this->config->item('telegram_bot_token'). "/";
          $url = $bot_url . "sendPhoto?chat_id=" . $chat_id ;

          $post_fields = array(
              'chat_id' => $chat_id,
              'caption' => "Мы его знаем?",
              'reply_markup'=> $replyMarkup,
              'photo' => new CURLFile(realpath($uploaddir."/". $name),)

          );
          echo "<pre>";

    //print_r($post_fields);


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type:multipart/form-data"
    ));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $output = curl_exec($ch);

    $output = json_decode($output);

    print_r($output->result->photo[0]->file_unique_id);

    $file_unique_id = $output->result->photo[0]->file_unique_id;
    $this->db->where('file_unique_id', $file_unique_id);
    $return = $this->db->get('telegram_face');

print_r($return->result_array());

    if ($return->num_rows()==0){

      $data = array(
        'file_unique_id'=>$file_unique_id,
        'md5'=>$md5,
        'file'=>$name,
        'region'=>$_POST['region']
        );
        $this->db->insert('telegram_face', $data );
    }



        echo "</pre>";

    }else{
      echo '<form action="" method="post" enctype="multipart/form-data"> Select image to upload:
  <input type="file" name="fileToUpload" id="fileToUpload">
  <input name="info" value="'.date("Y-m-d H:i:s").'"/>
  <input name="region" value="1"/>
  <input type="submit" value="Upload Image" name="submit">
  </form>
  </body>';
//  $this->tg_model->WriteLog("test");
    }


  }
  function webhook(){



    $obj = file_get_contents("php://input");
    $obj = json_decode($obj, TRUE);

    


    $chat_id = $obj['callback_query']['message']['chat']['id'];
    $message_id = $obj['callback_query']['message']['message_id'];

    $file_unique_id =  $obj['callback_query']['photo'][0]['file_unique_id'];

    $this->tg_model->WriteLog(json_encode($obj));

    if($obj['callback_query']['data']=='allow'){

      $data = array (
        'chat_id' => $chat_id,
        'file_unique_id' => $file_unique_id
      );
    $this->db->insert('telegram_face_allow', $data );
}



    $out = $this->tg_model->editMessageReplyMarkup($chat_id, $message_id);







    }
}



/*To send a message to a user or group chat:

$this->telegram->send
  ->chat("123456")
  ->text("Hello world!")
->send();
To reply a user command:

if($this->telegram->text_command("start")){
  $this->telegram->send
    ->text("Hi!")
  ->send();
}
To reply a user message:

if($this->telegram->text_has("are you alive")){
  $this->telegram->send
    ->text("Yes!")
  ->send();
}
*/
