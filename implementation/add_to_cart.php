<?php
ob_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');

require 'db_connect.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$book_id = isset($data['book_id']) ? trim($data['book_id']) : '';
$session_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

error_log("add_to_cart.php: book_id=$book_id, user_id=$user_id, session_user_id=$session_user_id");

if ($user_id === 0 || $user_id !== $session_user_id) {
    error_log("Add to cart failed: Invalid user_id=$user_id or session mismatch, session_user_id=$session_user_id");
    echo json_encode(['success' => false, 'message' => 'Please log in to add to cart.']);
    ob_end_flush();
    exit;
}

if (empty($book_id) || !preg_match('/^[a-zA-Z0-9_-]+$/', $book_id)) {
    error_log("Add to cart failed: Invalid book_id: " . ($book_id ?: 'empty'));
    echo json_encode(['success' => false, 'message' => 'Invalid book ID.']);
    ob_end_flush();
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT stock FROM BOOKS WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        $pdo->rollBack();
        error_log("Add to cart failed: Book not found for book_id: $book_id");
        echo json_encode(['success' => false, 'message' => 'Book not found.']);
        ob_end_flush();
        exit;
    }

    if ($book['stock'] <= 0) {
        $pdo->rollBack();
        error_log("Add to cart failed: Book out of stock for book_id: $book_id");
        echo json_encode(['success' => false, 'message' => 'Book is out of stock.']);
        ob_end_flush();
        exit;
    }

    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM CART WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$user_id, $book_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $stmt = $pdo->prepare("UPDATE CART SET quantity = quantity + 1 WHERE cart_id = ?");
        $stmt->execute([$cart_item['cart_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO CART (user_id, book_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $book_id]);
    }

    $stmt = $pdo->prepare("UPDATE BOOKS SET stock = stock - 1 WHERE book_id = ?");
    $stmt->execute([$book_id]);

    $stmt = $pdo->prepare("SELECT stock FROM BOOKS WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $new_stock = $stmt->fetch(PDO::FETCH_ASSOC)['stock'];

    $pdo->commit();
    error_log("add_to_cart.php: Success, book_id=$book_id, user_id=$user_id, new_stock=$new_stock");
    echo json_encode(['success' => true, 'message' => 'Book added to cart.', 'new_stock' => $new_stock]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("add_to_cart.php: PDOException: " . $e->getMessage() . " for book_id: $book_id, user_id=$user_id");
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("add_to_cart.php: Exception: " . $e->getMessage() . " for book_id: $book_id, user_id=$user_id");
    echo json_encode(['success' => false, 'message' => 'Unexpected error']);
} finally {
    ob_end_flush();
}
?>