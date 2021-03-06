<?php

require('../includes/config.php');
require('../includes/functions.php');

$error = array();

if(isset($_SESSION['username'])) {
    $error[] = 'You are already logged in';
}

if (!isset($_POST['username'])) {
    $error[] = 'Please enter your username';
}

if (!isset($_POST['password'])) {
    $error[] = 'Please enter your password';
}

if (isset($_POST['username']) && isset($_POST['password']) && count($error) == 0) {
    $username = $_POST['username'];
	$password = $_POST['password'];

	$stmt = $mysqli->prepare("SELECT `id`, `username`, `salt`,`password` FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 0,1") or die($mysqli->error);
	$stmt->bind_param('ss', $username, $username);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows() == 1) {
        $stmt->bind_result($id, $dbUsername, $dbSalt, $dbPassword);
    	$stmt->fetch();
        $stmt->close();
        $newSalt = hash('sha256',$dbSalt . $configSalt);

        if(hash('sha256',$newSalt . $password) == $dbPassword) { 
            $stmt = $mysqli->prepare("UPDATE `users` SET `lastlogin` = NOW() WHERE id = ?");
            $stmt->bind_param('i', $id); 
            $stmt->execute();
            $stmt->close();

            $_SESSION['username'] = $dbUsername;
            $_SESSION['id'] = $id;
            echo 1;
        } else {
            $error[] =  'Wrong password';
        }
        
	} else {
		$error[] = 'Invalid username or password';
	}
}

returnError($error, 0);

?>