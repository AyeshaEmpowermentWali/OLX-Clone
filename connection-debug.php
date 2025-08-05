<?php
// Debug connection issues
echo "<h2>🔧 Database Connection Debug</h2>";
echo "<hr>";

// Your credentials
$host = 'localhost';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';
$database = 'dbvbbmibbbboo2';

echo "<h3>Testing Connection with Your Credentials:</h3>";
echo "<p><strong>Host:</strong> $host</p>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Database:</strong> $database</p>";
echo "<p><strong>Password:</strong> " . (empty($password) ? 'Empty' : 'Set (' . strlen($password) . ' characters)') . "</p>";
echo "<hr>";

// Test 1: Basic MySQL connection
echo "<h4>Test 1: Basic MySQL Server Connection</h4>";
try {
    $pdo1 = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Successfully connected to MySQL server<br><br>";
    
    // Test 2: Check if database exists
    echo "<h4>Test 2: Check if Database Exists</h4>";
    $stmt = $pdo1->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
    $stmt->execute([$database]);
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "✅ Database '$database' exists<br><br>";
        
        // Test 3: Connect to specific database
        echo "<h4>Test 3: Connect to Specific Database</h4>";
        try {
            $pdo2 = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
            $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "✅ Successfully connected to database '$database'<br><br>";
            
            // Test 4: Check tables
            echo "<h4>Test 4: Check Database Tables</h4>";
            $tables = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($tables)) {
                echo "⚠️ No tables found in database. You need to run the setup.<br>";
                echo "<a href='setup-fixed.php' style='background: #007cba; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 10px 0; display: inline-block;'>Run Database Setup</a><br><br>";
            } else {
                echo "✅ Found " . count($tables) . " tables:<br>";
                foreach ($tables as $table) {
                    echo "  - $table<br>";
                }
                echo "<br>";
                
                // Test 5: Check if required tables exist
                echo "<h4>Test 5: Check Required Tables</h4>";
                $required_tables = ['users', 'categories', 'ads', 'messages', 'favorites'];
                $missing_tables = [];
                
                foreach ($required_tables as $table) {
                    if (in_array($table, $tables)) {
                        echo "✅ Table '$table' exists<br>";
                    } else {
                        echo "❌ Table '$table' missing<br>";
                        $missing_tables[] = $table;
                    }
                }
                
                if (empty($missing_tables)) {
                    echo "<br><h3 style='color: green;'>🎉 All tests passed! Your database is ready.</h3>";
                    echo "<a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block;'>Go to Homepage</a>";
                    echo "<a href='login.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; display: inline-block;'>Login Page</a>";
                } else {
                    echo "<br><h3 style='color: orange;'>⚠️ Some tables are missing. Run the setup to create them.</h3>";
                    echo "<a href='setup-fixed.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; display: inline-block;'>Run Database Setup</a>";
                }
            }
            
        } catch(PDOException $e) {
            echo "❌ Failed to connect to database '$database'<br>";
            echo "<strong>Error:</strong> " . $e->getMessage() . "<br><br>";
        }
        
    } else {
        echo "❌ Database '$database' does not exist<br>";
        echo "<strong>Solution:</strong> Create the database first or run the setup script<br><br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Failed to connect to MySQL server<br>";
    echo "<strong>Error Code:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Error Message:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<h4>🔧 Common Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Wrong Credentials:</strong> Double-check username and password</li>";
    echo "<li><strong>Database User:</strong> Make sure user '$username' exists and has proper permissions</li>";
    echo "<li><strong>MySQL Server:</strong> Ensure MySQL/MariaDB server is running</li>";
    echo "<li><strong>Host Issues:</strong> Try '127.0.0.1' instead of 'localhost'</li>";
    echo "<li><strong>Port Issues:</strong> Add port number if different from 3306</li>";
    echo "</ol>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 900px;
    margin: 30px auto;
    padding: 30px;
    background: #f8f9fa;
    line-height: 1.6;
}

h2, h3, h4 {
    color: #333;
}

ol, ul {
    margin: 15px 0;
    padding-left: 30px;
}

li {
    margin: 8px 0;
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
