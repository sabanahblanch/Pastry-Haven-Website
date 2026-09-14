<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

$allowed_status = ['Received', 'Preparing', 'Ready', 'Completed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('orders.php');
    }
    $id = sanitize_string($_POST['id'] ?? '', 32);
    $status = sanitize_string($_POST['status'] ?? '', 20);
    if (!in_array($status, $allowed_status, true)) {
        set_flash('error', 'Please choose a valid status.');
        redirect('orders.php');
    }
    $exists = db_query('SELECT id FROM orders WHERE id = ? LIMIT 1', [$id])->fetch();
    if (!$exists) {
        set_flash('error', 'That order was not found.');
        redirect('orders.php');
    }
    db_query('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    set_flash('success', 'Order status updated.');
    redirect('orders.php');
}

$status_filter = $_GET['status'] ?? 'all';
if ($status_filter !== 'all' && in_array($status_filter, $allowed_status, true)) {
    $rows = db_query('SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC', [$status_filter])->fetchAll();
} else {
    $rows = db_query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
}
$orders = array_map('attach_order_items', $rows);

admin_layout_start('Orders', 'orders.php');
?>
            <form method="get" class="admin-toolbar">
                <select name="status">
                    <option value="all">All statuses</option>
                    <?php foreach ($allowed_status as $status): ?>
                        <option value="<?php echo e($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="cta-button" type="submit">FILTER</button>
            </form>

            <?php if (!$orders): ?>
                <p class="about-text">No orders found.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Products</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo e($order['id']); ?></td>
                            <td>
                                <?php echo e($order['customer']['name']); ?><br>
                                <small><?php echo e($order['customer']['email']); ?></small>
                            </td>
                            <td>
                                <?php foreach ($order['items'] as $item): ?>
                                    <?php echo e($item['name']); ?> × <?php echo (int) $item['qty']; ?><br>
                                <?php endforeach; ?>
                            </td>
                            <td><?php echo format_price($order['total']); ?></td>
                            <td>
                                <form method="post" class="admin-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo e($order['id']); ?>">
                                    <select name="status">
                                        <?php foreach ($allowed_status as $status): ?>
                                            <option value="<?php echo e($status); ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo e($status); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="admin-link-btn">Save</button>
                                </form>
                            </td>
                            <td><?php echo e($order['created_at']); ?>
                                <?php if (!empty($order['ready_date'])): ?>
                                    <br><small>Needed <?php echo e($order['ready_date']); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
<?php
admin_layout_end();
