<?php
define('SMARTY_DIR', '/usr/share/php/smarty3/');
require_once(SMARTY_DIR . 'Smarty.class.php');
$smarty = new Smarty();

$smarty->setTemplateDir('templates/');
$smarty->setCompileDir('templates_c/');

if (isset($logged_in) && $logged_in) {
	$smarty->assign('logged_in', true);
	$smarty->assign('user', $user);
}
