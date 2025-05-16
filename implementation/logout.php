<?php
session_start();


$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
error_log("logout.php: User ID $user_id logged out");
session_unset();
session_destroy();


header("Location: index.php");
exit;
?>