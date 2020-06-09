<?php

require_once('database.php');
require_once('templating.php');
require_once('model/user.php');

$logged_in = false;

// Remember logins for 30 days
session_set_cookie_params(2592000);
ini_set('session.gc_maxlifetime', 2592000);

session_start();
if (isset($_GET['logout'])) {
	$_SESSION = array();
}

if(isset($_POST['username']) && isset($_POST['password'])) {
	try {
		$loginUser = new User($_POST['username']);
		if ($loginUser->password == hash('sha256', md5($_POST['password']) . $loginUser->salt)) {
			$_SESSION['username'] = $loginUser->username;
		} else {
			$smarty->assign('errors', array("Invalid password"));
		}
	} catch (Exception $e) {
		$smarty->assign('errors', array("No such user"));
	}
}

if(isset($_SESSION["username"]) && !empty($_SESSION['username'])) {
	try {
		$logged_in = true;
		$user = new User($_SESSION["username"]);
		$smarty->assign('user', $user);
	} catch (Exception $e) {
		die($e);
	}
		
}

$smarty->assign('logged_in', $logged_in);
