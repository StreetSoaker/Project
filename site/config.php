<?php 

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'supersoaker';

$connection = mysqli_connect($hostname,$username,$password,$database);

if (!$connection) {
	die(mysqli_connect_error());
}

?>