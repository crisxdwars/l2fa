<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$uid = current_user_id();
if ($uid === null) {
    json_response(['ok' => true, 'logged_in' => false]);
}

$stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['user_id']);
    json_response(['ok' => true, 'logged_in' => false]);
}

json_response([
    'ok' => true,
    'logged_in' => true,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
    ],
]);
