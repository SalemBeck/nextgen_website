<?php
session_start();
require_once "../config/database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $free_option = isset($_POST['free_option']) ? 1 : 0;
    $paid_option = isset($_POST['paid_option']) ? 1 : 0;
    $status = $_POST['status'];
    
    // Validation
    if (empty($title)) {
        $errors[] = "Product title is required";
    }
    
    if (empty($description)) {
        $errors[] = "Product description is required";
    }
    
    if ($category_id <= 0) {
        $errors[] = "Please select a category";
    }
    
    if ($price < 0) {
        $errors[] = "Price cannot be negative";
    }
    
    if (!$free_option && !$paid_option) {
        $errors[] = "Please select at least one access option (free or paid)";
    }
    
    // Handle thumbnail image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, GIF, and WebP images are allowed";
        } else {
            $upload_dir = "../assets/images/products/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = "assets/images/products/" . $filename;
            } else {
                $errors[] = "Failed to upload thumbnail image";
            }
        }
    }
    
  
$download_path = null; // Default to null for new products

if (isset($_FILES['download_file']) && $_FILES['download_file']['error'] === UPLOAD_ERR_OK) {
    $allowed_download_types = ['application/zip', 'application/pdf', 'text/plain'];
    $file_type = $_FILES['download_file']['type'];
    
    if (!in_array($file_type, $allowed_download_types)) {
        $errors[] = "Only ZIP, PDF, and TXT files are allowed for download";
    } else {
        $upload_dir = "../assets/downloads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['download_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['download_file']['tmp_name'], $destination)) {
            $download_path = "assets/downloads/" . $filename;
        } else {
            $errors[] = "Failed to upload download file";
        }
    }
}


if (empty($download_path)) {
    $download_path = null;
}
    
    // Handle product media upload (multiple images/videos)
    $media_files = [];
    if (!empty($_FILES['product_media']['name'][0])) {
        $media_upload_dir_images = "../assets/images/products/";
        $media_upload_dir_videos = "../assets/videos/products/";
        
        // Create directories if they don't exist
        if (!is_dir($media_upload_dir_images)) {
            mkdir($media_upload_dir_images, 0755, true);
        }
        if (!is_dir($media_upload_dir_videos)) {
            mkdir($media_upload_dir_videos, 0755, true);
        }
        
        foreach ($_FILES['product_media']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['product_media']['error'][$key] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['product_media']['type'][$key];
                $file_name = $_FILES['product_media']['name'][$key];
                $file_size = $_FILES['product_media']['size'][$key];
                
                // Check if it's an image or video
                if (strpos($file_type, 'image/') === 0) {
                    $allowed_media_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $upload_dir = $media_upload_dir_images;
                    $media_type = 'image';
                } elseif (strpos($file_type, 'video/') === 0) {
                    $allowed_media_types = ['video/mp4', 'video/mpeg', 'video/quicktime'];
                    $upload_dir = $media_upload_dir_videos;
                    $media_type = 'video';
                } else {
                    $errors[] = "Invalid file type for product media: " . $file_name;
                    continue;
                }
                
                if (!in_array($file_type, $allowed_media_types)) {
                    $errors[] = "File type not allowed for " . $file_name;
                    continue;
                }
                
                // Check file size (10MB for images, 50MB for videos)
                $max_size = ($media_type === 'image') ? 10 * 1024 * 1024 : 50 * 1024 * 1024;
                if ($file_size > $max_size) {
                    $errors[] = "File too large: " . $file_name . " (Max: " . ($max_size / (1024 * 1024)) . "MB)";
                    continue;
                }
                
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '_' . $key . '.' . $file_extension;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $file_path = ($media_type === 'image') ? 
                                "assets/images/products/" . $filename : 
                                "assets/videos/products/" . $filename;
                    $media_files[] = [
                        'type' => $media_type,
                        'path' => $file_path,
                        'order' => $key
                    ];
                } else {
                    $errors[] = "Failed to upload media file: " . $file_name;
                }
            }
        }
    }
    
    
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert product
            $query = "INSERT INTO products (category_id, title, description, image, features, free_option, paid_option, price, download_link, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $features = ''; // You can extend this to handle features
            $stmt->bind_param("issssiidss", 
                $category_id, 
                $title, 
                $description, 
                $image_path,
                $features,
                $free_option,
                $paid_option,
                $price,
                $download_path,
                $status
            );
            
            if ($stmt->execute()) {
                $product_id = $conn->insert_id;
                
                // Insert product media
                if (!empty($media_files)) {
                    $media_query = "INSERT INTO product_media (product_id, media_type, file_path, media_order) VALUES (?, ?, ?, ?)";
                    $media_stmt = $conn->prepare($media_query);
                    
                    foreach ($media_files as $index => $media) {
                        $media_stmt->bind_param("issi", $product_id, $media['type'], $media['path'], $index);
                        $media_stmt->execute();
                    }
                }
                
                // Commit transaction
                $conn->commit();
                $success = "Product added successfully!";
                // Reset form values
                $title = $description = '';
                $category_id = $price = 0;
                $free_option = $paid_option = 1;
                $status = 'active';
            } else {
                throw new Exception("Failed to add product: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - NextGen Admin</title>
    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        
        :root {
            --primary-blue: #010626;
            --secondary-blue: #010B40;
            --accent-blue: #020F59;
            --oxford-blue: #021373;
            --vista-blue: #8491D9;
            --dark-bg: #0a0e27;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: #fff;
            background: var(--primary-blue);
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
        
        /* Include all admin sidebar and header styles */
        .admin-sidebar { background: linear-gradient(180deg, var(--secondary-blue) 0%, var(--accent-blue) 100%); min-height: 100vh; padding: 0; border-right: 1px solid rgba(132, 145, 217, 0.2); }
        .sidebar-header { padding: 30px 25px; border-bottom: 1px solid rgba(132, 145, 217, 0.2); }
        .admin-brand { font-family: 'Poppins', sans-serif; font-size: 1.5rem; color: white; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .admin-brand-icon { background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue)); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .sidebar-nav { padding: 20px 0; }
        .nav-item-custom { margin-bottom: 5px; }
        .nav-link-custom { color: rgba(255, 255, 255, 0.8); padding: 15px 25px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.3s ease; border-left: 3px solid transparent; }
        .nav-link-custom:hover, .nav-link-custom.active { color: white; background: rgba(132, 145, 217, 0.15); border-left-color: var(--vista-blue); }
        .nav-link-custom i { width: 20px; text-align: center; }
        .admin-main { padding: 0; }
        .admin-header { background: rgba(132, 145, 217, 0.05); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(132, 145, 217, 0.1); padding: 20px 30px; }
        .admin-welcome h1 { font-size: 1.8rem; margin-bottom: 5px; color: white; }
        .admin-welcome p { color: rgba(255, 255, 255, 0.7); margin: 0; }
        .admin-logout { background: rgba(132, 145, 217, 0.1); border: 1px solid rgba(132, 145, 217, 0.3); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        .admin-logout:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.5); color: #fca5a5; }
        .dashboard-content { padding: 30px; }
        
        
        .form-container {
            background: rgba(132, 145, 217, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.15);
            border-radius: 15px;
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(132, 145, 217, 0.1);
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 1.3rem;
            color: var(--vista-blue);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group-custom {
            margin-bottom: 20px;
        }
        
        .form-label-custom {
            display: block;
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-input-custom, .form-select-custom, .form-textarea-custom {
            width: 100%;
            padding: 12px 15px;
            background: rgba(132, 145, 217, 0.1);
            border: 2px solid rgba(132, 145, 217, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input-custom:focus, .form-select-custom:focus, .form-textarea-custom:focus {
            outline: none;
            border-color: var(--vista-blue);
            background: rgba(132, 145, 217, 0.15);
        }
        
        .form-textarea-custom {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-input-custom::placeholder, .form-textarea-custom::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-check-custom {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .form-check-input-custom {
            width: 20px;
            height: 20px;
            background: rgba(132, 145, 217, 0.1);
            border: 2px solid rgba(132, 145, 217, 0.3);
            border-radius: 4px;
            appearance: none;
            cursor: pointer;
            position: relative;
        }
        
        .form-check-input-custom:checked {
            background: var(--vista-blue);
            border-color: var(--vista-blue);
        }
        
        .form-check-input-custom:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .form-check-label-custom {
            color: white;
            font-weight: 500;
            cursor: pointer;
        }
        
        .file-upload-container {
            border: 2px dashed rgba(132, 145, 217, 0.3);
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(132, 145, 217, 0.05);
        }
        
        .file-upload-container:hover {
            border-color: var(--vista-blue);
            background: rgba(132, 145, 217, 0.1);
        }
        
        .file-upload-icon {
            font-size: 2.5rem;
            color: var(--vista-blue);
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
        }
        
        .file-input-custom {
            display: none;
        }
        
        .file-input-label {
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5);
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .file-input-label:hover {
            transform: translateY(-1px);
        }
        
        .file-preview {
            margin-top: 15px;
            display: none;
        }
        
        .file-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid rgba(132, 145, 217, 0.3);
        }
        
      
        .media-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .media-preview-item {
            position: relative;
            width: 120px;
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid rgba(132, 145, 217, 0.3);
        }
        
        .media-preview-item img,
        .media-preview-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .media-type-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7rem;
        }
        
        .remove-media {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(239, 68, 68, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-submit-custom {
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5);
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-submit-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(132, 145, 217, 0.4);
        }
        
        .btn-cancel-custom {
            background: rgba(107, 114, 128, 0.2);
            border: 1px solid rgba(107, 114, 128, 0.3);
            padding: 15px 30px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-cancel-custom:hover {
            background: rgba(107, 114, 128, 0.3);
            color: white;
        }
        
        .alert-custom {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .alert-success-custom {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #6ee7b7;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="sidebar-header">
                    <a href="dashboard.php" class="admin-brand">
                        <span class="admin-brand-icon">
                            <i class="fas fa-cube"></i>
                        </span>
                        NextGen Admin
                    </a>
                </div>
                
                <nav class="sidebar-nav">
                    <div class="nav-item-custom">
                        <a href="dashboard.php" class="nav-link-custom">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="products.php" class="nav-link-custom active">
                            <i class="fas fa-box"></i>
                            Products
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="categories.php" class="nav-link-custom">
                            <i class="fas fa-tags"></i>
                            Categories
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="email-submissions.php" class="nav-link-custom">
                            <i class="fas fa-envelope"></i>
                            Email Submissions
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="purchases.php" class="nav-link-custom">
                            <i class="fas fa-shopping-cart"></i>
                            Purchases
                        </a>
                    </div>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-main">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <div class="admin-welcome">
                        <h1>Add New Product</h1>
                        <p>Create a new product for your store</p>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    <!-- Back to Products -->
                    <div class="mb-4">
                        <a href="products.php" class="btn-cancel-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                    </div>
                    
                    <!-- Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert-custom">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert-success-custom">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Form -->
                    <div class="form-container">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- Basic Information -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-info-circle"></i>
                                    Basic Information
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="title">Product Title *</label>
                                            <input 
                                                type="text" 
                                                class="form-input-custom" 
                                                id="title" 
                                                name="title" 
                                                placeholder="Enter product title"
                                                required
                                                value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="category_id">Category *</label>
                                            <select class="form-select-custom" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>" 
                                                        <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="description">Product Description *</label>
                                    <textarea 
                                        class="form-textarea-custom" 
                                        id="description" 
                                        name="description" 
                                        placeholder="Describe your product in detail..."
                                        required
                                        rows="6"
                                    ><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Pricing & Options -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-tag"></i>
                                    Pricing & Options
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="price">Price ($) *</label>
                                            <input 
                                                type="number" 
                                                class="form-input-custom" 
                                                id="price" 
                                                name="price" 
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                required
                                                value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0.00' ?>"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="status">Status</label>
                                            <select class="form-select-custom" id="status" name="status">
                                                <option value="active" <?= (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : 'selected' ?>>Active</option>
                                                <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check-custom">
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input-custom" 
                                                id="free_option" 
                                                name="free_option" 
                                                value="1"
                                                <?= (isset($_POST['free_option']) && $_POST['free_option']) || !isset($_POST['free_option']) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label-custom" for="free_option">
                                                Enable Free Access Option
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check-custom">
                                            <input 
                                                type="checkbox" 
                                                class="form-check-input-custom" 
                                                id="paid_option" 
                                                name="paid_option" 
                                                value="1"
                                                <?= (isset($_POST['paid_option']) && $_POST['paid_option']) || !isset($_POST['paid_option']) ? 'checked' : '' ?>
                                            >
                                            <label class="form-check-label-custom" for="paid_option">
                                                Enable Paid Access Option
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Media Upload -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-image"></i>
                                    Media & Files
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Thumbnail Image</label>
                                            <div class="file-upload-container" id="imageUploadContainer">
                                                <div class="file-upload-icon">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </div>
                                                <div class="file-upload-text">
                                                    Click to upload thumbnail image<br>
                                                    <small>JPG, PNG, GIF, WebP (Max 5MB)</small>
                                                </div>
                                                <input 
                                                    type="file" 
                                                    class="file-input-custom" 
                                                    id="image" 
                                                    name="image" 
                                                    accept="image/*"
                                                    onchange="previewImage(this, 'imagePreview')"
                                                >
                                                <label for="image" class="file-input-label">
                                                    <i class="fas fa-upload me-2"></i>Choose Image
                                                </label>
                                                <div class="file-preview mt-3" id="imagePreview"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Download File</label>
                                            <div class="file-upload-container" id="fileUploadContainer">
                                                <div class="file-upload-icon">
                                                    <i class="fas fa-file-archive"></i>
                                                </div>
                                                <div class="file-upload-text">
                                                    Click to upload product file<br>
                                                    <small>ZIP, PDF, TXT (Max 50MB)</small>
                                                </div>
                                                <input 
                                                    type="file" 
                                                    class="file-input-custom" 
                                                    id="download_file" 
                                                    name="download_file" 
                                                    accept=".zip,.pdf,.txt"
                                                    onchange="showFileName(this, 'filePreview')"
                                                >
                                                <label for="download_file" class="file-input-label">
                                                    <i class="fas fa-upload me-2"></i>Choose File
                                                </label>
                                                <div class="file-preview mt-3" id="filePreview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Product Media Gallery -->
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Product Gallery (Images & Videos)</label>
                                    <div class="file-upload-container" id="galleryUploadContainer">
                                        <div class="file-upload-icon">
                                            <i class="fas fa-images"></i>
                                        </div>
                                        <div class="file-upload-text">
                                            Click to upload multiple images and videos for product gallery<br>
                                            <small>Images: JPG, PNG, GIF, WebP (Max 10MB each)</small><br>
                                            <small>Videos: MP4, MPEG, MOV (Max 50MB each)</small>
                                        </div>
                                        <input 
                                            type="file" 
                                            class="file-input-custom" 
                                            id="product_media" 
                                            name="product_media[]" 
                                            accept="image/*,video/*"
                                            multiple
                                            onchange="previewMediaFiles(this)"
                                        >
                                        <label for="product_media" class="file-input-label">
                                            <i class="fas fa-upload me-2"></i>Choose Multiple Files
                                        </label>
                                        <div class="media-preview-container mt-3" id="mediaPreview"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="form-section text-end">
                                <button type="reset" class="btn-cancel-custom me-3">
                                    <i class="fas fa-redo me-2"></i>Reset Form
                                </button>
                                <button type="submit" class="btn-submit-custom">
                                    <i class="fas fa-plus me-2"></i>Add Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function showFileName(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                preview.innerHTML = `<div class="text-success"><i class="fas fa-check-circle me-2"></i>${input.files[0].name}</div>`;
                preview.style.display = 'block';
            }
        }
        
        function previewMediaFiles(input) {
            const previewContainer = document.getElementById('mediaPreview');
            previewContainer.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();
                    const mediaItem = document.createElement('div');
                    mediaItem.className = 'media-preview-item';
                    mediaItem.dataset.index = index;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-media';
                    removeBtn.innerHTML = '×';
                    removeBtn.onclick = function(e) {
                        e.preventDefault();
                        mediaItem.remove();
                        // Remove the file from the input files
                        const dt = new DataTransfer();
                        Array.from(input.files).forEach((f, i) => {
                            if (i !== index) dt.items.add(f);
                        });
                        input.files = dt.files;
                    };
                    
                    const typeBadge = document.createElement('div');
                    typeBadge.className = 'media-type-badge';
                    
                    if (file.type.startsWith('image/')) {
                        typeBadge.innerHTML = '<i class="fas fa-image"></i>';
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            mediaItem.appendChild(img);
                        };
                    } else if (file.type.startsWith('video/')) {
                        typeBadge.innerHTML = '<i class="fas fa-video"></i>';
                        reader.onload = function(e) {
                            const video = document.createElement('video');
                            video.src = e.target.result;
                            video.controls = true;
                            mediaItem.appendChild(video);
                        };
                    }
                    
                    mediaItem.appendChild(removeBtn);
                    mediaItem.appendChild(typeBadge);
                    previewContainer.appendChild(mediaItem);
                    
                    reader.readAsDataURL(file);
                });
                
                if (input.files.length > 0) {
                    previewContainer.style.display = 'flex';
                }
            }
        }
        
        // Add drag and drop functionality
        document.addEventListener('DOMContentLoaded', function() {
            const uploadContainers = document.querySelectorAll('.file-upload-container');
            
            uploadContainers.forEach(container => {
                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.style.borderColor = 'var(--vista-blue)';
                    this.style.background = 'rgba(132, 145, 217, 0.15)';
                });
                
                container.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.style.borderColor = 'rgba(132, 145, 217, 0.3)';
                    this.style.background = 'rgba(132, 145, 217, 0.05)';
                });
                
                container.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.borderColor = 'rgba(132, 145, 217, 0.3)';
                    this.style.background = 'rgba(132, 145, 217, 0.05)';
                    
                    const input = this.querySelector('input[type="file"]');
                    if (input && e.dataTransfer.files.length > 0) {
                        input.files = e.dataTransfer.files;
                        
                        if (input.id === 'image') {
                            previewImage(input, 'imagePreview');
                        } else if (input.id === 'download_file') {
                            showFileName(input, 'filePreview');
                        } else if (input.id === 'product_media') {
                            previewMediaFiles(input);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
<?php

$conn->close();
?>