<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$username = get_post_string('username');
$password = get_post_string('password');
$redirect = safe_redirect_target(get_post_string('redirect'), '/index.html');

if ($username === '' || $password === '') {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !$user['password_hash'] || !password_verify($password, $user['password_hash'])) {
    header('Location: ' . $redirect);
    exit;
}

$_SESSION['user_id'] = (int)$user['id'];

header('Location: ' . $redirect);
exit;
