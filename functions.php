<?php
require_once __DIR__ . '/../config/db.php';

function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function go($path) {
    header('Location: ' . url($path));
    exit;
}
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function get_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
function money($amount) {
    return 'LKR ' . number_format((float)$amount, 2);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}
function validate_dates($pickup, $return) {
    $first = DateTimeImmutable::createFromFormat('!Y-m-d', $pickup);
    $last = DateTimeImmutable::createFromFormat('!Y-m-d', $return);
    if (!$first || !$last || $first->format('Y-m-d') !== $pickup || $last->format('Y-m-d') !== $return) {
        return 'Please enter valid dates.';
    }
    if ($first < new DateTimeImmutable('today')) {
        return 'Pickup date cannot be in the past.';
    }
    if ($last < $first) {
        return 'Return date cannot be before pickup date.';
    }
    return null;
}
function total_days($pickup, $return) {
    // Both pickup AND return days are charged (inclusive day count).
    $first = new DateTimeImmutable($pickup);
    $last = new DateTimeImmutable($return);
    return (int)$first->diff($last)->days + 1;
}
function booking_conflicts(PDO $pdo, $vehicleId, $pickup, $return, $excludeId = 0) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings
        WHERE vehicle_id = ? AND id <> ? AND status IN ('pending','approved')
        AND pickup_date <= ? AND return_date >= ?");
    $stmt->execute([$vehicleId, $excludeId, $return, $pickup]);
    return (int)$stmt->fetchColumn() > 0;
}
function categories(PDO $pdo) {
    return $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}
function vehicle_image($image) {
    return $image ? url('uploads/vehicles/' . rawurlencode($image)) : url('assets/images/vehicle.svg');
}
function field($name, $fallback = '') {
    return h($_POST[$name] ?? $fallback);
}
function display_errors($errors) {
    if (!$errors) return;
    echo '<div class="alert error" role="alert"><ul>';
    foreach ($errors as $error) echo '<li>' . h($error) . '</li>';
    echo '</ul></div>';
}
function not_found($message = 'Record not found.') {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/header.php';
    echo '<main class="container section"><h1>404 - Not found</h1><p>' . h($message) . '</p><a class="btn" href="' . h(url()) . '">Go home</a></main>';
    require __DIR__ . '/footer.php';
    exit;
}
