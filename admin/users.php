<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/admin.php';
require_admin();

$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Your session expired. Please try again.');
        redirect('users.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $role = sanitize_string($_POST['role'] ?? '', 20);
    if (!in_array($role, ['admin', 'customer'], true) || $id < 1) {
        set_flash('error', 'Please choose a valid role.');
        redirect('users.php');
    }
    $target = db_query('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])->fetch();
    if (!$target) {
        set_flash('error', 'That account was not found.');
        redirect('users.php');
    }
    if ($id === (int) $me['id'] && $role !== 'admin') {
        set_flash('error', 'You cannot remove your own admin access.');
        redirect('users.php');
    }
    $admin_count = (int) db_query("SELECT COUNT(*) FROM users WHERE role = ?", ['admin'])->fetchColumn();
    if (($target['role'] ?? '') === 'admin' && $role !== 'admin' && $admin_count < 2) {
        set_flash('error', 'Keep at least one admin account.');
        redirect('users.php');
    }
    db_query('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
    set_flash('success', 'User role updated.');
    redirect('users.php');
}

$users = db_query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();

admin_layout_start('Users', 'users.php');
?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?php echo e($row['name']); ?></td>
                        <td><?php echo e($row['email']); ?></td>
                        <td><?php echo e($row['phone']); ?></td>
                        <td>
                            <form method="post" class="admin-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <select name="role">
                                    <option value="customer" <?php echo $row['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" class="admin-link-btn">Save</button>
                            </form>
                        </td>
                        <td><?php echo e($row['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
<?php
admin_layout_end();
