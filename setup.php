<?php
// Database Setup Script for OLX Clone
// Run this file once to set up your database

// Database configuration - Updated with your credentials
$host = 'localhost';
$username = 'ugrj543f7lree';
$password = 'cgmq43woifko';
$database = 'dbvbbmibbbboo2';

echo "<h2>🛒 OLX Clone Database Setup</h2>";
echo "<hr>";

try {
    // Connect to MySQL server (without selecting database)
    echo "1. Connecting to MySQL server...<br>";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to MySQL server successfully!<br><br>";

    // Create database
    echo "2. Creating database '$database'...<br>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$database' created successfully!<br><br>";

    // Connect to the new database
    echo "3. Connecting to database '$database'...<br>";
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully!<br><br>";

    // Create tables
    echo "4. Creating database tables...<br>";
    
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
        )
    ");
    echo "✅ Users table created<br>";

    // Categories table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        )
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
            FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Messages table created<br>";

    // Favorites table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ad_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, ad_id)
        )
    ");
    echo "✅ Favorites table created<br><br>";

    // Insert default categories
    echo "5. Inserting default categories...<br>";
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
    echo "6. Creating demo user...<br>";
    $demoPassword = password_hash('demo123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, phone, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['demo', 'demo@example.com', $demoPassword, '03001234567', 'Karachi, Pakistan']);
    echo "✅ Demo user created (Username: demo, Password: demo123)<br><br>";

    // Create indexes
    echo "7. Creating database indexes for better performance...<br>";
    try {
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ads_category ON ads(category_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ads_user ON ads(user_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ads_status ON ads(status)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ads_created ON ads(created_at)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_ad ON messages(ad_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_users ON messages(sender_id, receiver_id)");
        echo "✅ Database indexes created<br><br>";
    } catch (Exception $e) {
        echo "⚠️ Some indexes may already exist (this is normal)<br><br>";
    }

    // Create uploads directory
    echo "8. Creating uploads directory...<br>";
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
        echo "✅ Uploads directory created<br>";
    } else {
        echo "✅ Uploads directory already exists<br>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Database and tables are ready</li>";
    echo "<li>✅ Demo user created (Username: demo, Password: demo123)</li>";
    echo "<li>✅ You can now use your OLX Clone!</li>";
    echo "<li>🔗 <a href='index.php'>Go to Homepage</a></li>";
    echo "<li>🔗 <a href='login.php'>Login with Demo Account</a></li>";
    echo "</ul>";
    
    echo "<p><strong>⚠️ Important:</strong> Delete this setup.php file after successful setup for security reasons.</p>";

} catch(PDOException $e) {
    echo "<div style='color: red; background: #ffe6e6; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Database Setup Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h4>Common Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>XAMPP/WAMP not running:</strong> Start Apache and MySQL services</li>";
    echo "<li><strong>Wrong password:</strong> Update the password in this file (line 6)</li>";
    echo "<li><strong>MySQL not installed:</strong> Install XAMPP, WAMP, or MAMP</li>";
    echo "<li><strong>Port issues:</strong> Check if MySQL is running on port 3306</li>";
    echo "</ol>";
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
}

h2 {
    color: #333;
    text-align: center;
}

ul, ol {
    line-height: 1.6;
}

a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style>
