<!DOCTYPE html>
<html>
<head>
    <title>Bootstrap 101 Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Css -->
    <link href="css/bootstrap.min.css"          rel="stylesheet" />
    <!--<link href="css/bootstrap-responsive.css"   rel="stylesheet" />-->
    <link href="css/normalize.css"              rel="stylesheet" />
    <link href="css/fonts.css"                   rel="stylesheet" />
    <link href="css/core.css"                   rel="stylesheet" />
    <!-- Javascript -->
    <script src="js/jquery-1.9.1.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        $(document).ready( function() {
            
            function vcenter(){
                var win_top = $(window).height() / 2;
                var logo_top = $('.logo').height() / 2;
                var login_top = $('.inputbox').height() / 2;
                
                var login_mid = win_top - login_top;
                var logo_mid = win_top - logo_top;
                
                $('.logo').css({'top' : logo_mid+'px'});
                $('.inputbox').css({'top' : login_mid+'px'});
            }
            $('img').load(function(){
                vcenter();
                
                $(window).resize(function(){
                    vcenter();    
                });
            });
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4 logo">
                    <img class="offset1 loginImg" src="img/loginpage/login_logo.png" alt="StreetSoaker Logo" />
            </div>
            <div class="offset1 span7 inputbox">
<?php

//require config and functions files
require('../includes/config.php');
require('../includes/functions.php');

//create empty array to put errors into
$error = array();
if(isset($_SESSION['username'])) {
	echo '<script>history.back();</script>';
}

if(!isset($_GET['token']) && !isset($_POST['password1']) && !isset($_POST['password2']) && !isset($_POST['email'])) {
	?>
		<form method="post" action="passwordrecovery.php">
			<input type="email" name="email"  class="span9" placeholder="Email" />
			<input  class="btn btn-large btn-primary registerButton span9" type="submit" name="submit" value="Send mail" />
		</form>
	<?
} elseif(isset($_GET['token'])) {
	$token = strip_tags($_GET['token']);

	
	$stmt = $mysqli->prepare("SELECT `userid`,`starttime`, `token` FROM `passwordrecovery` WHERE `token` = ? AND `active` = 1 LIMIT 0,1");
	$stmt->bind_param('s', $token);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($userId, $startTime, $token);
	$stmt->fetch();
	/**
	 * Check if token is set and still active
	**/
	if($stmt->num_rows() == 1) {

		/**
		 * Add 3 Hours to the start time
		**/
		$time = new DateTime($startTime);
		$time->add(new DateInterval('PT3H'));
		$stamp = $time->format('Y-m-d H:i:s');

		/**
		 * Echo current time(Left) and Starttime + 3 hours(Right)
		**/
		echo strtotime(date('Y-m-d H:i:s')) . ' - ' . strtotime($stamp);

		/**
		 * Check if time limit isn't reached yet
		**/
		if(strtotime(date('Y-m-d H:i:s')) <= strtotime($stamp)) {
			$_SESSION['token'] = $token;
			$_SESSION['userid'] = $userId;

		/**
		 * Show input boxes to enter the new password
		**/?>
		<form method="post" action="http://<?= $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ?>">
			<input type="password" name="password1" class="span9" placeholder="New password" /><br />
			<input type="password" name="password2" class="span9" placeholder="Re-enter password" /><br />
			<input class="btn btn-large btn-primary registerButton span9" type="submit" name="submit" value="Reset Password" />
		</form>
		<?php

		} else { // End check time limit
			$error[] = 'Link is expired';
		}
	} else { // End num rows token
		$error[] = 'Link not found';
	}
} elseif(isset($_POST['email'])) {
	/**
	 * Check if email is registered
	**/
	$email = $_POST['email'];
	$stmt = $mysqli->prepare("SELECT `id`,`email`,`username` FROM `users` WHERE `email` = ? LIMIT 0,1") or die($mysqli->error());
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($userId, $startTime, $username);
	$stmt->fetch();
	if($stmt->num_rows() > 0) {
		/**
		 * Generate key and store it in the db
		**/
		$email = strip_tags($_POST['email']);
		$hashEmail = hash('sha256',$email);
		$token = hash('sha256',$hashEmail . $configSalt);

		$stmt = $mysqli->prepare("INSERT INTO `passwordrecovery` (userid, token, starttime, active) VALUES(?, ?, ? ,1)");
		$stmt->bind_param('sss', $userId, $token, date('Y-m-d H:i:s')) or die($mysqli->error());

		/**
		 * Check if key is stored and send a mail to the given email
		**/
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
} elseif(isset($_POST['password1']) && isset($_POST['password2'])) {
	if($_POST['password1'] != $_POST['password2']) {
		$error[] = "Given passwords don't match";		
	}

	if(count($error) == 0) {
		$stmt = $mysqli->prepare("SELECT `salt`,`password`,`id` FROM `users` WHERE id = (SELECT userid FROM passwordrecovery WHERE token = ? AND active = 1 LIMIT 0,1) LIMIT 0,1");
		$stmt->bind_param('s', $_SESSION['token']);
		$stmt->execute();
		$stmt->bind_result($dbSalt, $dbPassword,$userId);
		$stmt->fetch();
		$stmt->close();

		$combinedSalt = hash('sha256',$dbSalt . $configSalt);
		$hashedPassword = hash('sha256',$combinedSalt . $_POST['password1']);

		$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
		$stmt->bind_param('si', $hashedPassword, $userId);
		if($stmt->execute()) {
			$stmt = $mysqli->prepare("UPDATE passwordrecovery SET active = 0 WHERE userid = ?");
			$stmt->bind_param('i', $userId);
			if($stmt->execute()) {
				echo 'Password succesfully recovered, please login to continue the war!' . "\n" . '<a href="http://' . $_SERVER['HTTP_HOST'] .'">Login In</a>';
			}
		} else {
			$error[] = 'Whoops something went wrong, please try again later';
		}
	}
}

returnError($error, 0);
 
?>
</div>
        </div>
    </div>
</body>
</html>