<?php
require_once 'db.php';

$searchQuery = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$condition = isset($_GET['condition']) ? sanitizeInput($_GET['condition']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';

// Build search query
$sql = "
    SELECT a.*, c.name as category_name, u.username, u.location as user_location 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    JOIN users u ON a.user_id = u.id 
    WHERE a.status = 'active'
";

$params = [];

if ($searchQuery) {
    $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($categoryId) {
    $sql .= " AND a.category_id = ?";
    $params[] = $categoryId;
}

if ($minPrice > 0) {
    $sql .= " AND a.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $sql .= " AND a.price <= ?";
    $params[] = $maxPrice;
}

if ($condition) {
    $sql .= " AND a.condition_type = ?";
    $params[] = $condition;
}

if ($location) {
    $sql .= " AND a.location LIKE ?";
    $params[] = "%$location%";
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - OLX Clone</title>
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
            flex-wrap: wrap;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .search-form {
            display: flex;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            margin: 0 20px;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: none;
            outline: none;
        }

        .search-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            transition: opacity 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .search-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .search-results-count {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .filter-control {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .filter-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .filter-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .filter-btn:hover {
            background: #5a6fd8;
        }

        .results-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
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

        .ad-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .ad-location {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .ad-time {
            color: #999;
            font-size: 0.8rem;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .no-results p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #5a6fd8;
        }

        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                gap: 1rem;
            }

            .search-form {
                margin: 0;
                max-width: none;
                width: 100%;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .ads-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .search-header {
                padding: 1.5rem;
            }
        }

        .clear-filters {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .clear-filters:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">🛒 OLX Clone</a>
            <form class="search-form" action="search.php" method="GET">
                <input type="text" name="q" class="search-input" 
                       placeholder="Search for products..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
            <div class="nav-links">
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
        <!-- Search Header -->
        <div class="search-header">
            <h1 class="search-title">
                <?php if ($searchQuery): ?>
                    Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                <?php elseif ($categoryId): ?>
                    <?php
                    $categoryName = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $categoryId) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }
                    ?>
                    <?php echo htmlspecialchars($categoryName); ?> Category
                <?php else: ?>
                    All Ads
                <?php endif; ?>
            </h1>
            <div class="search-results-count">
                Found <?php echo count($ads); ?> result<?php echo count($ads) != 1 ? 's' : ''; ?>
            </div>

            <!-- Filters -->
            <form method="GET" action="search.php">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <div class="filters">
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" class="filter-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Min Price</label>
                        <input type="number" name="min_price" class="filter-control" 
                               placeholder="0" min="0" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label>Max Price</label>
                        <input type="number" name="max_price" class="filter-control" 
                               placeholder="Any" min="0" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                    </div>

                    <div class="filter-group">
                        <label>Condition</label>
                        <select name="condition" class="filter-control">
                            <option value="">Any Condition</option>
                            <option value="new" <?php echo $condition == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="used" <?php echo $condition == 'used' ? 'selected' : ''; ?>>Used</option>
                            <option value="refurbished" <?php echo $condition == 'refurbished' ? 'selected' : ''; ?>>Refurbished</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Location</label>
                        <input type="text" name="location" class="filter-control" 
                               placeholder="Any location" value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                </div>
                <button type="submit" class="filter-btn">Apply Filters</button>
                <button type="button" class="clear-filters" onclick="clearFilters()">Clear All</button>
            </form>
        </div>

        <!-- Results -->
        <div class="results-container">
            <?php if (empty($ads)): ?>
                <div class="no-results">
                    <h3>🔍 No results found</h3>
                    <p>Try adjusting your search criteria or browse all categories</p>
                    <a href="index.php" class="btn-primary">Browse All Ads</a>
                </div>
            <?php else: ?>
                <div class="ads-grid">
                    <?php foreach ($ads as $ad): ?>
                        <div class="ad-card" onclick="viewAd(<?php echo $ad['id']; ?>)">
                            <?php 
                            $images = json_decode($ad['images'], true);
                            $firstImage = !empty($images) ? $images[0] : '';
                            ?>
                            <?php if ($firstImage): ?>
                                <img src="<?php echo htmlspecialchars($firstImage); ?>" 
                                     alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
                            <?php else: ?>
                                <div class="ad-image" style="display: flex; align-items: center; justify-content: center; color: #999;">
                                    📷 No Image
                                </div>
                            <?php endif; ?>
                            <div class="ad-content">
                                <h3 class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></h3>
                                <div class="ad-price">Rs <?php echo number_format($ad['price']); ?></div>
                                <div class="ad-details">
                                    <span class="ad-location">📍 <?php echo htmlspecialchars($ad['location']); ?></span>
                                    <span class="ad-condition"><?php echo ucfirst($ad['condition_type']); ?></span>
                                </div>
                                <div class="ad-time"><?php echo timeAgo($ad['created_at']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewAd(adId) {
            window.location.href = `ad-details.php?id=${adId}`;
        }

        function clearFilters() {
            const form = document.querySelector('form[action="search.php"]');
            const inputs = form.querySelectorAll('input[type="number"], input[type="text"], select');
            inputs.forEach(input => {
                if (input.name !== 'q') {
                    input.value = '';
                }
            });
            form.submit();
        }

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
                }, index * 50);
            });
        });
    </script>
</body>
</html>
