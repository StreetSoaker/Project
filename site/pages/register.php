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

if (!isset($_POST['email'])) {
    $error[] = 'Please enter your email';
}

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']) && count($error) == 0) {
    $username = $_POST['username'];
	$password = $_POST['password'];
	$email = $_POST['email'];

	$stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `username` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows() == 1) {
		$stmt->close();

		$error[] = 'Username is already taken';
	} else {
		$stmt->close();

		$userSalt = str_pad(rand(0,9999999999), 10, '0', STR_PAD_LEFT);
        $dbSalt = hash('sha256',$userSalt . $configSalt);
        $dbPassword = hash('sha256', $dbSalt . $password);

        $stmt = $mysqli->prepare("INSERT INTO `users` VALUES('',?,?,?,?)") or die($mysqli->error);
		$stmt->bind_param('ssss', $username, $dbPassword, $email, $userSalt) or die($mysqli->error);

		if($stmt->execute() or die($mysqli->error)) {
			echo 'Succesfully registered';
		} else {
			$error[] = 'Something went wrong, try again later';
		}

	}

}

returnError($error, 0);

?>