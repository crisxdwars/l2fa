<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$redirect = safe_redirect_target($_GET['redirect'] ?? '', '/index.html');

unset($_SESSION['user_id']);

header('Location: ' . $redirect);
exit;
