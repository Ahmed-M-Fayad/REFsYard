<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("location:index.php");
}

// Validate and regenerate session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    error_log("index.php: Session not active, started new session, session_id=" . session_id());
}

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
error_log("index.php: session_user_id=" . json_encode($_SESSION));

// Include database connection for cart count
require 'db_connect.php';
try {
    $start_time = microtime(true);
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cart_count FROM CART WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = (int)$stmt->fetch(PDO::FETCH_ASSOC)['cart_count'];
    $query_time = microtime(true) - $start_time;
    error_log("index.php: user_id=$user_id, cart_count=$cart_count, cart_query_time={$query_time}s");
} catch (PDOException $e) {
    error_log("index.php: Cart count query error: " . $e->getMessage());
    $cart_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>REFsYard Bookstore</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/style.css?v=57">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>
  <header class="header" role="banner">
    <div class="header-1">
      <a href="home.php" class="logo" aria-label="REFsYard Bookstore Home"><i class="fas fa-book"
          aria-hidden="true"></i>REFsYard</a>
      <form action="display_books.php" method="GET" class="search-form" role="search">
        <input type="search" name="search" id="search-box" placeholder="Search books..." aria-label="Search books">
        <label for="search-box" class="fa-solid fa-magnifying-glass" aria-hidden="true"></label>
      </form>
      <div class="icons" role="toolbar">
        <div id="search-btn" class="fa-solid fa-magnifying-glass"></div>
        <a href="#" id="wishlist-btn" class="fas fa-heart" data-book-id="" aria-label="View wishlist"></a>
        <a href="cart.php" class="fas fa-shopping-cart" id="cart-btn"
          aria-label="View cart with <?php echo $cart_count; ?> items">
          <span class="cart-count" aria-hidden="true"><?php echo $cart_count; ?></span>
        </a>
        <a href="#" class="fas fa-user" id="login-btn" aria-label="Log in or view profile"></a>
        <?php if ($user_id !== 0): ?>
        <a href="logout.php" class="fas fa-sign-out-alt" title="Log Out" aria-label="Log out"
          onclick="return confirm('Are you sure you want to log out?')"></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="header-2">
      <nav class="navbar" role="navigation" aria-label="Main navigation">
        <a href="#home" aria-current="page">home</a>
        <a href="#featured">featured</a>
        <a href="#arrivals">arrivals</a>
        <a href="#reviews">reviews</a>
        <a href="#blogs">blogs</a>
        <a href="display_books.php">books</a>
      </nav>
    </div>
  </header>

  <nav class="bottom-navbar" role="navigation" aria-label="Mobile navigation">
    <a href="#home" class="fas fa-home" aria-label="Home"></a>
    <a href="javascript:void(0)" class="fas fa-list" id="pagesToggle" aria-label="Menu"></a>
    <a href="#arrivals" class="fas fa-tags" aria-label="New arrivals"></a>
    <a href="#reviews" class="fas fa-comments" aria-label="Customer reviews"></a>
    <a href="#blogs" class="fas fa-blog" aria-label="Blogs"></a>
  </nav>

  <!-- Basic Pages Menu -->
  <div class="pages-menu" id="pagesMenu" aria-hidden="true">
    <a href="#home">🏠 Home</a>
    <a href="#featured">⭐ Featured</a>
    <a href="#arrivals">🆕 Arrivals</a>
    <a href="#reviews">💬 Reviews</a>
    <a href="#blogs">📝 Blogs</a>
  </div>


  <section class="home animated-element" id="home" role="region" aria-labelledby="home-heading">
    <div class="row">
      <div class="content">
        <h3 id="home-heading">Up to 75% Off</h3>
        <p>Take advantage of our huge discount on a wide selection of books! Whether you're into the latest bestsellers,
          timeless classics, or niche genres, there's something for everyone.</p>
        <p>From fiction to non-fiction, children's books, self-help, and more, explore our amazing range of titles and
          enjoy substantial discounts.</p>
        <p>Shop today and discover new worlds through books, or pick up that one book you've been dying to read for
          months.</p>
        <a href="display_books.php?promo=featured" class="btn-shop" aria-label="Shop featured books">Shop Now</a>
      </div>
      <div class="swiper books-slider" role="region" aria-label="Featured books slider">
        <div class="swiper-wrapper">
          <?php
                    try {
                        $start_time = microtime(true);
                        $stmt = $pdo->prepare("SELECT book_id, image_url FROM BOOKS ORDER BY book_id DESC LIMIT 6");
                        $stmt->execute();
                        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $query_time = microtime(true) - $start_time;
                        error_log("home_section: Fetched " . count($books) . " books, home_query_time={$query_time}s");
                        foreach ($books as $book) {
                            echo '<a href="book_details.php?book_id=' . htmlspecialchars($book['book_id']) . '" class="swiper-slide" aria-label="View book details">';
                            echo '<img src="' . htmlspecialchars($book['image_url']) . '" alt="Book cover for book ID ' . htmlspecialchars($book['book_id']) . '">';
                            echo '</a>';
                        }
                        if (empty($books)) {
                            error_log("home_section: No books found in BOOKS table");
                            $fallback_images = [
                                'images/new-book1.avif',
                                'images/new-book2.jpg',
                                'images/new-book4.jpg',
                                'images/real-book2.jpeg',
                                'images/real-book4.webp',
                                'images/new-book5.webp'
                            ];
                            foreach ($fallback_images as $index => $image) {
                                echo '<a href="#" class="swiper-slide" aria-label="Placeholder book image ' . ($index + 1) . '"><img src="' . $image . '" alt="Placeholder book cover"></a>';
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("home_section: Error fetching books: " . $e->getMessage());
                        $fallback_images = [
                            'images/new-book1.avif',
                            'images/new-book2.jpg',
                            'images/new-book4.jpg',
                            'images/real-book2.jpeg',
                            'images/real-book4.webp',
                            'images/new-book5.webp'
                        ];
                        foreach ($fallback_images as $index => $image) {
                            echo '<a href="#" class="swiper-slide" aria-label="Placeholder book image ' . ($index + 1) . '"><img src="' . $image . '" alt="Placeholder book cover"></a>';
                        }
                    }
                    ?>
        </div>
      </div>
      <img src="images/new-stand4-removebg-preview.png" class="stand" alt="Decorative book stand" aria-hidden="true">
    </div>
  </section>

  <section class="icons-container animated-element" id="icons" role="region" aria-labelledby="services-heading">
    <h2 id="services-heading" class="sr-only">Our Services</h2>
    <div class="icons">
      <i class="fas fa-plane" aria-hidden="true"></i>
      <div class="content">
        <h3>Free Shipping</h3>
        <p>Order over $100</p>
      </div>
    </div>
    <div class="icons">
      <i class="fas fa-lock" aria-hidden="true"></i>
      <div class="content">
        <h3>Secure Payment</h3>
        <p>100% secure payment</p>
      </div>
    </div>
    <div class="icons">
      <i class="fas fa-redo-alt" aria-hidden="true"></i>
      <div class="content">
        <h3>Easy Returns</h3>
        <p>10 days returns</p>
      </div>
    </div>
    <div class="icons">
      <i class="fas fa-headset" aria-hidden="true"></i>
      <div class="content">
        <h3>24/7 Support</h3>
        <p>Call us anytime</p>
      </div>
    </div>
  </section>

  <section class="featured animated-element" id="featured" role="region" aria-labelledby="featured-heading">
    <h1 class="heading" id="featured-heading"><span>Featured Books</span></h1>
    <div class="swiper featured-slider" role="region" aria-label="Featured books slider">
      <div class="swiper-wrapper" id="featured-books-wrapper"></div>
      <button class="swiper-button-next" aria-label="Next featured book"></button>
      <button class="swiper-button-prev" aria-label="Previous featured book"></button>
    </div>
  </section>

  <section class="newsletter animated-element" role="region" aria-labelledby="newsletter-heading">
    <form id="newsletter-form" method="POST">
      <h3 id="newsletter-heading">Subscribe for Latest Updates</h3>
      <input type="email" name="email" id="newsletter-email" placeholder="Enter your email" required
        aria-label="Email for newsletter subscription">
      <input type="submit" name="subscribe" value="Subscribe" class="btn" aria-label="Subscribe to newsletter">
      <p id="message" style="margin-top: 10px;" aria-live="polite"></p>
    </form>
  </section>

  <?php
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $debug_mode = true;

    try {
        $start_time = microtime(true);
        $stmt = $pdo->prepare("
            SELECT book_id, title, price, image_url, stock
            FROM BOOKS
            ORDER BY book_id DESC
            LIMIT 20
        ");
        $stmt->execute();
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $query_time = microtime(true) - $start_time;
        error_log("new_arrivals: Fetched " . count($books) . " books, arrivals_query_time={$query_time}s");
    } catch (PDOException $e) {
        error_log("new_arrivals: Error fetching books: " . $e->getMessage());
        $books = [];
        if ($debug_mode) {
            $error_message = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }

    $books_top = array_slice($books, 0, 10);
    $books_bottom = array_slice($books, 10, 10);
    ?>

  <section class="arrivals animated-element" id="arrivals" role="region" aria-labelledby="arrivals-heading">
    <h1 class="heading" id="arrivals-heading"><span>New Arrivals</span></h1>
    <?php if (empty($books)): ?>
    <p aria-live="polite">No new arrivals available.
      <?php echo $debug_mode && isset($error_message) ? $error_message : ''; ?></p>
    <?php else: ?>
    <!-- Dummy slider to absorb dynamic fetch -->
    <div class="arrivals-slider" style="display: none;">
      <div class="swiper-wrapper"></div>
    </div>
    <div class="swiper arrivals-slider arrivals-slider-top" role="region" aria-label="New arrivals top slider">
      <div class="swiper-wrapper">
        <?php foreach ($books_bottom as $book): ?>
        <?php
                    $book_id = htmlspecialchars($book['book_id']);
                    $title = htmlspecialchars($book['title']);
                    $price = number_format($book['price'], 2);
                    $image_url = htmlspecialchars($book['image_url'] ?: 'images/new-book2.jpg');
                    $old_price = number_format($book['price'] + 5, 2);
                    ?>
        <div class="swiper-slide box">
          <a href="book_details.php?book_id=<?php echo urlencode($book_id); ?>" class="image"
            aria-label="View details for <?php echo $title; ?>">
            <img src="<?php echo $image_url; ?>" alt="Book cover for <?php echo $title; ?>"
              onerror="this.src='images/new-book2.jpg';">
          </a>
          <div class="content">
            <h3><?php echo $title; ?></h3>
            <div class="price">$<?php echo $price; ?> <span>$<?php echo $old_price; ?></span></div>
            <div class="stars" aria-label="4.5 out of 5 stars">
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star-half-alt" aria-hidden="true"></i>
            </div>
            <?php if ($book['stock'] > 0): ?>
            <a href="#" class="btn add-to-cart arrivals-btn" data-book-id="<?php echo $book_id; ?>"
              data-title="<?php echo $title; ?>" aria-label="Add <?php echo $title; ?> to cart">Add to Cart</a>
            <?php else: ?>
            <p class="out-of-stock" aria-live="polite">Out of Stock</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="swiper arrivals-slider arrivals-slider-bottom" role="region" aria-label="New arrivals bottom slider">
      <div class="swiper-wrapper">
        <?php foreach ($books_bottom as $book): ?>
        <?php
                    $book_id = htmlspecialchars($book['book_id']);
                    $title = htmlspecialchars($book['title']);
                    $price = number_format($book['price'], 2);
                    $image_url = htmlspecialchars($book['image_url'] ?: 'images/new-book2.jpg');
                    $old_price = number_format($book['price'] + 5, 2);
                    ?>
        <div class="swiper-slide box">
          <a href="book_details.php?book_id=<?php echo urlencode($book_id); ?>" class="image"
            aria-label="View details for <?php echo $title; ?>">
            <img src="<?php echo $image_url; ?>" alt="Book cover for <?php echo $title; ?>"
              onerror="this.src='images/new-book2.jpg';">
          </a>
          <div class="content">
            <h3><?php echo $title; ?></h3>
            <div class="price">$<?php echo $price; ?> <span>$<?php echo $old_price; ?></span></div>
            <div class="stars" aria-label="4.5 out of 5 stars">
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star" aria-hidden="true"></i>
              <i class="fas fa-star-half-alt" aria-hidden="true"></i>
            </div>
            <?php if ($book['stock'] > 0): ?>
            <a href="#" class="btn add-to-cart arrivals-btn" data-book-id="<?php echo $book_id; ?>"
              data-title="<?php echo $title; ?>" aria-label="Add <?php echo $title; ?> to cart">Add to Cart</a>
            <?php else: ?>
            <p class="out-of-stock" aria-live="polite">Out of Stock</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const userId = <?php echo json_encode($user_id); ?>;
    console.log('new_arrivals: userId=', userId);

    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.setAttribute('role', 'alert');
      notification.setAttribute('aria-live', 'assertive');
      notification.textContent = message;
      document.body.appendChild(notification);
      setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
      }, 3000);
    }

    document.querySelectorAll('.arrivals .add-to-cart').forEach(button => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const bookId = button.getAttribute('data-book-id');
        console.log('new_arrivals: Adding to cart, book_id:', bookId);

        if (userId === 0) {
          showNotification('Please log in to continue.', 'error');
          setTimeout(() => {
            window.location.href = 'login.html';
          }, 1000);
          return;
        }

        const data = {
          book_id: bookId,
          user_id: userId
        };

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          })
          .then(response => {
            console.log('new_arrivals: add_to_cart.php status:', response.status);
            if (!response.ok) {
              return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
              });
            }
            return response.json();
          })
          .then(data => {
            console.log('new_arrivals: add_to_cart.php response:', data);
            if (data.success) {
              showNotification('Book added to cart!', 'success');
              document.dispatchEvent(new Event('cart-updated'));
              if (data.new_stock === 0) {
                button.style.display = 'none';
                const parent = button.parentElement;
                parent.innerHTML += '<p class="out-of-stock" aria-live="polite">Out of Stock</p>';
              }
            } else {
              showNotification(data.message || 'Error adding to cart.', 'error');
            }
          })
          .catch(error => {
            console.error('new_arrivals: Error adding to cart:', error);
            showNotification('Error adding to cart: ' + error.message, 'error');
          });
      });
    });

    document.getElementById('wishlist-btn').addEventListener('click', (e) => {
      e.preventDefault();
      const bookId = e.currentTarget.getAttribute('data-book-id');
      const isLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;
      console.log('index.php: Wishlist button clicked, bookId=', bookId, 'isLoggedIn=', isLoggedIn);

      if (!isLoggedIn) {
        showNotification('Please log in to view wishlist.', 'error');
        setTimeout(() => window.location.href = 'login.html', 1000);
        return;
      }

      if (bookId) {
        fetch('save_to_wishlist.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              user_id: <?php echo $user_id; ?>,
              book_id: bookId
            })
          })
          .then(response => {
            console.log('index.php: save_to_wishlist.php status:', response.status);
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then(data => {
            console.log('index.php: save_to_wishlist.php response:', data);
            if (data.success) {
              showNotification('Book added to wishlist!', 'success');
            } else {
              showNotification(data.message || 'Failed to add to wishlist.', 'error');
            }
            window.location.href = 'wishlist.php';
          })
          .catch(error => {
            console.error('index.php: Error saving to wishlist:', error);
            showNotification('Error: ' + error.message, 'error');
            window.location.href = 'wishlist.php';
          });
      } else {
        window.location.href = 'wishlist.php';
      }
    });
  });
  </script>

  <section class="deal animated-element" role="region" aria-labelledby="deal-heading">
    <div class="content">
      <h3 id="deal-heading">Deal of the Day</h3>
      <h1>Up to 50% Off</h1>
      <p>Don't miss out on our exclusive deal! Get up to 50% off on bestsellers, new arrivals, and special editions.</p>
      <a href="book_details.php?book_id=2" class="btn" aria-label="Shop deal of the day">Shop Now</a>
    </div>
    <div class="image">
      <img src="images/new-book2.jpg" alt="Book deal promotional image">
    </div>
  </section>

  <section class="reviews animated-element42 animated-element" id="reviews" role="region"
    aria-labelledby="reviews-heading">
    <h1 class="heading" id="reviews-heading"><span>Client's Reviews</span></h1>
    <div class="reviews-slider swiper" role="region" aria-label="Customer reviews slider">
      <div class="swiper-wrapper">
        <div class="swiper-slide box">
          <img src="images/client1.jpg" alt="John Doe">
          <h3>John Doe</h3>
          <p>"I absolutely love the collection of books at this store! The recommendations were spot on."</p>
          <div class="stars" aria-label="4.5 out of 5 stars">
            <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star-half-alt" aria-hidden="true"></i>
          </div>
        </div>
        <div class="swiper-slide box">
          <img src="images/client2.jpg" alt="Charl Lee">
          <h3>Charl Lee</h3>
          <p>"The customer service was amazing! I received my books quickly, and the quality was perfect."</p>
          <div class="stars" aria-label="4.5 out of 5 stars">
            <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star-half-alt" aria-hidden="true"></i>
          </div>
        </div>
        <div class="swiper-slide box">
          <img src="images/client3.jpg" alt="Michael Smith">
          <h3>Michael Smith</h3>
          <p>"A fantastic selection of books for every genre! The website is easy to navigate."</p>
          <div class="stars" aria-label="5 out of 5 stars">
            <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i>
          </div>
        </div>
        <div class="swiper-slide box">
          <img src="images/client4.jpg" alt="Emily Davis">
          <h3>Emily Davis</h3>
          <p>"Great experience buying books here. The checkout process was smooth."</p>
          <div class="stars" aria-label="4.5 out of 5 stars">
            <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star-half-alt" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i>
          </div>
        </div>
        <div class="swiper-slide box">
          <img src="images/client5.jpg" alt="Chris Johnson">
          <h3>Chris Johnson</h3>
          <p>"The variety of books here is amazing. I always find what I need."</p>
          <div class="stars" aria-label="5 out of 5 stars">
            <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star" aria-hidden="true"></i><i
              class="fas fa-star" aria-hidden="true"></i>
          </div>
        </div>
      </div>
      <div class="swiper-pagination" role="navigation" aria-label="Slider pagination"></div>
    </div>
  </section>

  <section class="blogs animated-element" id="blogs" role="region" aria-labelledby="blogs-heading">
    <h1 class="heading" id="blogs-heading"><span>Our Blogs</span></h1>
    <div class="swiper blogs-slider" role="region" aria-label="Blog posts slider">
      <div class="swiper-wrapper">
        <div class="swiper-slide box">
          <div class="image">
            <img src="images/post-img1.jpg" alt="Fantasy books blog post">
          </div>
          <div class="content">
            <h3>Exploring the Best Fantasy Books of 2025</h3>
            <p>Discover the must-read fantasy books of 2025, from epic tales to magical worlds.</p>
            <a href="blogs_pages/blog1.php" class="btn-blog-shop" aria-label="Read more about fantasy books">Read
              more</a>
          </div>
        </div>
        <div class="swiper-slide box">
          <div class="image">
            <img src="images/post-img3.jpg" alt="Bestsellers blog post">
          </div>
          <div class="content">
            <h3>How to Choose Your Next Bestsellers</h3>
            <p>Looking for your next read? Here's how to choose the best books trending in 2025.</p>
            <a href="blogs_pages/blog2.php" class="btn-blog-shop" aria-label="Read more about choosing bestsellers">Read
              more</a>
          </div>
        </div>
        <div class="swiper-slide box">
          <div class="image">
            <img src="images/post-img2.jpg" alt="Creativity books blog post">
          </div>
          <div class="content">
            <h3>5 Books to Inspire Your Creativity</h3>
            <p>These five books will help you unlock new levels of creativity and innovation.</p>
            <a href="blogs_pages/blog3.php" class="btn-blog-shop" aria-label="Read more about creativity books">Read
              more</a>
          </div>
        </div>
        <div class="swiper-slide box">
          <div class="image">
            <img src="images/blog.jpg" alt="Library building blog post">
          </div>
          <div class="content">
            <h3>The Ultimate Guide to Building a Library</h3>
            <p>Learn how to create the perfect reading space with bookshelves, décor, and cozy lighting.</p>
            <a href="blogs_pages/blog4.php" class="btn-blog-shop" aria-label="Read more about building a library">Read
              more</a>
          </div>
        </div>
      </div>
      <button class="swiper-button-next" aria-label="Next blog post"></button>
      <button class="swiper-button-prev" aria-label="Previous blog post"></button>
    </div>
  </section>

  <section class="footer animated-element" id="footer" role="contentinfo">
    <div class="box-container">
      <div class="box">
        <h3>Our Locations</h3>
        <a href="#" aria-label="Cairo, Egypt location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Cairo,
          Egypt</a>
        <a href="#" aria-label="Alexandria location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>
          Alexandria</a>
        <a href="#" aria-label="Giza location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Giza</a>
        <a href="#" aria-label="Mansoura location"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Mansoura</a>
      </div>
      <div class="box">
        <h3>Quick Links</h3>
        <a href="#home" aria-label="Go to Home section"><i class="fas fa-arrow-right" aria-hidden="true"></i> Home</a>
        <a href="#featured" aria-label="Go to Featured section"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Featured</a>
        <a href="#arrivals" aria-label="Go to Arrivals section"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Arrivals</a>
        <a href="#reviews" aria-label="Go to Reviews section"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Reviews</a>
        <a href="#blogs" aria-label="Go to Blogs section"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Blogs</a>
      </div>
      <div class="box">
        <h3>Extra Links</h3>
        <a href="cart.php" aria-label="View ordered items"><i class="fas fa-arrow-right" aria-hidden="true"></i> Ordered
          Items</a>
        <a href="privacy.php" aria-label="View privacy policy"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Privacy Policy</a>
        <a href="payment.php" aria-label="View payment methods"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Payment Methods</a>
        <a href="our_services.php" aria-label="View our services"><i class="fas fa-arrow-right" aria-hidden="true"></i>
          Our Services</a>
      </div>
      <div class="box">
        <h3>Contact Info</h3>
        <a href="tel:01030308938" aria-label="Call 01030308938"><i class="fas fa-phone" aria-hidden="true"></i>
          01030308938</a>
        <a href="tel:01223232697" aria-label="Call 01223232697"><i class="fas fa-phone" aria-hidden="true"></i>
          01223232697</a>
        <a href="mailto:ahmedbahnacy5@gmail.com" aria-label="Email ahmedbahnacy5@gmail.com"><i class="fas fa-envelope"
            aria-hidden="true"></i> ahmedbahnacy5@gmail.com</a>
        <div class="map-container">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3454.560035684958!2d31.235711115117507!3d30.044419081879078!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14583f3f5a7aaf69%3A0x4cf0b7993d2761bd!2z2YXYt9i52YUg2KfZhNi52YTZiNmE2YrYqQ!5e0!3m2!1sar!2seg!4v1714488190000!5m2!1sar!2seg"
            width="100%" height="150" style="border:0; border-radius:10px; margin-top: 10px;" allowfullscreen=""
            loading="lazy" title="Map of Cairo, Egypt location">
          </iframe>
        </div>
      </div>
    </div>
    <div class="share" role="region" aria-label="Social media links">
      <a href="#" class="fab fa-facebook-f" aria-label="Visit our Facebook page"></a>
      <a href="#" class="fab fa-twitter" aria-label="Visit our Twitter page"></a>
      <a href="#" class="fab fa-instagram" aria-label="Visit our Instagram page"></a>
      <a href="#" class="fab fa-linkedin" aria-label="Visit our LinkedIn page"></a>
      <a href="#" class="fab fa-pinterest" aria-label="Visit our Pinterest page"></a>
    </div>
    <div class="credit">
      Created by <span>Ahmed Bahnacy</span> | All rights reserved!
    </div>
  </section>

  <div id="toast" class="toast" role="alert" aria-live="assertive">Book added to cart!</div>

  <script defer src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
  window.userId = <?php echo json_encode($user_id); ?>;
  console.log('index.php: userId=', window.userId, 'sessionId=', '<?php echo session_id(); ?>');

  function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'assertive');
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
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            user_id: userId
          })
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
          cartCount.parentElement.setAttribute('aria-label', `View cart with ${data.cart_count || 0} items`);
          setTimeout(() => cartCount.classList.remove('updated'), 300);
          console.log('Cart count updated:', data.cart_count);
        })
        .catch(error => {
          console.error(`Cart count fetch attempt ${maxRetries - retryCount + 1} failed:`, error);
          if (retryCount > 0) {
            setTimeout(() => attemptFetch(retryCount - 1), retryDelay);
          } else {
            cartCount.textContent = initialCount;
            cartCount.parentElement.setAttribute('aria-label', `View cart with ${initialCount} items`);
            showNotification('Failed to update cart count. Using last known value.', 'error');
          }
        });
    }

    attemptFetch(maxRetries);
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    document.addEventListener('cart-updated', updateCartCount);

    document.getElementById('cart-btn').addEventListener('click', (e) => {
      const isLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;
      if (!isLoggedIn) {
        e.preventDefault();
        window.location.href = 'login.html';
      }
    });

    document.getElementById('login-btn').addEventListener('click', (e) => {
      e.preventDefault();
      const isLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;
      if (isLoggedIn) {
        showNotification('You are already logged in!', 'success');
      } else {
        showNotification('Please log in to continue.', 'error');
        setTimeout(() => {
          window.location.href = 'login.html';
        }, 1000);
      }
    });


  });
  </script>
  <script defer src="js/main.js?v=61"></script>
</body>

</html>