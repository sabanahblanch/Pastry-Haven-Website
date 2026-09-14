<?php
global $custom_cake_prices;
$custom_flavors = ['Chocolate', 'Vanilla', 'Strawberry', 'Red Velvet'];
$custom_sizes = ['Small', 'Medium', 'Large'];
$customize_return = $customize_return ?? 'menu.php?category=cakes&customize=1';
$customize_form_class = $customize_form_class ?? 'build-card';
?>
<form class="<?php echo e($customize_form_class); ?>" method="post" action="cart.php" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="add_custom">
    <input type="hidden" name="return" value="<?php echo e($customize_return); ?>">
    <input type="hidden" name="flavor" id="custom-flavor" value="Chocolate">
    <input type="hidden" name="size" id="custom-size" value="Small">

    <div class="build-left">
        <div class="build-header">
            <h1 id="customize-cake-title">CUSTOMIZE CAKE</h1>
            <h2>Flavor &amp; Design</h2>
            <p class="subtext">Create a cake that's uniquely yours, then add it to your cart.</p>
            <div class="divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <div class="cake-image-container">
            <img src="images/dark choco.png" alt="Custom cake">
        </div>
        <button type="submit" class="cta-button">ADD TO CART</button>
    </div>

    <div class="build-right">
        <div class="step-row">
            <div class="step-label">
                <span class="step-num">01</span>
                <span class="step-title">Choose your flavor</span>
            </div>
            <div class="step-options" data-input="custom-flavor">
                <?php foreach ($custom_flavors as $index => $flavor): ?>
                    <button type="button" class="pill-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-value="<?php echo e($flavor); ?>"><?php echo e($flavor); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-row">
            <div class="step-label">
                <span class="step-num">02</span>
                <span class="step-title">Upload a reference photo</span>
            </div>
            <div class="step-options">
                <button type="button" class="pill-btn active" id="reference-btn">Add Your Reference</button>
                <input type="file" name="reference" id="custom-reference" accept="image/jpeg,image/png,image/webp,image/gif,.jfif" hidden>
            </div>
        </div>

        <div class="step-row">
            <div class="step-label">
                <span class="step-num">03</span>
                <span class="step-title">Choose a size</span>
            </div>
            <div class="step-options" data-input="custom-size">
                <?php foreach ($custom_sizes as $index => $size): ?>
                    <button type="button" class="pill-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-value="<?php echo e($size); ?>">
                        <?php echo e($size); ?> · <?php echo format_price($custom_cake_prices[$size]); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="step-row">
            <div class="step-label">
                <span class="step-num">04</span>
                <span class="step-title">Write your dedication <small>(Optional)</small></span>
            </div>
            <div class="step-input">
                <input type="text" name="dedication" placeholder="Happy Birthday!" maxlength="80">
            </div>
        </div>
    </div>
</form>
