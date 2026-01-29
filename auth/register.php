<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$username = get_post_string('username');
$password = get_post_string('password');
$password2 = get_post_string('password2');
$redirect = safe_redirect_target(get_post_string('redirect'), '/index.html');

if ($username === '' || $password === '' || $password !== $password2) {
    header('Location: ' . $redirect);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    header('Location: ' . $redirect);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);
    $_SESSION['user_id'] = (int)$pdo->lastInsertId();
} catch (Throwable $e) {
    header('Location: ' . $redirect);
    exit;
}

header('Location: ' . $redirect);
exit;
