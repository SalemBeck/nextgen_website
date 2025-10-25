<?php

session_start();
require_once "../config/database.php";


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}


$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';


if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product ID'
    ]);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address'
    ]);
    exit;
}

// Check if product exists and has free option
$product_query = "SELECT id, title, description, download_link, free_option FROM products 
                  WHERE id = ? AND status = 'active' LIMIT 1";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit;
}

$product = $result->fetch_assoc();

if (!$product['free_option']) {
    echo json_encode([
        'success' => false,
        'message' => 'This product does not have a free option'
    ]);
    exit;
}

// Check if email already submitted for this product (within last 24 hours to prevent spam)
$check_query = "SELECT id FROM email_submissions 
                WHERE product_id = ? AND email = ? 
                AND submitted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("is", $product_id, $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Email already exists within 24 hours
    $already_submitted = true;
    $message = 'You already requested access to this product. Please check your email (including spam folder).';
    $email_sent = true; 
} else {
    
    $insert_query = "INSERT INTO email_submissions (product_id, email, name, submitted_at) 
                     VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iss", $product_id, $email, $name);
    
    if (!$insert_stmt->execute()) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process your request. Please try again.'
        ]);
        exit;
    }
    $already_submitted = false;
    $message = 'Success! Check your email for the download file.';
    
   
    $email_sent = sendFreeAccessEmail($email, $name, $product);
}


if ($email_sent) {
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} else {
    
    echo json_encode([
        'success' => true,
        'message' => 'Your request has been processed. If you don\'t receive an email within 5 minutes, please contact support at info@nextgen.com'
    ]);
}

$conn->close();

function sendFreeAccessEmail($to_email, $name, $product) {
    try {
        // Load PHPMailer
        require_once __DIR__ . '/../phpmailer/PHPMailer.php';
        require_once __DIR__ . '/../phpmailer/SMTP.php';
        require_once __DIR__ . '/../phpmailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings for Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nazimdz2019@gmail.com';
        $mail->Password = 'vbsy uyuw fvgl gcgy';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('noreply@nextgen.com', 'NextGen');
        $mail->addAddress($to_email, $name);
        $mail->addReplyTo('info@nextgen.com', 'NextGen Support');
        
        // Check if file exists and attach it
        $file_attached = false;
        $file_path = '';
        $file_name = '';
        
        if (!empty($product['download_link']) && $product['download_link'] !== '0') {
            // Build full file path
            $file_path = __DIR__ . '/../' . $product['download_link'];
            
            if (file_exists($file_path)) {
                $file_size = filesize($file_path);
                $max_size = 25 * 1024 * 1024; // 25MB limit for Gmail
                
                if ($file_size <= $max_size) {
                  
                    $file_name = basename($product['download_link']);
                    
                    
                    $mail->addAttachment($file_path, $file_name);
                    $file_attached = true;
                    
                    error_log("File attached: $file_name ($file_size bytes)");
                } else {
                    error_log("File too large to attach: $file_size bytes (max: $max_size)");
                }
            } else {
                error_log("File not found: $file_path");
            }
        }
        
       
        $mail->isHTML(true);
        $mail->Subject = "Free Access to " . htmlspecialchars($product['title']) . " - NextGen";
        
        
        $message = createEmailTemplate($to_email, $name, $product, $file_attached, $file_name);
        $mail->Body = $message;
        
        
        $mail->AltBody = createPlainTextEmail($name, $product, $file_attached, $file_name);
        
        
        $mail->send();
        
        
        error_log("Email sent successfully to: $to_email" . ($file_attached ? " with attachment: $file_name" : " (no attachment)"));
        return true;
        
    } catch (Exception $e) {
      
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}


function createEmailTemplate($to_email, $name, $product, $file_attached, $file_name) {
    $product_title = htmlspecialchars($product['title']);
    $display_name = !empty($name) ? htmlspecialchars($name) : 'there';
    $product_description = strip_tags(substr($product['description'], 0, 200)) . '...';
    $download_link = getDownloadLink($product);
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Your Free Access</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #010626 0%, #021373 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content { 
                padding: 40px 30px;
                background: white;
            }
            .content p {
                margin: 15px 0;
                font-size: 16px;
                line-height: 1.8;
            }
            .product-info {
                background: #f8f9ff;
                border-left: 4px solid #8491D9;
                padding: 20px;
                margin: 25px 0;
                border-radius: 5px;
            }
            .product-info h2 {
                margin: 0 0 10px;
                color: #021373;
                font-size: 20px;
            }
            .product-info p {
                margin: 5px 0;
                color: #666;
                font-size: 14px;
            }
            .attachment-box {
                background: #e8f5e9;
                border: 2px dashed #4caf50;
                border-radius: 10px;
                padding: 20px;
                text-align: center;
                margin: 25px 0;
            }
            .attachment-icon {
                font-size: 48px;
                color: #4caf50;
                margin-bottom: 10px;
            }
            .attachment-name {
                font-weight: bold;
                color: #2e7d32;
                font-size: 16px;
                margin: 10px 0;
            }
            .attachment-note {
                color: #666;
                font-size: 14px;
                margin-top: 10px;
            }
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            .button { 
                display: inline-block; 
                background: linear-gradient(135deg, #8491D9, #9BA8E5);
                color: white !important; 
                padding: 16px 40px; 
                text-decoration: none; 
                border-radius: 50px; 
                font-weight: bold;
                font-size: 18px;
                box-shadow: 0 4px 15px rgba(132, 145, 217, 0.4);
            }
            .features {
                margin: 25px 0;
            }
            .feature-item {
                padding: 10px 0;
                border-bottom: 1px solid #eee;
            }
            .feature-item:last-child {
                border-bottom: none;
            }
            .footer { 
                text-align: center; 
                padding: 30px; 
                background: #f8f9ff;
                color: #666; 
                font-size: 14px; 
            }
            .footer p {
                margin: 5px 0;
            }
            .divider {
                height: 1px;
                background: linear-gradient(to right, transparent, #ddd, transparent);
                margin: 30px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Your Free Access is Ready!</h1>
            </div>
            <div class='content'>
                <p>Hi $display_name,</p>
                <p>Thank you for your interest in our products! We're excited to give you free access to:</p>
                
                <div class='product-info'>
                    <h2>$product_title</h2>
                    <p>$product_description</p>
                </div>
                
                " . ($file_attached ? "
                <div class='attachment-box'>
                    <div class='attachment-icon'>📎</div>
                    <div class='attachment-name'>File Attached: $file_name</div>
                    <p class='attachment-note'>Your file is attached to this email. Simply download it from the attachment below!</p>
                </div>
                " : "
                <p><strong>Download your free access:</strong></p>
                <div class='button-container'>
                    <a href='$download_link' class='button' style='color: white !important;'>
                        Download Now
                    </a>
                </div>
                ") . "
                
                <div class='divider'></div>
                
                <div class='features'>
                    <h3 style='color: #021373; margin-bottom: 15px;'>What's Included:</h3>
                    <div class='feature-item'>✓ Instant access to free version</div>
                    <div class='feature-item'>✓ No credit card required</div>
                    <div class='feature-item'>✓ Free updates and support</div>
                </div>
                
                <div class='divider'></div>
                
                <p style='font-size: 14px; color: #666;'><strong>Need help?</strong> Contact us at <a href='mailto:info@nextgen.com' style='color: #8491D9;'>info@nextgen.com</a> and we'll be happy to assist you!</p>
                
                <p style='margin-top: 30px;'>Enjoy your product!</p>
                <p style='margin: 5px 0;'>Best regards,<br><strong style='color: #021373;'>The NextGen Team</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " NextGen. All rights reserved.</p>
                <p style='margin-top: 10px;'>Annaba, Algeria</p>
                <p style='margin-top: 15px; font-size: 12px;'>
                    You received this email because you requested free access to our product.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}


function createPlainTextEmail($name, $product, $file_attached, $file_name) {
    $display_name = !empty($name) ? $name : 'there';
    $download_link = getDownloadLink($product);
    
    $text = "Hi $display_name,\n\n";
    $text .= "Thank you for your interest in {$product['title']}!\n\n";
    
    if ($file_attached) {
        $text .= "Your file ($file_name) is attached to this email.\n";
        $text .= "Simply download the attachment to access your free product!\n\n";
    } else {
        $text .= "Download your free access here:\n$download_link\n\n";
    }
    
    $text .= "What's included:\n";
    $text .= "- Instant access to free version\n";
    $text .= "- No credit card required\n";
    $text .= "- Free updates and support\n\n";
    $text .= "Need help? Contact us at info@nextgen.com\n\n";
    $text .= "Best regards,\nThe NextGen Team";
    
    return $text;
}


function getDownloadLink($product) {
    if (empty($product['download_link']) || $product['download_link'] === '0') {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/product-detail.php?id=' . $product['id'];
    }
    
    if (strpos($product['download_link'], 'http') === 0) {
        return $product['download_link'];
    } else {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . '/' . ltrim($product['download_link'], '/');
    }
}
?>