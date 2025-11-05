<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit;
}

// If not logged in, redirect to login page
header("Location: login.php");
exit;
?>