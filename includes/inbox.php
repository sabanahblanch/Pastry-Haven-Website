<?php

function inbox_unread_count($user = null)
{
    $user = $user ?: current_user();
    if (!$user) {
        return 0;
    }
    if (is_admin($user)) {
        $stmt = db_query(
            'SELECT COUNT(*) FROM inbox_messages m
             INNER JOIN inbox_threads t ON t.id = m.thread_id
             INNER JOIN users u ON u.id = m.sender_id
             WHERE m.read_at IS NULL AND u.role <> ?',
            ['admin']
        );
        return (int) $stmt->fetchColumn();
    }
    $stmt = db_query(
        'SELECT COUNT(*) FROM inbox_messages m
         INNER JOIN inbox_threads t ON t.id = m.thread_id
         WHERE t.user_id = ? AND m.sender_id <> ? AND m.read_at IS NULL',
        [$user['id'], $user['id']]
    );
    return (int) $stmt->fetchColumn();
}

function inbox_threads_for_customer($user_id)
{
    return db_query(
        'SELECT t.*,
            (SELECT body FROM inbox_messages WHERE thread_id = t.id ORDER BY id DESC LIMIT 1) AS last_body,
            (SELECT COUNT(*) FROM inbox_messages m WHERE m.thread_id = t.id AND m.sender_id <> t.user_id AND m.read_at IS NULL) AS unread
         FROM inbox_threads t
         WHERE t.user_id = ?
         ORDER BY t.last_at DESC',
        [$user_id]
    )->fetchAll();
}

function inbox_all_threads()
{
    return db_query(
        'SELECT t.*, u.name AS customer_name, u.email AS customer_email,
            (SELECT body FROM inbox_messages WHERE thread_id = t.id ORDER BY id DESC LIMIT 1) AS last_body,
            (SELECT COUNT(*) FROM inbox_messages m
             INNER JOIN users su ON su.id = m.sender_id
             WHERE m.thread_id = t.id AND m.read_at IS NULL AND su.role <> ?) AS unread
         FROM inbox_threads t
         INNER JOIN users u ON u.id = t.user_id
         ORDER BY t.last_at DESC',
        ['admin']
    )->fetchAll();
}

function inbox_get_thread($id)
{
    $stmt = db_query(
        'SELECT t.*, u.name AS customer_name, u.email AS customer_email
         FROM inbox_threads t
         INNER JOIN users u ON u.id = t.user_id
         WHERE t.id = ? LIMIT 1',
        [(int) $id]
    );
    return $stmt->fetch() ?: null;
}

function inbox_can_access($thread, $user = null)
{
    $user = $user ?: current_user();
    if (!$thread || !$user) {
        return false;
    }
    if (is_admin($user)) {
        return true;
    }
    return (int) $thread['user_id'] === (int) $user['id'];
}

function inbox_thread_messages($thread_id)
{
    return db_query(
        'SELECT m.*, u.name AS sender_name, u.role AS sender_role
         FROM inbox_messages m
         INNER JOIN users u ON u.id = m.sender_id
         WHERE m.thread_id = ?
         ORDER BY m.id ASC',
        [(int) $thread_id]
    )->fetchAll();
}

function inbox_mark_read($thread_id, $reader)
{
    if (is_admin($reader)) {
        db_query(
            'UPDATE inbox_messages m
             INNER JOIN users u ON u.id = m.sender_id
             SET m.read_at = CURRENT_TIMESTAMP
             WHERE m.thread_id = ? AND m.read_at IS NULL AND u.role <> ?',
            [(int) $thread_id, 'admin']
        );
        return;
    }
    db_query(
        'UPDATE inbox_messages SET read_at = CURRENT_TIMESTAMP
         WHERE thread_id = ? AND sender_id <> ? AND read_at IS NULL',
        [(int) $thread_id, $reader['id']]
    );
}

function inbox_time($value)
{
    $ts = strtotime((string) $value);
    return $ts ? date('M j, Y g:i A', $ts) : '';
}

function inbox_preview($body, $max = 42)
{
    $body = trim(preg_replace('/\s+/', ' ', (string) $body));
    if (mb_strlen($body) <= $max) {
        return $body;
    }
    return mb_substr($body, 0, $max) . '…';
}

function inbox_latest_thread($user_id)
{
    $stmt = db_query(
        'SELECT * FROM inbox_threads WHERE user_id = ? ORDER BY last_at DESC, id DESC LIMIT 1',
        [(int) $user_id]
    );
    return $stmt->fetch() ?: null;
}

function inbox_ensure_thread($user_id, $subject = 'Messages')
{
    $thread = inbox_latest_thread($user_id);
    if ($thread) {
        return (int) $thread['id'];
    }
    $subject = sanitize_string($subject, 120) ?: 'Messages';
    db_query(
        'INSERT INTO inbox_threads (user_id, subject, last_at) VALUES (?, ?, NOW())',
        [(int) $user_id, $subject]
    );
    return (int) db()->lastInsertId();
}

function inbox_customer($user_id)
{
    $stmt = db_query(
        'SELECT id, name, email, phone, role FROM users WHERE id = ? LIMIT 1',
        [(int) $user_id]
    );
    $row = $stmt->fetch();
    if (!$row || ($row['role'] ?? '') === 'admin') {
        return null;
    }
    return $row;
}

function inbox_conversations()
{
    return db_query(
        'SELECT u.id AS user_id, u.name AS customer_name, u.email AS customer_email,
            (SELECT m.body FROM inbox_messages m
             INNER JOIN inbox_threads t ON t.id = m.thread_id
             WHERE t.user_id = u.id
             ORDER BY m.id DESC LIMIT 1) AS last_body,
            (SELECT MAX(t.last_at) FROM inbox_threads t WHERE t.user_id = u.id) AS last_at,
            (SELECT COUNT(*) FROM inbox_messages m
             INNER JOIN inbox_threads t ON t.id = m.thread_id
             INNER JOIN users su ON su.id = m.sender_id
             WHERE t.user_id = u.id AND m.read_at IS NULL AND su.role <> ?) AS unread
         FROM users u
         WHERE EXISTS (SELECT 1 FROM inbox_threads t WHERE t.user_id = u.id)
           AND (u.role IS NULL OR u.role <> ?)
         ORDER BY last_at DESC',
        ['admin', 'admin']
    )->fetchAll();
}

function inbox_messages_for_customer($user_id)
{
    return db_query(
        'SELECT m.*, u.name AS sender_name, u.role AS sender_role
         FROM inbox_messages m
         INNER JOIN inbox_threads t ON t.id = m.thread_id
         INNER JOIN users u ON u.id = m.sender_id
         WHERE t.user_id = ?
         ORDER BY m.id ASC',
        [(int) $user_id]
    )->fetchAll();
}

function inbox_mark_customer_read($user_id, $reader)
{
    $rows = db_query('SELECT id FROM inbox_threads WHERE user_id = ?', [(int) $user_id])->fetchAll();
    foreach ($rows as $row) {
        inbox_mark_read((int) $row['id'], $reader);
    }
}

function inbox_send($customer_id, $sender_id, $body, $subject = 'Messages')
{
    $body = sanitize_string($body, 2000);
    if ($body === '') {
        return false;
    }
    $thread_id = inbox_ensure_thread($customer_id, $subject);
    inbox_reply($thread_id, $sender_id, $body);
    return $thread_id;
}

function inbox_create_thread($user_id, $subject, $body)
{
    return inbox_send($user_id, $user_id, $body, $subject);
}

function inbox_reply($thread_id, $sender_id, $body)
{
    $body = sanitize_string($body, 2000);
    if ($body === '') {
        return false;
    }
    db_query(
        'INSERT INTO inbox_messages (thread_id, sender_id, body) VALUES (?, ?, ?)',
        [(int) $thread_id, $sender_id, $body]
    );
    db_query('UPDATE inbox_threads SET last_at = NOW() WHERE id = ?', [(int) $thread_id]);
    return true;
}
