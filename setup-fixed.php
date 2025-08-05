<?php
// Fixed Database Setup Script for OLX Clone with your credentials
// Run this file once to set up your database tables

// Your database credentials
$host = 'localhost';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';
$database = 'dbvbbmibbbboo2';

echo "<h2>🛒 OLX Clone Database Setup</h2>";
echo "<p>Setting up database: <strong>$database</strong></p>";
echo "<hr>";

try {
    // Connect directly to your existing database
    echo "1. Connecting to your database '$database'...<br>";
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully!<br><br>";

    // Create tables
    echo "2. Creating database tables...<br>";
    
    // Users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            location VARCHAR(100),
            profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Users table created<br>";

    // Categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Categories table created<br>";

    // Ads table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            condition_type ENUM('new', 'used', 'refurbished') DEFAULT 'used',
            location VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            images TEXT,
            status ENUM('active', 'sold', 'inactive') DEFAULT 'active',
            featured BOOLEAN DEFAULT FALSE,
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_category_id (category_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Ads table created<br>";

    // Messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ad_id INT NOT NULL,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ad_id (ad_id),
            INDEX idx_sender_receiver (sender_id, receiver_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Messages table created<br>";

    // Favorites table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ad_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, ad_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Favorites table created<br><br>";

    // Insert default categories
    echo "3. Inserting default categories...<br>";
    $categories = [
        ['Electronics', '📱'],
        ['Vehicles', '🚗'],
        ['Furniture', '🪑'],
        ['Fashion', '👕'],
        ['Books', '📚'],
        ['Sports', '⚽'],
        ['Home & Garden', '🏠'],
        ['Jobs', '💼'],
        ['Services', '🔧'],
        ['Others', '📦']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, icon) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "✅ Default categories inserted<br><br>";

    // Create demo user
    echo "4. Creating demo user...<br>";
    $demoPassword = password_hash('demo123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, phone, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['demo', 'demo@example.com', $demoPassword, '03001234567', 'Karachi, Pakistan']);
    echo "✅ Demo user created (Username: demo, Password: demo123)<br><br>";

    // Create uploads directory
    echo "5. Creating uploads directory...<br>";
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
        echo "✅ Uploads directory created<br>";
    } else {
        echo "✅ Uploads directory already exists<br>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Your OLX Clone is ready to use!</strong></p>";
    echo "<ul>";
    echo "<li>✅ All tables created in database: <strong>$database</strong></li>";
    echo "<li>✅ Demo user created (Username: demo, Password: demo123)</li>";
    echo "<li>✅ Sample categories added</li>";
    echo "<li>✅ Upload directory created</li>";
    echo "</ul>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🏠 Go to Homepage</a>";
    echo "<a href='login.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>🔑 Login Page</a>";
    echo "</div>";
    
    echo "<p style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px;'><strong>⚠️ Security Note:</strong> Delete this setup file after successful installation.</p>";

} catch(PDOException $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3>❌ Database Setup Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
    
    echo "<h4>🔧 Possible Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Database Access:</strong> Make sure user 'ugrj543f7lree' has full access to database 'dbvbbmibbbboo2'</li>";
    echo "<li><strong>Database Exists:</strong> Verify the database 'dbvbbmibbbboo2' exists in your hosting panel</li>";
    echo "<li><strong>Permissions:</strong> Ensure the database user has CREATE, INSERT, SELECT privileges</li>";
    echo "<li><strong>Connection:</strong> Check if the database server is accessible</li>";
    echo "</ol>";
    
    echo "<p><strong>💡 Try this:</strong> Login to your hosting control panel (cPanel/Plesk) and verify:</p>";
    echo "<ul>";
    echo "<li>Database 'dbvbbmibbbboo2' exists</li>";
    echo "<li>User 'ugrj543f7lree' is assigned to this database</li>";
    echo "<li>User has all necessary permissions</li>";
    echo "</ul>";
    echo "</div>";
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

h2 {
    color: #333;
    text-align: center;
    margin-bottom: 10px;
}

h3 {
    color: #333;
}

ul, ol {
    margin: 15px 0;
    padding-left: 30px;
}

li {
    margin: 8px 0;
}

a {
    text-decoration: none;
    font-weight: 600;
}

a:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    margin: 20px 0;
}
</style>
