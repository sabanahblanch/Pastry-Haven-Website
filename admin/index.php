<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

$low = low_stock_threshold();
$product_count = (int) db_query("SELECT COUNT(*) FROM products WHERE id <> ?", ['custom-cake'])->fetchColumn();
$total_stock = (int) db_query("SELECT COALESCE(SUM(stock), 0) FROM products WHERE id <> ?", ['custom-cake'])->fetchColumn();
$low_count = (int) db_query(
    'SELECT COUNT(*) FROM products WHERE id <> ? AND stock <= ?',
    ['custom-cake', $low]
)->fetchColumn();
$pending = (int) db_query(
    "SELECT COUNT(*) FROM orders WHERE status IN ('Received', 'Preparing', 'Ready')"
)->fetchColumn();
$completed = (int) db_query("SELECT COUNT(*) FROM orders WHERE status = ?", ['Completed'])->fetchColumn();
$users = (int) db_query('SELECT COUNT(*) FROM users')->fetchColumn();
$recent = db_query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 5')->fetchAll();
$low_items = db_query(
    'SELECT id, name, stock FROM products WHERE id <> ? AND stock <= ? ORDER BY stock ASC, name ASC LIMIT 8',
    ['custom-cake', $low]
)->fetchAll();

admin_layout_start('Dashboard', 'index.php');
?>
            <div class="admin-stats">
                <div class="admin-stat">
                    <span class="admin-stat-label">Total Products</span>
                    <strong><?php echo $product_count; ?></strong>
                </div>
                <div class="admin-stat">
                    <span class="admin-stat-label">Total Stock</span>
                    <strong><?php echo $total_stock; ?></strong>
                </div>
                <div class="admin-stat">
                    <span class="admin-stat-label">Low Stock Items</span>
                    <strong><?php echo $low_count; ?></strong>
                </div>
                <div class="admin-stat">
                    <span class="admin-stat-label">Pending Orders</span>
                    <strong><?php echo $pending; ?></strong>
                </div>
                <div class="admin-stat">
                    <span class="admin-stat-label">Completed Orders</span>
                    <strong><?php echo $completed; ?></strong>
                </div>
                <div class="admin-stat">
                    <span class="admin-stat-label">Users</span>
                    <strong><?php echo $users; ?></strong>
                </div>
            </div>

            <div class="admin-two">
                <div>
                    <h3 class="about-subtitle">Low stock</h3>
                    <?php if (!$low_items): ?>
                        <p class="about-text">All pastries are above the low-stock level.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead><tr><th>Product</th><th>Stock</th></tr></thead>
                            <tbody>
                            <?php foreach ($low_items as $row): ?>
                                <tr>
                                    <td><?php echo e($row['name']); ?></td>
                                    <td><?php echo (int) $row['stock']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a class="view-all" href="inventory.php?filter=low">Manage inventory &rarr;</a>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="about-subtitle">Recent orders</h3>
                    <?php if (!$recent): ?>
                        <p class="about-text">No orders yet.</p>
                    <?php else: ?>
                        <table class="admin-table">
                            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($recent as $order): ?>
                                <tr>
                                    <td><?php echo e($order['id']); ?></td>
                                    <td><?php echo e($order['customer_name']); ?></td>
                                    <td><?php echo format_price($order['total']); ?></td>
                                    <td><?php echo e($order['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <a class="view-all" href="orders.php">View orders &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
<?php
admin_layout_end();
