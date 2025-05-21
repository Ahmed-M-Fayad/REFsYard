<?php
session_start();

// Log the user ID before logout for debugging
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
error_log("logout.php: User ID $user_id logged out");

// Destroy all session data
session_unset();
session_destroy();

// Redirect to index.php
header("Location: index.php");
exit;
?>