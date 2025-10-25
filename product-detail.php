<?php

session_start();


require_once "config/database.php";

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch product details with category
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ? AND p.status = 'active'
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$product = $result->fetch_assoc();

// Fetch product media (images and videos)
$media_query = "SELECT * FROM product_media 
                WHERE product_id = ? 
                ORDER BY media_order ASC";
$media_stmt = $conn->prepare($media_query);
$media_stmt->bind_param("i", $product_id);
$media_stmt->execute();
$media_result = $media_stmt->get_result();

$media_items = [];
while ($media = $media_result->fetch_assoc()) {
    $media_items[] = $media;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> - NextGen</title>
    
    
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
        
        /* Breadcrumb */
        .breadcrumb-section {
            padding: 30px 0;
            background: linear-gradient(180deg, var(--accent-blue) 0%, var(--secondary-blue) 100%);
            border-bottom: 1px solid rgba(132, 145, 217, 0.1);
        }
        
        .breadcrumb {
            background: transparent;
            margin: 0;
            padding: 0;
        }
        
        .breadcrumb-item a {
            color: var(--vista-blue);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: #9BA8E5;
        }
        
        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Product Detail Section */
        .product-detail-section {
            padding: 60px 0 100px;
            background: linear-gradient(180deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
        }
        
        /* Media Gallery */
        .media-gallery {
            background: rgba(132, 145, 217, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.15);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .main-media {
            width: 100%;
            height: 500px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }
        
        .main-media img,
        .main-media video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: rgba(1,6, 38,0.8);
        }
        
        .media-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .media-thumbnails {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 10px 0;
        }
        
        .media-thumbnails::-webkit-scrollbar {
            height: 8px;
        }
        
        .media-thumbnails::-webkit-scrollbar-track {
            background: rgba(132, 145, 217, 0.1);
            border-radius: 10px;
        }
        
        .media-thumbnails::-webkit-scrollbar-thumb {
            background: var(--vista-blue);
            border-radius: 10px;
        }
        
        .thumbnail-item {
            min-width: 120px;
            height: 90px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid;
            border-color: #0a0e27;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .thumbnail-item:hover {
            border-color: var(--vista-blue);
            transform: translateY(-1px);
        }
        
        .thumbnail-item.active {
            border-color: var(--vista-blue);
            box-shadow: 0 0 4px rgba(132, 145, 217, 0.5);
        }
        
        .thumbnail-item img,
        .thumbnail-item video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: rgba(1,6, 38,0.8);
        }
        
        .thumbnail-play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }
        
        /* Product Info */
        .product-info {
            background: rgba(132, 145, 217, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.15);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
        }
        
        .category-badge {
            display: inline-block;
            background: rgba(132, 145, 217, 0.2);
            border: 1px solid rgba(132, 145, 217, 0.3);
            color: var(--vista-blue);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .product-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 800;
            line-height: 1.2;
        }
        
        .price-section {
            background: rgba(132, 145, 217, 0.1);
            border: 1px solid rgba(132, 145, 217, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .price-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .price-amount {
            font-size: 3rem;
            font-weight: 800;
            color: var(--vista-blue);
            margin-bottom: 15px;
        }
        
        .free-option-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
        }
        
        .description-section {
            margin: 30px 0;
        }
        
        .description-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: white;
        }
        
        .description-content {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            font-size: 1.05rem;
            white-space: pre-line;
        }
        
        .cta-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-primary-custom {
            flex: 1;
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5);
            border: none;
            padding: 16px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 6px rgba(132, 145, 217, 0.5);
            color: white;
        }
        
        .btn-secondary-custom {
            flex: 1;
            border: 2px solid var(--vista-blue);
            background: rgba(132, 145, 217, 0.1);
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-secondary-custom:hover {
            background: var(--vista-blue);
            box-shadow: 0 0 6px rgba(132, 145, 217, 0.5);
            transform: translateY(-2px);
            color: white;
        }
        
        @media (max-width: 768px) {
            .product-title {
                font-size: 2rem;
            }
            
            .price-amount {
                font-size: 2.5rem;
            }
            
            .main-media {
                height: 350px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/access-modal.php'; ?>
    
    <!-- Breadcrumb -->
    <section class="breadcrumb-section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php#products"><?= htmlspecialchars($product['category_name']) ?></a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($product['title']) ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Product Detail Section -->
    <section class="product-detail-section">
        <div class="container">
            <div class="row">
                
                <div class="col-lg-6">
                    <div class="media-gallery">
                       
                        <div class="main-media" id="mainMedia">
                            <?php if (count($media_items) > 0): ?>
                                <?php $first_media = $media_items[0]; ?>
                                <?php if ($first_media['media_type'] === 'image'): ?>
                                    <?php if (file_exists($first_media['file_path'])): ?>
                                        <img src="<?= htmlspecialchars($first_media['file_path']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                        <span class="media-type-badge"><i class="fas fa-image me-2"></i>Image</span>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.7);">
                                            <div style="text-align: center;">
                                                <i class="fas fa-image" style="font-size: 4rem; margin-bottom: 15px;"></i>
                                                <p>Image not found</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (file_exists($first_media['file_path'])): ?>
                                        <video controls>
                                            <source src="<?= htmlspecialchars($first_media['file_path']) ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <span class="media-type-badge"><i class="fas fa-video me-2"></i>Video</span>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.7);">
                                            <div style="text-align: center;">
                                                <i class="fas fa-video" style="font-size: 4rem; margin-bottom: 15px;"></i>
                                                <p>Video not found</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php
                                // Default icon if no media
                                $icons = [
                                    'webdev-services' => 'fa-laptop-code',
                                    'digital-products' => 'fa-box',
                                    'formations' => 'fa-graduation-cap'
                                ];
                                $icon = $icons[$product['category_slug']] ?? 'fa-cube';
                                ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                                    <i class="fas <?= $icon ?>" style="font-size: 8rem; color: rgba(255,255,255,0.3);"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Thumbnails -->
                        <?php if (count($media_items) > 1): ?>
                            <div class="media-thumbnails">
                                <?php foreach ($media_items as $index => $media): ?>
                                    <div class="thumbnail-item <?= $index === 0 ? 'active' : '' ?>" 
                                         data-type="<?= $media['media_type'] ?>"
                                         data-path="<?= htmlspecialchars($media['file_path']) ?>"
                                         onclick="changeMedia(this, <?= $index ?>)">
                                        <?php if ($media['media_type'] === 'image'): ?>
                                            <?php if (file_exists($media['file_path'])): ?>
                                                <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="Thumbnail <?= $index + 1 ?>">
                                            <?php else: ?>
                                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.5);">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (file_exists($media['file_path'])): ?>
                                                <video>
                                                    <source src="<?= htmlspecialchars($media['file_path']) ?>" type="video/mp4">
                                                </video>
                                                <div class="thumbnail-play-icon">
                                                    <i class="fas fa-play"></i>
                                                </div>
                                            <?php else: ?>
                                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: rgba(255,255,255,0.5);">
                                                    <i class="fas fa-video"></i>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info Column -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <span class="category-badge">
                            <i class="fas fa-tag me-2"></i><?= htmlspecialchars($product['category_name']) ?>
                        </span>
                        
                        <h1 class="product-title"><?= htmlspecialchars($product['title']) ?></h1>
                        
                        <div class="price-section">
                            <div class="price-amount">$<?= number_format($product['price'], 2) ?></div>
                            <?php if ($product['free_option']): ?>
                                <div class="free-option-badge">
                                    <i class="fas fa-gift me-2"></i>Free Option Available
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="description-section">
                            <h3 class="description-title"><i class="fas fa-info-circle me-2"></i>Description</h3>
                            <div class="description-content"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
                        </div>
                        
                        <div class="cta-buttons">
                            <?php if ($product['free_option']): ?>
                               <a href="#" class="btn-secondary-custom" onclick="openFreeModal(event, <?= $product['id'] ?>, '<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>', <?= $product['price'] ?>)">
    <i class="fas fa-envelope me-2"></i>Get Free Access
</a>
                            <?php endif; ?>
                            <?php if ($product['paid_option']): ?>
                                <a href="#" class="btn-primary-custom" onclick="openBuyModal(event, <?= $product['id'] ?>, '<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>', <?= $product['price'] ?>)">
    <i class="fas fa-shopping-cart me-2"></i>Buy Now
</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>

        // Change media when thumbnail is clicked
        function changeMedia(thumbnail, index) {
            const mainMedia = document.getElementById('mainMedia');
            const mediaType = thumbnail.getAttribute('data-type');
            const mediaPath = thumbnail.getAttribute('data-path');
            
            
            document.querySelectorAll('.thumbnail-item').forEach(item => {
                item.classList.remove('active');
            });
            
            
            thumbnail.classList.add('active');
            
           
            if (mediaType === 'image') {
                mainMedia.innerHTML = `
                    <img src="${mediaPath}" alt="Product Media">
                    <span class="media-type-badge"><i class="fas fa-image me-2"></i>Image</span>
                `;
            } else {
                mainMedia.innerHTML = `
                    <video controls>
                        <source src="${mediaPath}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <span class="media-type-badge"><i class="fas fa-video me-2"></i>Video</span>
                `;
            }
        }
        
        
    </script>
</body>
</html>
<?php

$conn->close();
?>