<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('l2fa_session');
    session_start();
}

require_once __DIR__ . '/db.php';

$pdo = db_connect($config['db']);

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function get_post_string(string $key): string
{
    $val = $_POST[$key] ?? '';
    return is_string($val) ? trim($val) : '';
}

function safe_redirect_target(string $target, string $default = '/index.html'): string
{
    $target = trim($target);
    if ($target === '') return $default;

    if (preg_match('~^https?://~i', $target)) return $default;
    if (str_starts_with($target, '//')) return $default;

    if (str_starts_with($target, '/')) return $target;
    if (str_contains($target, ':')) return $default;

    return '/' . ltrim($target, '/');
}

function current_user_id(): ?int
{
    $id = $_SESSION['user_id'] ?? null;
    return is_int($id) ? $id : null;
}

function require_login(): int
{
    $uid = current_user_id();
    if ($uid === null) {
        json_response(['ok' => false, 'error' => 'not_logged_in'], 401);
    }
    return $uid;
}
