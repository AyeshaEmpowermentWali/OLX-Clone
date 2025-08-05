<?php
require_once 'db.php';

$adId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$adId) {
    header('Location: index.php');
    exit;
}

// Get ad details
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, u.username, u.phone as user_phone, u.location as user_location, u.created_at as user_joined
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.id = ? AND a.status = 'active'
");
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad) {
    header('Location: index.php');
    exit;
}

// Update view count
$pdo->prepare("UPDATE ads SET views = views + 1 WHERE id = ?")->execute([$adId]);

// Get related ads
$relatedAds = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.category_id = ? AND a.id != ? AND a.status = 'active' 
    ORDER BY a.created_at DESC 
    LIMIT 4
");
$relatedAds->execute([$ad['category_id'], $adId]);
$relatedAds = $relatedAds->fetchAll();

$images = json_decode($ad['images'], true) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - OLX Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: opacity 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .ad-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .ad-main {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .image-gallery {
            position: relative;
            height: 400px;
            background: #f0f0f0;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-nav {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .image-thumb.active {
            border-color: #667eea;
        }

        .image-thumb:hover {
            transform: scale(1.1);
        }

        .ad-content {
            padding: 2rem;
        }

        .ad-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }

        .ad-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 1.5rem;
        }

        .ad-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .meta-item {
            text-align: center;
        }

        .meta-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .meta-value {
            font-weight: 600;
            color: #333;
        }

        .ad-description {
            margin-bottom: 2rem;
        }

        .ad-description h3 {
            margin-bottom: 1rem;
            color: #333;
        }

        .ad-description p {
            color: #666;
            line-height: 1.8;
            white-space: pre-wrap;
        }

        .seller-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .seller-info {
            text-align: center;
            margin-bottom: 2rem;
        }

        .seller-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1rem;
        }

        .seller-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .seller-joined {
            color: #666;
            font-size: 0.9rem;
        }

        .contact-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .related-ads {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .related-content {
            padding: 1rem;
        }

        .related-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .related-price {
            font-weight: bold;
            color: #ff6b6b;
        }

        @media (max-width: 768px) {
            .ad-container {
                grid-template-columns: 1fr;
            }

            .seller-card {
                position: static;
            }

            .ad-title {
                font-size: 1.5rem;
            }

            .ad-price {
                font-size: 2rem;
            }

            .ad-meta {
                grid-template-columns: 1fr 1fr;
            }

            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        .breadcrumb {
            margin-bottom: 2rem;
            color: #666;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #999;
            background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
        }

        .safety-tips {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .safety-tips h4 {
            color: #856404;
            margin-bottom: 0.5rem;
        }

        .safety-tips ul {
            color: #856404;
            font-size: 0.9rem;
            padding-left: 1.5rem;
        }

        .safety-tips li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">🛒 OLX Clone</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="search.php">Search</a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php">Profile</a>
                    <a href="post-ad.php">Post Ad</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> > 
            <a href="search.php?category=<?php echo $ad['category_id']; ?>"><?php echo htmlspecialchars($ad['category_name']); ?></a> > 
            <?php echo htmlspecialchars($ad['title']); ?>
        </div>

        <div class="ad-container">
            <!-- Main Ad Content -->
            <div class="ad-main">
                <!-- Image Gallery -->
                <div class="image-gallery">
                    <?php if (!empty($images)): ?>
                        <img src="<?php echo htmlspecialchars($images[0]); ?>" 
                             alt="<?php echo htmlspecialchars($ad['title']); ?>" 
                             class="main-image" id="mainImage">
                        
                        <?php if (count($images) > 1): ?>
                            <div class="image-nav">
                                <?php foreach ($images as $index => $image): ?>
                                    <img src="<?php echo htmlspecialchars($image); ?>" 
                                         alt="Image <?php echo $index + 1; ?>" 
                                         class="image-thumb <?php echo $index === 0 ? 'active' : ''; ?>"
                                         onclick="changeImage('<?php echo htmlspecialchars($image); ?>', this)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="main-image no-image">📷 No Image Available</div>
                    <?php endif; ?>
                </div>

                <!-- Ad Content -->
                <div class="ad-content">
                    <h1 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h1>
                    <div class="ad-price">Rs <?php echo number_format($ad['price']); ?></div>

                    <!-- Ad Meta -->
                    <div class="ad-meta">
                        <div class="meta-item">
                            <div class="meta-label">Category</div>
                            <div class="meta-value"><?php echo htmlspecialchars($ad['category_name']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Condition</div>
                            <div class="meta-value"><?php echo ucfirst($ad['condition_type']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Location</div>
                            <div class="meta-value"><?php echo htmlspecialchars($ad['location']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Views</div>
                            <div class="meta-value"><?php echo number_format($ad['views']); ?></div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="ad-description">
                        <h3>📝 Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Seller Card -->
            <div class="seller-card">
                <div class="seller-info">
                    <div class="seller-avatar">
                        <?php echo strtoupper(substr($ad['username'], 0, 1)); ?>
                    </div>
                    <div class="seller-name"><?php echo htmlspecialchars($ad['username']); ?></div>
                    <div class="seller-joined">Member since <?php echo date('M Y', strtotime($ad['user_joined'])); ?></div>
                </div>

                <div class="contact-buttons">
                    <a href="tel:<?php echo htmlspecialchars($ad['phone']); ?>" class="btn btn-success">
                        📞 Call <?php echo htmlspecialchars($ad['phone']); ?>
                    </a>
                    
                    <?php if (isLoggedIn() && $_SESSION['user_id'] != $ad['user_id']): ?>
                        <a href="chat.php?ad_id=<?php echo $ad['id']; ?>&seller_id=<?php echo $ad['user_id']; ?>" class="btn btn-primary">
                            💬 Send Message
                        </a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php?redirect=ad-details.php?id=<?php echo $ad['id']; ?>" class="btn btn-primary">
                            💬 Login to Message
                        </a>
                    <?php endif; ?>
                    
                    <button class="btn btn-outline" onclick="shareAd()">
                        📤 Share Ad
                    </button>
                </div>

                <!-- Safety Tips -->
                <div class="safety-tips">
                    <h4>🛡️ Safety Tips</h4>
                    <ul>
                        <li>Meet in a public place</li>
                        <li>Check the item before paying</li>
                        <li>Don't pay in advance</li>
                        <li>Report suspicious activity</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Ads -->
        <?php if (!empty($relatedAds)): ?>
            <div class="related-ads">
                <h2 class="section-title">Related Ads</h2>
                <div class="related-grid">
                    <?php foreach ($relatedAds as $relatedAd): ?>
                        <div class="related-card" onclick="viewAd(<?php echo $relatedAd['id']; ?>)">
                            <?php 
                            $relatedImages = json_decode($relatedAd['images'], true);
                            $firstImage = !empty($relatedImages) ? $relatedImages[0] : '';
                            ?>
                            <?php if ($firstImage): ?>
                                <img src="<?php echo htmlspecialchars($firstImage); ?>" 
                                     alt="<?php echo htmlspecialchars($relatedAd['title']); ?>" 
                                     class="related-image">
                            <?php else: ?>
                                <div class="related-image no-image">📷</div>
                            <?php endif; ?>
                            <div class="related-content">
                                <div class="related-title"><?php echo htmlspecialchars($relatedAd['title']); ?></div>
                                <div class="related-price">Rs <?php echo number_format($relatedAd['price']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function changeImage(src, thumb) {
            document.getElementById('mainImage').src = src;
            
            // Update active thumbnail
            document.querySelectorAll('.image-thumb').forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        }

        function viewAd(adId) {
            window.location.href = `ad-details.php?id=${adId}`;
        }

        function shareAd() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo htmlspecialchars($ad['title']); ?>',
                    text: 'Check out this item on OLX Clone',
                    url: window.location.href
                });
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        }

        // Add smooth scrolling for related ads
        document.addEventListener('DOMContentLoaded', function() {
            const relatedCards = document.querySelectorAll('.related-card');
            relatedCards.forEach((card, index) => {
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
