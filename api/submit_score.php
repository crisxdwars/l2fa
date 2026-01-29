<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$uid = require_login();

$scoreRaw = $_POST['score'] ?? null;
$mode = get_post_string('mode');

$score = is_numeric($scoreRaw) ? (int)$scoreRaw : 0;
if ($score < 0) $score = 0;

if ($mode === '') $mode = 'java';
if (!preg_match('/^[a-zA-Z0-9_]{1,16}$/', $mode)) {
    json_response(['ok' => false, 'error' => 'bad_mode'], 400);
}

$stmt = $pdo->prepare('INSERT INTO scores (user_id, score, mode) VALUES (?, ?, ?)');
$stmt->execute([$uid, $score, $mode]);

json_response(['ok' => true]);
