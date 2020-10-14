<?php


class Wander_wafla extends CI_Model
{
  private $cToken='78181a9ca222467cb1516fb2c818377d';
  private $dToken='eb0c509cb8984841bf091bd5739bcebf';
  private $endpoint='https://dialogflow.googleapis.com';

  function getWanderWafla($user_id, $message){

  }

  function find_response_message($user_id, $message, $priv=0){
    if($priv == 0){
  /*
      $message = urldecode($message);
      $message = str_replace('/', ' ', $message);
      $message = str_replace('\'', ' ', $message);
      $message = str_replace('"', ' ', $message);
      $message = str_replace('\\', ' ', $message);
      $message = str_replace('?', ' ', $message);
      $message = str_replace('.', ' ', $message);
      $message = str_replace(',', ' ', $message);
      $message = preg_replace("/\s{2,}/", ' ', $message);
*/

      $this->db->where('MATCH (response) AGAINST ("' . $message .
                    '" IN NATURAL LANGUAGE MODE) > 0', null, false);
      $this->db->limit(1);
      $this->db->order_by('id', 'RANDOM');
      $query = $this->db->get('tb_wanderwafla');

      if ($query->num_rows() > 0) {
        return $query->result()[0]->answer;
      } else {
        return $this->find_response_message($user_id, $message, 1);
      }
  }else{
      //Зпрос на вандервафлю
      $response = $this->getWanderWafla($user_id, $message);
      //Добавление ответа в локальную бд
      $data = array(
        'response'=> $message,
        'answer'=> $response
      );
       $this->db->insert('tb_wanderwafla', $data);
      return $response;
    }

  }
}
