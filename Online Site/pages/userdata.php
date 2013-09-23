<?php 
	require_once('../includes/config.php');
	$userdata = array('userid' => $_SESSION['id'], 'username' => $_SESSION['username']);
	echo json_encode($userdata);
?>