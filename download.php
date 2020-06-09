<?php
require_once('templating.php');
$smarty->assign('page', 'contribute');
require_once('authentication.php');

$smarty->assign('title', 'Download');
$smarty->display('download.tpl');
?>
