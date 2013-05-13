<?php 

require('includes/config.php');



if (!isset($_POST)) {
	echo 'Please enter a username';
	echo 'Please enter a password';

} elseif (!isset($_POST['username'])) {
	echo 'Please enter a username';


} elseif (!isset($_POST['password'])) {
	echo 'Please enter a password';

} else {

	$username = $mysqli->real_escape_string($_POST['username']);
	$password = $mysqli->real_escape_string($_POST['password']);

	$sql = $mysqli->query("SELECT * FROM `users` WHERE `username` = '{$username}' AND `password` = '{$password}'");
	$rows = $sql->num_rows;

	if ($rows = 1) {
		
	}


}

?>