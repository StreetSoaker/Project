<?php
require('../includes/config.php');
require('../includes/functions.php');

$json = array();

$userid = $_SESSION['id'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$accuracy = $_POST['accuracy'];
$timestamp = $_POST['timestamp'];

$stmt = $mysqli->prepare("SELECT `userid` FROM `location` WHERE `userid` = ?");
$stmt->bind_param('i', $userid);
$stmt->execute();
$stmt->store_result();
$rows = $stmt->num_rows();
$stmt->close();

if ($rows == 1) {
	$stmt = $mysqli->prepare("UPDATE `location` SET `latitude` = ?, `longitude` = ?, `accuracy` = ?, `time` = FROM_UNIXTIME(?) WHERE `userid` = ?");
	$stmt->bind_param('dddii', $latitude, $longitude, $accuracy, $timestamp, $userid);
	$stmt->execute();
	$stmt->close();
} else {
	$stmt = $mysqli->prepare("INSERT INTO `location` VALUES('',?,?,?,?,FROM_UNIXTIME(?))");
	$stmt->bind_param('idddi', $userid, $latitude, $longitude, $accuracy, $timestamp);
	$stmt->execute();
	$stmt->close();
}

$result = $mysqli->query("SELECT `username`, `latitude`, `longitude`, `time` FROM `location` INNER JOIN `users` ON `users`.`id` = `location`.`userid`");

while ($row = $result->fetch_assoc()) {
	$json[] = $row;
}

$result->free();
echo json_encode($json, JSON_FORCE_OBJECT);

?>