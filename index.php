<?php

session_start();


require_once "config/database.php";

// Fetch all active products with their categories
$query = "SELECT p.*, c.name as category_name, c.slug as category_slug
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'
          ORDER BY p.created_at DESC";

$result = $conn->query($query);

if (!$result) {
    die("Error fetching products: " . $conn->error);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NextGen Digital Solutions.</title>
    
    
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
            overflow-x: hidden;
            background: var(--primary-blue);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
        
        
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 50%, var(--accent-blue) 100%);
            padding: 140px 0 120px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(132, 145, 217, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(2, 19, 115, 0.3) 0%, transparent 50%);
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(132,145,217,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-badge {
            display: inline-block;
            background: rgba(132, 145, 217, 0.15);
            border: 1px solid rgba(132, 145, 217, 0.3);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
            color: var(--vista-blue);
        }
        
        .hero-title {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, var(--vista-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
            line-height: 1.7;
        }
        
        .btn-glow {
            background: linear-gradient(135deg, var(--vista-blue), #9BA8E5);
            border: none;
            padding: 16px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-glow:hover {
            transform: translateY(-3px);
        }
        
        .btn-glow:hover::before {
            left: 100%;
        }
        
        .btn-outline-glow {
            border: 2px solid var(--vista-blue);
            color: white;
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            background: rgba(132, 145, 217, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .btn-outline-glow:hover {
            background: var(--vista-blue);
            color: white;
            transform: translateY(-3px);
        }
        
        .hero-visual {
            position: relative;
            z-index: 2;
        }
        
        .floating-card {
            background: rgba(132, 145, 217, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.2);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .floating-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(132, 145, 217, 0.4);
        }
        
        /* Products Header */
        .products-header {
            background: linear-gradient(180deg, var(--accent-blue) 0%, var(--secondary-blue) 100%);
            padding: 80px 0 50px;
            text-align: center;
            position: relative;
        }
        
        .products-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(132, 145, 217, 0.5), transparent);
        }
        
        .section-badge {
            display: inline-block;
            background: rgba(132, 145, 217, 0.15);
            border: 1px solid rgba(132, 145, 217, 0.3);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--vista-blue);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .products-header h2 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        
        .products-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Category Filter */
        .category-filter {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .category-btn {
            background: rgba(132, 145, 217, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(132, 145, 217, 0.3);
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .category-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            border-radius: 50%;
            background: var(--vista-blue);
            transition: width 0.4s ease, height 0.4s ease;
            z-index: -1;
        }
        
        .category-btn:hover::before,
        .category-btn.active::before {
            width: 100%;
            height: 300%;
        }
        
        .category-btn:hover,
        .category-btn.active {
            border-color: var(--vista-blue);
            color: white;
        }
        
        /* Products Section */
        .products-section {
            padding: 60px 0 100px;
            background: linear-gradient(180deg, var(--secondary-blue) 0%, var(--primary-blue) 100%);
        }
        
        .product-card {
            background: rgba(132, 145, 217, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(132, 145, 217, 0.15);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(132, 145, 217, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .product-card:hover {
            border-color: rgba(132, 145, 217, 0.4);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .product-card:hover::before {
            opacity: 1;
        }
        
        .product-image {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, var(--oxford-blue), var(--accent-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        
        .product-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(132, 145, 217, 0.3) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .product-image i {
            font-size: 5rem;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
            transition: transform 0.4s ease;
        }
        
        .product-card:hover .product-image i {
            transform: scale(1.1) rotate(5deg);
        }

        .product-card:hover .product-image img {
    transform: scale(1.05);
}
        
        .product-body {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .product-category {
            display: inline-block;
            background: rgba(132, 145, 217, 0.2);
            border: 1px solid rgba(132, 145, 217, 0.3);
            color: var(--vista-blue);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .product-title {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            color: white;
            font-weight: 700;
        }
        
        .product-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .product-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(132, 145, 217, 0.15);
        }
        
        .price-tag {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--vista-blue), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .free-tag {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-view {
            flex: 1;
            background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .btn-view::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-view:hover::before {
            width: 300%;
            height: 300%;
        }
        
        .btn-view:hover {
            color: white;
            transform: scale(1.05);
        }
         
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .no-products i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--vista-blue);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .products-header h2 {
                font-size: 2rem;
            }
            
            .category-filter {
                gap: 10px;
            }
            
            .category-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .floating-card {
                margin-top: 50px;
            }
        }
    </style>
    
</head>
<body>
    
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/access-modal.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    
                    <h1 class="hero-title" data-translate="hero-title">Transform Your Digital Vision Into Reality</h1>
                    <p class="hero-subtitle" data-translate="hero-subtitle">Unlock premium web development services, cutting-edge digital products, and expert training programs.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#products" class="btn btn-glow" data-translate="hero-btn-explore">
                            <i class="fas fa-rocket me-2"></i>Explore Products
                        </a>
                        <a href="#contact" class="btn btn-outline-glow" data-translate="hero-btn-contact">
                            <i class="fas fa-envelope me-2"></i>Get in Touch
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-visual">
                    <div class="floating-card">
                        <div class="floating-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h4 style="margin-bottom: 10px;" data-translate="hero-web-dev">Web Development</h4>
                        <p style="color: rgba(255,255,255,0.7); margin: 0;" data-translate="hero-web-dev-desc">Build stunning, responsive websites</p>
                    </div>
                    <div class="floating-card mt-4" style="animation-delay: 0.5s;">
                        <div class="floating-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4 style="margin-bottom: 10px;" data-translate="hero-training">Expert Training</h4>
                        <p style="color: rgba(255,255,255,0.7); margin: 0;" data-translate="hero-training-desc">Master modern development skills</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Header -->
    <section class="products-header" id="products">
        <div class="container">
            <div class="section-badge" data-translate="products-badge">
                Our Collection
            </div>
            <h2 data-translate="products-title">Featured Products & Services</h2>
            <p data-translate="products-subtitle">Discover our carefully crafted selection of digital solutions designed to elevate your business</p>
            
            <!-- Category Filter -->
            <div class="category-filter">
                <button class="category-btn active" data-category="all" data-translate="products-btn-all">
                    <i class="fas fa-th me-2"></i>All Products
                </button>
                <button class="category-btn" data-category="webdev-services" data-translate="products-btn-webdev">
                    <i class="fas fa-code me-2"></i>Web Development
                </button>
                <button class="category-btn" data-category="digital-products" data-translate="products-btn-digital">
                    <i class="fas fa-box me-2"></i>Digital Products
                </button>
                <button class="category-btn" data-category="formations" data-translate="products-btn-formations">
                    <i class="fas fa-graduation-cap me-2"></i>Formations
                </button>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <?php if (count($products) > 0): ?>
                <div class="row g-4" id="productsContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4 product-item" data-category="<?= htmlspecialchars($product['category_slug']) ?>">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>">
                                    <?php else: ?>
                                        <?php
                                       
                                        $icons = [
                                            'webdev-services' => 'fa-laptop-code',
                                            'digital-products' => 'fa-box',
                                            'formations' => 'fa-graduation-cap'
                                        ];
                                        $icon = $icons[$product['category_slug']] ?? 'fa-cube';
                                        ?>
                                        <i class="fas <?= $icon ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="product-body">
                                    <span class="product-category">
                                        <i class="fas fa-tag me-1"></i> <span data-translate="cat-<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></span>
                                    </span>
                                    <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                    <div class="product-price">
                                        <span class="price-tag">$<?= number_format($product['price'], 2) ?></span>
                                        <?php if ($product['free_option']): ?>
                                            <span class="free-tag"><i class="fas fa-gift me-1"></i><span data-translate="products-free-option">Free Option</span></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn-view" data-translate="products-btn-view">
    <i class="fas fa-eye me-2"></i>View Details
</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-inbox"></i>
                    <h3 data-translate="products-no-products">No Products Available</h3>
                    <p data-translate="products-no-products-desc">Check back soon for new products!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Translator JS -->
    <script src="assets/js/translator.js"></script>
    
    <script>

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Category filter functionality
        const categoryButtons = document.querySelectorAll('.category-btn');
        const productItems = document.querySelectorAll('.product-item');

        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                
                
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
              
                productItems.forEach(item => {
                    if (category === 'all' || item.getAttribute('data-category') === category) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'scale(1)';
                        }, 10);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });


    
        function openAccessModal(event, productName, price, productId, hasFreeOption) {
            event.preventDefault();
            alert(`Product: ${productName}\nPrice: ${price}\nProduct ID: ${productId}\nFree Option: ${hasFreeOption ? 'Yes' : 'No'}\n\nWe'll create the beautiful access modal next!`);
        }

        
        productItems.forEach(item => {
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        });
    </script>
</body>
</html>
<?php

$conn->close();
?>