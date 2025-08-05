<?php
// Start session
session_start();

// Store user info for goodbye message (optional)
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Destroy all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect with a goodbye message
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - OLX Clone</title>
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
            animation: wave 2s ease-in-out infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }

        .logout-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .logout-message {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .username {
            color: #667eea;
            font-weight: bold;
        }

        .redirect-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            color: #666;
            font-size: 0.9rem;
        }

        .countdown {
            font-weight: bold;
            color: #667eea;
            font-size: 1.2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
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

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }

        .security-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            color: #856404;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .logout-container {
                padding: 2rem;
                margin: 10px;
            }

            .logout-title {
                font-size: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">👋</div>
        <h1 class="logout-title">Goodbye, <span class="username"><?php echo htmlspecialchars($username); ?></span>!</h1>
        <p class="logout-message">
            You have been successfully logged out from your OLX Clone account. 
            Thank you for using our platform!
        </p>

        <div class="redirect-info">
            <div class="loading-spinner"></div>
            Redirecting you to homepage in <span class="countdown" id="countdown">5</span> seconds...
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">🏠 Go to Homepage</a>
            <a href="login.php" class="btn btn-success">🔑 Login Again</a>
            <a href="signup.php" class="btn btn-secondary">📝 Create Account</a>
        </div>

        <div class="security-note">
            <strong>🛡️ Security Tip:</strong> For your security, make sure to close your browser if you're using a shared computer.
        </div>
    </div>

    <script>
        // Countdown timer
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = '<?php echo htmlspecialchars($redirect_url); ?>';
            }
        }, 1000);

        // Allow user to cancel redirect by clicking anywhere
        document.addEventListener('click', () => {
            clearInterval(timer);
            document.querySelector('.redirect-info').innerHTML = 
                '<span style="color: #28a745;">✅ Auto-redirect cancelled. You can stay on this page.</span>';
        });

        // Clear any cached data
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => {
                    caches.delete(name);
                });
            });
        }

        // Clear localStorage related to user session
        try {
            localStorage.removeItem('user_preferences');
            localStorage.removeItem('cart_items');
            sessionStorage.clear();
        } catch (e) {
            // Handle browsers with disabled storage
        }
    </script>
</body>
</html>
