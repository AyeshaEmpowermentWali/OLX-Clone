<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $location = sanitizeInput($_POST['location']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($phone) || empty($location)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if username or email is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email is already taken by another user';
        } else {
            // Handle profile image upload
            $profile_image = $user['profile_image'];
            if (!empty($_FILES['profile_image']['name'])) {
                $uploaded_image = uploadImage($_FILES['profile_image'], 'uploads/profiles/');
                if ($uploaded_image) {
                    // Delete old image if it's not the default
                    if ($user['profile_image'] != 'default-avatar.png' && file_exists($user['profile_image'])) {
                        unlink($user['profile_image']);
                    }
                    $profile_image = $uploaded_image;
                }
            }
            
            // Handle password change
            $password_update = '';
            $params = [$username, $email, $phone, $location, $profile_image];
            
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Please enter your current password to change it';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match';
                } else {
                    $password_update = ', password = ?';
                    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            if (empty($error)) {
                // Update user profile
                $sql = "UPDATE users SET username = ?, email = ?, phone = ?, location = ?, profile_image = ?" . $password_update . " WHERE id = ?";
                $params[] = $_SESSION['user_id'];
                
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $_SESSION['username'] = $username;
                    $success = 'Profile updated successfully!';
                    // Refresh user data
                    $user = getCurrentUser();
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        }
    }
}

// Get user's ads count
$stmt = $pdo->prepare("SELECT COUNT(*) as total_ads FROM ads WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$ads_count = $stmt->fetch()['total_ads'];

// Get user's recent ads
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM ads a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_ads = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - OLX Clone</title>
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

        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .profile-avatar {
            text-align: center;
            margin-bottom: 2rem;
        }

        .avatar-container {
            position: relative;
            display: inline-block;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: bold;
        }

        .avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        .avatar-upload:hover {
            background: #5a6fd8;
        }

        .profile-info {
            text-align: center;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .profile-email {
            color: #666;
            margin-bottom: 1rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        .profile-main {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .password-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e1e5e9;
        }

        .recent-ads {
            margin-top: 3rem;
        }

        .ads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .ad-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .ad-card:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .ad-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .ad-price {
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 0.5rem;
        }

        .ad-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }

        .required {
            color: #ff6b6b;
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: static;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .profile-stats {
                grid-template-columns: 1fr;
            }

            .ads-grid {
                grid-template-columns: 1fr;
            }
        }

        .file-input {
            display: none;
        }

        .tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e1e5e9;
        }

        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab:hover {
            color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
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
                <a href="my-ads.php">My Ads</a>
                <a href="messages.php">Messages</a>
                <a href="post-ad.php">Post Ad</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <div class="avatar-container">
                        <?php if ($user['profile_image'] && $user['profile_image'] != 'default-avatar.png' && file_exists($user['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile" class="avatar">
                        <?php else: ?>
                            <div class="avatar">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <button class="avatar-upload" onclick="document.getElementById('profileImageInput').click()">
                            📷
                        </button>
                    </div>
                </div>

                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    <div class="profile-location">📍 <?php echo htmlspecialchars($user['location']); ?></div>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $ads_count; ?></div>
                        <div class="stat-label">Total Ads</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                        <div class="stat-label">Member Since</div>
                    </div>
                </div>
            </div>

            <!-- Profile Main Content -->
            <div class="profile-main">
                <div class="tabs">
                    <div class="tab active" onclick="showTab('profile')">Profile Settings</div>
                    <div class="tab" onclick="showTab('ads')">My Recent Ads</div>
                </div>

                <!-- Profile Settings Tab -->
                <div id="profile-tab" class="tab-content active">
                    <h2 class="section-title">👤 Profile Settings</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="file" id="profileImageInput" name="profile_image" class="file-input" accept="image/*" onchange="previewImage(this)">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input type="text" id="username" name="username" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="phone" name="phone" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="location">Location <span class="required">*</span></label>
                                <input type="text" id="location" name="location" class="form-control" required 
                                       value="<?php echo htmlspecialchars($user['location']); ?>">
                            </div>
                        </div>

                        <div class="password-section">
                            <h3 style="margin-bottom: 1rem; color: #333;">🔒 Change Password</h3>
                            
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="btn">💾 Update Profile</button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">🔄 Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Recent Ads Tab -->
                <div id="ads-tab" class="tab-content">
                    <h2 class="section-title">📝 My Recent Ads</h2>
                    
                    <?php if (empty($recent_ads)): ?>
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                            <h3>No ads posted yet</h3>
                            <p>Start selling by posting your first ad!</p>
                            <a href="post-ad.php" class="btn" style="margin-top: 1rem; display: inline-block; text-decoration: none;">📤 Post Your First Ad</a>
                        </div>
                    <?php else: ?>
                        <div class="ads-grid">
                            <?php foreach ($recent_ads as $ad): ?>
                                <div class="ad-card" onclick="viewAd(<?php echo $ad['id']; ?>)">
                                    <div class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></div>
                                    <div class="ad-price">Rs <?php echo number_format($ad['price']); ?></div>
                                    <div class="ad-meta">
                                        <span><?php echo htmlspecialchars($ad['category_name']); ?></span>
                                        <span><?php echo timeAgo($ad['created_at']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="my-ads.php" class="btn" style="text-decoration: none;">📋 View All My Ads</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatar = document.querySelector('.avatar');
                    if (avatar.tagName === 'IMG') {
                        avatar.src = e.target.result;
                    } else {
                        // Replace div with img
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'avatar';
                        img.alt = 'Profile';
                        avatar.parentNode.replaceChild(img, avatar);
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetForm() {
            if (confirm('Are you sure you want to reset all changes?')) {
                document.getElementById('profileForm').reset();
                location.reload();
            }
        }

        function viewAd(adId) {
            window.location.href = `ad-details.php?id=${adId}`;
        }

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (newPassword && !currentPassword) {
                e.preventDefault();
                alert('Please enter your current password to change it');
                return;
            }
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match');
                return;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long');
                return;
            }
        });

        // Auto-hide success message
        <?php if ($success): ?>
            setTimeout(function() {
                const successAlert = document.querySelector('.alert-success');
                if (successAlert) {
                    successAlert.style.opacity = '0';
                    setTimeout(() => successAlert.remove(), 300);
                }
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
