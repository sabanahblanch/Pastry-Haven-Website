<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

global $categories;
$edit_id = $_GET['edit'] ?? '';
$edit = $edit_id !== '' ? get_product($edit_id) : null;
if ($edit_id !== '' && !$edit) {
    set_flash('error', 'That product was not found.');
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('products.php');
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = sanitize_string($_POST['id'] ?? '', 64);
        if ($id === 'custom-cake') {
            set_flash('error', 'The custom cake item cannot be deleted.');
            redirect('products.php');
        }
        if (!get_product($id)) {
            set_flash('error', 'That product was not found.');
            redirect('products.php');
        }
        db_query('DELETE FROM products WHERE id = ?', [$id]);
        set_flash('success', 'Product deleted.');
        redirect('products.php');
    }

    if ($action === 'save') {
        $name = sanitize_string($_POST['name'] ?? '', 150);
        $price = round((float) ($_POST['price'] ?? 0), 2);
        $category = sanitize_string($_POST['category'] ?? '', 40);
        $stock = max(0, (int) ($_POST['stock'] ?? 0));
        $rating = min(5, max(1, (int) ($_POST['rating'] ?? 5)));
        $featured = isset($_POST['featured']) ? 1 : 0;
        $description = sanitize_string($_POST['description'] ?? '', 1000);
        $details = sanitize_string($_POST['details'] ?? '', 1000);
        $id = sanitize_string($_POST['id'] ?? '', 64);
        $allowed = array_keys($categories);
        $allowed[] = 'cakes';

        if ($name === '' || $price <= 0 || !isset($categories[$category])) {
            set_flash('error', 'Please enter a name, price, and valid category.');
            redirect('products.php' . ($id ? '?edit=' . urlencode($id) : ''));
        }

        $image = handle_product_image_upload('image');
        if ($id !== '') {
            $current = get_product($id);
            if (!$current) {
                set_flash('error', 'That product was not found.');
                redirect('products.php');
            }
            if ($image === '') {
                $image = $current['image'];
            }
            $old_stock = (int) $current['stock'];
            db_query(
                'UPDATE products SET name = ?, price = ?, category = ?, image = ?, rating = ?, featured = ?, stock = ?, description = ?, details = ? WHERE id = ?',
                [$name, $price, $category, $image, $rating, $featured, $stock, $description, $details, $id]
            );
            if ($old_stock !== $stock) {
                record_stock_move($id, $stock - $old_stock, $stock, 'Updated from product form');
            }
            set_flash('success', 'Product updated.');
            redirect('products.php');
        }

        if ($image === '') {
            $image = 'images/cake.jfif';
        }
        $new_id = product_slug($name);
        db_query(
            'INSERT INTO products (id, name, price, category, image, reviews, rating, featured, stock, description, details)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$new_id, $name, $price, $category, $image, 0, $rating, $featured, $stock, $description, $details]
        );
        record_stock_move($new_id, $stock, $stock, 'New product');
        set_flash('success', 'Product added to the menu.');
        redirect('products.php');
    }
}

$items = db_query("SELECT * FROM products WHERE id <> ? ORDER BY name ASC", ['custom-cake'])->fetchAll();
admin_layout_start($edit ? 'Edit Product' : 'Products / Menu', 'products.php');
?>
            <?php if ($edit): ?>
                <p class="about-text">Editing <?php echo e($edit['name']); ?>. <a class="view-all" href="products.php">Cancel</a></p>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="build-right admin-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?php echo e($edit['id'] ?? ''); ?>">
                <div class="step-row">
                    <span class="step-title">Name</span>
                    <div class="step-input"><input type="text" name="name" required maxlength="150" value="<?php echo e($edit['name'] ?? ''); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Price</span>
                    <div class="step-input"><input type="number" name="price" min="1" step="0.01" required value="<?php echo e($edit['price'] ?? ''); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Category</span>
                    <div class="step-input">
                        <select name="category" required>
                            <?php foreach ($categories as $key => $label): ?>
                                <option value="<?php echo e($key); ?>" <?php echo (($edit['category'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="step-row">
                    <span class="step-title">Stock</span>
                    <div class="step-input"><input type="number" name="stock" min="0" required value="<?php echo e($edit['stock'] ?? 20); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Rating (1-5)</span>
                    <div class="step-input"><input type="number" name="rating" min="1" max="5" value="<?php echo e($edit['rating'] ?? 5); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Image</span>
                    <div class="step-input"><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif,.jfif"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Description</span>
                    <div class="step-input"><input type="text" name="description" maxlength="1000" value="<?php echo e($edit['description'] ?? ''); ?>"></div>
                </div>
                <div class="step-row">
                    <span class="step-title">Details</span>
                    <div class="step-input"><input type="text" name="details" maxlength="1000" value="<?php echo e($edit['details'] ?? ''); ?>"></div>
                </div>
                <label class="step-title">
                    <input type="checkbox" name="featured" value="1" <?php echo !empty($edit['featured']) ? 'checked' : ''; ?>>
                    Featured on home
                </label>
                <button type="submit" class="cta-button"><?php echo $edit ? 'SAVE PRODUCT' : 'ADD PRODUCT'; ?></button>
            </form>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $product): ?>
                    <tr>
                        <td><?php echo e($product['name']); ?></td>
                        <td><?php echo e($product['category']); ?></td>
                        <td><?php echo format_price($product['price']); ?></td>
                        <td><?php echo (int) $product['stock']; ?></td>
                        <td class="admin-actions">
                            <a class="view-all" href="products.php?edit=<?php echo e(urlencode($product['id'])); ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this product?');">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($product['id']); ?>">
                                <button type="submit" class="admin-link-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
<?php
admin_layout_end();
