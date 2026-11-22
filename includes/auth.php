<?php
require_once __DIR__ . '/csrf.php';
function require_login() {
    global $pdo;
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Please log in first.');
        go('login.php');
    }
    // Stop a disabled customer from continuing to use an existing session.
    $stmt = $pdo->prepare('SELECT id, is_active FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $record = $stmt->fetch();
    if (!$record || !(int)$record['is_active']) {
        unset($_SESSION['user']);
        set_flash('error', 'Account is unavailable.');
        go('login.php');
    }
}
function require_customer() {
    require_login();
    if (current_user()['role'] !== 'customer') {
        http_response_code(403);
        exit('Customer area only.');
    }
}
function require_admin() {
    require_login();
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Administrator area only.');
    }
}
