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

// Handle form submission for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete product from database
        $delete_query = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_query->bind_param("i", $product_id);
        
        if (!$delete_query->execute()) {
            throw new Exception("Failed to delete product from database.");
        }
        
        // Delete associated image file if it exists
        if (!empty($product['image']) && file_exists("../" . $product['image'])) {
            $image_path = "../" . $product['image'];
            
            // Check if the file exists and is not a default placeholder
            if (file_exists($image_path) && !str_contains($product['image'], 'placeholder')) {
                if (!unlink($image_path)) {
                    // Log the error but don't stop the deletion process
                    error_log("Failed to delete product image: " . $image_path);
                }
            }
        }
        
        
        $conn->commit();
        
        $_SESSION['success'] = "Product '{$product['title']}' deleted successfully.";
        header("Location: products.php");
        exit;
        
    } catch (Exception $e) {
        
        $conn->rollback();
        $error = "Error deleting product: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - NextGen Admin</title>
    
    
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
        
        .admin-sidebar {
            background: linear-gradient(180deg, var(--secondary-blue) 0%, var(--accent-blue) 100%);
            min-height: 100vh;
            padding: 0;
            border-right: 1px solid rgba(132, 145, 217, 0.2);
        }
        
        .sidebar-header {
            padding: 30px 25px;
            border-bottom: 1px solid rgba(132, 145, 217, 0.2);
        }
        
        .admin-brand {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            color: white;
            font-weight: 800;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .admin-brand-icon {
            background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .nav-item-custom {
            margin-bottom: 5px;
        }
        
        .nav-link-custom {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link-custom:hover,
        .nav-link-custom.active {
            color: white;
            background: rgba(132, 145, 217, 0.15);
            border-left-color: var(--vista-blue);
        }
        
        .nav-link-custom i {
            width: 20px;
            text-align: center;
        }
        
        .admin-main {
            padding: 0;
        }
        
        .admin-header {
            background: rgba(132, 145, 217, 0.05);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(132, 145, 217, 0.1);
            padding: 20px 30px;
        }
        
        .admin-welcome h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: white;
        }
        
        .admin-welcome p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
        }
        
        .admin-logout {
            background: rgba(132, 145, 217, 0.1);
            border: 1px solid rgba(132, 145, 217, 0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }
        
        .dashboard-content {
            padding: 30px;
        }
        
        .confirmation-card {
            background: rgba(1, 11, 64, 0.5);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .warning-icon {
            font-size: 4rem;
            color: #fca5a5;
            margin-bottom: 20px;
        }
        
        .product-info {
            background: rgba(132, 145, 217, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-danger-custom:hover {
            background: linear-gradient(135deg, #b91c1c, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
            color: white;
        }
        
        .btn-secondary-custom {
            background: rgba(132, 145, 217, 0.1);
            border: 1px solid rgba(132, 145, 217, 0.3);
            padding: 12px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary-custom:hover {
            background: rgba(132, 145, 217, 0.2);
            color: white;
        }
        
        .alert-custom {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
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
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-main">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <div class="admin-welcome">
                        <h1>Delete Product</h1>
                        <p>Remove product from your store</p>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    <?php if (isset($error)): ?>
                        <div class="alert-custom">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="confirmation-card text-center">
                        <div class="warning-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        
                        <h2 class="mb-3">Confirm Deletion</h2>
                        <p class="mb-4">Are you sure you want to delete this product? This action cannot be undone.</p>
                        
                        <div class="product-info">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <div class="product-image mx-auto">
                                        <?php if (!empty($product['image']) && file_exists("../" . $product['image'])): ?>
                                            <img src="../<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                        <?php else: ?>
                                            <i class="fas fa-box"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <h4 class="text-start"><?= htmlspecialchars($product['title']) ?></h4>
                                    <div class="row text-start mt-2">
                                        <div class="col-md-6">
                                            <p><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                                            <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Status:</strong> 
                                                <span class="badge <?= $product['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($product['status']) ?>
                                                </span>
                                            </p>
                                            <p><strong>Created:</strong> <?= date('M j, Y', strtotime($product['created_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" class="d-inline">
                            <button type="submit" class="btn btn-danger-custom me-3">
                                <i class="fas fa-trash me-2"></i>
                                Yes, Delete Product
                            </button>
                        </form>
                        
                        <a href="products.php" class="btn-secondary-custom">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-circle me-2"></i>Important Note</h5>
                        <p class="mb-0">Deleting this product will also remove it from any customer views and searches. If this product has been purchased, the purchase records will remain but the product details will no longer be accessible.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php

$conn->close();
?>

