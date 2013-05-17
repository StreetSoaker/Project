<?php 

session_start();

error_reporting(E_ALL);

$hostname = '46.21.172.159';
$username = 'alberjg10_soaker';
$password = 'C2AQtirl';
$database = 'alberjg10_supersoaker';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_errno) {
	echo 'Failed to connect to MySQL: ' . $mysqli->connect_error;
}

$configSalt = 9930152851;

?>