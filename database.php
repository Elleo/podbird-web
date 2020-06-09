<?php
require_once('config.php');
require_once('adodb/adodb-exceptions.inc.php');
require_once('adodb/adodb.inc.php');
try {
	$adodb =& NewADOConnection($connect_string);
} catch (Exception $e) {
	die("Couldn't connect to database.");
}
?>
