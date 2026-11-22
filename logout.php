<?php
require_once __DIR__ . '/includes/functions.php';
// End the login session and start a new session for a message.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: ' . url('login.php'));
exit;
