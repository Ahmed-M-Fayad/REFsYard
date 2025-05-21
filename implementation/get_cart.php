<?php
require_once 'db_connection.php';

$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$sql = "SELECT c.book_id, b.title, b.price, c.quantity
        FROM cart c
        JOIN books b ON c.book_id = b.book_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total_price = 0;

while ($row = $result->fetch_assoc()) {
    $row['total'] = $row['price'] * $row['quantity'];
    $total_price += $row['total'];
    $cart_items[] = $row;
}

echo json_encode([
    'success' => true,
    'cart' => $cart_items,
    'total_price' => $total_price
]);
?>
