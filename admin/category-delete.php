<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Check if category ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Category ID is required.";
    header("Location: categories.php");
    exit;
}

$category_id = intval($_GET['id']);

$category_query = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$category_query->bind_param("i", $category_id);
$category_query->execute();
$category_result = $category_query->get_result();

if ($category_result->num_rows === 0) {
    $_SESSION['error'] = "Category not found.";
    header("Location: categories.php");
    exit;
}

$category = $category_result->fetch_assoc();

$product_count_query = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
$product_count_query->bind_param("i", $category_id);
$product_count_query->execute();
$product_count_result = $product_count_query->get_result();
$product_count = $product_count_result->fetch_assoc()['product_count'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if category has products
        if ($product_count > 0) {
            throw new Exception("Cannot delete category that contains products. Please reassign or delete the products first.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete category from database
        $delete_query = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $delete_query->bind_param("i", $category_id);
        
        if (!$delete_query->execute()) {
            throw new Exception("Failed to delete category from database.");
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Category '{$category['name']}' deleted successfully.";
        header("Location: categories.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Category - NextGen Admin</title>
    
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
        
        .category-info {
            background: rgba(132, 145, 217, 0.1);
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .category-icon {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--vista-blue);
            font-size: 2rem;
            margin: 0 auto 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .info-item {
            background: rgba(132, 145, 217, 0.05);
            border: 1px solid rgba(132, 145, 217, 0.1);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .info-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .product-count-badge {
            background: <?= $product_count > 0 ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)' ?>;
            color: <?= $product_count > 0 ? '#fca5a5' : '#6ee7b7' ?>;
            border: 1px solid <?= $product_count > 0 ? 'rgba(239, 68, 68, 0.3)' : 'rgba(16, 185, 129, 0.3)' ?>;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            <?= $product_count > 0 ? 'opacity: 0.6; cursor: not-allowed;' : '' ?>
        }
        
        .btn-danger-custom:hover:not(:disabled) {
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
        
        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 10px;
            padding: 20px;
            color: #fcd34d;
        }
        
        .products-warning {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
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
                        <a href="products.php" class="nav-link-custom">
                            <i class="fas fa-box"></i>
                            Products
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="categories.php" class="nav-link-custom active">
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
                        <h1>Delete Category</h1>
                        <p>Remove category from your store</p>
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
                        
                        <h2 class="mb-3">Confirm Category Deletion</h2>
                        <p class="mb-4">Are you sure you want to delete this category? This action cannot be undone.</p>
                        
                        <div class="category-info">
                            <div class="category-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h3 class="mb-2"><?= htmlspecialchars($category['name']) ?></h3>
                            <p class="text-muted mb-4"><?= htmlspecialchars($category['slug']) ?></p>
                            
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">Category ID</div>
                                    <div class="info-value">#<?= $category['id'] ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Products</div>
                                    <div class="info-value">
                                        <span class="product-count-badge">
                                            <?= $product_count ?> product<?= $product_count != 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Created</div>
                                    <div class="info-value"><?= date('M j, Y', strtotime($category['created_at'])) ?></div>
                                </div>
                            </div>
                            
                            <?php if (!empty($category['description'])): ?>
                                <div class="mt-4 p-3 bg-dark rounded">
                                    <div class="info-label text-start mb-2">Description</div>
                                    <p class="text-start mb-0"><?= htmlspecialchars($category['description']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($product_count > 0): ?>
                            <div class="products-warning">
                                <h5 class="text-warning mb-3">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    Cannot Delete Category
                                </h5>
                                <p class="mb-3">This category contains <strong><?= $product_count ?> product<?= $product_count != 1 ? 's' : '' ?></strong>. You must either:</p>
                                <ul class="text-start">
                                    <li>Delete all products in this category first</li>
                                    <li>Reassign the products to a different category</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="products.php?category=<?= $category['id'] ?>" class="btn-secondary-custom me-2">
                                        <i class="fas fa-box me-2"></i>View Products
                                    </a>
                                    <a href="categories.php" class="btn-secondary-custom">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Categories
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="d-inline">
                                <button type="submit" class="btn btn-danger-custom me-3">
                                    <i class="fas fa-trash me-2"></i>
                                    Yes, Delete Category
                                </button>
                            </form>
                            
                            <a href="categories.php" class="btn-secondary-custom">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product_count === 0): ?>
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-circle me-2"></i>Important Note</h5>
                            <p class="mb-0">Deleting this category will permanently remove it from your system. This action cannot be undone, and the category will no longer be available for product organization.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();