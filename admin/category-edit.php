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

// Fetch category details
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

// Get product count for this category
$product_count_query = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
$product_count_query->bind_param("i", $category_id);
$product_count_query->execute();
$product_count_result = $product_count_query->get_result();
$product_count = $product_count_result->fetch_assoc()['product_count'];

// Handle form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($name)) {
        $errors[] = "Category name is required";
    }
    
    if (empty($slug)) {
        $errors[] = "Category slug is required";
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors[] = "Slug can only contain lowercase letters, numbers, and hyphens";
    }
    
    // Check if slug already exists (excluding current category)
    $check_slug = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $check_slug->bind_param("si", $slug, $category_id);
    $check_slug->execute();
    $check_slug->store_result();
    
    if ($check_slug->num_rows > 0) {
        $errors[] = "Slug already exists. Please choose a different one.";
    }
    
    // If no errors, update category
    if (empty($errors)) {
        $query = "UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $name, $slug, $description, $category_id);
        
        if ($stmt->execute()) {
            $success = "Category updated successfully!";
            // Refresh category data
            $category_query->execute();
            $category_result = $category_query->get_result();
            $category = $category_result->fetch_assoc();
        } else {
            $errors[] = "Failed to update category: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - NextGen Admin</title>
    
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
        
        .slug-preview {
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--vista-blue);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .slug-preview i {
            color: rgba(132, 145, 217, 0.7);
        }
        
        .category-info-box {
            background: rgba(132, 145, 217, 0.1);
            border: 1px solid rgba(132, 145, 217, 0.2);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(132, 145, 217, 0.1);
        }
        
        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .info-label {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }
        
        .info-value {
            color: white;
            font-weight: 600;
        }
        
        .product-count-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
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
                        <h1>Edit Category</h1>
                        <p>Update category information</p>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    <!-- Back to Categories -->
                    <div class="mb-4">
                        <a href="categories.php" class="btn-cancel-custom">
                            <i class="fas fa-arrow-left me-2"></i>Back to Categories
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
                    
                    <!-- Category Information Box -->
                    <div class="category-info-box">
                        <h4 class="mb-3" style="color: var(--vista-blue);">
                            <i class="fas fa-info-circle me-2"></i>Category Information
                        </h4>
                        <div class="info-item">
                            <span class="info-label">Category ID:</span>
                            <span class="info-value">#<?= $category['id'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Products in this category:</span>
                            <span class="info-value">
                                <span class="product-count-badge">
                                    <?= $product_count ?> product<?= $product_count != 1 ? 's' : '' ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Created:</span>
                            <span class="info-value"><?= date('M j, Y g:i A', strtotime($category['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <!-- Category Form -->
                    <div class="form-container">
                        <form method="POST" action="">
                            <!-- Basic Information -->
                            <div class="form-section">
                                <h3 class="section-title">
                                    <i class="fas fa-edit"></i>
                                    Edit Category Details
                                </h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="name">Category Name *</label>
                                            <input 
                                                type="text" 
                                                class="form-input-custom" 
                                                id="name" 
                                                name="name" 
                                                placeholder="Enter category name"
                                                required
                                                value="<?= htmlspecialchars($category['name']) ?>"
                                                oninput="generateSlug(this.value)"
                                            >
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group-custom">
                                            <label class="form-label-custom" for="slug">Category Slug *</label>
                                            <input 
                                                type="text" 
                                                class="form-input-custom" 
                                                id="slug" 
                                                name="slug" 
                                                placeholder="category-slug"
                                                required
                                                pattern="[a-z0-9-]+"
                                                title="Slug can only contain lowercase letters, numbers, and hyphens"
                                                value="<?= htmlspecialchars($category['slug']) ?>"
                                            >
                                            <div class="slug-preview">
                                                <i class="fas fa-link"></i>
                                                <span id="slugPreview">URL: yoursite.com/categories/<?= htmlspecialchars($category['slug']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="description">Category Description</label>
                                    <textarea 
                                        class="form-textarea-custom" 
                                        id="description" 
                                        name="description" 
                                        placeholder="Describe this category (optional)..."
                                        rows="4"
                                    ><?= htmlspecialchars($category['description']) ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="form-section text-end">
                                <a href="categories.php" class="btn-cancel-custom me-3">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn-submit-custom">
                                    <i class="fas fa-check me-2"></i>Update Category
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
        function generateSlug(name) {
            const slugInput = document.getElementById('slug');
            const slugPreview = document.getElementById('slugPreview');
            
            // Only generate slug if the slug field matches the auto-generated pattern
            const currentSlug = '<?= $category['slug'] ?>';
            const autoGeneratedSlug = slugify('<?= $category['name'] ?>');
            
            if (slugInput.value === currentSlug && currentSlug === autoGeneratedSlug) {
                const slug = slugify(name);
                slugInput.value = slug;
                slugPreview.textContent = 'URL: yoursite.com/categories/' + slug;
            }
        }
        
        function slugify(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        }
        
        // Update slug preview when slug input changes
        document.getElementById('slug').addEventListener('input', function() {
            const slugPreview = document.getElementById('slugPreview');
            slugPreview.textContent = 'URL: yoursite.com/categories/' + this.value;
        });
        
        // Initialize slug preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            const slugInput = document.getElementById('slug');
            const slugPreview = document.getElementById('slugPreview');
            slugPreview.textContent = 'URL: yoursite.com/categories/' + slugInput.value;
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>