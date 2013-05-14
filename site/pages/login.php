<?php

require('../includes/config.php');
require('../includes/functions.php');

$error = array();

if (!isset($_POST['username'])) {
	$error[] = 'Please enter your username';
}

if (!isset($_POST['password'])) {
	$error[] = 'Please enter your password';
}

if (isset($_POST['username']) && isset($_POST['password']) && count($error) == 0) {
	$username = $_POST['username'];
	$password = $_POST['password'];

	$query = $mysqli->prepare('SELECT * FROM `users` WHERE `username` = ? AND `password` = ?');
	$query->bind_param('ss', $username, $password);
	$query->execute(); 
	$query->store_result();

	if ($query->num_rows() == 1) {
		echo 'Login success';
	} else {
		$error[] = 'Invalid username or password';
	}

	$query->close();
}

returnError($error, 0);
?>