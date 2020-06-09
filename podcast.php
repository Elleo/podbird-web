<?php
require_once('templating.php');
$smarty->assign('page', 'profile');
require_once('authentication.php');

$podcast = new Podcast($_GET['id']);

$smarty->assign('episodes', $podcast->episodes());
$smarty->assign('podcast', $podcast);
$smarty->assign('title', $podcast->name);
$smarty->display('podcast.tpl');
		 

?>
