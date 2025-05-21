<?php
header('Content-Type: application/json');
require 'db_connect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $book_id = isset($data['book_id']) ? trim($data['book_id']) : '';

    if ($user_id === 0 || !$book_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid user_id or book_id']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM WISHLIST WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $is_in_wishlist = $stmt->fetchColumn() > 0;

    echo json_encode(['success' => true, 'is_in_wishlist' => $is_in_wishlist]);
} catch (PDOException $e) {
    error_log("check_wishlist.php: Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>