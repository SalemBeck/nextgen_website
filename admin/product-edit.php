<?php
session_start();
require_once "../config/database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Product ID is required.";
    header("Location: products.php");
    exit;
}

$product_id = intval($_GET['id']);

// Fetch product details
$product_query = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$product_query->bind_param("i", $product_id);
$product_query->execute();
$product_result = $product_query->get_result();

if ($product_result->num_rows === 0) {
    $_SESSION['error'] = "Product not found.";
    header("Location: products.php");
    exit;
}

$product = $product_result->fetch_assoc();

// Fetch existing product media
$media_query = $conn->prepare("
    SELECT * FROM product_media 
    WHERE product_id = ? 
    ORDER BY media_order ASC
");
$media_query->bind_param("i", $product_id);
$media_query->execute();
$media_result = $media_query->get_result();
$existing_media = $media_result->fetch_all(MYSQLI_ASSOC);

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Handle form submission
$errors = [];
$success = '';

// Check if POST content is too large
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $errors[] = "The uploaded files are too large. Maximum allowed size is " . ini_get('post_max_size');
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields are set
    if (!isset($_POST['title']) || !isset($_POST['description']) || !isset($_POST['category_id']) || !isset($_POST['price'])) {
        $errors[] = "Required form fields are missing. Please check if your upload size is within limits.";
    } else {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category_id = intval($_POST['category_id']);
        $price = floatval($_POST['price']);
        $free_option = isset($_POST['free_option']) ? 1 : 0;
        $paid_option = isset($_POST['paid_option']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';
        
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
        $image_path = $product['image']; // Keep existing image by default
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
                    // Delete old image if it exists
                    if (!empty($product['image']) && file_exists("../" . $product['image'])) {
                        unlink("../" . $product['image']);
                    }
                    $image_path = "assets/images/products/" . $filename;
                } else {
                    $errors[] = "Failed to upload thumbnail image";
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle file upload errors
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive.",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive.",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
                UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
            ];
            $error_code = $_FILES['image']['error'];
            $errors[] = $upload_errors[$error_code] ?? "Unknown upload error (Code: $error_code)";
        }
        
        // Handle download file upload - FIXED VERSION
$download_path = $product['download_link']; // Keep existing file by default

// Only process download file if a new file was actually uploaded
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
            // Delete old file if it exists and is not '0'
            if (!empty($product['download_link']) && $product['download_link'] !== '0' && file_exists("../" . $product['download_link'])) {
                unlink("../" . $product['download_link']);
            }
            $download_path = "assets/downloads/" . $filename;
        } else {
            $errors[] = "Failed to upload download file";
        }
    }
} elseif (isset($_FILES['download_file']) && $_FILES['download_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    // Only show error if there was an actual upload error (not just no file selected)
    $error_code = $_FILES['download_file']['error'];
    $errors[] = "Download file upload error (Code: $error_code)";
}

// FIX: Ensure download_path doesn't get set to '0' if no file was uploaded
if ($download_path === '0') {
    $download_path = $product['download_link']; // Restore original value
}


        
        // Handle product media upload (multiple images/videos)
        $media_files = [];
        if (isset($_FILES['product_media']) && !empty($_FILES['product_media']['name'][0])) {
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
                        $max_size = 10 * 1024 * 1024; // 10MB for images
                    } elseif (strpos($file_type, 'video/') === 0) {
                        $allowed_media_types = ['video/mp4', 'video/mpeg', 'video/quicktime'];
                        $upload_dir = $media_upload_dir_videos;
                        $media_type = 'video';
                        $max_size = 50 * 1024 * 1024; // 50MB for videos
                    } else {
                        $errors[] = "Invalid file type for product media: " . $file_name;
                        continue;
                    }
                    
                    if (!in_array($file_type, $allowed_media_types)) {
                        $errors[] = "File type not allowed for " . $file_name;
                        continue;
                    }
                    
                    // Check file size
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
                            'order' => count($existing_media) + $key
                        ];
                    } else {
                        $errors[] = "Failed to upload media file: " . $file_name;
                    }
                } elseif ($_FILES['product_media']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $error_code = $_FILES['product_media']['error'][$key];
                    $errors[] = "Media file upload error for " . $_FILES['product_media']['name'][$key] . " (Code: $error_code)";
                }
            }
        }
        
        // Handle media deletion
        $media_to_delete = isset($_POST['delete_media']) ? $_POST['delete_media'] : [];
        if (!empty($media_to_delete)) {
            foreach ($media_to_delete as $media_id) {
                $media_id = intval($media_id);
                // Get media info before deletion
                $get_media_query = $conn->prepare("SELECT * FROM product_media WHERE id = ?");
                $get_media_query->bind_param("i", $media_id);
                $get_media_query->execute();
                $media_result = $get_media_query->get_result();
                $media = $media_result->fetch_assoc();
                
                if ($media && file_exists("../" . $media['file_path'])) {
                    unlink("../" . $media['file_path']);
                }
                
                $delete_query = $conn->prepare("DELETE FROM product_media WHERE id = ?");
                $delete_query->bind_param("i", $media_id);
                $delete_query->execute();
            }
        }
        
        // If no errors, update product
if (empty($errors)) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update product - FIXED BIND PARAM
        $query = "UPDATE products SET 
                  category_id = ?, 
                  title = ?, 
                  description = ?, 
                  image = ?, 
                  free_option = ?, 
                  paid_option = ?, 
                  price = ?, 
                  download_link = ?, 
                  status = ?,
                  updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        
        // FIX: Changed from "issssiidsi" to "issssiidsi" - removed extra 'i'
        // Parameter types: i (category_id), s (title), s (description), s (image), 
        // i (free_option), i (paid_option), d (price), s (download_link), 
        // s (status), i (product_id)
        $stmt->bind_param("isssiidssi",
    $category_id,
    $title,
    $description,
    $image_path,
    $free_option,
    $paid_option,
    $price,
    $download_path,
    $status,
    $product_id
);

        
        if ($stmt->execute()) {
            // Insert new product media
            if (!empty($media_files)) {
                $media_insert_query = "INSERT INTO product_media (product_id, media_type, file_path, media_order) VALUES (?, ?, ?, ?)";
                $media_insert_stmt = $conn->prepare($media_insert_query);
                
                foreach ($media_files as $index => $media) {
                    $media_insert_stmt->bind_param("issi", $product_id, $media['type'], $media['path'], $media['order']);
                    $media_insert_stmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            $success = "Product updated successfully!";
            
            // Refresh product data
            $product_query->execute();
            $product_result = $product_query->get_result();
            $product = $product_result->fetch_assoc();
            
            // Refresh media data
            $media_query->execute();
            $media_result = $media_query->get_result();
            $existing_media = $media_result->fetch_all(MYSQLI_ASSOC);
            
        } else {
            throw new Exception("Failed to update product: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = $e->getMessage();
    }
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - NextGen Admin</title>
    
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
   
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
       /* Include all admin styles from previous files */
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
        
        /* Form Styles */
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
        
        /* Product Media Styles */
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

        /* Existing Media Gallery Styles */
.existing-media-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 15px;
}

.existing-media-item {
    position: relative;
    width: 120px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid rgba(132, 145, 217, 0.3);
    background: rgba(132, 145, 217, 0.05);
}

.existing-media-item img,
.existing-media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.delete-media-checkbox {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 20px;
    height: 20px;
    cursor: pointer;
    z-index: 10;
    accent-color: var(--vista-blue); 
}

.text-muted {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    display: block;
    margin-top: 10px;
}

.current-file {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
            
            
            <div class="col-md-9 col-lg-10 admin-main">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <div class="admin-welcome">
                        <h1>Edit Product</h1>
                        <p>Update product information</p>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    
                    <div class="mb-4">
                        <a href="products.php" class="btn-cancel-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                    </div>
                    
                    
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
                    
                    
                    <div class="form-container">
                        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm()">
                           
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
                                                value="<?= htmlspecialchars($product['title']) ?>"
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
                                                        <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
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
                                    ><?= htmlspecialchars($product['description']) ?></textarea>
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
                                                value="<?= number_format($product['price'], 2) ?>"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="status">Status</label>
                                            <select class="form-select-custom" id="status" name="status">
                                                <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                                                <?= $product['free_option'] ? 'checked' : '' ?>
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
                                                <?= $product['paid_option'] ? 'checked' : '' ?>
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
                                                    <i class="fas fa-upload me-2"></i>Choose New Image
                                                </label>
                                                
                                                <!-- Current Image Preview -->
                                                <?php if (!empty($product['image']) && file_exists("../" . $product['image'])): ?>
                                                    <div class="file-preview mt-3" id="imagePreview">
                                                        <img src="../<?= htmlspecialchars($product['image']) ?>" alt="Current product image">
                                                        <div class="current-file mt-2">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            Current image: <?= basename($product['image']) ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="file-preview mt-3" id="imagePreview" style="display: none;"></div>
                                                <?php endif; ?>
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
                                                    <i class="fas fa-upload me-2"></i>Choose New File
                                                </label>
                                                
                                               
<?php if (!empty($product['download_link']) && $product['download_link'] !== '0'): ?>
    <div class="current-file mt-2">
        <i class="fas fa-check-circle me-2"></i>
        Current file: <?= basename($product['download_link']) ?>
        <?php if (file_exists("../" . $product['download_link'])): ?>
            <span class="text-success">(File exists)</span>
        <?php else: ?>
            <span class="text-warning">(File not found on server)</span>
        <?php endif; ?>
    </div>
    <div class="file-preview mt-3" id="filePreview" style="display: none;"></div>
<?php else: ?>
    <div class="current-file mt-2 text-muted">
        <i class="fas fa-info-circle me-2"></i>
        No download file currently set
    </div>
    <div class="file-preview mt-3" id="filePreview" style="display: none;"></div>
<?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Existing Product Media Gallery -->
                                <?php if (!empty($existing_media)): ?>
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">Existing Product Gallery (Check the box on any media item to delete it)</label>
                                        <div class="existing-media-container">
                                            <?php foreach ($existing_media as $media): ?>
                                                <div class="existing-media-item">
                                                    <?php if ($media['media_type'] === 'image'): ?>
                                                        <img src="../<?= htmlspecialchars($media['file_path']) ?>" alt="Product media">
                                                    <?php else: ?>
                                                        <video>
                                                            <source src="../<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                                                        </video>
                                                    <?php endif; ?>
                                                    <input type="checkbox" 
                                                           class="delete-media-checkbox" 
                                                           name="delete_media[]" 
                                                           value="<?= $media['id'] ?>"
                                                           title="Check to delete this media">
                                                    <div class="media-type-badge">
                                                        <?php if ($media['media_type'] === 'image'): ?>
                                                            <i class="fas fa-image"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-video"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- New Product Media Gallery -->
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Add More Product Gallery Media</label>
                                    <div class="file-upload-container" id="galleryUploadContainer">
                                        <div class="file-upload-icon">
                                            <i class="fas fa-images"></i>
                                        </div>
                                        <div class="file-upload-text">
                                            Click to upload multiple images and videos for product gallery<br>
                                            <small>Images: JPG, PNG, GIF, WebP (Max 10MB each)</small><br>
                                            <small>Videos: MP4, MPEG, MOV (Max 50MB each)</small><br>
                                            <small>Total File Size 100MB</small>
                                        </div>
                                        <input 
                                            type="file" 
                                            class="file-input-custom" 
                                            id="product_media" 
                                            name="product_media[]" 
                                            accept="image/*,video/*"
                                            multiple
                                            onchange="previewMediaFiles(this); validateTotalSize(this);"
                                        >
                                        <label for="product_media" class="file-input-label">
                                            <i class="fas fa-upload me-2"></i>Choose Multiple Files
                                        </label>
                                        <div class="file-size-warning mt-2" id="sizeWarning" style="display: none; color: #fca5a5;"></div>
                                        <div class="media-preview-container mt-3" id="mediaPreview"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="form-section text-end">
                                <a href="products.php" class="btn-cancel-custom me-3">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn-submit-custom">
                                    <i class="fas fa-check me-2"></i>Update Product
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
        // File size validation
        function validateTotalSize(input) {
            const maxTotalSize = 100 * 1024 * 1024; // 100MB in bytes
            const sizeWarning = document.getElementById('sizeWarning');
            let totalSize = 0;
            
            if (input.files) {
                Array.from(input.files).forEach(file => {
                    totalSize += file.size;
                });
            }
            
            if (totalSize > maxTotalSize) {
                sizeWarning.textContent = `Warning: Total selected files (${(totalSize / (1024 * 1024)).toFixed(2)}MB) exceed the 100MB limit.`;
                sizeWarning.style.display = 'block';
                return false;
            } else {
                sizeWarning.style.display = 'none';
                return true;
            }
        }
        
        function validateForm() {
            const mediaInput = document.getElementById('product_media');
            if (mediaInput.files && !validateTotalSize(mediaInput)) {
                alert('Total file size exceeds the limit. Please reduce the number or size of files.');
                return false;
            }
            return true;
        }
        
        

        
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Remove any existing current file info
                    const currentFile = preview.querySelector('.current-file');
                    if (currentFile) {
                        currentFile.remove();
                    }
                    
                    // Create or update image preview
                    let img = preview.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        preview.appendChild(img);
                    }
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function showFileName(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                // Remove any existing current file info
                const currentFile = preview.querySelector('.current-file');
                if (currentFile) {
                    currentFile.remove();
                }
                
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