<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$stmt = $pdo->query('SELECT u.username, MAX(s.score) AS score, MAX(s.created_at) AS updated_at\nFROM scores s\nJOIN users u ON u.id = s.user_id\nGROUP BY u.id, u.username\nORDER BY score DESC, updated_at DESC\nLIMIT 50');
$rows = $stmt->fetchAll();

json_response(['ok' => true, 'rows' => $rows]);
