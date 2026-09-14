<?php
require_once __DIR__ . '/includes/config.php';

$active_nav = 'menu';
$category = $_GET['category'] ?? 'all';
$query = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'featured';
$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 6;
$flash = get_flash();

$allowed_sorts = ['featured', 'price-asc', 'price-desc', 'name'];
if (!in_array($sort, $allowed_sorts, true)) {
    $sort = 'featured';
}
$allowed_filters = ['all', 'featured', 'in-stock'];
if (!in_array($filter, $allowed_filters, true)) {
    $filter = 'all';
}

if ($query !== '') {
    $items = search_products($query);
    if ($category !== '' && $category !== 'all') {
        $items = array_values(array_filter($items, function ($product) use ($category) {
            return $product['category'] === $category;
        }));
    }
} else {
    $items = products_by_category($category);
}

if ($filter === 'featured') {
    $items = filter_menu_products($items, 'featured');
} elseif ($filter === 'in-stock') {
    $items = array_values(array_filter($items, function ($product) {
        return (int) ($product['stock'] ?? 0) > 0;
    }));
}
$items = sort_menu_products($items, $sort);

$total = count($items);
$pages = max(1, (int) ceil($total / $per_page));
if ($page > $pages) {
    $page = $pages;
}
$page_items = array_slice($items, ($page - 1) * $per_page, $per_page);

$category_tiles = [
    'all' => ['label' => 'All', 'image' => 'images/logo-.png'],
    'cakes' => ['label' => 'Cakes', 'image' => 'images/cake.jfif'],
    'breads' => ['label' => 'Breads', 'image' => 'images/bread.jfif'],
    'cookies' => ['label' => 'Cookies', 'image' => 'images/cookies.jfif'],
    'cupcakes' => ['label' => 'Cupcakes', 'image' => 'images/cupcake.jfif'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | <?php echo e($site_title); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require __DIR__ . '/includes/site-nav.php'; ?>
    <section class="menu-browse inner-page">
        <div class="menu-browse-shell">
            <div class="cravings-header">
                <h2>What Are You Craving?</h2>
                <div class="heart-divider">
                    <span class="line"></span>
                    <i class="fas fa-heart"></i>
                    <span class="line"></span>
                </div>
            </div>

            <form class="menu-search" action="menu.php" method="get">
                <?php if ($category !== '' && $category !== 'all'): ?>
                    <input type="hidden" name="category" value="<?php echo e($category); ?>">
                <?php endif; ?>
                <div class="step-input">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" name="q" placeholder="Search cakes, cookies, breads..." value="<?php echo e($query); ?>">
                </div>
                <button type="submit" class="cta-button">SEARCH</button>
            </form>

            <div class="menu-cats">
                <?php foreach ($category_tiles as $key => $tile): ?>
                    <a href="<?php echo e(menu_url(['category' => $key, 'page' => 1])); ?>" class="menu-cat <?php echo $category === $key ? 'active' : ''; ?>">
                        <div class="menu-cat-icon">
                            <img src="<?php echo e($tile['image']); ?>" alt="<?php echo e($tile['label']); ?>">
                        </div>
                        <span><?php echo e($tile['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="menu-tools">
                <details class="menu-tool">
                    <summary aria-label="Filter"><i class="fas fa-sliders-h"></i></summary>
                    <div class="menu-tool-panel">
                        <a href="<?php echo e(menu_url(['filter' => 'all', 'page' => 1])); ?>" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All treats</a>
                        <a href="<?php echo e(menu_url(['filter' => 'featured', 'page' => 1])); ?>" class="<?php echo $filter === 'featured' ? 'active' : ''; ?>">Best sellers</a>
                        <a href="<?php echo e(menu_url(['filter' => 'in-stock', 'page' => 1])); ?>" class="<?php echo $filter === 'in-stock' ? 'active' : ''; ?>">In stock</a>
                    </div>
                </details>
                <details class="menu-tool">
                    <summary aria-label="Sort"><i class="fas fa-sort"></i></summary>
                    <div class="menu-tool-panel">
                        <a href="<?php echo e(menu_url(['sort' => 'featured', 'page' => 1])); ?>" class="<?php echo $sort === 'featured' ? 'active' : ''; ?>">Featured</a>
                        <a href="<?php echo e(menu_url(['sort' => 'price-asc', 'page' => 1])); ?>" class="<?php echo $sort === 'price-asc' ? 'active' : ''; ?>">Price: low to high</a>
                        <a href="<?php echo e(menu_url(['sort' => 'price-desc', 'page' => 1])); ?>" class="<?php echo $sort === 'price-desc' ? 'active' : ''; ?>">Price: high to low</a>
                        <a href="<?php echo e(menu_url(['sort' => 'name', 'page' => 1])); ?>" class="<?php echo $sort === 'name' ? 'active' : ''; ?>">Name A–Z</a>
                    </div>
                </details>
            </div>

            <?php if ($flash): ?>
                <p class="about-text auth-flash auth-flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></p>
            <?php endif; ?>
            <?php if ($query !== ''): ?>
                <p class="about-text">Showing results for "<?php echo e($query); ?>"</p>
            <?php endif; ?>

            <?php if ($category !== 'cakes' && !$page_items): ?>
                <p class="about-text">No pastries found. Try another search or category.</p>
                <a href="menu.php" class="cta-button">VIEW FULL MENU</a>
            <?php else: ?>
                <div class="menu-grid">
                    <?php if ($category === 'cakes'): ?>
                        <article class="menu-card customize-cake-card">
                            <div class="menu-card-image">
                                <img src="images/dark choco.png" alt="Customize Cake">
                            </div>
                            <div class="menu-card-body">
                                <div class="menu-card-title-row">
                                    <h3>Customize Cake</h3>
                                </div>
                                <p class="menu-card-desc">Choose a flavor, size, reference photo, and dedication for a cake made your way.</p>
                                <button type="button" class="cta-button js-open-customize">CUSTOMIZE CAKE</button>
                            </div>
                        </article>
                    <?php endif; ?>
                    <?php foreach ($page_items as $product) {
                        echo render_menu_card($product);
                    } ?>
                </div>
                <?php if ($pages > 1): ?>
                    <nav class="menu-pages" aria-label="Menu pages">
                        <a class="menu-page-btn" href="<?php echo e(menu_url(['page' => max(1, $page - 1)])); ?>" aria-label="Previous">&lsaquo;</a>
                        <?php for ($n = 1; $n <= $pages; $n++): ?>
                            <a class="menu-page-btn <?php echo $n === $page ? 'active' : ''; ?>" href="<?php echo e(menu_url(['page' => $n])); ?>"><?php echo $n; ?></a>
                        <?php endfor; ?>
                        <a class="menu-page-btn" href="<?php echo e(menu_url(['page' => min($pages, $page + 1)])); ?>" aria-label="Next">&rsaquo;</a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php if ($category === 'cakes') {
        require __DIR__ . '/includes/customize-cake-modal.php';
    } ?>
    <?php require __DIR__ . '/includes/site-footer.php'; ?>
</body>
</html>
