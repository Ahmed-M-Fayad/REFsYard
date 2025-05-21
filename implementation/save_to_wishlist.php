<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$input = json_decode(file_get_contents('php://input'), true);
$book_id = isset($input['book_id']) ? urldecode(trim($input['book_id'])) : '';

if ($user_id === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (empty($book_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid book_id']);
    exit;
}

// Validate book_id exists in BOOKS
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM BOOKS WHERE book_id = ?");
    $stmt->execute([$book_id]);
    if ($stmt->fetchColumn() == 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid book_id']);
        exit;
    }

    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM WISHLIST WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Book already in wishlist']);
        exit;
    }

    // Add to wishlist
    $stmt = $pdo->prepare("INSERT INTO WISHLIST (user_id, book_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $book_id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    error_log("save_to_wishlist.php: Database error: " . $e->getMessage());
}
?>