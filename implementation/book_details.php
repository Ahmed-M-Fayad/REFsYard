<?php
session_start();
require 'db_connect.php';

// Validate and regenerate session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    error_log("book_details.php: Session not active, started new session, session_id=" . session_id());
}
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
error_log("book_details.php: session_id=" . session_id() . ", user_id=$user_id");

// Fetch cart count
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cart_count FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
    error_log("book_details.php: user_id=$user_id, cart_count=$cart_count");
} catch (PDOException $e) {
    error_log("book_details.php: Cart count error: " . $e->getMessage());
    $cart_count = 0;
}

// Get book_id and fetch book details
$book_id = isset($_GET['book_id']) ? trim($_GET['book_id']) : '';
if (!$book_id) {
    error_log("book_details.php: No book_id provided");
    header("Location: home.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT book_id, title, author, price, stock, image_url FROM BOOKS WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$book) {
        error_log("book_details.php: Book not found, book_id=$book_id");
        header("Location: home.php?error=Book not found");
        exit;
    }
    error_log("book_details.php: Fetched book_id=$book_id, title=" . $book['title']);
} catch (PDOException $e) {
    error_log("book_details.php: Error fetching book: " . $e->getMessage());
    header("Location: home.php?error=Database error");
    exit;
}

// Check if book is already in wishlist
$is_in_wishlist = false;
if ($user_id !== 0) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM WISHLIST WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        $is_in_wishlist = $stmt->fetchColumn() > 0;
        error_log("book_details.php: Wishlist check, user_id=$user_id, book_id=$book_id, in_wishlist=" . ($is_in_wishlist ? 'true' : 'false'));
    } catch (PDOException $e) {
        error_log("book_details.php: Error checking wishlist: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - REFsYard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="../css/style.css?v=20">
    <script>
        const userId = <?php echo json_encode($user_id); ?>;
        console.log('book_details.php: userId=', userId, ', book_id=<?php echo json_encode($book_id); ?>');
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f4f0;
            height:auto;
        }
        
        .icons .fa-shopping-cart {
            position: relative;
            font-size: 20px;
            color: #2c6e49;
        }
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #2c6e49;
            color: #fff;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .cart-count.updated {
            animation: pulse 0.3s ease;
        }
        .book-details {
            max-width: 1200px;
            margin: 20rem auto;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
            align-items: flex-start;
            opacity: 0;
            animation: fadeIn 0.5s ease-in forwards;
        }
        .book-details .book-image img {
            max-width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .book-details .book-image img:hover {
            transform: scale(1.05);
        }
        .book-details .book-info {
            flex: 1;
            padding: 20px;
        }
        .book-details h1 {
            font-size: 28px;
            color: #2c6e49;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .book-details p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }
        .book-details .price {
            font-size: 20px;
            color: #2c6e49;
            font-weight: 600;
            margin: 15px 0;
        }
        .book-details .stock {
            font-size: 14px;
            color: #666;
        }
        .book-details .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2c6e49, #8ac926);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .book-details .btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .book-details .btn.disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: -300px;
            padding: 15px 20px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 400;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease forwards;
        }
        .notification.success {
            background: #1a3c5e;
        }
        .notification.error {
            background: #ff6f61;
        }
        .book-details .btn.save-to-wishlist {
            background: #ff758f;
            margin-left: 10px;
        }
        .book-details .btn.save-to-wishlist:hover {
            background: #e63958;
        }
        .book-details .btn.save-to-wishlist.active {
            background: #e63958;
            cursor: default;
        }
        .book-details .btn.save-to-wishlist.disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { right: -300px; }
            to { right: 20px; }
        }
        @keyframes slideOut {
            from { right: 20px; opacity: 1; }
            to { right: -300px; opacity: 0; }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        @media (max-width: 768px) {
            .header-2 .navbar a {
                margin: 10px 0;
                display: inline-block;
            }
            .book-details {
                flex-direction: column;
                align-items: center;
                margin: 20px;
                padding: 15px;
            }
            .book-details .book-image img {
                height: 300px;
            }
            .book-details h1 {
                font-size: 24px;
                text-align: center;
            }
            .book-details .book-info {
                padding: 10px;
                text-align: center;
            }
            .book-details .btn {
                width: 100%;
                box-sizing: border-box;
            }
            .cart-count {
                padding: 2px 6px;
                font-size: 10px;
                min-width: 16px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-1">
            <a href="home.php" class="logo"><i class="fas fa-book"></i>REFsYard</a>
            <form action="display_books.php" method="GET" class="search-form">
                <input type="search" name="search" id="search-box" placeholder="search here...">
                <label for="search-box" class="fas fa-search"></label>
            </form>
            <div class="icons">
                <div id="search-btn" class="fas fa-search"></div>
                <a href="#" id="wishlist-btn" class="fas fa-heart" data-book-id=""></a>
                <a href="cart.php" class="fas fa-shopping-cart">
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </a>
                <div id="login-btn" class="fas fa-user"></div>
                <?php if ($user_id !== 0): ?>
                    <a href="logout.php" class="fas fa-sign-out-alt" title="Log Out" onclick="return confirm('Are you sure you want to log out?')"></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-2">
            <nav class="navbar">
                <a href="home.php#home">home</a>
                <a href="home.php#featured">featured</a>
                <a href="home.php#arrivals">arrivals</a>
                <a href="home.php#reviews">reviews</a>
                <a href="home.php#blogs">blogs</a>
                <a href="display_books.php">books</a>
            </nav>
        </div>
    </header>

    <section class="book-details">
        <div class="book-image">
            <img src="<?php echo htmlspecialchars($book['image_url'] ?: '../images/new-book2.jpg'); ?>" alt="Book Cover" onerror="this.src='../images/new-book2.jpg';">
        </div>
        <div class="book-info">
            <h1><?php echo htmlspecialchars($book['title']); ?></h1>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p class="price">$<?php echo number_format($book['price'], 2); ?></p>
            <p class="stock">Stock: <?php echo $book['stock']; ?></p>
            <a href="#" class="btn add-to-cart <?php echo $book['stock'] == 0 ? 'disabled' : ''; ?>" data-book-id="<?php echo htmlspecialchars($book['book_id']); ?>">
                <?php echo $book['stock'] == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
            </a>
            <a href="#" class="btn save-to-wishlist <?php echo $is_in_wishlist ? 'active disabled' : ''; ?>" data-book-id="<?php echo htmlspecialchars($book['book_id']); ?>" data-title="<?php echo htmlspecialchars($book['title']); ?>">
                <i class="fas fa-heart"></i> <?php echo $is_in_wishlist ? 'In Wishlist' : 'Save to Wishlist'; ?>
            </a>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="../js/main.js?v=26"></script>
    <script>
        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Update cart count
        function updateCartCount() {
            fetch('get_cart_count.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                const cartCount = document.querySelector('.cart-count');
                cartCount.textContent = data.cart_count || 0;
                cartCount.classList.add('updated');
                setTimeout(() => cartCount.classList.remove('updated'), 300);
                console.log('book_details.php: Cart count updated:', data.cart_count);
            })
            .catch(error => {
                console.error('book_details.php: Error updating cart count:', error);
                showNotification('Error updating cart count.', 'error');
            });
        }

        // Add to cart
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (button.classList.contains('disabled')) {
                    showNotification('This book is out of stock.', 'error');
                    return;
                }
                const bookId = button.getAttribute('data-book-id');
                const data = {
                    book_id: bookId,
                    user_id: userId
                };

                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    console.log('book_details.php: add_to_cart.php status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                        });
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        button.innerHTML = data.new_stock === 0 ? 'Out of Stock' : 'Add to Cart';
                        if (data.success) {
                            showNotification('Book added to cart!', 'success');
                            updateCartCount();
                            const stockElement = document.querySelector('.stock');
                            if (stockElement) {
                                stockElement.textContent = `Stock: ${data.new_stock}`;
                            }
                            if (data.new_stock === 0) {
                                button.classList.add('disabled');
                            }
                        } else {
                            showNotification(data.message || 'Error adding to cart.', 'error');
                        }
                    } catch (e) {
                        console.error('book_details.php: JSON parse error:', e, 'Raw response:', text);
                        showNotification('Error adding to cart: Invalid server response.', 'error');
                    }
                })
                .catch(error => {
                    console.error('book_details.php: Error adding to cart:', error);
                    button.innerHTML = 'Add to Cart';
                    if (error.message.includes('403')) {
                        showNotification('Please log in to add items to cart.', 'error');
                        setTimeout(() => window.location.href = 'login.html', 1000);
                    } else {
                        showNotification('Error adding to cart: ' + error.message, 'error');
                    }
                });
            });
        });

        // Redirect to login.html on user icon click
        document.getElementById('login-btn').addEventListener('click', () => {
            window.location.href = 'login.html';
        });
    </script>
</body>
</html>