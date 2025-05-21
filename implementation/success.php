<?php
session_start();

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id === 0) {
    header("Location: login.php?error=Please log in to view this page");
    exit;
}

// Include database connection
require 'db_connect.php';

try {
    // Clear the user's cart
    $stmt = $pdo->prepare("DELETE FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    error_log("success.php: Cleared cart for user_id=$user_id");
} catch (PDOException $e) {
    error_log("success.php: Error clearing cart: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Completed</title>
    <link rel="stylesheet" href="success.css">
    <!-- FontAwesome for the check icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .thank-you {
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
        h1 {
            color: #1a3c5e;
            font-size: 28px;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background: #1a3c5e;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        a:hover {
            background: #2a5d8f;
        }
    </style>
</head>
<body>
    <section class="thank-you">
        <div class="container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Payment Completed</h1>
            <p>Your payment has been successfully processed. Thank you for your purchase!</p>
            <a href="home.php">Back to Home</a>
        </div>
    </section>
</body>
</html>