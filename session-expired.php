<?php
// Handle expired sessions
session_start();

// Clear any remaining session data
$_SESSION = array();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - OLX Clone</title>
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

        .expired-container {
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

        .expired-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }

        .expired-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #ffc107;
        }

        .expired-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .expired-message {
            color: #666;
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #0066cc;
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
            margin: 0 0.5rem 0.5rem 0;
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

        @media (max-width: 768px) {
            .expired-container {
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
    <div class="expired-container">
        <div class="expired-icon">⏰</div>
        <h1 class="expired-title">Session Expired</h1>
        <p class="expired-message">
            Your session has expired for security reasons. Please log in again to continue using OLX Clone.
        </p>

        <div class="info-box">
            <strong>💡 Why did this happen?</strong><br>
            Sessions expire automatically after a period of inactivity to protect your account from unauthorized access.
        </div>

        <a href="login.php" class="btn btn-primary">🔑 Login Again</a>
        <a href="index.php" class="btn btn-secondary">🏠 Go to Homepage</a>
    </div>
</body>
</html>
