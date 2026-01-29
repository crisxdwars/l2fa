<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$redirect = safe_redirect_target($_GET['redirect'] ?? '', '/index.html');
$_SESSION['oauth_redirect'] = $redirect;

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$google = $config['google'];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $google['client_id'],
    'redirect_uri' => $google['redirect_uri'],
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account',
]);

header('Location: ' . $authUrl);
exit;
