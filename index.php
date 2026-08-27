<?php
// Site configuration and dynamic options
$site_title = "Pastry Haven";
$phone_number = "09263445337";
$email_address = "pastryhaven@gmail.com";
$location = "Dumaguete City";
$current_year = date("Y");
?>

<?php
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="logo">
            <img src="images/logo-.png" alt="Pastry Haven Logo">
        </div>
        <nav>
            <a class="active">HOME</a>
            <a >MENU</a>
            <a >HOW TO ORDER</a>
            <a >ABOUT US</a>
            <a >CONTACT</a>
        </nav>
        <div class="icons-container">
            <a href="#search" class="icon-link"><i class="fas fa-search"></i></a>
            <a href="#cart" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
            <a href="#account" class="icon-link"><i class="fas fa-user"></i></a>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-content">
            <p class="small-title">FRESHLY BAKED, EVERYDAY</p>
            <h1>Made With Love,<br>Baked For You.</h1>
            <p class="hero-description">Fresh pastries made with care, from our kitchen to your table.</p>
            <div class="hero-buttons">
                <a class="btn primary-btn">SHOP NOW</a>
                <a class="btn secondary-btn">EXPLORE MENU</a>
            </div>
        </div>

    </section>

    <!-- WHAT ARE YOU CRAVING SECTION -->
<section class="cravings">
    <div class="cravings-header">
        <h2>What Are You Craving?</h2>
        <div class="heart-divider">
            <span class="line"></span>
            <i class="fas fa-heart"></i>
            <span class="line"></span>
        </div>
    </div>

    <div class="cravings-grid">
        <!-- Category 1 -->
        <a class="craving-item">
            <div class="craving-circle">
                <img src="images/cake.jfif" alt="Cakes">
            </div>
            <h3>CAKES</h3>
        </a>

        <!-- Category 2 -->
        <a class="craving-item">
            <div class="craving-circle">
                <img src="images/bread.jfif" alt="Breads">
            </div>
            <h3>BREADS</h3>
        </a>

        <!-- Category 3 -->
        <a class="craving-item">
            <div class="craving-circle">
                <img src="images/cookies.jfif" alt="Cookies">
            </div>
            <h3>COOKIES</h3>
        </a>

        <!-- Category 4 -->
        <a class="craving-item">
            <div class="craving-circle">
                <img src="images/cupcake.jfif" alt="Cupcakes">
            </div>
            <h3>CUPCAKES</h3>
        </a>
    </div>
</section>

    <!-- BEST SELLERS SECTION -->
<section class="best-sellers">
    <div class="section-title-row">
        <h2>BEST SELLERS</h2>
        <a class="view-all">VIEW ALL &rarr;</a>
    </div>
    
    <div class="product-grid">
        <!-- Card 1 -->
        <div class="product-card">
            <div class="product-image">
                <button class="heart-btn" aria-label="Add to Favorites">
                    <i class="far fa-heart"></i>
                </button>
                <img src="images/choco_cake.png" alt="Chocolate Cake">
            </div>
            <div class="product-info">
                <h3>Chocolate Cake</h3>
                <p class="price">₱570.00</p>
                <div class="reviews">
                    <span class="hearts">
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                    </span>
                    <span class="count">(964)</span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="product-card">
            <div class="product-image">
                <button class="heart-btn" aria-label="Add to Favorites">
                    <i class="far fa-heart"></i>
                </button>
                <img src="images/red_cookies.png" alt="Red Velvet Cookie">
            </div>
            <div class="product-info">
                <h3>Red Velvet Cookie</h3>
                <p class="price">₱45.00</p>
                <div class="reviews">
                    <span class="hearts">
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                    </span>
                    <span class="count">(1369)</span>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="product-card">
            <div class="product-image">
                <button class="heart-btn" aria-label="Add to Favorites">
                    <i class="far fa-heart"></i>
                </button>
                <img src="images/berry_cupcake.png" alt="Strawberry Cupcake">
            </div>
            <div class="product-info">
                <h3>Strawberry Cupcake</h3>
                <p class="price">₱50.00</p>
                <div class="reviews">
                    <span class="hearts">
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                    </span>
                    <span class="count">(1587)</span>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="product-card">
            <div class="product-image">
                <button class="heart-btn" aria-label="Add to Favorites">
                    <i class="far fa-heart"></i>
                </button>
                <img src="images/croissant-.png" alt="Croissant">
            </div>
            <div class="product-info">
                <h3>Croissant</h3>
                <p class="price">₱80.00</p>
                <div class="reviews">
                    <span class="hearts">
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                        <i class="fas fa-heart"></i>
                    </span>
                    <span class="count">(853)</span>
                </div>
            </div>
        </div>
    </div>
</section>

   <!-- BUILD YOUR OWN SECTION -->
<section class="build-section-wrapper">
    <div class="build-card">
        <!-- Left Side: Title, Description, Image, CTA -->
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

            <button class="cta-button">CREATE MY TREAT</button>
        </div>

        <!-- Right Side: Step-by-Step Selection Form -->
        <div class="build-right">
            <!-- Step 01 -->
            <div class="step-row">
                <div class="step-label">
                    <span class="step-num">01</span>
                    <span class="step-title">Choose your flavor</span>
                </div>
                <div class="step-options">
                    <button class="pill-btn active">Chocolate</button>
                    <button class="pill-btn">Vanilla</button>
                    <button class="pill-btn">Strawberry</button>
                    <button class="pill-btn">Red Velvet</button>
                </div>
            </div>

            <!-- Step 02 -->
            <div class="step-row">
                <div class="step-label">
                    <span class="step-num">02</span>
                    <span class="step-title">Choose your design</span>
                </div>
                <div class="step-options">
                    <button class="pill-btn active">Add Your Reference</button>
                </div>
            </div>

            <!-- Step 03 -->
            <div class="step-row">
                <div class="step-label">
                    <span class="step-num">03</span>
                    <span class="step-title">Choose a size</span>
                </div>
                <div class="step-options">
                    <button class="pill-btn active">Small</button>
                    <button class="pill-btn">Medium</button>
                    <button class="pill-btn">Large</button>
                </div>
            </div>

            <!-- Step 04 -->
            <div class="step-row">
                <div class="step-label">
                    <span class="step-num">04</span>
                    <span class="step-title">Write your dedications <small>(Optional)</small></span>
                </div>
                <div class="step-input">
                    <input type="text" placeholder="Happy Birthday!" value="Happy Birthday!">
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- ABOUT US SECTION -->
<section class="about" id="about">
    <div class="about-container">
        <!-- Left Side: Image -->
        <div class="about-image-wrapper">
            <img src="images/about us.jfif" alt="Baking Kitchen with Pink Mixer">
        </div>

        <!-- Right Side: Content -->
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
                <!-- Features / Icons Row -->
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

                <!-- Learn More Button -->
                <a class="learn-more-btn">
                    Learn More..... <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

    <!-- HOW TO ORDER SECTION -->
<section class="how-to-order" id="how-to-order">
    <!-- Header with flanking horizontal lines -->
    <div class="order-header">
        <span class="line"></span>
        <h2>HOW TO ORDER</h2>
        <span class="line"></span>
    </div>

    <!-- Stepper Container -->
    <div class="order-steps-container">
        <!-- Step 1 -->
        <div class="order-step-item">
            <div class="icon-circle">
                <i class="fas fa-search"></i>
            </div>
            <div class="step-text">
                <h3>Browse</h3>
                <p>Explore our pastries</p>
            </div>
        </div>

        <div class="step-connector"></div>

        <!-- Step 2 -->
        <div class="order-step-item">
            <div class="icon-circle">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="step-text">
                <h3>Customise</h3>
                <p>Choose your flavor &amp; design</p>
            </div>
        </div>

        <div class="step-connector"></div>

        <!-- Step 3 -->
        <div class="order-step-item">
            <div class="icon-circle">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="step-text">
                <h3>Add to Cart</h3>
                <p>Review your order</p>
            </div>
        </div>

        <div class="step-connector"></div>

        <!-- Step 4 -->
        <div class="order-step-item">
            <div class="icon-circle">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="step-text">
                <h3>Checkout</h3>
                <p>Complete your order and pay</p>
            </div>
        </div>

        <div class="step-connector"></div>

        <!-- Step 5 -->
        <div class="order-step-item">
            <div class="icon-circle">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="step-text">
                <h3>Enjoy</h3>
                <p>Wait for your freshly baked treat</p>
            </div>
        </div>
    </div>
</section>

    <!-- FOOTER SECTION -->
<footer id="contact" class="site-footer">
    <div class="footer-container">
        <!-- Brand / Logo Column -->
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="images/logo-.png" alt="<?php echo $site_title; ?> Logo">
            </div>
            <p class="brand-tagline">Freshly baked pastries<br>made with love</p>
            <div class="social-icons">
                <a aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>

        <!-- Quick Links Column -->
        <div class="footer-column">
            <h3>QUICK LINKS</h3>
            <a >Home</a>
            <a >Menu</a>
            <a >About Us</a>
            <a >How to Order</a>
        </div>

        <!-- Customer Service Column -->
        <div class="footer-column">
            <h3>CUSTOMER SERVICE</h3>
            <a >FAQ</a>
            <a >Shipping &amp; Delivery</a>
            <a >Returns</a>
            <a >Contact Us</a>
        </div>

        <!-- Contact Us Column -->
        <div class="footer-column contact-column">
            <h3>CONTACT US</h3>
            <p><i class="fas fa-phone-alt"></i> <?php echo $phone_number; ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo $email_address; ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo $location; ?></p>
        </div>
    </div>

    <!-- Copyright -->
    <div class="footer-copyright">
        &copy; <?php echo $current_year; ?> <?php echo $site_title; ?>. All Rights Reserved.
    </div>
</footer>

</body>
