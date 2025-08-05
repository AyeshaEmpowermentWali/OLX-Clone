<?php
require_once 'db.php';

// Get featured and recent ads
$featuredAds = $pdo->query("
    SELECT a.*, c.name as category_name, u.username, u.location as user_location 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.status = 'active' AND a.featured = 1 
    ORDER BY a.created_at DESC 
    LIMIT 8
")->fetchAll();

$recentAds = $pdo->query("
    SELECT a.*, c.name as category_name, u.username, u.location as user_location 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.status = 'active' 
    ORDER BY a.created_at DESC 
    LIMIT 12
")->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Clone - Buy & Sell Everything</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .btn-post {
            background: #ff6b6b;
            color: white !important;
            padding: 10px 20px !important;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255,107,107,0.3);
        }

        .btn-post:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,107,107,0.4);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-form {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            font-size: 1rem;
            outline: none;
        }

        .search-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 15px 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .search-btn:hover {
            background: #ff5252;
        }

        /* Categories Section */
        .categories {
            padding: 4rem 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
            font-weight: 700;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }

        .category-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            border-color: #667eea;
        }

        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .category-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        /* Ads Section */
        .ads-section {
            padding: 4rem 0;
            background: #f8f9fa;
        }

        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .ad-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .ad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .ad-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
        }

        .ad-content {
            padding: 1.5rem;
        }

        .ad-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ad-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 0.5rem;
        }

        .ad-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .ad-time {
            color: #999;
            font-size: 0.8rem;
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 3rem 0 1rem;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #ff6b6b;
        }

        .footer-section a {
            color: #ccc;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ff6b6b;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 1rem;
            color: #999;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .search-form {
                flex-direction: column;
                border-radius: 15px;
            }

            .search-btn {
                border-radius: 0 0 15px 15px;
            }

            .nav {
                flex-direction: column;
                gap: 1rem;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .ads-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .ad-card {
            position: relative;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">
                    🛒 OLX Clone
                </a>
                <div class="nav-links">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php">My Profile</a>
                        <a href="my-ads.php">My Ads</a>
                        <a href="messages.php">Messages</a>
                        <a href="post-ad.php" class="btn-post">+ Post Ad</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="signup.php">Sign Up</a>
                        <a href="login.php" class="btn-post">+ Post Ad</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Find Everything You Need</h1>
            <p>Buy and sell with confidence on Pakistan's largest marketplace</p>
            <div class="search-container">
                <form class="search-form" action="search.php" method="GET">
                    <input type="text" name="q" class="search-input" placeholder="Search for products, brands and more...">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Browse Categories</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card" onclick="searchByCategory(<?php echo $category['id']; ?>)">
                        <div class="category-icon"><?php echo $category['icon']; ?></div>
                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Ads -->
    <?php if (!empty($featuredAds)): ?>
    <section class="ads-section">
        <div class="container">
            <h2 class="section-title">Featured Ads</h2>
            <div class="ads-grid">
                <?php foreach ($featuredAds as $ad): ?>
                    <div class="ad-card" onclick="viewAd(<?php echo $ad['id']; ?>)">
                        <div class="featured-badge">Featured</div>
                        <?php 
                        $images = json_decode($ad['images'], true);
                        $firstImage = !empty($images) ? $images[0] : '';
                        ?>
                        <?php if ($firstImage): ?>
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
                        <?php else: ?>
                            <div class="ad-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                📷 No Image
                            </div>
                        <?php endif; ?>
                        <div class="ad-content">
                            <h3 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <div class="ad-price">Rs <?php echo number_format($ad['price']); ?></div>
                            <div class="ad-location">📍 <?php echo htmlspecialchars($ad['location']); ?></div>
                            <div class="ad-time"><?php echo timeAgo($ad['created_at']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recent Ads -->
    <section class="ads-section">
        <div class="container">
            <h2 class="section-title">Recent Ads</h2>
            <div class="ads-grid">
                <?php foreach ($recentAds as $ad): ?>
                    <div class="ad-card" onclick="viewAd(<?php echo $ad['id']; ?>)">
                        <?php 
                        $images = json_decode($ad['images'], true);
                        $firstImage = !empty($images) ? $images[0] : '';
                        ?>
                        <?php if ($firstImage): ?>
                            <img src="<?php echo htmlspecialchars($firstImage); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
                        <?php else: ?>
                            <div class="ad-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                📷 No Image
                            </div>
                        <?php endif; ?>
                        <div class="ad-content">
                            <h3 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                            <div class="ad-price">Rs <?php echo number_format($ad['price']); ?></div>
                            <div class="ad-location">📍 <?php echo htmlspecialchars($ad['location']); ?></div>
                            <div class="ad-time"><?php echo timeAgo($ad['created_at']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About OLX Clone</h3>
                    <a href="#">About Us</a>
                    <a href="#">Careers</a>
                    <a href="#">Contact</a>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <a href="#">Help Center</a>
                    <a href="#">Safety Tips</a>
                    <a href="#">Terms of Use</a>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <a href="#">Facebook</a>
                    <a href="#">Twitter</a>
                    <a href="#">Instagram</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 OLX Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function searchByCategory(categoryId) {
            window.location.href = `search.php?category=${categoryId}`;
        }

        function viewAd(adId) {
            window.location.href = `ad-details.php?id=${adId}`;
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add loading animation for ad cards
        document.addEventListener('DOMContentLoaded', function() {
            const adCards = document.querySelectorAll('.ad-card');
            adCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
