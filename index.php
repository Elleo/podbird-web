<?php
require_once('templating.php');
$smarty->assign('page', 'home');
require_once('authentication.php');

$smarty->assign('title', 'Home');
$smarty->display('home.tpl');
?>
