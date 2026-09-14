<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

global $categories;
$q = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$category = strtolower(trim((string) ($_GET['category'] ?? 'all')));
if ($category !== 'all' && !isset($categories[$category])) {
    $category = 'all';
}
$add_id = $_GET['add'] ?? '';
$low = low_stock_threshold();

$inventory_query = [];
if ($q !== '') {
    $inventory_query['q'] = $q;
}
if ($category !== 'all') {
    $inventory_query['category'] = $category;
}
if ($filter === 'low') {
    $inventory_query['filter'] = 'low';
}
$inventory_url = 'inventory.php' . ($inventory_query ? '?' . http_build_query($inventory_query) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect($inventory_url);
    }
    $action = $_POST['action'] ?? '';
    $product_id = sanitize_string($_POST['product_id'] ?? '', 64);
    $reason = sanitize_string($_POST['reason'] ?? '', 120);

    if ($action === 'add_stock') {
        $qty = (int) ($_POST['qty'] ?? 0);
        if ($qty < 1) {
            set_flash('error', 'Enter how many pieces to add.');
            redirect($inventory_url . ($inventory_query ? '&' : '?') . 'add=' . urlencode($product_id));
        }
        $result = adjust_product_stock($product_id, $qty, $reason !== '' ? $reason : 'Stock added');
        if ($result === true) {
            set_flash('success', 'Stock updated.');
            redirect($inventory_url);
        }
        set_flash('error', $result);
        redirect($inventory_url . ($inventory_query ? '&' : '?') . 'add=' . urlencode($product_id));
    }

    if ($action === 'adjust') {
        $qty = (int) ($_POST['qty'] ?? 0);
        $result = adjust_product_stock($product_id, $qty, $reason !== '' ? $reason : 'Manual adjust');
        if ($result === true) {
            set_flash('success', 'Stock updated.');
            redirect($inventory_url);
        }
        set_flash('error', $result);
        redirect($inventory_url);
    }

    if ($action === 'set_stock') {
        $qty = (int) ($_POST['qty'] ?? 0);
        $result = set_product_stock($product_id, $qty, $reason !== '' ? $reason : 'Stock set');
        if ($result === true) {
            set_flash('success', 'Stock quantity saved.');
            redirect($inventory_url);
        }
        set_flash('error', $result);
        redirect($inventory_url);
    }
}

$sql = 'SELECT * FROM products WHERE id <> ?';
$params = ['custom-cake'];
if ($q !== '') {
    $sql .= ' AND name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($category !== 'all') {
    $sql .= ' AND LOWER(category) = ?';
    $params[] = $category;
}
if ($filter === 'low') {
    $sql .= ' AND stock <= ?';
    $params[] = $low;
}
$sql .= ' ORDER BY name ASC';
$items = db_query($sql, $params)->fetchAll();
$add_product = $add_id !== '' ? get_product($add_id) : null;

admin_layout_start('Inventory / Stocks', 'inventory.php');
?>
            <form method="get" class="admin-toolbar" id="inventory-filter">
                <div class="step-input">
                    <input type="search" name="q" placeholder="Search products" value="<?php echo e($q); ?>">
                </div>
                <select name="category" onchange="this.form.submit()">
                    <option value="all">All categories</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo e($key); ?>" <?php echo $category === $key ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All stock</option>
                    <option value="low" <?php echo $filter === 'low' ? 'selected' : ''; ?>>Low stock</option>
                </select>
                <button class="cta-button" type="submit">FILTER</button>
            </form>

            <?php if ($add_product && $add_product['id'] !== 'custom-cake'): ?>
                <form method="post" class="build-right admin-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add_stock">
                    <input type="hidden" name="product_id" value="<?php echo e($add_product['id']); ?>">
                    <h3 class="about-subtitle">Add stock</h3>
                    <p class="about-text"><?php echo e($add_product['name']); ?> — current stock: <?php echo (int) $add_product['stock']; ?></p>
                    <div class="step-row">
                        <span class="step-title">Quantity to add</span>
                        <div class="step-input"><input type="number" name="qty" min="1" required value="1"></div>
                    </div>
                    <div class="step-row">
                        <span class="step-title">Note / reason <small>(optional)</small></span>
                        <div class="step-input"><input type="text" name="reason" maxlength="120" placeholder="Delivery, leftover, correction"></div>
                    </div>
                    <button type="submit" class="cta-button">ADD STOCK</button>
                    <a class="view-all" href="<?php echo e($inventory_url); ?>">Cancel</a>
                </form>
            <?php endif; ?>

            <?php if (!$items): ?>
                <p class="about-text">No products match this category or filter.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Add</th>
                        <th>Set</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $product): ?>
                    <tr class="<?php echo (int) $product['stock'] <= $low ? 'admin-low' : ''; ?>">
                        <td><?php echo e($product['name']); ?></td>
                        <td><?php echo e($product['category']); ?></td>
                        <td><?php echo (int) $product['stock']; ?></td>
                        <td>
                            <a class="view-all" href="<?php echo e($inventory_url . ($inventory_query ? '&' : '?') . 'add=' . urlencode($product['id'])); ?>">Add stock</a>
                            <form method="post" class="admin-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="adjust">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <input type="hidden" name="reason" value="Decrease 1">
                                <input type="hidden" name="qty" value="-1">
                                <button type="submit" class="admin-link-btn">-1</button>
                            </form>
                            <form method="post" class="admin-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="adjust">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <input type="hidden" name="reason" value="Increase 1">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="admin-link-btn">+1</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="admin-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="set_stock">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <input type="number" name="qty" min="0" value="<?php echo (int) $product['stock']; ?>">
                                <button type="submit" class="admin-link-btn">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
<?php
admin_layout_end();
