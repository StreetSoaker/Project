<?php

//require config and functions files
require('../includes/config.php');
require('../includes/functions.php');

//create empty array to put errors into
$error = array();

if(!isset($_GET['token']) && !isset($_POST['password1']) && !isset($_POST['password2']) || !isset($_POST['email'])) {
	return false;
} elseif(isset($_GET['token'])) {
	$token = strip_tags($_GET['token']);

	$stmt = $mysqli->prepare("SELECT `userid`,`starttime` FROM `passwordrecovery` WHERE `token` = ? AND `active` = 1 LIMIT 0,1");
	$stmt->bind_param('s', $token);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($userId, $startTime);
	$stmt->fetch();
	if($stmt->num_rows() == 1) {
		if((mktime() - $startTime) < 3600) {
		?>

		<form method="post">
			<input type="password" name="password1" placeholder="New password" />
			<input type="password" name="password2" placeholder="Re-enter new password" />
			<input type="hidden" name="userId" value="<?= $userId ?>" />
			<input type="hidden" name="token" value="<?= $token ?>" />
			<input type="submit" name="submit" value="Resetten" />
		</form>
		
		<?php
		} else {
			$error[] = 'Link is expired';
		}
	} else {
		$error[] = 'Link not found';
	}
} elseif(isset($_POST['email'])) {
	$stmt = $mysqli->prepare("SELECT `userid`,`email`,`username` FROM `users` WHERE `email` = ? LIMIT 0,1");
	$stmt->bind_param('s', $token);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($userId, $startTime, $username);
	$stmt->fetch();
	if($stmt->num_rows() > 0) {
		$email = strip_tags($_POST['email']);
		$hashEmail = hash('sha256',$email);
		$token = hash('sha256',$hashEmail . $configSalt);

		$stmt = $mysqli->prepare("INSERT INTO `passwordrecovery` SET `userid` = ? AND `starttime` = ? AND `token` = ? AND `active` = 1");
		$stmt->bind_param('sss', $userId, mktime(), $token);
		;

		if($stmt->execute()) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'To: '. $username .' <'.$email.'>' . "\r\n";
			$headers .= 'From: StreetSoaker <no-reply@streetsoaker.com>' . "\r\n";

			$subject = 'StreetSoaker - Reset your password';
			$link 	 = 'http://localhost/~Robin/StreetSoaker/Project/site/pages/paswordrecovery.php?token='. $token;
			$content = "
Hello ". $username .", \n
\n
<a href=\"". $link ."\">Reset password</a>\n
\n
Regards, \n
StreetSoaker
			";

			$mail = mail($email,$subject, $content, $headers);
			if($mail) {
				echo 'Email has been send to '. $email .". Click on the link in the mail to enter your new password.";
			} else {
				$error[] = 'Couldn\'t send email, please try again later';
			}

		} else {
			$error[] = 'Couldn\'t reset your password password, please try again later';
		}

	} else {
		$error[] = 'Email address not registered';
	}
} else {

}

returnError($error, 0);
 
?>