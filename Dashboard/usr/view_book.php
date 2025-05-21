<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "bookstore_db";
$conn = new mysqli($db_server, $db_user, $db_password, $db_name);

if (!isset($_GET['id'])) {
  echo "No book ID provided.";
  exit;
}

$book_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("s", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "Book not found.";
  exit;
}

$book = $result->fetch_assoc();

function time_elapsed_string($datetime, $full = false) {
  $timezone = new DateTimeZone('Africa/Cairo');
  $ago = new DateTime($datetime, $timezone);
  $now = new DateTime('now', $timezone);
  $diff = $now->diff($ago);

  $string = [
      'y' => 'year',
      'm' => 'month',
      'd' => 'day',
      'h' => 'hour',
      'i' => 'minute',
      's' => 'second',
  ];

  foreach ($string as $k => &$v) {
      if ($diff->$k) {
          $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
          unset($string[$k]);
      }
  }

  if (!$full) $string = array_slice($string, 0, 1);

  return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  >
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" 
    rel="stylesheet"
  >
  <style>
    body {
      background-color: #f5f7fa;
      color: #333;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
      line-height: 1.7;
    }
    .book-card {
      background-color: #ffffff;
      border: 1px solid #e0e0e0;
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .book-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }
    .book-img {
      max-width: 100%;
      height: auto;
      border-radius: 0.5rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .highlight {
      color: #007bff;
    }
    .description {
      
      line-height: 1.8;
      color: #555;
      background-color: #f8f9fa;
      padding: 1.25rem;
      border-radius: 0.5rem;
      margin-top: 1.5rem;
      border: 1px solid #e9ecef;
    }
    .btn-custom {
      background-color: #28a745;
      color: #fff;
      border: none;
    }
    .btn-custom:hover {
      background-color: #218838;
    }
    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
      color: #fff;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
      border-color: #545b62;
    }
    .btn-warning {
      background-color: #007bff;
      border-color: #007bff;
      color: #fff;
    }
    .btn-warning:hover {
      background-color: #0056b3;
      border-color: #004085;
    }
    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
      color: #fff;
    }
    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }
    .text-muted {
      color: #6c757d !important;
    }
    strong {
      color: #222;
    }
    h2 {
      font-size: 2rem;
      font-weight: 600;
    }
    h3 {
      font-size: 1.75rem;
      font-weight: 500;
    }
    p {
      font-size: 1.1rem;
      margin-bottom: 0.75rem;
    }
    .container {
      max-width: 960px;
    }
  </style>
</head>
<body class="p-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold highlight">📘 Book Details</h2>
      <p class="text-muted">Explore full information about the book</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8">
        <div class="card book-card p-4">
          <div class="row g-4 align-items-center">
            <div class="col-md-4 text-center">
              <img src="<?= $book['image_url'] ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="book-img">
            </div>
            <div class="col-md-8">
              <h3 class="mb-3"><?= htmlspecialchars($book['title']) ?></h3>
              <p><strong>Price:</strong> $<?= number_format($book['price'], 2) ?></p>
              <p><strong>Stock:</strong> <?= $book['stock'] ?></p>
              <p><strong>Added:</strong> <?= time_elapsed_string($book['added_at']) ?></p>
              <div class="description">
                <strong>Description:</strong><br>
                <?= $book['description'] ? nl2br(htmlspecialchars($book['description'])) : 'No description available.' ?>
              </div>
              <div class="mt-4">
                <a href="display_books.php" class="btn btn-custom"><i class="bi bi-arrow-left"></i> Back</a>
                <a href="edit_book.php?id=<?= $book['book_id'] ?>" class="btn btn-warning ms-2"><i class="bi bi-pencil"></i> Edit</a>
                <a href="delete_book.php?id=<?= $book['book_id'] ?>" class="btn btn-danger ms-2"><i class="bi bi-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>