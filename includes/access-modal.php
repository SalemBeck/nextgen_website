<style>
/* Modal Styles */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(1, 6, 38, 0.95);
    backdrop-filter: blur(10px);
    z-index: 9998;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-backdrop-custom.show {
    display: block;
    opacity: 1;
}

.access-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: linear-gradient(135deg, var(--secondary-blue) 0%, var(--accent-blue) 100%);
    border: 1px solid rgba(132, 145, 217, 0.3);
    border-radius: 25px;
    padding: 0;
    max-width: 550px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 9999;
    display: none;
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.access-modal.show {
    display: block;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.modal-header-custom {
    padding: 30px 30px 20px;
    border-bottom: 1px solid rgba(132, 145, 217, 0.2);
    position: relative;
}

.modal-close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(132, 145, 217, 0.2);
    border: 1px solid rgba(132, 145, 217, 0.3);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1.2rem;
}

.modal-close-btn:hover {
    background: var(--vista-blue);
    transform: rotate(90deg);
}

.modal-title-custom {
    font-size: 1.8rem;
    font-weight: 800;
    color: white;
    margin: 0;
    padding-right: 50px;
}

.modal-subtitle {
    color: rgba(255, 255, 255, 0.7);
    margin-top: 10px;
    font-size: 0.95rem;
}

.modal-icon-header {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--oxford-blue), var(--vista-blue));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(132, 145, 217, 0.4);
}

.modal-body-custom {
    padding: 30px;
}

.form-group-custom {
    margin-bottom: 20px;
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

.price-display {
    background: rgba(132, 145, 217, 0.2);
    border: 1px solid rgba(132, 145, 217, 0.3);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    text-align: center;
}

.price-label {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
    margin-bottom: 8px;
}

.price-amount {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--vista-blue);
}

.btn-submit-custom {
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
}

.btn-submit-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.btn-submit-custom:hover {
    transform: translateY(-1px);
    box-shadow: 0 0 5px rgba(132, 145, 217, 0.5);
}

.btn-submit-custom:hover::before {
    left: 100%;
}

.btn-submit-custom:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.info-box {
    background: rgba(132, 145, 217, 0.1);
    border-left: 4px solid var(--vista-blue);
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 20px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
    line-height: 1.6;
}

.info-box i {
    color: var(--vista-blue);
    margin-right: 10px;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.benefits-list li {
    padding: 10px 0;
    color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 12px;
}

.benefits-list i {
    color: #10b981;
    font-size: 1.1rem;
}

.error-message {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.5);
    color: #fca5a5;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    display: none;
}

.success-message {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.5);
    color: #6ee7b7;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    display: none;
}

.access-modal::-webkit-scrollbar {
    display: none;
}

.access-modal {
    -ms-overflow-style: none;
    scrollbar-width: none;
}



@media (max-width: 768px) {
    .modal-title-custom {
        font-size: 1.5rem;
    }
    
    .price-amount {
        font-size: 2rem;
    }
}
</style>


<div class="modal-backdrop-custom" id="modalBackdrop" onclick="closeAllModals()"></div>

<!-- FREE ACCESS MODAL -->
<div class="access-modal" id="freeAccessModal">
    <div class="modal-header-custom">
        <button class="modal-close-btn" onclick="closeFreeModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-icon-header">
            <i class="fas fa-gift"></i>
        </div>
        <h2 class="modal-title-custom" data-translate="modal-title">Get Free Access</h2>
        <p class="modal-subtitle" id="freeModalProductTitle">Product Title</p>
    </div>

    <div class="modal-body-custom">
        <div class="error-message" id="freeErrorMessage"></div>
        <div class="success-message" id="freeSuccessMessage"></div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Get free access by providing your email. We'll send you the download link instantly!
        </div>

        <form id="freeAccessForm" onsubmit="submitFreeAccess(event)">
            <input type="hidden" id="freeProductId" name="product_id">
            
            <div class="form-group-custom">
                <label class="form-label-custom" for="freeEmail">
                    <i class="fas fa-envelope me-2"></i><span data-translate="modal-email">Email Address</span>
                </label>
                <input 
                    type="email" 
                    class="form-input-custom" 
                    id="freeEmail" 
                    name="email" 
                    placeholder="your@email.com"
                    required
                >
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom" for="freeName">
                    <i class="fas fa-user me-2"></i><span data-translate="modal-name">Your Name (Optional)</span>
                </label>
                <input 
                    type="text" 
                    class="form-input-custom" 
                    id="freeName" 
                    name="name" 
                    placeholder="John Doe"
                >
            </div>

            <ul class="benefits-list">
                <li><i class="fas fa-check-circle"></i> Instant access to free version</li>
                <li><i class="fas fa-check-circle"></i> No credit card required</li>
                <li><i class="fas fa-check-circle"></i> Unsubscribe anytime</li>
            </ul>

            <button type="submit" class="btn-submit-custom" data-translate="modal-submit">
                <i class="fas fa-paper-plane me-2"></i>Get Free Access
            </button>
        </form>
    </div>
</div>

<!-- BUY NOW MODAL -->
<div class="access-modal" id="buyNowModal">
    <div class="modal-header-custom">
        <button class="modal-close-btn" onclick="closeBuyModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-icon-header">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <h2 class="modal-title-custom">Complete Your Purchase</h2>
        <p class="modal-subtitle" id="buyModalProductTitle">Product Title</p>
    </div>

    <div class="modal-body-custom">
        <div class="error-message" id="buyErrorMessage"></div>
        <div class="success-message" id="buySuccessMessage"></div>

        <div class="price-display">
            <div class="price-label">Total Price</div>
            <div class="price-amount" id="buyModalPrice">$0.00</div>
        </div>

        <form id="buyNowForm" onsubmit="submitBuyNow(event)">
            <input type="hidden" id="buyProductId" name="product_id">
            <input type="hidden" id="buyProductPrice" name="price">
            
            <div class="form-group-custom">
                <label class="form-label-custom" for="buyEmail">
                    <i class="fas fa-envelope me-2"></i><span data-translate="modal-email">Email Address</span>
                </label>
                <input 
                    type="email" 
                    class="form-input-custom" 
                    id="buyEmail" 
                    name="email" 
                    placeholder="your@email.com"
                    required
                >
            </div>

            <div class="form-group-custom">
                <label class="form-label-custom" for="buyName">
                    <i class="fas fa-user me-2"></i>Full Name
                </label>
                <input 
                    type="text" 
                    class="form-input-custom" 
                    id="buyName" 
                    name="name" 
                    placeholder="John Doe"
                    required
                >
            </div>

            <div class="info-box">
                <i class="fas fa-shield-alt"></i>
                Secure payment powered by Stripe. Your data is encrypted and safe.
            </div>

            <ul class="benefits-list">
                <li><i class="fas fa-check-circle"></i> Full access to all features</li>
                <li><i class="fas fa-check-circle"></i> Lifetime updates</li>
                <li><i class="fas fa-check-circle"></i> Premium support</li>
                <li><i class="fas fa-check-circle"></i> 30-day money-back guarantee</li>
            </ul>

            <button type="submit" class="btn-submit-custom">
                <i class="fas fa-lock me-2"></i>Proceed to Payment
            </button>
        </form>
    </div>
</div>

<script>
// Open Free Access Modal
function openFreeModal(event, productId, productTitle, productPrice) {
    if (event) event.preventDefault();
    
    document.getElementById('freeModalProductTitle').textContent = productTitle;
    document.getElementById('freeProductId').value = productId;
    
    document.getElementById('modalBackdrop').classList.add('show');
    document.getElementById('freeAccessModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    
    hideFreeMessages();
}

// Open Buy Now Modal
function openBuyModal(event, productId, productTitle, productPrice) {
    if (event) event.preventDefault();
    
    document.getElementById('buyModalProductTitle').textContent = productTitle;
    document.getElementById('buyModalPrice').textContent = '$' + parseFloat(productPrice).toFixed(2);
    document.getElementById('buyProductId').value = productId;
    document.getElementById('buyProductPrice').value = productPrice;
    
    document.getElementById('modalBackdrop').classList.add('show');
    document.getElementById('buyNowModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    
    hideBuyMessages();
}

// Close Free Modal
function closeFreeModal() {
    document.getElementById('freeAccessModal').classList.remove('show');
    document.getElementById('modalBackdrop').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('freeAccessForm').reset();
    hideFreeMessages();
}

// Close Buy Modal
function closeBuyModal() {
    document.getElementById('buyNowModal').classList.remove('show');
    document.getElementById('modalBackdrop').classList.remove('show');
    document.body.style.overflow = 'auto';
    document.getElementById('buyNowForm').reset();
    hideBuyMessages();
}

// Close All Modals
function closeAllModals() {
    closeFreeModal();
    closeBuyModal();
}

// Submit Free Access Form
async function submitFreeAccess(event) {
    event.preventDefault();
    
    const form = document.getElementById('freeAccessForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    hideFreeMessages();
    
    try {
        const response = await fetch('api/submit-email.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showFreeSuccess('Success! Check your email for the download link.');
            form.reset();
            setTimeout(() => {
                closeFreeModal();
            }, 3000);
        } else {
            showFreeError(data.message || 'Something went wrong. Please try again.');
        }
    } catch (error) {
        showFreeError('Network error. Please check your connection and try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Get Free Access';
    }
}

// Submit Buy Now Form
async function submitBuyNow(event) {
    event.preventDefault();
    
    const form = document.getElementById('buyNowForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    hideBuyMessages();
    
    try {
        const response = await fetch('api/process-payment.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                showBuySuccess('Payment successful! Check your email for access details.');
                form.reset();
                setTimeout(() => {
                    closeBuyModal();
                }, 3000);
            }
        } else {
            showBuyError(data.message || 'Payment failed. Please try again.');
        }
    } catch (error) {
        showBuyError('Network error. Please check your connection and try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
    }
}

// Message Functions
function showFreeError(message) {
    const errorDiv = document.getElementById('freeErrorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showFreeSuccess(message) {
    const successDiv = document.getElementById('freeSuccessMessage');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
}

function hideFreeMessages() {
    document.getElementById('freeErrorMessage').style.display = 'none';
    document.getElementById('freeSuccessMessage').style.display = 'none';
}

function showBuyError(message) {
    const errorDiv = document.getElementById('buyErrorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showBuySuccess(message) {
    const successDiv = document.getElementById('buySuccessMessage');
    successDiv.textContent = message;
    successDiv.style.display = 'block';
}

function hideBuyMessages() {
    document.getElementById('buyErrorMessage').style.display = 'none';
    document.getElementById('buySuccessMessage').style.display = 'none';
}


document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});
</script>