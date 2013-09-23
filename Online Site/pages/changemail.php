<?php 

require('../includes/config.php');
require('../includes/functions.php');

$error = array();

if (!isset($_POST['password'])) {
	$error[] = 'Please enter your password';
}

if (!isset($_POST['email'])) {
	$error[] = 'Please enter your new email';
}

if (isset($_POST['password']) && isset($_POST['email']) && count($error) == 0) {
	$password = $_POST['password'];
	$email = $_POST['email'];
	$id = $_SESSION['id'];

	$stmt = $mysqli->prepare("SELECT `password`, `salt` FROM `users` WHERE `id` = ? LIMIT 0,1");
	$stmt->bind_param('s', $id);
	$stmt->execute();
	$stmt->bind_result($dbPassword, $dbSalt);
	$stmt->fetch();
	$stmt->close();

	$combinedSalt = hash('sha256',$dbSalt . $configSalt);
	$hashedPassword = hash('sha256',$combinedSalt . $password);

	if ($hashedPassword != $dbPassword) {
		$error[] = 'Wrong password';
	}

	if ($hashedPassword == $dbPassword) {
		$stmt = $mysqli->prepare("UPDATE `users` SET `email` = ? WHERE `id` = ?");
		$stmt->bind_param('si', $email, $id);
		$stmt->execute();
		$stmt->close();

		$error[] = 'Email succesfully changed!';
	}

}

returnError($error, 0);

?>