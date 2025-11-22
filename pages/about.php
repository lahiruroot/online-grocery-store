<?php
require_once '../config/db.php';
require_once '../config/constants.php';
require_once '../config/functions.php';

$page_title = 'About Us';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div style="max-width: 800px; margin: 0 auto;">
        <h1>About FreshCart</h1>
        
        <div class="card mt-4">
            <div class="card-body">
                <h2>Our Mission</h2>
                <p>At FreshCart, we're dedicated to bringing fresh, high-quality groceries directly to your doorstep. We believe that everyone deserves access to fresh, healthy products at affordable prices.</p>
                
                <h2 style="margin-top: 2rem;">Why Choose Us?</h2>
                <ul style="list-style-position: inside; line-height: 1.8;">
                    <li>Fresh products sourced directly from farms</li>
                    <li>Competitive prices and regular discounts</li>
                    <li>Fast and reliable delivery</li>
                    <li>24/7 customer support</li>
                    <li>Easy and secure checkout</li>
                    <li>Wide variety of products</li>
                </ul>

                <h2 style="margin-top: 2rem;">Contact Us</h2>
                <p>
                    <strong>Email:</strong> info@freshcart.com<br>
                    <strong>Phone:</strong> 1-800-FRESHCART<br>
                    <strong>Address:</strong> 123 Market Street, City, State 12345
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
