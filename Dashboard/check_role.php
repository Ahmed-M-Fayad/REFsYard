<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php'; // This should set up $pdo

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['exists' => false, 'role' => null, 'error' => 'Invalid request method']);
    exit();
}

$username = trim($_POST["username"] ?? '');

if (empty($username)) {
    echo json_encode(['exists' => false, 'role' => null]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE name = :name");
    $stmt->execute(['name' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['exists' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['exists' => false, 'role' => null]);
    }
} catch (PDOException $e) {
    error_log("Check role error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['exists' => false, 'role' => null, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in check_role: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
    echo json_encode(['exists' => false, 'role' => null, 'error' => 'Server error: ' . $e->getMessage()]);
}
exit();
?>