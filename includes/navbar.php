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
                    <a class="nav-link" href="index.php#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#products">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact" onclick="scrollToContact(event)">Contact</a>
                </li>
            </ul>
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