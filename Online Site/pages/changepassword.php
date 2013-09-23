<?php

require('../includes/config.php');
require('../includes/functions.php');

$error = array();

if (!isset($_POST['password'])) {
	$error[] = 'Please enter your current password';
}

if (!isset($_POST['newpassword'])) {
	$error[] = 'Please enter new password';
}

if (!isset($_POST['newpasswordagain'])) {
	$error[] = 'Please enter new password again';
}

if (isset($_POST['password']) && isset($_POST['newpassword']) && isset($_POST['newpasswordagain']) && count($error) == 0) {
	$username = $_SESSION['username'];
	$password = $_POST['password'];
	$newPassword = $_POST['newpassword'];
	$newPasswordAgain = $_POST['newpasswordagain'];
	$id = $_SESSION['id'];

	$stmt = $mysqli->prepare("SELECT `password`, `salt` FROM `users` WHERE id = ? LIMIT 0,1");
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$stmt->bind_result($dbPassword, $dbSalt);
	$stmt->fetch();
	$stmt->close();

	$combinedSalt = hash('sha256',$dbSalt . $configSalt);
	$hashedPassword = hash('sha256',$combinedSalt . $password);

    if ($hashedPassword != $dbPassword) { 
    	$error[] = 'Wrong password';
	}

	if ($newPassword != $newPasswordAgain) {
		$error[] = 'Passwords do not match';
	}

	if ($hashedPassword == $dbPassword && $newPassword == $newPasswordAgain) {
		$userSalt = str_pad(rand(0,9999999999), 10, '0', STR_PAD_LEFT);
		$dbSalt = hash('sha256',$userSalt . $configSalt);
		$dbPassword = hash('sha256', $dbSalt . $newPassword);

		$stmt = $mysqli->prepare("UPDATE `users` SET `password` = ?, `salt` = ? WHERE `id` = ?");
		$stmt->bind_param('ssi', $dbPassword, $userSalt, $id);
		$stmt->execute();
		$stmt->close();

		$error[] = 'Password change success';
	}

}

returnError($error, 0);

?>