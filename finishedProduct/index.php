<?php
    require_once('includes/config.php');

    if(!isset($_SESSION['username']) && !isset($_SESSION['id'])) {
        require('pages/loginPage.php');
    } else {
        require('pages/mainPage.php');
    }
?>