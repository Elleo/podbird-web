<?php
require_once('templating.php');
$smarty->assign('page', 'contribute');
require_once('authentication.php');

$smarty->assign('title', 'Contribute');
$smarty->display('contribute.tpl');
?>
