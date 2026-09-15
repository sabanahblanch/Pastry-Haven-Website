<?php
require __DIR__ . '/includes/config.php';
$cols = db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "tables: ", implode(', ', $cols), PHP_EOL;
$u = db_query('SELECT id, name, email, role FROM users ORDER BY role DESC, id ASC')->fetchAll();
echo json_encode($u, JSON_PRETTY_PRINT), PHP_EOL;
