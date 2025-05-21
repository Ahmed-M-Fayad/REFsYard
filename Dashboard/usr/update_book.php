<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "bookstore_db";

$conn = new mysqli($db_server, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$old_id = $conn->real_escape_string($_POST["old_id"]); 
$new_id = $conn->real_escape_string($_POST["id"]);
$title = $conn->real_escape_string($_POST["title"]);
$price = $conn->real_escape_string($_POST["price"]);
$description = $conn->real_escape_string($_POST["description"]);
$stock = $conn->real_escape_string($_POST["stock"]);
$author = $conn->real_escape_string($_POST["author"]);
$featured = $conn->real_escape_string($_POST["featured"]);

// Update book without image first
$update_books = "UPDATE books 
    SET book_id='$new_id', title='$title', price='$price',
        description='$description', stock='$stock',
        author='$author', is_featured='$featured', added_at=NOW()
    WHERE book_id='$old_id'";
$conn->query($update_books);

// --- Handle file upload ---
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $image_name = basename($_FILES['image_file']['name']);
    $target_dir = "images/";
    $target_file = $target_dir . $image_name;

    // Move file to images/ folder
    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_file)) {
        // Save image path in database
        $escaped_img = $conn->real_escape_string($target_file);
        $update_img = "UPDATE books SET image_url = '$escaped_img' WHERE book_id = '$new_id'";
        $conn->query($update_img);
    }
}

header("Location: display_books.php");
exit;
?>
