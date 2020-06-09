<?php
require_once('templating.php');
$smarty->assign('page', 'profile');
require_once('authentication.php');

if(!$logged_in) {
	$smarty->assign('title', 'Login');
	$smarty->display('login.tpl');
	die();
}

if(isset($_POST['subscribe'])) {
	$user->subscribe($_POST['subscribe']);
}

if(isset($_POST['unsubscribe'])) {
	$user->unsubscribe($_POST['unsubscribe']);
}

$smarty->assign('subscriptions', $user->subscriptions());
$smarty->assign('recommendations', $user->getRecommended());
$smarty->assign('title', 'Profile');
$smarty->display('profile.tpl');
		 

?>
