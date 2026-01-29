<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$state = $_GET['state'] ?? '';
$code = $_GET['code'] ?? '';

if (!is_string($state) || !is_string($code) || $state === '' || $code === '') {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$expected = $_SESSION['oauth_state'] ?? null;
if (!is_string($expected) || !hash_equals($expected, $state)) {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

unset($_SESSION['oauth_state']);

$google = $config['google'];

$tokenPayload = [
    'code' => $code,
    'client_id' => $google['client_id'],
    'client_secret' => $google['client_secret'],
    'redirect_uri' => $google['redirect_uri'],
    'grant_type' => 'authorization_code',
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($tokenPayload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);
$tokenRaw = curl_exec($ch);
$tokenErr = curl_error($ch);
$tokenCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($tokenRaw === false || $tokenErr || $tokenCode < 200 || $tokenCode >= 300) {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$token = json_decode($tokenRaw, true);
$accessToken = is_array($token) ? ($token['access_token'] ?? '') : '';

if (!is_string($accessToken) || $accessToken === '') {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$ch = curl_init('https://openidconnect.googleapis.com/v1/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
]);
$userRaw = curl_exec($ch);
$userErr = curl_error($ch);
$userCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($userRaw === false || $userErr || $userCode < 200 || $userCode >= 300) {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$userInfo = json_decode($userRaw, true);
if (!is_array($userInfo)) {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$googleId = $userInfo['sub'] ?? '';
$email = $userInfo['email'] ?? null;

if (!is_string($googleId) || $googleId === '') {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

if ($email !== null && !is_string($email)) {
    $email = null;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE google_id = ? LIMIT 1');
$stmt->execute([$googleId]);
$row = $stmt->fetch();

$userId = null;

if ($row) {
    $userId = (int)$row['id'];
} else {
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row) {
            $userId = (int)$row['id'];
            $upd = $pdo->prepare('UPDATE users SET google_id = ? WHERE id = ?');
            $upd->execute([$googleId, $userId]);
        }
    }

    if ($userId === null) {
        $baseUsername = $email ? preg_replace('/[^a-zA-Z0-9_]/', '_', explode('@', $email)[0]) : 'player';
        $baseUsername = substr($baseUsername, 0, 20);
        if ($baseUsername === '') $baseUsername = 'player';

        $username = $baseUsername;
        for ($i = 0; $i < 10; $i++) {
            try {
                $ins = $pdo->prepare('INSERT INTO users (username, email, google_id, password_hash) VALUES (?, ?, ?, NULL)');
                $ins->execute([$username, $email, $googleId]);
                $userId = (int)$pdo->lastInsertId();
                break;
            } catch (Throwable $e) {
                $username = $baseUsername . random_int(10, 99);
            }
        }
    }
}

if ($userId === null) {
    header('Location: ' . safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html'));
    exit;
}

$_SESSION['user_id'] = $userId;

$redirect = safe_redirect_target($_SESSION['oauth_redirect'] ?? '', '/index.html');
unset($_SESSION['oauth_redirect']);

header('Location: ' . $redirect);
exit;
