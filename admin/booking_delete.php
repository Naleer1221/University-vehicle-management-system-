<?php
require_once __DIR__.'/../includes/auth.php';
require_admin();
check_csrf();
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM refund_requests WHERE booking_id=?');
$stmt->execute([$id]);
if ((int)$stmt->fetchColumn() > 0) {
    set_flash('error', 'Cannot delete a booking that has a refund request. Keep it as a record.');
    go('admin/bookings.php');
}
try {
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id=?');
    $stmt->execute([$id]);
    set_flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Booking deleted.' : 'Booking not found.');
} catch (PDOException $e) {
    set_flash('error', 'Could not delete booking.');
}
go('admin/bookings.php');
