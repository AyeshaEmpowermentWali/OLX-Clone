<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle logout from all devices
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout_all'])) {
    $user_id = $_SESSION['user_id'];
    
    // Update user's session token to invalidate all sessions
    $new_token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE users SET session_token = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_token, $user_id]);
    
    // Clear all remember me tokens for this user
    try {
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } catch (Exception $e) {
        // Ignore if tokens table doesn't exist
    }
    
    // Destroy current session
    session_destroy();
    
    // Clear cookies
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout All Devices - OLX Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .logout-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .logout-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }

        .logout-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .logout-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .logout-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #856404;
        }

        .warning-box h4 {
            margin-bottom: 0.5rem;
        }

        .warning-box ul {
            text-align: left;
            padding-left: 1.5rem;
        }

        .warning-box li {
            margin-bottom: 0.25rem;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.5rem;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .logout-container {
                padding: 2rem;
                margin: 10px;
            }

            .btn {
                display: block;
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <?php if (isset($success)): ?>
            <div class="logout-icon">✅</div>
            <h1 class="logout-title">Successfully Logged Out</h1>
            <div class="success-message">
                <strong>Security Action Completed!</strong><br>
                You have been logged out from all devices. All active sessions have been terminated.
            </div>
            <a href="index.php" class="btn btn-primary">🏠 Go to Homepage</a>
            <a href="login.php" class="btn btn-primary">🔑 Login Again</a>
        <?php else: ?>
            <div class="logout-icon">🔐</div>
            <h1 class="logout-title">Logout from All Devices</h1>
            <p class="logout-message">
                This will log you out from all devices where you're currently signed in, including mobile apps, tablets, and other computers.
            </p>

            <div class="warning-box">
                <h4>⚠️ This action will:</h4>
                <ul>
                    <li>End all active sessions on all devices</li>
                    <li>Clear all "Remember Me" tokens</li>
                    <li>Require re-login on all devices</li>
                    <li>Enhance your account security</li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="logout_all" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to logout from all devices?')">
                    🚪 Logout from All Devices
                </button>
                <a href="profile.php" class="btn btn-primary">↩️ Back to Profile</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
