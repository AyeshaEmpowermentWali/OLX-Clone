<?php
// Database Connection Test
echo "<h2>🔧 Database Connection Test</h2>";
echo "<hr>";

// Your database credentials
$host = 'localhost';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';
$database = 'dbvbbmibbbboo2';

try {
    echo "1. Testing connection to MySQL server...<br>";
    
    // First test connection to MySQL server
    $pdo_test = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Successfully connected to MySQL server!<br><br>";
    
    echo "2. Testing connection to database '$database'...<br>";
    
    // Test connection to specific database
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Successfully connected to database '$database'!<br><br>";
    
    echo "3. Testing database operations...<br>";
    
    // Test a simple query
    $result = $pdo->query("SELECT DATABASE() as current_db, NOW() as current_time");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Current Database: " . $row['current_db'] . "<br>";
    echo "✅ Current Time: " . $row['current_time'] . "<br><br>";
    
    echo "4. Checking existing tables...<br>";
    
    // Check if tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️ No tables found. You need to run the setup to create tables.<br>";
        echo "<a href='setup.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Run Database Setup</a><br><br>";
    } else {
        echo "✅ Found " . count($tables) . " tables:<br>";
        foreach ($tables as $table) {
            echo "  - $table<br>";
        }
        echo "<br>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 Database connection is working perfectly!</h3>";
    echo "<p>You can now use your OLX Clone application.</p>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Homepage</a>";
    echo "<a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login Page</a>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Connection Failed</h3>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    
    echo "<h4>🔍 Troubleshooting Steps:</h4>";
    echo "<ol>";
    echo "<li><strong>Check Credentials:</strong> Verify username, password, and database name</li>";
    echo "<li><strong>Database Exists:</strong> Make sure database 'dbvbbmibbbboo2' exists</li>";
    echo "<li><strong>User Permissions:</strong> Ensure user 'ugrj543f7lree' has access to the database</li>";
    echo "<li><strong>Server Running:</strong> Check if MySQL server is running</li>";
    echo "<li><strong>Host/Port:</strong> Verify if localhost and port 3306 are correct</li>";
    echo "</ol>";
    
    echo "<h4>💡 Quick Fixes:</h4>";
    echo "<ul>";
    echo "<li>Try creating the database manually in phpMyAdmin/cPanel</li>";
    echo "<li>Check if the hosting provider uses a different host (not localhost)</li>";
    echo "<li>Verify the database user has proper privileges</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
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

ol, ul {
    margin: 10px 0;
    padding-left: 30px;
}

li {
    margin: 5px 0;
}
</style>
