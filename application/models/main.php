<?
$smarty = new Smarty();

$smarty->assign('THEME', base_url('assets/main') . "/");

if (isset($data)) {
     
       
    $smarty->assign('data', $data);

}

$body = $smarty->fetch($content);
$smarty->assign('content', $body);

$smarty->display('main/general.tpl');


?>