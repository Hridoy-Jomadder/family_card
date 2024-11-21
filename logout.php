<?php
session_start(); // Start the session

// Destroy all session data
session_unset(); // Unset session variables
session_destroy(); // Destroy the session

// Redirect to login page or home page
header("Location: login.php"); // Change 'login.php' to your desired page
exit();
?>
