<?php
require_once('templating.php');
$smarty->assign('page', 'profile');
require_once('authentication.php');
require_once('utils/EmailAddressValidator.php');

if (isset($_POST['email'])) {

	$errors = '';
	$username = $_POST['username'];
	$password = $_POST['password'];
	$passwordrepeat = $_POST['password-repeat'];
	$email = $_POST['email'];

	if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,14}[a-zA-Z0-9]$/', $username)) {
		$errors .= 'Your username must be at least 3 characters in length (max 16) and only consist of <i>a-z, A-Z, 0-9</i> and _ (underscore), and may not begin or end with an underscore.<br />';
	}
	if (empty($password)) {
		$errors .= 'You must enter a password.<br />';
	}
	if ($password != $passwordrepeat) {
		$errors .= 'Your passwords do not match.<br />';
	}
	if (empty($email)) {
		$errors .= 'You must enter an e-mail address.<br />';
	} else {
		$validator = new EmailAddressValidator();
		if (!$validator->check_email_address($email)) {
			$errors .= 'You must provide a valid email address!<br />';
		}
	}

	//Check this username is available
	try {   
		$res = $adodb->GetOne('SELECT username FROM users WHERE lower(username) = lower(' . $adodb->qstr($username) . ')');
	} catch (Exception $e) {
		$errors .= 'Database error.<br />';
	}
	if ($res) {
		$errors .= 'Sorry, that username is already registered.<br />';
	}

	if (empty($errors)) {
		$salt = md5(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
		// Create the user
		$sql = 'INSERT INTO users (username, password, email, salt) VALUES ('
			. $adodb->qstr($username) . ', '
			. $adodb->qstr(hash('sha256', md5($password) . $salt)) . ', '
			. $adodb->qstr($email) . ', '
			. $adodb->qstr($salt) . ')';
		try {   
			$insert = $adodb->Execute($sql);
			$adodb->CacheFlush('SELECT * FROM users WHERE lower(username) = lower(' . $adodb->qstr($username) . ') LIMIT 1');
		} catch (Exception $e) {
			$errors .= $e->getMessage() . "<br />";
		}
	}

	if (!empty($errors)) {
		$smarty->assign('errors', $errors);
	} else {
		$_SESSION['username'] = $username;
		$logged_in = true;
	}

}


if(!$logged_in) {
	$smarty->assign('title', 'Register');
	$smarty->display('register.tpl');
} else {
	header('Location: /profile.php');
}

?>
