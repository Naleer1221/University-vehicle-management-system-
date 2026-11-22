<?php
require_once __DIR__ . '/functions.php';
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}
function check_csrf() {
    if (!is_post() || !hash_equals(csrf_token(), (string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request. Please reload the page and try again.');
    }
}
