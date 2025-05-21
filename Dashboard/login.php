<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require 'db_connect.php'; // Ensure this file sets up $pdo correctly

// Log errors to a file without displaying to the user
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

// Ensure it's a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit();
    }

    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    // Check if username or password is empty
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in both username and password']);
        exit();
    }

    try {
        // Prepare query to fetch user
        $stmt = $pdo->prepare("SELECT user_id, name, password, role FROM users WHERE name = :name");
        $stmt->execute(['name' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user not found
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            exit();
        }

        // If role is not admin
        if ($user["role"] !== "admin") {
            error_log("Customer attempted login: $username");
            echo json_encode([
                'success' => false,
                'error' => "Welcome {$user['name']}, you are a Customer and not allowed to access the dashboard."
            ]);
            exit();
        }

        // If password is correct
        if (password_verify($password, $user['password'])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["name"];

            // Send redirect URL as success response (AJAX fetch can't follow header redirect)
            echo json_encode(['success' => true, 'redirect' => 'home.php']);
            exit();
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
            exit();
        }

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error occurred']);
        exit();
    }
}
?>
