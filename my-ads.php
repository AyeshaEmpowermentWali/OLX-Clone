<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=my-ads.php');
    exit;
}

$user = getCurrentUser();

// Handle ad status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $ad_id = intval($_POST['ad_id']);
    $action = $_POST['action'];
    
    // Verify ad belongs to current user
    $stmt = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        switch ($action) {
            case 'mark_sold':
                $pdo->prepare("UPDATE ads SET status = 'sold' WHERE id = ?")->execute([$ad_id]);
                $success = 'Ad marked as sold successfully!';
                break;
            case 'mark_active':
                $pdo->prepare("UPDATE ads SET status = 'active' WHERE id = ?")->execute([$ad_id]);
                $success = 'Ad marked as active successfully!';
                break;
            case 'delete':
                $pdo->prepare("UPDATE ads SET status = 'inactive' WHERE id = ?")->execute([$ad_id]);
                $success = 'Ad deleted successfully!';
                break;
        }
    }
}

// Get user's ads with filters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "
    SELECT a.*, c.name as category_name 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.user_id = ?
";

$params = [$_SESSION['user_id']];

if ($status_filter != 'all') {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$my_ads = $stmt->fetchAll();

// Get ads statistics
$stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
        SUM(views) as total_views
    FROM ads WHERE user_id = ?
");
$stats->execute([$_SESSION['user_id']]);
$stats = $stats->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads - OLX Clone</title>
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

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .filter-control {
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .filter-control:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9rem;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-warning:hover {
            background: #e0a800;
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .ad-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
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

        .ad-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background: #28a745;
            color: white;
        }

        .status-sold {
            background: #6c757d;
            color: white;
        }

        .status-inactive {
            background: #dc3545;
            color: white;
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

        .ad-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .ad-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .no-ads {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-ads h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .no-ads p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .ads-grid {
                grid-template-columns: 1fr;
            }

            .ad-actions {
                justify-content: center;
            }
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
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
                <a href="profile.php">Profile</a>
                <a href="messages.php">Messages</a>
                <a href="post-ad.php">Post Ad</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">📋 My Ads</h1>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Ads</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['active']; ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['sold']; ?></div>
                    <div class="stat-label">Sold</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_views']); ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="my-ads.php">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Ads</label>
                        <input type="text" name="search" class="filter-control" 
                               placeholder="Search your ads..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Status Filter</label>
                        <select name="status" class="filter-control">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Ads</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="sold" <?php echo $status_filter == 'sold' ? 'selected' : ''; ?>>Sold</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn">🔍 Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Ads Grid -->
        <?php if (empty($my_ads)): ?>
            <div class="no-ads">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                <h3>No ads found</h3>
                <p>
                    <?php if ($status_filter != 'all' || $search): ?>
                        Try adjusting your filters or search terms.
                    <?php else: ?>
                        You haven't posted any ads yet. Start selling today!
                    <?php endif; ?>
                </p>
                <a href="post-ad.php" class="btn">📤 Post Your First Ad</a>
            </div>
        <?php else: ?>
            <div class="ads-grid">
                <?php foreach ($my_ads as $ad): ?>
                    <div class="ad-card">
                        <div class="ad-status status-<?php echo $ad['status']; ?>">
                            <?php echo ucfirst($ad['status']); ?>
                        </div>
                        
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
                            
                            <div class="ad-meta">
                                <span><?php echo htmlspecialchars($ad['category_name']); ?></span>
                                <span><?php echo $ad['views']; ?> views</span>
                            </div>
                            
                            <div class="ad-meta">
                                <span>📍 <?php echo htmlspecialchars($ad['location']); ?></span>
                                <span><?php echo timeAgo($ad['created_at']); ?></span>
                            </div>
                            
                            <div class="ad-actions">
                                <a href="ad-details.php?id=<?php echo $ad['id']; ?>" class="btn btn-sm">👁️ View</a>
                                
                                <?php if ($ad['status'] == 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <input type="hidden" name="action" value="mark_sold">
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('Mark this ad as sold?')">✅ Mark Sold</button>
                                    </form>
                                <?php elseif ($ad['status'] == 'sold'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <input type="hidden" name="action" value="mark_active">
                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                onclick="return confirm('Mark this ad as active again?')">🔄 Reactivate</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($ad['status'] != 'inactive'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this ad?')">🗑️ Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-hide success message
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 3000);
    </script>
</body>
</html>
