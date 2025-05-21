<?php
header('Content-Type: application/json');
session_start();
require 'db_connect.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please log in to modify cart.');
    }
    $user_id = (int)$_SESSION['user_id'];
    $cart_id = json_decode(file_get_contents('php://input'), true)['cart_id'] ?? '';
    $book_id = json_decode(file_get_contents('php://input'), true)['book_id'] ?? '';
    $quantity = (int)(json_decode(file_get_contents('php://input'), true)['quantity'] ?? 0);

    if (!$cart_id || !$book_id || !$quantity) {
        throw new Exception('Invalid cart or book ID.');
    }

    // Delete from CART
    $stmt = $pdo->prepare("DELETE FROM CART WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);

    // Restore stock
    $stmt = $pdo->prepare("UPDATE BOOKS SET stock = stock + ? WHERE book_id = ?");
    $stmt->execute([$quantity, $book_id]);

    // Calculate new total
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * b.price) as total
        FROM CART c
        JOIN BOOKS b ON c.book_id = b.book_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $new_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode(['success' => true, 'message' => 'Item removed', 'new_total' => $new_total]);
} catch (Exception $e) {
    error_log("remove_from_cart.php: Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>