<style>
/* Footer Styles */
.footer {
    background: linear-gradient(135deg, var(--primary-blue) 0%, #000 100%);
    color: white;
    padding: 70px 0 30px;
    border-top: 1px solid rgba(132, 145, 217, 0.1);
    position: relative;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--vista-blue), transparent);
}

.footer h5 {
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
    font-weight: 700;
}

.footer ul {
    list-style: none;
    padding: 0;
}

.footer ul li {
    margin-bottom: 0.8rem;
}

.footer a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer a:hover {
    color: var(--vista-blue);
}

.footer-bottom {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 1px solid rgba(132, 145, 217, 0.1);
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
}

.social-links {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.social-links a {
    width: 45px;
    height: 45px;
    background: rgba(132, 145, 217, 0.15);
    border: 1px solid rgba(132, 145, 217, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: var(--vista-blue);
    border-color: var(--vista-blue);
}

.social-links a i {
    color: white;
    transition: all 0.3s ease;
}
</style>


<footer class="footer" id="contact">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-md-4 mb-4">
                <h5 data-translate="footer-quick-links">Quick Links</h5>
                <ul>
                    <li><a href="index.php#home" data-translate="nav-home">Home</a></li>
                    <li><a href="index.php#products" data-translate="nav-products">Products</a></li>
                    <li><a href="index.php#contact" data-translate="nav-contact">Contact</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5 data-translate="footer-categories">Categories</h5>
                <ul>
                    <li><a href="index.php#products" onclick="filterProductsFromFooter('webdev-services')" data-translate="footer-webdev">Web Dev Services</a></li>
                    <li><a href="index.php#products" onclick="filterProductsFromFooter('digital-products')" data-translate="footer-digital">Digital Products</a></li>
                    <li><a href="index.php#products" onclick="filterProductsFromFooter('formations')" data-translate="footer-formations">Formations</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5 data-translate="footer-contact">Contact Info</h5>
                <ul>
                    <li><i class="fas fa-envelope me-2"></i>salembakhouche42@gmail.com</li>
                    <li><i class="fas fa-phone me-2"></i>+213 656901721</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>algiers, Algeria</li>
                </ul>
            </div>
        </div>

       
        <div class="row">
            <div class="col-12 text-center">
                <div class="social-links">
                    <a href="https://www.facebook.com/Nextgeninnovate" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <!-- <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a> -->
                    <a href="https://www.instagram.com/next.gen.innovate?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <span data-translate="footer-copyright">2025 NextGen. All rights reserved</span></p>
        </div>
    </div>
</footer>

<script>
    // Footer filter function for category links
    function filterProductsFromFooter(category) {
        event.preventDefault();
        
        // Check if we're already on index.php
        if (window.location.pathname.includes('index.php') || window.location.pathname === '/') {
            // We're on the homepage, so filter directly
            const productsSection = document.querySelector('#products');
            if (productsSection) {
                productsSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Click the corresponding category button after scroll
                setTimeout(() => {
                    const button = document.querySelector(`[data-category="${category}"]`);
                    if (button) button.click();
                }, 500);
            }
        } else {
            // We're on another page, store category and redirect
            sessionStorage.setItem('filterCategory', category);
            window.location.href = 'index.php#products';
        }
    }
</script>