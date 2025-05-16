<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php'; 


ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit();
    }

    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

  
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in both username and password']);
        exit();
    }

    
    try {
        $stmt = $pdo->prepare("SELECT user_id, name, password FROM users WHERE name = :name");
        $stmt->execute(['name' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        
        if ($user && password_verify($password, $user['password'])) {
          
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["name"];
            error_log("Login successful for user: $username");
            header('Location: home.php');
            exit();
        } else {
            error_log("Invalid credentials for user: $username");
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
        exit();
    }
}
?>