<footer id="contact" class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="footer-logo">
                <a href="index.php"><img src="images/logo-.png" alt="<?php echo e($site_title); ?> Logo"></a>
            </div>
            <p class="brand-tagline">Freshly baked pastries<br>made with love</p>
            <div class="social-icons">
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/" aria-label="TikTok" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div class="footer-column">
            <h3>QUICK LINKS</h3>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="index.php#about">About Us</a>
            <a href="index.php#how-to-order">How to Order</a>
        </div>
        <div class="footer-column">
            <h3>CUSTOMER SERVICE</h3>
            <a href="faq.php">FAQ</a>
            <a href="shipping.php">Shipping &amp; Delivery</a>
            <a href="returns.php">Returns</a>
            <a href="contact.php">Contact Us</a>
        </div>
        <div class="footer-column contact-column">
            <h3>CONTACT US</h3>
            <p><i class="fas fa-phone-alt"></i> <a href="tel:<?php echo e($phone_number); ?>"><?php echo e($phone_number); ?></a></p>
            <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo e($email_address); ?>"><?php echo e($email_address); ?></a></p>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo e($location); ?></p>
        </div>
    </div>
    <div class="footer-copyright">
        &copy; <?php echo e($current_year); ?> <?php echo e($site_title); ?>. All Rights Reserved.
    </div>
</footer>
<script src="js/site.js"></script>
<script src="js/validate.js"></script>
