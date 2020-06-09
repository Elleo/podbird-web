<?php
require_once("templating.php");
$smarty->assign('page', 'notifications');
require_once("authentication.php");

$smarty->assign('title', 'Notifications');
$smarty->display('notifications.tpl');
?>
