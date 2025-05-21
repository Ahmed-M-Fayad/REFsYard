<?php
session_start();
require 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id === 0) {
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT w.book_id, b.title, b.author, b.price, b.image_url
        FROM WISHLIST w
        JOIN BOOKS b ON w.book_id = b.book_id
        WHERE w.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("wishlist.php: user_id=$user_id, wishlist_count=" . count($wishlist));
} catch (PDOException $e) {
    error_log("wishlist.php: Wishlist fetch error: " . $e->getMessage());
    $wishlist = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - REFsYard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=15">
    <style>
        :root {
            --primary-color: #6C5CE7;
            --secondary-color: #00CEC9;
            --background-color: #F7FAFC;
            --card-bg: #FFFFFF;
            --text-color: #2D3748;
            --muted-text: #718096;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            padding: 40px 20px;
            margin: 0 auto;
        }

        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
            gap: 15px;
        }

        .wishlist-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
        }

        .btn-back {
            background: var(--primary-color);
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-back:hover {
            background: #5A4BCA;
            transform: translateY(-2px);
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
        }

        .wishlist-card {
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .wishlist-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .wishlist-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid #E2E8F0;
        }

        .card-body {
            padding: 20px;
        }

        .card-body h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-color);
            margin: 0 0 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-body p {
            font-size: 0.9rem;
            color: var(--muted-text);
            margin: 0 0 8px;
        }

        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: 12px;
        }

        .btn-view, .btn-remove {
            flex: 1;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-view {
            background: var(--secondary-color);
            color: #fff;
        }

        .btn-view:hover {
            background: #00A8A3;
            transform: translateY(-2px);
        }

        .btn-remove {
            background: #E53E3E;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .btn-remove:hover {
            background: #C53030;
            transform: translateY(-2px);
        }

        .empty-wishlist {
            text-align: center;
            padding: 80px 0;
            color: var(--muted-text);
            font-size: 1.25rem;
        }

        .empty-wishlist i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 16px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #fff;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: opacity 0.3s ease;
        }

        .notification.success {
            background: #38A169;
        }

        .notification.error {
            background: #E53E3E;
        }

        .spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            width: 40px;
            height: 40px;
            border: 4px solid #E2E8F0;
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @media (max-width: 768px) {
            .wishlist-header h2 {
                font-size: 2rem;
            }

            .wishlist-grid {
                grid-template-columns: 1fr;
            }

            .wishlist-card img {
                height: 200px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px 15px;
            }

            .card-actions {
                flex-direction: column;
                gap: 8px;
            }

            .btn-view, .btn-remove {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="wishlist-header">
            <h2>Your Wishlist</h2>
            <a href="home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Books</a>
        </div>
        <?php if (empty($wishlist)): ?>
            <div class="empty-wishlist">
                <i class="fas fa-heart-broken"></i>
                <p>Your wishlist is empty. Start adding your favorite books!</p>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist as $item): ?>
                    <div class="wishlist-card">
                        <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'images/new-book2.jpg'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" onerror="this.src='images/new-book2.jpg';">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p>Author: <?php echo htmlspecialchars($item['author']); ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <div class="card-actions">
                                <a href="book_details.php?book_id=<?php echo urlencode($item['book_id']); ?>" class="btn-view">View Book</a>
                                <button class="btn-remove" data-book-id="<?php echo htmlspecialchars($item['book_id']); ?>" data-title="<?php echo htmlspecialchars($item['title']); ?>">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="spinner"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const userId = <?php echo json_encode($user_id); ?>;
        console.log('wishlist.php: userId=', userId);

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

        function showSpinner(show) {
            const spinner = document.querySelector('.spinner');
            spinner.style.display = show ? 'block' : 'none';
        }

        // Remove from wishlist
        document.querySelectorAll('.btn-remove').forEach(button => {
            button.addEventListener('click', (e) => {
                const bookId = button.getAttribute('data-book-id');
                const bookTitle = button.getAttribute('data-title');
                console.log('wishlist.php: Removing from wishlist, book_id:', bookId);

                if (!confirm(`Are you sure you want to remove "${bookTitle}" from your wishlist?`)) {
                    return;
                }

                showSpinner(true);
                fetch('remove_from_wishlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: userId, book_id: bookId })
                })
                .then(response => {
                    console.log('wishlist.php: remove_from_wishlist.php status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('wishlist.php: remove_from_wishlist.php response:', data);
                    showSpinner(false);
                    if (data.success) {
                        showNotification(`"${bookTitle}" removed from wishlist!`, 'success');
                        button.closest('.wishlist-card').remove();
                        if (!document.querySelector('.wishlist-card')) {
                            const grid = document.querySelector('.wishlist-grid');
                            grid.innerHTML = `
                                <div class="empty-wishlist">
                                    <i class="fas fa-heart-broken"></i>
                                    <p>Your wishlist is empty. Start adding your favorite books!</p>
                                </div>
                            `;
                        }
                    } else {
                        showNotification(data.message || 'Error removing from wishlist.', 'error');
                    }
                })
                .catch(error => {
                    console.error('wishlist.php: Error removing from wishlist:', error);
                    showSpinner(false);
                    showNotification('Error removing from wishlist: ' + error.message, 'error');
                });
            });
        });
    });
    </script>
</body>
</html>