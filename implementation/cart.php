<?php
session_start();
require 'db_connect.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id === 0) {
    header("Location: login.html");
    exit;
}

// Fetch cart count
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cart_count FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
    error_log("cart.php: user_id=$user_id, cart_count=$cart_count");
} catch (PDOException $e) {
    error_log("cart.php: Cart count error: " . $e->getMessage());
    $cart_count = 0;
}

// Fetch cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.book_id, c.quantity, b.title, b.author, b.price, b.image_url
        FROM CART c
        JOIN BOOKS b ON c.book_id = b.book_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    error_log("cart.php: Cart items error: " . $e->getMessage());
    $cart_items = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REFsYard - Cart</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fa;
            color: #333;
        }
        .cart-container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 10rem auto;
        }
        .cart-container h1 {
            text-align: center;
            font-size: 32px;
            color: #1a3c5e;
            margin-bottom: 40px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .cart-table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .cart-table thead {
            background: linear-gradient(135deg, #1a3c5e, #2a5d8f);
        }
        .cart-table th {
            padding: 16px;
            text-align: center;
            font-size: 15px;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cart-table tbody tr {
            transition: background 0.2s ease;
        }
        .cart-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        .cart-table tbody tr:hover {
            background: #f1f4f6;
        }
        .cart-table td {
            padding: 16px;
            text-align: center;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #e5e7eb;
        }
        .cart-table img {
            width: 50px;
            height: 75px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .cart-table .remove-btn {
            background: linear-gradient(135deg, #ff6f61, #ff8a80);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .cart-table .remove-btn:hover {
            background: linear-gradient(135deg, #ff8a80, #ff6f61);
            transform: scale(1.05);
        }
        .cart-total {
            text-align: right;
            font-size: 18px;
            color: #1a3c5e;
            font-weight: 600;
            margin-top: 24px;
            padding-right: 20px;
        }
        .checkout-btn {
            display: block;
            width: 220px;
            margin: 30px auto;
            padding: 14px;
            background: linear-gradient(135deg, #1a3c5e, #2a5d8f);
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        .checkout-btn:hover {
            background: linear-gradient(135deg, #2a5d8f, #1a3c5e);
            box-shadow: 0 4px 12px rgba(26, 60, 94, 0.3);
        }
        .empty-cart {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 40px;
            font-weight: 400;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            font-weight: 400;
            z-index: 1000;
            transition: opacity 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .notification.success {
            background: #1a3c5e;
        }
        .notification.error {
            background: #ff6f61;
        }
        .cart-count.updated {
            animation: pulse 0.3s;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-container {
                padding: 20px 10px;
            }
            .cart-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .cart-table thead {
                display: none;
            }
            .cart-table tbody tr {
                display: block;
                margin-bottom: 20px;
                border-bottom: 2px solid #e5e7eb;
                padding: 15px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            }
            .cart-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 15px;
                text-align: left;
                border-bottom: none;
            }
            .cart-table tbody td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #1a3c5e;
                width: 40%;
                flex-shrink: 0;
            }
            .cart-table img {
                width: 40px;
                height: 60px;
            }
            .cart-table .remove-btn {
                width: 100%;
                padding: 10px;
            }
            .cart-total {
                text-align: center;
                padding-right: 0;
            }
            .checkout-btn {
                width: 100%;
                max-width: 300px;
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
                <a href="wishlist.php" id="wishlist-btn" class="fas fa-heart" data-book-id=""></a>
                <a href="cart.php" class="fas fa-shopping-cart" id="cart-btn">
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                </a>
                <div id="login-btn" class="fas fa-user"></div>
                <a href="logout.php" class="fas fa-sign-out-alt" title="Log Out" onclick="return confirm('Are you sure you want to log out?')"></a>
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

    <div class="cart-container">
        <h1>Your Cart</h1>
        <?php if (empty($cart_items)): ?>
            <p class="empty-cart">Your cart is empty.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td data-label="Image">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?: '../images/new-book2.jpg'); ?>" alt="Book Cover" onerror="this.src='../images/new-book2.jpg';">
                            </td>
                            <td data-label="Title"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td data-label="Author"><?php echo htmlspecialchars($item['author']); ?></td>
                            <td data-label="Price">$<?php echo number_format($item['price'], 2); ?></td>
                            <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                            <td data-label="Total">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td data-label="Action">
                                <button class="remove-btn" data-cart-id="<?php echo $item['cart_id']; ?>" data-book-id="<?php echo $item['book_id']; ?>" data-quantity="<?php echo $item['quantity']; ?>">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-total">
                Total: $<?php echo number_format($total, 2); ?>
            </div>
            <a href="payment.php" class="checkout-btn">Proceed to Checkout</a>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        function updateCartCount(maxRetries = 3, retryDelay = 1000) {
            const cartCount = document.querySelector('.cart-count');
            if (!cartCount) {
                console.error('Cart count element not found');
                return;
            }

            const userId = <?php echo $user_id; ?>;
            const initialCount = <?php echo $cart_count; ?>;

            function attemptFetch(retryCount) {
                fetch('get_cart_count.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    cartCount.textContent = data.cart_count || 0;
                    cartCount.classList.add('updated');
                    setTimeout(() => cartCount.classList.remove('updated'), 300);
                    console.log('Cart count updated:', data.cart_count);
                })
                .catch(error => {
                    console.error(`Cart count fetch attempt ${maxRetries - retryCount + 1} failed:`, error);
                    if (retryCount > 0) {
                        setTimeout(() => attemptFetch(retryCount - 1), retryDelay);
                    } else {
                        cartCount.textContent = initialCount;
                        showNotification('Failed to update cart count. Using last known value.', 'error');
                    }
                });
            }

            attemptFetch(maxRetries);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateCartCount();

            // Handle remove from cart
            document.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const cartId = button.getAttribute('data-cart-id');
                    const bookId = button.getAttribute('data-book-id');
                    const quantity = parseInt(button.getAttribute('data-quantity'));

                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ cart_id: cartId, book_id: bookId, quantity: quantity })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Item removed from cart.', 'success');
                            button.closest('tr').remove();
                            updateCartCount();
                            document.dispatchEvent(new Event('cart-updated')); // Sync with index.php
                            const totalElement = document.querySelector('.cart-total');
                            totalElement.textContent = `Total: $${parseFloat(data.new_total).toFixed(2)}`;
                            if (!document.querySelector('.cart-table tbody tr')) {
                                document.querySelector('.cart-table').remove();
                                document.querySelector('.cart-total').remove();
                                document.querySelector('.checkout-btn').remove();
                                document.querySelector('.cart-container').innerHTML += '<p class="empty-cart">Your cart is empty.</p>';
                            }
                        } else {
                            showNotification(data.message || 'Error removing item.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error removing item:', error);
                        showNotification('Error removing item.', 'error');
                    });
                });
            });

            // Handle user icon click
            document.getElementById('login-btn').addEventListener('click', () => {
                showNotification('You are already logged in!', 'success');
            });

            // Handle search button
            document.getElementById('search-btn').addEventListener('click', () => {
                document.querySelector('.search-form').classList.toggle('active');
            });
        });
    </script>
    <script src="../js/main.js?v=6"></script>
</body>
</html>