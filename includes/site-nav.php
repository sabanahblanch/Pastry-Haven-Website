<?php
$active_nav = $active_nav ?? '';
$cart_n = cart_count();
$fav_n = count(get_favorites());
$logged_in = is_logged_in();
$admin = is_admin();
$home = home_url();
$inbox_n = ($logged_in && function_exists('inbox_unread_count')) ? inbox_unread_count() : 0;
?>
<header class="navbar">
    <div class="logo">
        <a href="<?php echo e($home); ?>"><img src="images/logo-.png" alt="Pastry Haven Logo"></a>
    </div>
    <nav>
        <a href="index.php" class="<?php echo $active_nav === 'home' ? 'active' : ''; ?>">HOME</a>
        <a href="menu.php" class="<?php echo $active_nav === 'menu' ? 'active' : ''; ?>">MENU</a>
        <a href="about.php" class="<?php echo $active_nav === 'about' ? 'active' : ''; ?>">ABOUT US</a>
        <a href="how-to-order.php" class="<?php echo $active_nav === 'order' ? 'active' : ''; ?>">HOW TO ORDER</a>
        <a href="contact.php" class="<?php echo $active_nav === 'contact' ? 'active' : ''; ?>">CONTACT</a>
    </nav>
    <div class="icons-container">
        <a href="menu.php" class="icon-link" id="search-toggle" aria-label="Search"><i class="fas fa-search"></i></a>
        <a href="favorites.php" class="icon-link" aria-label="My Favorites">
            <i class="fas fa-heart"></i>
            <?php if ($fav_n > 0): ?>
                <span class="cart-badge"><?php echo (int) $fav_n; ?></span>
            <?php endif; ?>
        </a>
        <?php if (!$admin): ?>
        <a href="cart.php" class="icon-link" aria-label="Cart">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cart_n > 0): ?>
                <span class="cart-badge"><?php echo (int) $cart_n; ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <a href="<?php echo $logged_in ? 'account.php' : 'login.php'; ?>" class="icon-link" aria-label="<?php echo $logged_in ? ($inbox_n > 0 ? 'Account, ' . (int) $inbox_n . ' inbox notifications' : 'Account') : 'Log in'; ?>">
            <i class="fas fa-user"></i>
            <?php if ($logged_in && !$admin && $inbox_n > 0): ?>
                <span class="cart-badge notify-badge"><?php echo (int) $inbox_n; ?></span>
            <?php endif; ?>
        </a>
        <?php if ($admin): ?>
            <a href="admin/inbox.php" class="icon-link" aria-label="Inbox">
                <i class="fas fa-inbox"></i>
                <?php if ($inbox_n > 0): ?>
                    <span class="cart-badge"><?php echo (int) $inbox_n; ?></span>
                <?php endif; ?>
            </a>
            <a href="admin/index.php" class="icon-link" aria-label="Admin"><i class="fas fa-store"></i></a>
        <?php endif; ?>
    </div>
</header>
<div id="search-overlay" class="search-overlay" hidden>
    <form action="menu.php" method="get" class="step-input search-form">
        <input type="search" name="q" placeholder="Search cakes, cookies, breads..." value="<?php echo e($_GET['q'] ?? ''); ?>">
        <button type="submit" class="cta-button">SEARCH</button>
        <button type="button" class="btn secondary-btn" id="search-close">CLOSE</button>
    </form>
</div>
