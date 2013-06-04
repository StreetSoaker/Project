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

if (!isset($_POST['passwordagain'])) {
	$error[] = 'Please enter your password again';
}

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['passwordagain']) && isset($_POST['email']) && count($error) == 0) {
    $username = $_POST['username'];
	$password = $_POST['password'];
	$passwordagain = $_POST['passwordagain'];
	$email = $_POST['email'];

	$stmt = $mysqli->prepare("SELECT `username`, `email` FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('ss', $username, $email);
	$stmt->execute();
	$stmt->bind_result($dbUsername, $dbEmail);
	$stmt->fetch();
	$stmt->close();
	
	if ($password != $passwordagain) {
		$error[] = 'Passwords do not match';
	}

	if ($username == $dbUsername) {
		$error[] = 'Username is already taken';
	}

	if ($email == $dbEmail) {
		$error[] = 'Email is already taken';
	}

	if($username != $dbUsername && $email != $dbEmail && $password == $passwordagain) {
		$userSalt = str_pad(rand(0,9999999999), 10, '0', STR_PAD_LEFT);
	    $dbSalt = hash('sha256',$userSalt . $configSalt);
	    $dbPassword = hash('sha256', $dbSalt . $password);
	    
	    $stmt = $mysqli->prepare("INSERT INTO `users` VALUES('',?,?,?,?,NOW(),'')") or die($mysqli->error);
		$stmt->bind_param('ssss', $username, $dbPassword, $email, $userSalt) or die($mysqli->error);

		if($stmt->execute() or die($mysqli->error)) {
			echo 'Succesfully registered';

			
		} else {
			$error[] = 'Something went wrong, try again later';
		}
		$stmt->close();
	}
	

}

returnError($error, 0);

?>