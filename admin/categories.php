<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Get all categories
$categories = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - NextGen Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@10/dist/style.css" rel="stylesheet" />
    
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
            font-family: 'Inter', sans-serif;
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
            font-family: 'Inter', sans-serif;
            font-weight: 500;
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
            font-family: 'Poppins', sans-serif;
        }
        .admin-welcome p { 
            color: rgba(255, 255, 255, 0.7); 
            margin: 0; 
            font-family: 'Inter', sans-serif;
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
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }
        .admin-logout:hover { 
            background: rgba(239, 68, 68, 0.2); 
            border-color: rgba(239, 68, 68, 0.5); 
            color: #fca5a5; 
        }
        .dashboard-content { 
            padding: 30px; 
        }
        
        .page-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px; 
        }
        .page-title { 
            font-size: 2rem; 
            color: white; 
            margin: 0; 
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary-custom { 
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5); 
            border: none; 
            padding: 12px 25px; 
            border-radius: 8px; 
            color: white; 
            font-weight: 600; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            font-family: 'Inter', sans-serif;
        }
        .btn-primary-custom:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(132, 145, 217, 0.4); 
            color: white; 
        }
        
        .category-icon { 
            width: 50px; 
            height: 50px; 
            border-radius: 10px; 
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue)); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--vista-blue); 
            font-size: 1.2rem; 
        }
        
        .category-name { 
            font-family: 'Inter', sans-serif;
            font-weight: 600; 
            color: white; 
            font-size: 1rem; 
        }
        
        .category-slug { 
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem; 
            color: var(--vista-blue); 
            margin-top: 2px; 
        }
        
        .category-description {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.4;
            max-width: 300px;
        }
        
        .product-count-badge { 
            background: rgba(16, 185, 129, 0.2); 
            color: #6ee7b7; 
            border: 1px solid rgba(16, 185, 129, 0.3); 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 0.85rem; 
            font-weight: 600; 
            font-family: 'Inter', sans-serif;
        }
        
        .btn-action { 
            padding: 6px 12px; 
            border-radius: 6px; 
            font-size: 0.85rem; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
            font-family: 'Inter', sans-serif;
        }
        .btn-edit { 
            background: rgba(59, 130, 246, 0.2); 
            color: #93c5fd; 
            border: 1px solid rgba(59, 130, 246, 0.3); 
        }
        .btn-edit:hover { 
            background: rgba(59, 130, 246, 0.3); 
            color: white; 
        }
        .btn-delete { 
            background: rgba(239, 68, 68, 0.2); 
            color: #fca5a5; 
            border: 1px solid rgba(239, 68, 68, 0.3); 
        }
        .btn-delete:hover { 
            background: rgba(239, 68, 68, 0.3); 
            color: white; 
        }
        
        .empty-state { 
            text-align: center; 
            padding: 60px 20px; 
            color: rgba(255, 255, 255, 0.7); 
            font-family: 'Inter', sans-serif;
        }
        .empty-state i { 
            font-size: 4rem; 
            margin-bottom: 20px; 
            color: var(--vista-blue); 
        }

        .datatable-wrapper {
            background: rgba(1, 11, 64, 0.5) !important;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(132, 145, 217, 0.2);
            margin-bottom: 20px;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-table {
            background: transparent !important;
            color: rgba(255, 255, 255, 0.9) !important;
            border-color: rgba(132, 145, 217, 0.3) !important;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-table thead th {
            background-color: rgba(2, 19, 115, 0.7) !important;
            color: var(--vista-blue) !important;
            border-bottom: 2px solid rgba(132, 145, 217, 0.4) !important;
            font-weight: 600;
            padding: 15px 20px;
            text-align: left;
            font-family: 'Poppins', sans-serif !important;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .datatable-table thead th:hover {
            background-color: rgba(2, 19, 115, 0.9) !important;
        }

        .datatable-table thead th::after {
            content: "↕";
            position: absolute;
            right: 10px;
            opacity: 0.5;
            font-size: 0.8em;
        }

        .datatable-table thead th.asc::after {
            content: "↑";
            opacity: 1;
        }

        .datatable-table thead th.desc::after {
            content: "↓";
            opacity: 1;
        }

        .datatable-table tbody td {
            background-color: transparent !important;
            border-color: rgba(132, 145, 217, 0.15) !important;
            padding: 15px 20px;
            vertical-align: middle;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-table tbody tr:nth-child(even) {
            background-color: rgba(132, 145, 217, 0.05) !important;
        }

        .datatable-table tbody tr:hover {
            background-color: rgba(132, 145, 217, 0.1) !important;
        }

        .datatable-pagination {
            background: rgba(1, 11, 64, 0.5) !important;
            border-top: 1px solid rgba(132, 145, 217, 0.2) !important;
            padding: 20px 0 !important;
            margin-top: 20px;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-pagination a {
            color: var(--vista-blue) !important;
            background: rgba(132, 145, 217, 0.1) !important;
            border: 1px solid rgba(132, 145, 217, 0.3) !important;
            border-radius: 6px !important;
            margin: 0 2px;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-pagination a:hover {
            background: rgba(132, 145, 217, 0.2) !important;
        }

        .datatable-pagination li.active a {
            background: var(--vista-blue) !important;
            color: white !important;
            border-color: var(--vista-blue) !important;
        }

        .datatable-input {
            background: rgba(132, 145, 217, 0.1) !important;
            border: 2px solid rgba(132, 145, 217, 0.3) !important;
            border-radius: 8px !important;
            color: white !important;
            padding: 10px 15px !important;
            margin-bottom: 20px;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-input:focus {
            border-color: var(--vista-blue) !important;
            background: rgba(132, 145, 217, 0.15) !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(132, 145, 217, 0.1);
        }

        .datatable-input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .datatable-selector {
            background: rgba(132, 145, 217, 0.1) !important;
            border: 2px solid rgba(132, 145, 217, 0.3) !important;
            border-radius: 8px !important;
            color: white !important;
            padding: 8px 15px !important;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-info {
            color: rgba(255, 255, 255, 0.7) !important;
            margin: 10px 0 !important;
            font-family: 'Inter', sans-serif !important;
        }

        .datatable-table thead th:nth-child(1),
        .datatable-table thead th:nth-child(4),
        .datatable-table thead th:nth-child(5),
        .datatable-table thead th:nth-child(6) {
            text-align: center !important;
        }

        .datatable-table tbody td:nth-child(1),
        .datatable-table tbody td:nth-child(4),
        .datatable-table tbody td:nth-child(5),
        .datatable-table tbody td:nth-child(6) {
            text-align: center !important;
            vertical-align: middle !important;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-wrap: wrap;
            }
            
            .datatable-wrapper {
                padding: 15px;
            }
            
            .datatable-table thead th::after {
                display: none;
            }
            
            .category-description {
                max-width: 200px;
            }
        }

        .datatable-table thead th,
        .datatable-table tbody td {
            vertical-align: middle !important;
            text-align: center !important;
        }

        .datatable-table thead th:nth-child(2),
        .datatable-table tbody td:nth-child(2),
        .datatable-table thead th:nth-child(3),
        .datatable-table tbody td:nth-child(3) {
            text-align: left !important;
        }

        .category-name,
        .category-slug,
        .category-description {
            text-align: left;
            display: block;
        }

        .category-icon {
            margin: 0 auto;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }

        .product-count-badge {
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .datatable-table thead th::after {
            display: none !important;
        }

        .datatable-table thead th {
            cursor: default !important;
        }

        .datatable-table thead th:hover {
            background-color: rgba(2, 19, 115, 0.7) !important;
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
                        <h1>Manage Categories</h1>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    <!-- Page Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <h2 class="page-title">All Categories</h2>
                        <a href="category-add.php" class="btn-primary-custom">
                            <i class="fas fa-plus"></i>
                            Add New Category
                        </a>
                    </div>
                    
                    <!-- Categories Table -->
                    <div class="datatable-wrapper">
                        <div class="categories-table">
                            <?php if (count($categories) > 0): ?>
                                <table id="datatablesSimple" class="datatable-table">
                                    <thead>
                                        <tr>
                                            <th>Icon</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Products</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td>
                                                    <div class="category-icon">
                                                        <i class="fas fa-tag"></i>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="category-name"><?= htmlspecialchars($category['name']) ?></div>
                                                    <div class="category-slug"><?= htmlspecialchars($category['slug']) ?></div>
                                                </td>
                                                <td>
                                                    <div class="category-description">
                                                        <?= !empty($category['description']) ? htmlspecialchars($category['description']) : '<span style="color: rgba(255,255,255,0.3);">No description</span>' ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="product-count-badge">
                                                        <?= $category['product_count'] ?> product<?= $category['product_count'] != 1 ? 's' : '' ?>
                                                    </span>
                                                </td>
                                                <td data-order="<?= strtotime($category['created_at']) ?>">
                                                    <?= date('M j, Y', strtotime($category['created_at'])) ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="category-edit.php?id=<?= $category['id'] ?>" class="btn-action btn-edit" title="Edit Category">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="category-delete.php?id=<?= $category['id'] ?>" class="btn-action btn-delete" title="Delete Category">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-tags"></i>
                                    <h3>No Categories Found</h3>
                                    <p>Get started by creating your first category to organize products.</p>
                                    <a href="category-add.php" class="btn-primary-custom mt-3">
                                        <i class="fas fa-plus me-2"></i>Create Your First Category
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@9.1.0" type="text/javascript"></script>
    <script>
        // Initialize DataTable
        window.addEventListener("DOMContentLoaded", () => {
            const datatablesSimple = document.getElementById("datatablesSimple");
            if (!datatablesSimple) return;

            let dataTable = null;
            try {
                dataTable = new simpleDatatables.DataTable(datatablesSimple, {
                    sortable: false,
                    labels: {
                        placeholder: "Search categories...",
                        perPage: "Entries per page",
                        noRows: "No categories found",
                        info: "Showing {start} to {end} of {rows} entries"
                    },
                    perPage: 10,
                    perPageSelect: [5, 10, 15, 20, 25],
                    classes: {
                        active: "active",
                        disabled: "disabled",
                        selector: "form-select",
                        paginationList: "pagination",
                        paginationListItem: "page-item",
                        paginationListItemLink: "page-link"
                    }
                });

                console.info('Categories datatable initialized successfully.');
            } catch (initErr) {
                console.error('Error initializing categories datatable:', initErr);
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();