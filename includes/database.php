<?php

function db()
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function db_query($sql, $params = [])
{
    $stmt = db()->prepare($sql);
    foreach (array_values($params) as $index => $value) {
        $type = PDO::PARAM_STR;
        if (is_int($value)) {
            $type = PDO::PARAM_INT;
        } elseif (is_null($value)) {
            $type = PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            $type = PDO::PARAM_BOOL;
        }
        $stmt->bindValue($index + 1, $value, $type);
    }
    $stmt->execute();
    return $stmt;
}
