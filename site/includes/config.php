<?php 

$hostname = 'localhost';
$username = 'root';
$password = 'test123';
$database = 'supersoaker2';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_errno) {
	echo 'Failed to connect to MySQL: ' . $mysqli->connect_error;
}

?>