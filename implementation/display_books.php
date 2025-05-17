<?php
session_start();
require 'db_connect.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id === 0) {
    header("Location: login.php");
    exit;
}

// Fetch cart count
try {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cart_count FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
    error_log("display_books.php: user_id=$user_id, cart_count=$cart_count");
} catch (PDOException $e) {
    error_log("display_books.php: Cart count error: " . $e->getMessage());
    $cart_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REFsYard Bookstore</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>

      body{
        height:auto
      }
        /* Book Container Styling */
        .book-container {
            padding: 40px;
            background: #f0f4f0;
            min-height: 100vh;
        }
        .book-container h1 {
            text-align: center;
            font-size: 36px;
            color: #2c6e49;
            margin-bottom: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Filter Form Styling */
        .filter-form {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 5rem auto 30px;
            max-width: 1000px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: center;
        }
        .filter-form label {
            font-size: 16px;
            color: #2c6e49;
            margin-right: 10px;
        }
        .filter-form input[type="number"],
        .filter-form select {
            padding: 8px;
            border: 1px solid #2c6e49;
            border-radius: 5px;
            font-size: 14px;
            width: 120px;
        }
        .filter-form input[type="checkbox"] {
            margin-left: 10px;
        }
        .filter-form button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #2c6e49, #8ac926);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .filter-form button:hover {
            background: linear-gradient(135deg, #8ac926, #2c6e49);
        }

        /* Books List Styling (Grid Layout) */
        #books-list {
            display: grid;
            grid-template-columns: repeat(4, minmax(300px, 1fr));
            gap: 20px;
            justify-items: center;
            max-width: 1400px;
            margin: 0 auto;
            min-height: 50vh;
            position: relative;
        }
        #books-list h1 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 28px;
            color: #1a3c5e;
            font-weight: 500;
            text-align: center;
            margin: 0;
            width: 100%;
        }

        /* Creative Book Card Styling with Fixed Height */
        .book-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 300px;
            height: 450px;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .book-card img {
            cursor: pointer;
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .book-card .content {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
        }
        .book-card h2 {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
            font-weight: 600;
            height: 50px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .book-card p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .book-card .price {
            font-size: 16px;
            color: #2c6e49;
            font-weight: 600;
        }
        .book-card .stock {
            font-size: 14px;
            color: #666;
        }
        .book-card a.add-to-cart {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #2c6e49, #8ac926);
            color: #fff;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 10px;
            font-weight: 500;
            transition: background 0.3s ease;
            align-self: center;
        }
        .book-card a.add-to-cart:hover {
            background: linear-gradient(135deg, #8ac926, #2c6e49);
        }

        /* Cart Icon Styling */
        .icons .fa-shopping-cart {
            position: relative;
        }
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #2c6e49;
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .cart-count.updated {
            animation: pulse 0.3s;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Notification Styling */
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            #books-list {
                grid-template-columns: repeat(3, minmax(300px, 1fr));
            }
        }
        @media (max-width: 900px) {
            #books-list {
                grid-template-columns: repeat(2, minmax(300px, 1fr));
            }
        }
        @media (max-width: 600px) {
            #books-list {
                grid-template-columns: 1fr;
            }
            .book-card {
                width: 100%;
                height: auto;
            }
            .filter-form {
                flex-direction: column;
                gap: 10px;
            }
            .filter-form input[type="number"],
            .filter-form select {
                width: 100%;
            }
            .header-1, .header-2 {
                flex-direction: column;
                padding: 10px;
            }
            .search-form {
                margin: 10px 0;
                width: 100%;
            }
            .search-form input {
                width: 100%;
            }
            .navbar a {
                margin: 10px 0;
            }
            #books-list h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-1">
            <a href="home.php" class="logo"><i class="fas fa-book"></i>REFsYard</a>
            <form action="#" method="GET" class="search-form" id="search-form">
                <input type="search" name="search" id="search-box" placeholder="search here..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <label for="search-box" class="fas fa-search"></label>
            </form>
            <div class="icons">
                <div id="search-btn" class="fas fa-search"></div>
                <a href="#" id="wishlist-btn" class="fas fa-heart" data-book-id=""></a>
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

    <?php
    // Function to fetch all unique authors for the dropdown
    function getAuthors($pdo) {
        $sql = "SELECT DISTINCT author FROM BOOKS ORDER BY author";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Function to fetch books (with search and filter options)
    function getAllBooks($pdo, $search = '', $min_price = null, $max_price = null, $in_stock = false, $author = '') {
        $sql = "SELECT book_id, title, author, price, stock, image_url FROM BOOKS WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (title LIKE :search OR author LIKE :search)";
            $params['search'] = "%$search%";
        }

        if (!is_null($min_price) && $min_price !== '') {
            $sql .= " AND price >= :min_price";
            $params['min_price'] = $min_price;
        }
        if (!is_null($max_price) && $max_price !== '') {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    try {
        // Get initial filter parameters from the URL
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
        $in_stock = isset($_GET['in_stock']) ? true : false;
        $author = isset($_GET['author']) ? trim($_GET['author']) : '';

        // Fetch authors for the dropdown
        $authors = getAuthors($pdo);

        // Fetch initial books
        $books = getAllBooks($pdo, $search, $min_price, $max_price, $in_stock, $author);
    ?>

    <div class="book-container">
        <h1>All Books</h1>
        <div class="filter-form" id="filter-form">
            <div>
                <label for="min_price">Min Price:</label>
                <input type="number" name="min_price" id="min_price" value="<?php echo htmlspecialchars($min_price ?? ''); ?>" step="0.01" min="0">
            </div>
            <div>
                <label for="max_price">Max Price:</label>
                <input type="number" name="max_price" id="max_price" value="<?php echo htmlspecialchars($max_price ?? ''); ?>" step="0.01" min="0">
            </div>
            <div>
                <label for="author">Author:</label>
                <select name="author" id="author">
                    <option value="">All Authors</option>
                    <?php foreach ($authors as $auth): ?>
                        <option value="<?php echo htmlspecialchars($auth); ?>" <?php echo $author === $auth ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($auth); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="in_stock">In Stock Only:</label>
                <input type="checkbox" name="in_stock" id="in_stock" <?php echo $in_stock ? 'checked' : ''; ?>>
            </div>
            <div>
                <button type="button" id="apply-filters">Apply Filters</button>
            </div>
        </div>

        <div id="books-list">
            <?php if (empty($books)): ?>
                <h1>No books found.</h1>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <a href="book_details.php?book_id=<?php echo htmlspecialchars($book['book_id']); ?>">
                            <?php if (!empty($book['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="Book Cover" onerror="this.src='../images/new-book2.jpg';">
                            <?php else: ?>
                                <img src="../images/new-book2.jpg" alt="Book Cover">
                            <?php endif; ?>
                        </a>
                        <div class="content">
                            <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                            <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                            <p class="price">$<?php echo number_format($book['price'], 2); ?></p>
                            <p class="stock">Stock: <?php echo $book['stock']; ?></p>
                            <a href="#" class="add-to-cart" data-book-id="<?php echo htmlspecialchars($book['book_id']); ?>">Add to Cart</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php
    } catch (PDOException $e) {
        echo "<p>Error fetching books: " . $e->getMessage() . "</p>";
    }
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Show notification
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

// Function to update cart count
function updateCartCount(maxRetries = 3, retryDelay = 1000) {
    const cartCount = document.querySelector('.cart-count');
    if (!cartCount) {
        console.error('Cart count element not found');
        return;
    }

    const userId = <?php echo json_encode($user_id); ?>;
    const initialCount = <?php echo json_encode($cart_count); ?>;
    console.log('updateCartCount: Starting with userId=', userId, 'initialCount=', initialCount);

    function attemptFetch(retryCount) {
        fetch('get_cart_count.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => {
            console.log('get_cart_count.php response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('get_cart_count.php response:', data);
            const newCount = data.cart_count || 0;
            cartCount.textContent = newCount;
            cartCount.classList.add('updated');
            setTimeout(() => cartCount.classList.remove('updated'), 300);
            console.log('Cart count updated:', newCount);
            if (data.error) {
                showNotification('Cart count error: ' + data.error, 'error');
            }
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

// Function to fetch books via AJAX
function fetchBooks() {
    const booksList = document.getElementById('books-list');
    booksList.innerHTML = '<p>Loading...</p>';

    const search = document.getElementById('search-box').value;
    const minPrice = document.getElementById('min_price').value;
    const maxPrice = document.getElementById('max_price').value;
    const inStock = document.getElementById('in_stock').checked;
    const author = document.getElementById('author').value;

    const data = {
        search: search,
        min_price: minPrice,
        max_price: maxPrice,
        in_stock: inStock,
        author: author
    };

    fetch('fetch_books.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(books => {
        booksList.innerHTML = '';
        if (books.length === 0) {
            booksList.innerHTML = '<h1>No books found.</h1>';
        } else {
            books.forEach(book => {
                const bookCard = document.createElement('div');
                bookCard.className = 'book-card';
                bookCard.innerHTML = `
                    <a href="book_details.php?book_id=${encodeURIComponent(book.book_id)}">
                        <img src="${book.image_url || '../images/new-book2.jpg'}" alt="Book Cover" onerror="this.src='../images/new-book2.jpg';">
                    </a>
                    <div class="content">
                        <h2>${book.title}</h2>
                        <p>Author: ${book.author}</p>
                        <p class="price">$${Number(book.price).toFixed(2)}</p>
                        <p class="stock">Stock: ${book.stock}</p>
                        <a href="#" class="add-to-cart" data-book-id="${encodeURIComponent(book.book_id)}">Add to Cart</a>
                    </div>
                `;
                booksList.appendChild(bookCard);
            });

            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const bookId = button.getAttribute('data-book-id');
                    console.log('Adding to cart, book_id:', bookId);
                    addToCart(bookId, button);
                });
            });
        }
    })
    .catch(error => {
        console.error('Error fetching books:', error);
        booksList.innerHTML = '<p>Error loading books. Please try again.</p>';
    });
}

// Function to handle add to cart
function addToCart(bookId, button) {
    const data = {
        book_id: decodeURIComponent(bookId),
        user_id: <?php echo json_encode($user_id); ?>
    };
    console.log('addToCart: Sending data:', data);

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('add_to_cart.php response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('add_to_cart.php response:', data);
        if (data.success) {
            showNotification('Book added to cart!', 'success');
            updateCartCount();
            document.dispatchEvent(new Event('cart-updated'));
            const stockElement = button.parentElement.querySelector('.stock');
            if (stockElement) {
                stockElement.textContent = `Stock: ${data.new_stock}`;
            }
            if (data.new_stock === 0) {
                button.style.display = 'none';
            }
        } else {
            showNotification(data.message || 'Error adding to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showNotification('Error adding to cart: ' + error.message, 'error');
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded: userId=', <?php echo json_encode($user_id); ?>, 'sessionId=', '<?php echo session_id(); ?>');
    updateCartCount();

    document.getElementById('apply-filters').addEventListener('click', fetchBooks);
    document.getElementById('search-form').addEventListener('submit', (e) => {
        e.preventDefault();
        fetchBooks();
    });
    document.getElementById('search-btn').addEventListener('click', () => {
        fetchBooks();
    });
    document.getElementById('min_price').addEventListener('input', fetchBooks);
    document.getElementById('max_price').addEventListener('input', fetchBooks);
    document.getElementById('in_stock').addEventListener('change', fetchBooks);
    document.getElementById('author').addEventListener('change', fetchBooks);
    document.getElementById('search-box').addEventListener('input', fetchBooks);

    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const bookId = button.getAttribute('data-book-id');
            console.log('Adding to cart, book_id:', bookId);
            addToCart(bookId, button);
        });
    });

    document.getElementById('login-btn').addEventListener('click', () => {
        showNotification('You are already logged in!', 'success');
    });
});
</script>
    <script src="../js/main.js?v=6"></script>
</body>
</html>