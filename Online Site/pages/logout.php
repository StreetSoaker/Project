<?php

require('../includes/config.php');

session_unset();
session_destroy();

if(isset($_POST['test'])) {
	echo $_POST['test'];
}

exit();
?>