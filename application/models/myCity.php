<?php
/*

1. Адмиралтейский район;
2. Василеостровский район;
3. Выборгский район;
4. Калининский район;
5. Кировский район;
6. Колпинский район;
7. Красногвардейский район;
8. Красносельский район;
9. Кронштадтский район;
10. Курортный район;
11. Московский район;
12. Невский район;
13. Петроградский район;
14. Петродворцовый район;
15. Приморский район;
16. Пушкинский район;
17. Фрунзенский район;
18. Центральный район.



*/

define('TG_API_URL', 'https://api.telegram.org/bot');

class myCity extends CI_Model{

  	function __construct(){
  		parent::__construct();
  	}

function getRandomArea($count=3, $city=1)
  {
    $this->db->limit($count);
    $this->db->where('city', $city);
    $query =  $this->db->get('tb_Area');
    return $query->result();
  }

}
