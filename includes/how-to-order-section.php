<?php
$order_section_class = $order_section_class ?? 'how-to-order';
$customise_href = $customise_href ?? 'menu.php?category=cakes&customize=1';
?>
<section class="<?php echo e($order_section_class); ?>" id="how-to-order">
    <div class="order-header">
        <span class="line"></span>
        <h2>HOW TO ORDER</h2>
        <span class="line"></span>
    </div>
    <div class="order-steps-container">
        <a href="menu.php" class="order-step-item">
            <div class="icon-circle"><i class="fas fa-search"></i></div>
            <div class="step-text">
                <h3>Browse</h3>
                <p>Explore our pastries</p>
            </div>
        </a>
        <div class="step-connector"></div>
        <a href="<?php echo e($customise_href); ?>" class="order-step-item">
            <div class="icon-circle"><i class="fas fa-pencil-alt"></i></div>
            <div class="step-text">
                <h3>Customise</h3>
                <p>Choose your flavor &amp; design</p>
            </div>
        </a>
        <div class="step-connector"></div>
        <a href="cart.php" class="order-step-item">
            <div class="icon-circle"><i class="fas fa-shopping-cart"></i></div>
            <div class="step-text">
                <h3>Add to Cart</h3>
                <p>Review your order</p>
            </div>
        </a>
        <div class="step-connector"></div>
        <a href="checkout.php" class="order-step-item">
            <div class="icon-circle"><i class="fas fa-money-bill-wave"></i></div>
            <div class="step-text">
                <h3>Checkout</h3>
                <p>Complete your order and pay</p>
            </div>
        </a>
        <div class="step-connector"></div>
        <a href="<?php echo e(home_url()); ?>" class="order-step-item">
            <div class="icon-circle"><i class="fas fa-box-open"></i></div>
            <div class="step-text">
                <h3>Enjoy</h3>
                <p>Wait for your freshly baked treat</p>
            </div>
        </a>
    </div>
</section>
