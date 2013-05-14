<?php 

error_reporting(E_ALL);

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'streetsoaker';

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_errno) {
	echo 'Failed to connect to MySQL: ' . $mysqli->connect_error;
}

?>