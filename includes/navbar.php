<style>
/* Navbar Styles */
.navbar {
    background: rgba(1, 6, 38, 0.95);
    backdrop-filter: blur(10px);
    padding: 1rem 0;
    border-bottom: 1px solid rgba(132, 145, 217, 0.1);
    transition: all 0.3s ease;
}

.navbar.scrolled {
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
}

.navbar-brand {
    font-family: 'Poppins', sans-serif;
    font-size: 1.6rem;
    color: white !important;
    font-weight: 800;
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-icon {
    background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    margin: 0 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    padding: 0.5rem 1rem;
}

.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: var(--vista-blue);
    transition: width 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: var(--vista-blue) !important;
}

.navbar-nav .nav-link:hover::after {
    width: 80%;
}

.language-toggle-btn {
    background: rgba(132, 145, 217, 0.1);
    border: 2px solid rgba(132, 145, 217, 0.3);
    color: rgba(255, 255, 255, 0.9);
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-left: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
}

.language-toggle-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s ease;
}

.language-toggle-btn:hover {
    border-color: var(--vista-blue);
    background: rgba(132, 145, 217, 0.2);
    transform: translateY(-2px);
}

.language-toggle-btn:hover::before {
    left: 100%;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <span class="brand-icon"><i class="fas fa-cube"></i></span>
            NextGen
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php#home" data-translate="nav-home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#products" data-translate="nav-products">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact" onclick="scrollToContact(event)" data-translate="nav-contact">Contact</a>
                </li>
            </ul>
            <button class="language-toggle-btn" id="langBtn" onclick="toggleLanguage()">
                <i class="fas fa-language me-2"></i>العربية
            </button>
        </div>
    </div>
</nav>

<script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNav');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>