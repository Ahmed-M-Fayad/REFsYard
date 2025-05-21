<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

$session_id = session_id();
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
error_log("check_session.php: session_id=$session_id, user_id=$user_id, session_status=" . session_status());

echo json_encode([
    'isLoggedIn' => $user_id !== 0,
    'userId' => $user_id
]);
?>