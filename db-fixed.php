<?php
// Database configuration - Your actual credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'ugrj543f7lree');
define('DB_PASS', 'cgmq43woifko');
define('DB_NAME', 'dbvbbmibbbboo2');

// Create connection with better error handling
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Detailed error for debugging
    die("
    <div style='font-family: Arial; padding: 20px; background: #ffe6e6; border: 1px solid #ff9999; border-radius: 5px; margin: 20px;'>
        <h3 style='color: #cc0000;'>❌ Database Connection Failed</h3>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p><strong>Database:</strong> " . DB_NAME . "</p>
        <p><strong>Username:</strong> " . DB_USER . "</p>
        <p><strong>Host:</strong> " . DB_HOST . "</p>
        <hr>
        <h4>🔧 Quick Fixes:</h4>
        <ol>
            <li>Make sure database '<strong>" . DB_NAME . "</strong>' exists</li>
            <li>Verify user '<strong>" . DB_USER . "</strong>' has access to this database</li>
            <li>Check if MySQL server is running</li>
            <li>Confirm the password is correct</li>
        </ol>
        <p><a href='test-connection.php' style='background: #007cba; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>Test Connection</a></p>
    </div>
    ");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $directory = 'uploads/') {
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

// Database connection test function
function testDatabaseConnection() {
    try {
        $test_pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $test_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $result = $test_pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
        $row = $result->fetch();
        
        return [
            'success' => true,
            'message' => 'Connection successful',
            'tables' => $row['table_count']
        ];
    } catch(PDOException $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}
?>
