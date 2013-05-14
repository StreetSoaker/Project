

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

	$stmt = $mysqli->prepare("SELECT `salt`,`password` FROM `users` WHERE `username` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows() == 1) {
        $stmt->bind_result($dbSalt, $dbPassword);
    	while ($stmt->fetch()) {
            $newSalt = hash('sha256',$dbSalt . $configSalt);
            if(hash('sha256',$newSalt . $password) == $dbPassword) {
                echo 'Login Succes';
            } else {
                echo 'Wrong password';
            }
        }
	} else {
		$error[] = 'Invalid username or password';
	}

	$stmt->close();
}

returnError($error, 0);

?>