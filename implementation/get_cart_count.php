<?php
header('Content-Type: application/json');
require 'db_connect.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$session_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id === 0) {
    $user_id = $session_user_id;
}

error_log("get_cart_count.php: user_id=$user_id, session_user_id=$session_user_id");

if ($user_id === 0) {
    error_log("get_cart_count.php: Invalid user_id");
    echo json_encode(['cart_count' => 0, 'error' => 'Invalid user_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cart_count FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
    error_log("get_cart_count.php: cart_count=$cart_count");
    echo json_encode(['cart_count' => $cart_count]);
} catch (PDOException $e) {
    error_log("get_cart_count.php: PDOException: " . $e->getMessage());
    echo json_encode(['cart_count' => 0, 'error' => 'Database error']);
}
?>