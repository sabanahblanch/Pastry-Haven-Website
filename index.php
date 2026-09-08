<?php
require_once __DIR__ . '/includes/config.php';
$active_nav = 'home';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php require __DIR__ . '/includes/site-nav.php'; ?>

    <?php if ($flash): ?>
        <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?> home-flash"><?php echo e($flash['message']); ?></p>
    <?php endif; ?>

    <section class="hero" id="home">
        <div class="hero-content">
            <p class="small-title">FRESHLY BAKED, EVERYDAY</p>
            <h1>Made With Love,<br>Baked For You.</h1>
            <p class="hero-description">Fresh pastries made with care, from our kitchen to your table.</p>
            <div class="hero-buttons">
                <a href="menu.php" class="btn primary-btn">SHOP NOW</a>
                <a href="menu.php" class="btn secondary-btn">EXPLORE MENU</a>
            </div>
        </div>
    </section>

    <section class="cravings" id="menu">
        <div class="cravings-header">
            <h2>What Are You Craving?</h2>
            <div class="heart-divider">
                <span class="line"></span>
                <i class="fas fa-heart"></i>
                <span class="line"></span>
            </div>
        </div>
        <div class="cravings-grid">
            <a href="menu.php?category=cakes" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cake.jfif" alt="Cakes">
                </div>
                <h3>CAKES</h3>
            </a>
            <a href="menu.php?category=breads" class="craving-item">
                <div class="craving-circle">
                    <img src="images/bread.jfif" alt="Breads">
                </div>
                <h3>BREADS</h3>
            </a>
            <a href="menu.php?category=cookies" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cookies.jfif" alt="Cookies">
                </div>
                <h3>COOKIES</h3>
            </a>
            <a href="menu.php?category=cupcakes" class="craving-item">
                <div class="craving-circle">
                    <img src="images/cupcake.jfif" alt="Cupcakes">
                </div>
                <h3>CUPCAKES</h3>
            </a>
        </div>
    </section>

    <section class="best-sellers">
        <div class="section-title-row">
            <h2>BEST SELLERS</h2>
            <a href="menu.php" class="view-all">VIEW ALL &rarr;</a>
        </div>
        <div class="product-grid">
            <?php foreach (featured_products() as $product) {
                echo render_product_card($product);
            } ?>
        </div>
    </section>

    <section class="build-section-wrapper" id="build">
        <form class="build-card" method="post" action="cart.php" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add_custom">
            <input type="hidden" name="flavor" id="custom-flavor" value="Chocolate">
            <input type="hidden" name="size" id="custom-size" value="Small">

            <div class="build-left">
                <div class="build-header">
                    <h1>BUILD YOUR OWN</h1>
                    <h2>Flavor &amp; Design</h2>
                    <p class="subtext">Create a sweet treat that's uniquely yours.</p>
                    <div class="divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <div class="cake-image-container">
                    <img src="images/dark choco.png" alt="Custom Black Forest Cake">
                </div>
                <button type="submit" class="cta-button">CREATE MY TREAT</button>
            </div>

            <div class="build-right">
                <div class="step-row">
                    <div class="step-label">
                        <span class="step-num">01</span>
                        <span class="step-title">Choose your flavor</span>
                    </div>
                    <div class="step-options" data-input="custom-flavor">
                        <button type="button" class="pill-btn active" data-value="Chocolate">Chocolate</button>
                        <button type="button" class="pill-btn" data-value="Vanilla">Vanilla</button>
                        <button type="button" class="pill-btn" data-value="Strawberry">Strawberry</button>
                        <button type="button" class="pill-btn" data-value="Red Velvet">Red Velvet</button>
                    </div>
                </div>

                <div class="step-row">
                    <div class="step-label">
                        <span class="step-num">02</span>
                        <span class="step-title">Choose your design</span>
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
                        <button type="button" class="pill-btn active" data-value="Small">Small</button>
                        <button type="button" class="pill-btn" data-value="Medium">Medium</button>
                        <button type="button" class="pill-btn" data-value="Large">Large</button>
                    </div>
                </div>

                <div class="step-row">
                    <div class="step-label">
                        <span class="step-num">04</span>
                        <span class="step-title">Write your dedications <small>(Optional)</small></span>
                    </div>
                    <div class="step-input">
                        <input type="text" name="dedication" placeholder="Happy Birthday!" maxlength="80" value="Happy Birthday!">
                    </div>
                </div>
            </div>
        </form>
    </section>

    <section class="about" id="about">
        <div class="about-container">
            <div class="about-image-wrapper">
                <img src="images/about us.jfif" alt="Baking Kitchen with Pink Mixer">
            </div>
            <div class="about-content">
                <div class="about-header">
                    <h2>ABOUT US</h2>
                    <div class="small-divider">
                        <span class="line"></span>
                        <i class="fas fa-heart"></i>
                        <span class="line"></span>
                    </div>
                </div>
                <h3 class="about-subtitle">A Little Taste of Home</h3>
                <p class="about-text">
                    Pastry Haven was created from our passion for baking and our desire to make enjoying pastries easier and more convenient. We offer homemade, freshly baked, and customizable treats that you can browse and order online from the comfort of your home. Whether you're a student, a family, celebrating a special occasion, or simply craving something sweet, we want you to feel welcomed, comfortable, and satisfied. With customizable flavors, cake designs, toppings, sizes, and messages, every treat can be made your way. Above all, Pastry Haven hopes to create unforgettable flavors and a feeling of home in every bite.
                </p>
                <div class="about-footer-row">
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-utensils"></i>
                            <span>Quality Ingredients</span>
                        </div>
                        <div class="feature-item">
                            <i class="far fa-heart"></i>
                            <span>Made with Love</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-cookie"></i>
                            <span>Freshly Baked</span>
                        </div>
                    </div>
                    <a href="contact.php" class="learn-more-btn">
                        Learn More..... <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="how-to-order" id="how-to-order">
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
            <a href="#build" class="order-step-item">
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
            <a href="index.php" class="order-step-item">
                <div class="icon-circle"><i class="fas fa-box-open"></i></div>
                <div class="step-text">
                    <h3>Enjoy</h3>
                    <p>Wait for your freshly baked treat</p>
                </div>
            </a>
        </div>
    </section>

    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
