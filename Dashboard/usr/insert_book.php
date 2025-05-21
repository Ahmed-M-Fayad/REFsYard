<?php
session_start();
header('Content-Type: application/json');

$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "bookstore_db";

try {
    $conn = new mysqli($db_server, $db_user, $db_password, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $book_id = (int)($_POST['old_id'] ?? 10000);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $author = trim($_POST['author'] ?? '');
        $featured = ($_POST['featured'] === 'Featured') ? 1 : 0;

        // Handle file upload
        $image_url = 'images/new-book2.jpg'; // Default
        if (isset($_FILES['image_url'])) {
            $file = $_FILES['image_url'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB

                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception("Invalid image format. Use JPEG/PNG/GIF.");
                }

                if ($file['size'] > $max_size) {
                    throw new Exception("Image exceeds 2MB.");
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . '.' . $ext;
                $upload_dir = 'images/';
                $upload_path = $upload_dir . $filename;

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_url = $upload_path;
                } else {
                    throw new Exception("Failed to save file. Check permissions.");
                }
            }
        }

        // Validate
        if (empty($title) || $price <= 0 || $stock < 0) {
            throw new Exception("Title, price (>0), and stock (≥0) are required.");
        }

        // Insert into DB
        $stmt = $conn->prepare("
            INSERT INTO books (
                book_id, title, description, image_url, 
                price, stock, author, is_featured, added_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "isssdisi", 
            $book_id, $title, $description, $image_url, 
            $price, $stock, $author, $featured
        );

        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Book added!',
                'image_url' => $image_url
            ];
            echo json_encode($response);
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }

        $stmt->close();
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    http_response_code(400); // Bad request
    echo json_encode($response);
}

$conn->close();