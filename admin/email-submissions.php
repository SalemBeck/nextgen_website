<?php
session_start();
require_once "../config/database.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete_query = $conn->prepare("DELETE FROM email_submissions WHERE id = ?");
    $delete_query->bind_param("i", $id);
    if ($delete_query->execute()) {
        $_SESSION['success'] = "Email submission deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete email submission.";
    }
    header("Location: email-submissions.php");
    exit;
}


$total_submissions = $conn->query("SELECT COUNT(*) as count FROM email_submissions")->fetch_assoc()['count'];
$submissions_today = $conn->query("SELECT COUNT(*) as count FROM email_submissions WHERE DATE(submitted_at) = CURDATE()")->fetch_assoc()['count'];
$submissions_this_week = $conn->query("SELECT COUNT(*) as count FROM email_submissions WHERE YEARWEEK(submitted_at) = YEARWEEK(NOW())")->fetch_assoc()['count'];


$submissions = $conn->query("
    SELECT es.*, p.title as product_title, p.image as product_image, c.name as category_name
    FROM email_submissions es
    LEFT JOIN products p ON es.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY es.submitted_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Submissions - NextGen Admin</title>
    
   
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
        
        /* Sidebar */
        .admin-sidebar { background: linear-gradient(180deg, var(--secondary-blue) 0%, var(--accent-blue) 100%); min-height: 100vh; padding: 0; border-right: 1px solid rgba(132, 145, 217, 0.2); }
        .sidebar-header { padding: 30px 25px; border-bottom: 1px solid rgba(132, 145, 217, 0.2); }
        .admin-brand { font-family: 'Poppins', sans-serif; font-size: 1.5rem; color: white; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .admin-brand-icon { background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue)); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .sidebar-nav { padding: 20px 0; }
        .nav-item-custom { margin-bottom: 5px; }
        .nav-link-custom { color: rgba(255, 255, 255, 0.8); padding: 15px 25px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.3s ease; border-left: 3px solid transparent; font-weight: 500; }
        .nav-link-custom:hover, .nav-link-custom.active { color: white; background: rgba(132, 145, 217, 0.15); border-left-color: var(--vista-blue); }
        .nav-link-custom i { width: 20px; text-align: center; }
        .admin-main { padding: 0; }
        .admin-header { background: rgba(132, 145, 217, 0.05); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(132, 145, 217, 0.1); padding: 20px 30px; }
        .admin-welcome h1 { font-size: 1.8rem; margin-bottom: 5px; color: white; }
        .admin-welcome p { color: rgba(255, 255, 255, 0.7); margin: 0; }
        .admin-logout { background: rgba(132, 145, 217, 0.1); border: 1px solid rgba(132, 145, 217, 0.3); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .admin-logout:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.5); color: #fca5a5; }
        .dashboard-content { padding: 30px; }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(132, 145, 217, 0.05);
            border: 1px solid rgba(132, 145, 217, 0.15);
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .stat-info h3 {
            font-size: 2rem;
            margin: 0;
            color: var(--vista-blue);
        }
        
        .stat-info p {
            margin: 5px 0 0;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 2rem; color: white; margin: 0; }
        
        .datatable-wrapper { background: rgba(1, 11, 64, 0.5) !important; border-radius: 15px; padding: 20px; border: 1px solid rgba(132, 145, 217, 0.2); margin-bottom: 20px; }
        .datatable-table { background: transparent !important; color: rgba(255, 255, 255, 0.9) !important; border-color: rgba(132, 145, 217, 0.3) !important; }
        .datatable-table thead th { background-color: rgba(2, 19, 115, 0.7) !important; color: var(--vista-blue) !important; border-bottom: 2px solid rgba(132, 145, 217, 0.4) !important; font-weight: 600; padding: 15px 20px; text-align: left; cursor: default !important; }
        .datatable-table tbody td { background-color: transparent !important; border-color: rgba(132, 145, 217, 0.15) !important; padding: 15px 20px; vertical-align: middle; }
        .datatable-table tbody tr:nth-child(even) { background-color: rgba(132, 145, 217, 0.05) !important; }
        .datatable-table tbody tr:hover { background-color: rgba(132, 145, 217, 0.1) !important; }
        .datatable-input { background: rgba(132, 145, 217, 0.1) !important; border: 2px solid rgba(132, 145, 217, 0.3) !important; border-radius: 8px !important; color: white !important; padding: 10px 15px !important; margin-bottom: 20px; }
        .datatable-input:focus { border-color: var(--vista-blue) !important; background: rgba(132, 145, 217, 0.15) !important; outline: none; }
        .datatable-input::placeholder { color: rgba(255, 255, 255, 0.5) !important; }
        .datatable-selector { background: rgba(132, 145, 217, 0.1) !important; border: 2px solid rgba(132, 145, 217, 0.3) !important; border-radius: 8px !important; color: white !important; padding: 8px 15px !important; }
        .datatable-info { color: rgba(255, 255, 255, 0.7) !important; margin: 10px 0 !important; }
        
        .datatable-pagination { background: rgba(1, 11, 64, 0.5) !important; border-top: 1px solid rgba(132, 145, 217, 0.2) !important; padding: 20px 0 !important; margin-top: 20px; }
        .datatable-pagination a { color: var(--vista-blue) !important; background: rgba(132, 145, 217, 0.1) !important; border: 1px solid rgba(132, 145, 217, 0.3) !important; border-radius: 6px !important; margin: 0 2px; }
        .datatable-pagination a:hover { background: rgba(132, 145, 217, 0.2) !important; }
        .datatable-pagination li.active a { background: var(--vista-blue) !important; color: white !important; border-color: var(--vista-blue) !important; }
        
        .product-thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
        
        
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 5px; }
        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); cursor: pointer; }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.3); color: white; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: rgba(255, 255, 255, 0.7); }
        .empty-state i { font-size: 4rem; margin-bottom: 20px; color: var(--vista-blue); }
        
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); color: #6ee7b7; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: #fca5a5; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        
        
        .datatable-table thead th,
        .datatable-table tbody td {
            text-align: center !important;
            vertical-align: middle !important;
        }
        
        .datatable-table thead th:nth-child(2),
        .datatable-table tbody td:nth-child(2),
        .datatable-table thead th:nth-child(3),
        .datatable-table tbody td:nth-child(3) {
            text-align: left !important;
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
                        <a href="categories.php" class="nav-link-custom">
                            <i class="fas fa-tags"></i>
                            Categories
                        </a>
                    </div>
                    <div class="nav-item-custom">
                        <a href="email-submissions.php" class="nav-link-custom active">
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
                        <h1>Email Submissions</h1>
                        <p>Manage free access requests</p>
                    </div>
                    <a href="logout.php" class="admin-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
                
                <div class="dashboard-content">
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $total_submissions ?></h3>
                                <p>Total Submissions</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $submissions_today ?></h3>
                                <p>Today</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $submissions_this_week ?></h3>
                                <p>This Week</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Page Header -->
                    <div class="page-header">
                        <h2 class="page-title">All Email Submissions</h2>
                    </div>
                    
                    <!-- Submissions Table -->
                    <div class="datatable-wrapper">
                        <?php if (count($submissions) > 0): ?>
                            <table id="datatablesSimple" class="datatable-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td><?= $submission['id'] ?></td>
                                            <td style="text-align: left !important;">
                                                <strong><?= htmlspecialchars($submission['email']) ?></strong>
                                                <?php if (!empty($submission['name'])): ?>
                                                    <br><small style="color: rgba(255,255,255,0.6);"><?= htmlspecialchars($submission['name']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: left !important;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="product-thumb">
                                                        <?php if (!empty($submission['product_image']) && file_exists("../" . $submission['product_image'])): ?>
                                                            <img src="../<?= htmlspecialchars($submission['product_image']) ?>" alt="Product">
                                                        <?php else: ?>
                                                            <i class="fas fa-box"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($submission['product_title']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="color: var(--vista-blue);"><?= htmlspecialchars($submission['category_name']) ?></span>
                                            </td>
                                            <td data-order="<?= strtotime($submission['submitted_at']) ?>">
                                                <?= date('M d, Y', strtotime($submission['submitted_at'])) ?><br>
                                                <small style="color: rgba(255,255,255,0.6);"><?= date('h:i A', strtotime($submission['submitted_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="deleteSubmission(<?= $submission['id'] ?>)" class="btn-action btn-delete" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>No Email Submissions Yet</h3>
                                <p>Email submissions will appear here when users request free access to your products.</p>
                            </div>
                        <?php endif; ?>
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
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple, {
                    sortable: false,
                    labels: {
                        placeholder: "Search submissions...",
                        perPage: "Entries per page",
                        noRows: "No submissions found",
                        info: "Showing {start} to {end} of {rows} entries"
                    },
                    perPage: 10,
                    perPageSelect: [5, 10, 15, 20, 25]
                });
            }
        });
        
        // Delete submission with confirmation
        function deleteSubmission(id) {
            if (confirm('Are you sure you want to delete this email submission? This action cannot be undone.')) {
                window.location.href = 'email-submissions.php?delete=1&id=' + id;
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>