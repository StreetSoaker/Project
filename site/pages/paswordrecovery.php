<?php

//require config and functions files
require('../includes/config.php');
require('../includes/functions.php');

//create empty array to put errors into
$error = array();

// Check if token get value is given
if (isset($_GET['token'] && !empty($_GET['token']))) {
		$token = $_GET['token'];

		//Check if the password1 and password2 post values are set
		if (isset($_POST['password1'] && isset($_POST['password2']))) {	
			$password1 = $_POST['password1'];
			$password2 = $_POST['password2'];

			//check if password1 matches password2
			if ($password1 == $password2) {

				//Hash the password with sha256 and generate unique usersalt
				$userSalt = str_pad(rand(0,9999999999), 10, '0', STR_PAD_LEFT);
		        $dbSalt = hash('sha256',$userSalt . $configSalt);
		        $dbPassword = hash('sha256', $dbSalt . $password1);

		        //update the users table with the new password and usersalt
				$stmt = $mysqli->prepare("UPDATE `users` SET `password`=? `salt`=? WHERE `id` = ?") or die($mysqli->error);
				$stmt->bind_param('ss', $dbPassword, $userSalt, $userid);
				$stmt->execute();
				$stmt->close();

				//Set the password recovery token in the database on non active 
				$stmt = $mysqli->prepare("UPDATE `passwordrecovery` SET `active`= 0 WHERE `token` = ?") or die($mysqli->error);
				$stmt->bind_param('s', $token);
				$stmt->execute();
				$stmt->close();

			} else {
				$error[] = 'New Passwords do not match';
			}

		} else {
			//Select userid and starttime from database to check if the token has expired yet
			$stmt = $mysqli->prepare("SELECT `userid`, `starttime` FROM `passwordrecovery` WHERE `token` = ? LIMIT 0,1") or die($mysqli->error);
			$stmt->bind_param('s', $token);
			$stmt->execute();
			$stmt->store_result();
	 
			if ($stmt->num_rows == 1) {
					$stmt->bind_result($userid, $startime);
					if ((mktime() - $startime) <= 3600) {
					?>

						<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
							<input type="password" name="password1" />
							<input type="password" name="password2" />
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
		
}
 
if (!isset($_POST['email'])) {
	$error[] = 'Please enter your password';
}
 
if (isset($_POST['email']) && count($error) == 0) {
 
		$email = $_POST['email'];
 
		$stmt = $mysqli->prepare("SELECT `id`, `username` FROM `users` WHERE `email` = ? LIMIT 0,1") or die($mysqli->error);
		$stmt->bind_param('s', $email);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows == 1) {
			$stmt->bind_result($id, $username);

		} else {
			$error[] = 'This email does not exist in our database';
		}
 
 
} else {
 
}
 
 
?>