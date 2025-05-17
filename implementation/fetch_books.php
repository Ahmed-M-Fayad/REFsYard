<?php
header('Content-Type: application/json');
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$search = isset($data['search']) ? trim($data['search']) : '';
$min_price = isset($data['min_price']) && $data['min_price'] !== '' ? floatval($data['min_price']) : null;
$max_price = isset($data['max_price']) && $data['max_price'] !== '' ? floatval($data['max_price']) : null;
$in_stock = isset($data['in_stock']) ? filter_var($data['in_stock'], FILTER_VALIDATE_BOOLEAN) : false;
$author = isset($data['author']) ? trim($data['author']) : '';

try {
    $sql = "SELECT book_id, title, author, price, stock, image_url FROM BOOKS WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (title LIKE :search OR author LIKE :search)";
        $params['search'] = "%$search%";
    }

    if (!is_null($min_price)) {
        $sql .= " AND price >= :min_price";
        $params['min_price'] = $min_price;
    }
    if (!is_null($max_price)) {
        $sql .= " AND price <= :max_price";
        $params['max_price'] = $max_price;
    }

    if ($in_stock) {
        $sql .= " AND stock > 0";
    }

    if (!empty($author)) {
        $sql .= " AND author = :author";
        $params['author'] = $author;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($books);
} catch (PDOException $e) {
    error_log("Fetch books failed: " . $e->getMessage());
    echo json_encode([]);
}
?>