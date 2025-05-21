<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Ensure PDO is in exception mode
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Log raw input for debugging
    $raw_input = file_get_contents('php://input');
    error_log("store_books.php: Raw input: " . $raw_input);

    // Decode JSON input
    $books = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }
    if (!is_array($books) || empty($books)) {
        throw new Exception('Invalid or empty data format');
    }

    // Validate required fields
    foreach ($books as $index => $book) {
        if (!isset($book['book_id']) || empty($book['book_id'])) {
            throw new Exception("Missing book_id for book at index $index");
        }
        if (!isset($book['title']) || empty($book['title'])) {
            throw new Exception("Missing title for book at index $index");
        }
        if (!isset($book['author']) || empty($book['author'])) {
            throw new Exception("Missing author for book at index $index");
        }
        if (!isset($book['price'])) {
            throw new Exception("Missing price for book at index $index");
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Prepare SQL statement (fixed publish_date and added is_featured)
    $stmt = $pdo->prepare("
        INSERT INTO BOOKS (
            book_id, title, author, price, stock, isbn, image_url,
            is_featured, description, publisher, publish_date
        ) VALUES (
            :book_id, :title, :author, :price, :stock, :isbn, :image_url,
            :is_featured, :description, :publisher, :publish_date
        )
        ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            author = VALUES(author),
            price = VALUES(price),
            stock = VALUES(stock),
            isbn = VALUES(isbn),
            image_url = VALUES(image_url),
            is_featured = VALUES(is_featured),
            description = VALUES(description),
            publisher = VALUES(publisher),
            publish_date = VALUES(publish_date)
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $pdo->errorInfo()[2]);
    }

    // Process each book
    foreach ($books as $book) {
        // Provide fallbacks and validate
        $book_id = $book['book_id'] ?? '';
        $title = $book['title'] ?? 'Untitled';
        $author = $book['author'] ?? 'Unknown Author';
        $price = isset($book['price']) ? floatval($book['price']) : 0.00;
        $stock = isset($book['stock']) ? intval($book['stock']) : 0;
        $isbn = $book['isbn'] ?? null;
        $image_url = $book['image_url'] ?? null;
        $is_featured = isset($book['is_featured']) ? intval($book['is_featured']) : 0;
        $description = $book['description'] ?? null;
        $publisher = $book['publisher'] ?? null;
        $publish_date = isset($book['published_date']) ? $book['published_date'] : ($book['publish_date'] ?? null);

        // Bind parameters
        $stmt->execute([
            ':book_id' => $book_id,
            ':title' => $title,
            ':author' => $author,
            ':price' => $price,
            ':stock' => $stock,
            ':isbn' => $isbn,
            ':image_url' => $image_url,
            ':is_featured' => $is_featured,
            ':description' => $description,
            ':publisher' => $publisher,
            ':publish_date' => $publish_date
        ]);
    }

    // Commit transaction
    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Books stored successfully',
        'count' => count($books)
    ]);
} catch (Exception $e) {
    // Roll back transaction on error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("store_books.php: Error: " . $e->getMessage() . " | Input: " . $raw_input);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to store books. Please try again or contact support.'
    ]);
}
?>