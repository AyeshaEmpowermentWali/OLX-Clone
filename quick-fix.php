<?php
// Quick fix script to update all files with correct credentials
echo "<h2>🔧 Quick Fix - Update Database Credentials</h2>";
echo "<hr>";

$files_to_update = [
    'db.php',
    'index.php',
    'login.php',
    'signup.php',
    'post-ad.php',
    'search.php',
    'ad-details.php'
];

$old_credentials = [
    "define('DB_USER', 'root');",
    "define('DB_PASS', '');",
    "define('DB_NAME', 'olx_clone');"
];

$new_credentials = [
    "define('DB_USER', 'ugrj543f7lree');",
    "define('DB_PASS', 'cgmq43woifko');",
    "define('DB_NAME', 'dbvbbmibbbboo2');"
];

echo "<h3>Updating files with correct credentials...</h3>";

foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Replace old credentials with new ones
        for ($i = 0; $i < count($old_credentials); $i++) {
            $content = str_replace($old_credentials[$i], $new_credentials[$i], $content);
        }
        
        // Also replace any hardcoded 'root' references
        $content = str_replace("'root'", "'ugrj543f7lree'", $content);
        $content = str_replace('"root"', '"ugrj543f7lree"', $content);
        
        if (file_put_contents($file, $content)) {
            echo "✅ Updated: $file<br>";
        } else {
            echo "❌ Failed to update: $file<br>";
        }
    } else {
        echo "⚠️ File not found: $file<br>";
    }
}

echo "<br><h3>Creating corrected db.php file...</h3>";

$db_content = '<?php
// Database configuration - Your actual credentials
define(\'DB_HOST\', \'localhost\');
define(\'DB_USER\', \'ugrj543f7lree\');
define(\'DB_PASS\', \'cgmq43woifko\');
define(\'DB_NAME\', \'dbvbbmibbbboo2\');

// Create connection with better error handling
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database Connection Error: " . $e->getMessage() . "<br><br>
         <strong>Database:</strong> " . DB_NAME . "<br>
         <strong>Username:</strong> " . DB_USER . "<br>
         <strong>Host:</strong> " . DB_HOST . "<br><br>
         <a href=\'connection-debug.php\'>Debug Connection</a>");
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION[\'user_id\']);
}

function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION[\'user_id\']]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $directory = \'uploads/\') {
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $allowedTypes = [\'image/jpeg\', \'image/png\', \'image/gif\', \'image/webp\'];
    if (!in_array($file[\'type\'], $allowedTypes)) {
        return false;
    }
    
    $extension = pathinfo($file[\'name\'], PATHINFO_EXTENSION);
    $filename = uniqid() . \'.\' . $extension;
    $filepath = $directory . $filename;
    
    if (move_uploaded_file($file[\'tmp_name\'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return \'just now\';
    if ($time < 3600) return floor($time/60) . \' minutes ago\';
    if ($time < 86400) return floor($time/3600) . \' hours ago\';
    if ($time < 2592000) return floor($time/86400) . \' days ago\';
    if ($time < 31536000) return floor($time/2592000) . \' months ago\';
    return floor($time/31536000) . \' years ago\';
}
?>';

if (file_put_contents('db.php', $db_content)) {
    echo "✅ Created new db.php with correct credentials<br>";
} else {
    echo "❌ Failed to create db.php<br>";
}

echo "<br><hr>";
echo "<h3 style='color: green;'>🎉 Quick fix completed!</h3>";
echo "<p>All files have been updated with your correct database credentials.</p>";

echo "<div style='margin: 20px 0;'>";
echo "<a href='connection-debug.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Test Connection</a>";
echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Go to Homepage</a>";
echo "<a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Login Page</a>";
echo "</div>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 30px auto;
    padding: 30px;
    background: #f8f9fa;
    line-height: 1.6;
}

h2, h3 {
    color: #333;
}

a {
    text-decoration: none;
}

a:hover {
    opacity: 0.9;
}

hr {
    border: none;
    height: 1px;
    background: #ddd;
    margin: 20px 0;
}
</style>
