<?php

require('../includes/config.php');
require('../includes/functions.php');

$error = array();

if (isset($_GET'token')) {
	$token = $_GET['token'];
	
	$stmt = $mysqli->prepare("SELECT `userid`, `starttime` WHERE `token` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('s', $token);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows == 1) {
		$stmt->bind_result($userid, $startime);
		if ((mktime() - $startime) <= 3600) {
		?>

			<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="password" name="pass1" />
				<input type="password" name="pass1" />
				<input type="submit" name="submit" />
			</form>

		<?php
		} else {
			$error[] = 'Password recovery token has expired';
		}

		$userSalt = str_pad(rand(0,9999999999), 10, '0', STR_PAD_LEFT);

	} else {
		$error[] = 'Invalid password recovery token';
	}

	$stmt->close();
}

if (!isset($_POST['email'])) {
    $error[] = 'Please enter your password';
}

if (isset($_POST['email']) && count($error) == 0) { 

	$email = $_POST['email'];

	$stmt = $mysqli->prepare("SELECT `email` FROM `users` WHERE `username` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$stmt->store_result();


} else {

}


?>