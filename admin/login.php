<?php
session_start();
require_once "../config/database.php";

// Check if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            header("Location: dashboard.php");
            exit;
        }
    }
    
    $error = "Invalid username or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NextGen</title>
    
    
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
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 50%, var(--accent-blue) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(132, 145, 217, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.2);
            border-radius: 25px;
            padding: 50px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(132, 145, 217, 0.1) 0%, transparent 70%);
            animation: pulse 6s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(132, 145, 217, 0.4);
        }
        
        .login-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, var(--vista-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .form-group-custom {
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        .form-label-custom {
            display: block;
            color: white;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .form-input-custom {
            width: 100%;
            padding: 15px 20px;
            background: rgba(132, 145, 217, 0.1);
            border: 2px solid rgba(132, 145, 217, 0.3);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input-custom:focus {
            outline: none;
            border-color: var(--vista-blue);
            background: rgba(132, 145, 217, 0.15);
        }
        
        .form-input-custom::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .alert-custom {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        .back-to-site {
            text-align: center;
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        
        .back-to-site a {
            color: var(--vista-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-to-site a:hover {
            color: #9BA8E5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="login-title">Admin Access</h1>
            <p class="login-subtitle">Enter your credentials to continue</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert-custom">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group-custom">
                <label class="form-label-custom" for="username">
                    <i class="fas fa-user me-2"></i>Username
                </label>
                <input 
                    type="text" 
                    class="form-input-custom" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your username"
                    required
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                >
            </div>
            
            <div class="form-group-custom">
                <label class="form-label-custom" for="password">
                    <i class="fas fa-key me-2"></i>Password
                </label>
                <input 
                    type="password" 
                    class="form-input-custom" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                >
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>
        
        <div class="back-to-site">
            <a href="../index.php">
                <i class="fas fa-arrow-left me-2"></i>Back to Main Site
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php

$conn->close();
?>