<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();

// Handle logout confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_logout'])) {
    // Log the logout activity (optional)
    try {
        $stmt = $pdo->prepare("INSERT INTO user_activity (user_id, activity_type, activity_data, created_at) VALUES (?, 'logout', ?, NOW())");
        $stmt->execute([$_SESSION['user_id'], json_encode(['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']])]);
    } catch (Exception $e) {
        // Ignore if activity table doesn't exist
    }
    
    // Redirect to main logout
    header('Location: logout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout - OLX Clone</title>
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

        .confirm-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .confirm-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }

        .confirm-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #ffc107;
        }

        .confirm-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .confirm-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: left;
        }

        .user-info h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .user-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .user-detail strong {
            color: #667eea;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 117, 125, 0.3);
        }

        @media (max-width: 768px) {
            .confirm-container {
                padding: 2rem;
                margin: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-icon">⚠️</div>
        <h1 class="confirm-title">Confirm Logout</h1>
        <p class="confirm-message">
            Are you sure you want to log out from your account? You will need to enter your credentials again to access your account.
        </p>

        <div class="user-info">
            <h4>Current Session:</h4>
            <div class="user-detail">
                <span>Username:</span>
                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
            </div>
            <div class="user-detail">
                <span>Email:</span>
                <strong><?php echo htmlspecialchars($user['email']); ?></strong>
            </div>
            <div class="user-detail">
                <span>Last Login:</span>
                <strong><?php echo date('M d, Y H:i', strtotime($user['updated_at'])); ?></strong>
            </div>
        </div>

        <form method="POST" style="display: inline;">
            <div class="action-buttons">
                <button type="submit" name="confirm_logout" class="btn btn-danger">
                    🚪 Yes, Logout
                </button>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    ↩️ Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
