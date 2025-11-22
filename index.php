<?php

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';
require_once __DIR__ . '/classes/Cart.php';

// Get database connection
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    die("Database connection failed. Please check your configuration.");
}

$product = new Product();
$category = new Category();
$cart = new Cart();

// Get featured products
$featuredProducts = $product->getFeatured(8);

// Get categories
$categories = $category->getAll('active');

// Get cart count for logged in users
$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = $cart->getCount(getCurrentUserId());
}

$page_title = 'Home';
$extra_css = 'categories-section.css';
$extra_css = 'index.css';

require_once 'includes/header.php';
?>


<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Fresh And Organic Products For You</h1>
        <p>Get Your Groceries Delivered From Local Stores To Your Doorstep.</p>
        <a href="<?php echo SITE_URL; ?>pages/products.php" class="hero-btn">Shop Now →</a>
    </div>
</section>

<section class="categories-section container mt-4">
    <div class="section-header">
        <h2>Shop by Category</h2>
        <p>Browse our wide selection of fresh categories</p>
    </div>
    <div class="categories-grid" id="categoriesGrid">
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo SITE_URL; ?>pages/category-products.php?id=<?php echo $cat['id']; ?>" class="category-card">
                <div class="category-card-image-wrapper">
                    <?php if (!empty($cat['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($cat['image']); ?>" 
                             alt="<?php echo e($cat['name']); ?>" 
                             class="category-card-image"
                             loading="lazy">
                    <?php else: ?>
                        <div class="category-card-placeholder">
                            <span></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="category-card-body">
                    <h3 class="category-card-title"><?php echo e($cat['name']); ?></h3>
                    <?php if (!empty($cat['description'])): ?>
                        <p class="category-card-description"><?php echo e($cat['description']); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="products-section container mt-4">
    <div class="section-header">
        <h2>Featured Products</h2>
        <p>Handpicked selections just for you</p>
    </div>
    <div class="products-grid" id="productsGrid">
        <?php foreach ($featuredProducts as $prod): ?>
            <div class="product-card">
                <div class="product-card-image-wrapper">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?php echo SITE_URL; ?>public/uploads/<?php echo e($prod['image']); ?>" 
                             alt="<?php echo e($prod['name']); ?>" 
                             class="product-card-image"
                             loading="lazy">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #f3f4f6, #e5e7eb); display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 1rem;">
                            No Image
                        </div>
                    <?php endif; ?>
                    <?php 
                    $originalPrice = validatePrice($prod['price'] ?? 0);
                    $discountPrice = validatePrice($prod['discount_price'] ?? 0);
                    if ($discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0): 
                        $discountPercent = round((($originalPrice - $discountPrice) / $originalPrice) * 100);
                    ?>
                        <div class="product-card-badge">-<?php echo $discountPercent; ?>%</div>
                    <?php endif; ?>
                </div>
                <div class="product-card-body">
                    <h3 class="product-card-title"><?php echo e($prod['name']); ?></h3>
                    <p class="product-card-description"><?php echo e($prod['description'] ?? 'Fresh and organic product'); ?></p>
                    <div class="product-card-price-wrapper">
                        <?php 
                        $displayPrice = $product->getPrice($prod);
                        if ($displayPrice > 0):
                        ?>
                            <span class="product-card-price"><?php echo formatPrice($displayPrice); ?></span>
                            <?php 
                            if ($discountPrice > 0 && $discountPrice < $originalPrice && $originalPrice > 0): 
                            ?>
                                <span class="product-card-price-old"><?php echo formatPrice($originalPrice); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="product-card-actions">
                        <a href="<?php echo SITE_URL; ?>pages/product-detail.php?id=<?php echo $prod['id']; ?>" class="product-card-btn">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
(function() {
    'use strict';
    
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Animate category cards
    const categoriesGrid = document.getElementById('categoriesGrid');
    if (categoriesGrid) {
        const categoryCards = categoriesGrid.querySelectorAll('.category-card');
        categoryCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
    
    // Animate product cards
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid) {
        const productCards = productsGrid.querySelectorAll('.product-card');
        productCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
    
    // Add parallax effect to hero section on scroll
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll <= heroSection.offsetHeight) {
                heroSection.style.transform = `translateY(${currentScroll * 0.5}px)`;
                lastScroll = currentScroll;
            }
        }, { passive: true });
    }
    
    // Add smooth hover effects to buttons
    const heroBtn = document.querySelector('.hero-btn');
    if (heroBtn) {
        heroBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        heroBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }
    
    // Add ripple effect to product card buttons
    const productCardBtns = document.querySelectorAll('.product-card-btn');
    productCardBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
