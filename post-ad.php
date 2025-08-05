<?php
require_once 'db.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=post-ad.php');
    exit;
}

$error = '';
$success = '';

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $condition_type = sanitizeInput($_POST['condition_type']);
    $location = sanitizeInput($_POST['location']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validation
    if (empty($title) || empty($description) || $price <= 0 || empty($location) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle image uploads
        $uploadedImages = [];
        if (!empty($_FILES['images']['name'][0])) {
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] == 0) {
                    $imageFile = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];
                    
                    $imagePath = uploadImage($imageFile);
                    if ($imagePath) {
                        $uploadedImages[] = $imagePath;
                    }
                }
            }
        }
        
        // Insert ad
        $stmt = $pdo->prepare("
            INSERT INTO ads (user_id, category_id, title, description, price, condition_type, location, phone, images) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $imagesJson = json_encode($uploadedImages);
        
        if ($stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $condition_type, $location, $phone, $imagesJson])) {
            $success = 'Ad posted successfully!';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to post ad. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Ad - OLX Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: opacity 0.3s ease;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .post-ad-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }

        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 20px;
            border: 2px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #f0f4ff;
            border-color: #5a6fd8;
        }

        .file-upload-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e1e5e9;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #363;
            border: 1px solid #cfc;
        }

        .required {
            color: #ff6b6b;
        }

        .form-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .form-row,
            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .post-ad-container {
                padding: 1.5rem;
                margin: 1rem;
            }

            .page-title h1 {
                font-size: 2rem;
            }
        }

        .price-input {
            position: relative;
        }

        .price-input::before {
            content: 'Rs';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 600;
        }

        .price-input input {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">🛒 OLX Clone</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="profile.php">Profile</a>
                <a href="my-ads.php">My Ads</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="post-ad-container">
            <div class="page-title">
                <h1>📝 Post Your Ad</h1>
                <p>Sell your items quickly and easily</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="postAdForm">
                <div class="form-group">
                    <label for="title">Ad Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           placeholder="e.g., iPhone 13 Pro Max 256GB"
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    <div class="form-help">Make it descriptive and specific</div>
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-control" required 
                              placeholder="Describe your item in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <div class="form-help">Include condition, features, and any defects</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category <span class="required">*</span></label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['icon'] . ' ' . htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="condition_type">Condition</label>
                        <select id="condition_type" name="condition_type" class="form-control">
                            <option value="used" <?php echo (isset($_POST['condition_type']) && $_POST['condition_type'] == 'used') ? 'selected' : ''; ?>>Used</option>
                            <option value="new" <?php echo (isset($_POST['condition_type']) && $_POST['condition_type'] == 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="refurbished" <?php echo (isset($_POST['condition_type']) && $_POST['condition_type'] == 'refurbished') ? 'selected' : ''; ?>>Refurbished</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <div class="price-input">
                        <input type="number" id="price" name="price" class="form-control" required 
                               min="1" step="0.01" placeholder="0.00"
                               value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>">
                    </div>
                    <div class="form-help">Enter price in Pakistani Rupees</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location <span class="required">*</span></label>
                        <input type="text" id="location" name="location" class="form-control" required 
                               placeholder="e.g., Karachi, Lahore, Islamabad"
                               value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" required 
                               placeholder="03XX-XXXXXXX"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="images">Images (Max 5)</label>
                    <div class="file-upload">
                        <input type="file" id="images" name="images[]" multiple accept="image/*">
                        <label for="images" class="file-upload-label">
                            <div class="file-upload-icon">📷</div>
                            <div>Click to upload images or drag and drop</div>
                            <div style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                                JPG, PNG, GIF up to 5MB each
                            </div>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>

                <button type="submit" class="btn">📤 Post Ad</button>
            </form>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (files.length > 5) {
                alert('Maximum 5 images allowed');
                this.value = '';
                return;
            }
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Form validation
        document.getElementById('postAdForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const category = document.getElementById('category_id').value;
            const location = document.getElementById('location').value.trim();
            const phone = document.getElementById('phone').value.trim();
            
            if (!title || !description || !price || !category || !location || !phone) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                return;
            }
            
            if (title.length < 10) {
                e.preventDefault();
                alert('Title must be at least 10 characters long');
                return;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Description must be at least 20 characters long');
                return;
            }
        });

        // Auto-redirect after successful post
        <?php if ($success): ?>
            setTimeout(function() {
                window.location.href = 'my-ads.php';
            }, 2000);
        <?php endif; ?>

        // Price formatting
        document.getElementById('price').addEventListener('input', function() {
            let value = this.value;
            if (value && !isNaN(value)) {
                // Format number with commas
                this.setAttribute('data-formatted', parseFloat(value).toLocaleString());
            }
        });
    </script>
</body>
</html>
