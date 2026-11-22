<?php
require_once __DIR__ . '/includes/auth.php';
require_customer();
check_csrf();

$id = (int)($_POST['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
$requestRefund = isset($_POST['request_refund']);

if ($id <= 0 || strlen($reason) < 10 || strlen($reason) > 1000) {
    set_flash('error', 'Please give a cancellation reason (10 to 1000 characters).');
    go('my_bookings.php');
}

try {
    $pdo->beginTransaction();
    // Only the owner can cancel a PENDING booking.
    $stmt = $pdo->prepare('SELECT id,user_id,total_price,status FROM bookings WHERE id=? AND user_id=? FOR UPDATE');
    $stmt->execute([$id, current_user()['id']]);
    $booking = $stmt->fetch();
    if (!$booking || $booking['status'] !== 'pending') {
        throw new RuntimeException('Only your pending bookings can be cancelled directly.');
    }
    $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled', cancellation_reason=? WHERE id=?");
    $stmt->execute([$reason, $id]);
    if ($requestRefund) {
        $stmt = $pdo->prepare("INSERT INTO refund_requests (booking_id,user_id,reason,requested_amount)
                               VALUES (?,?,?,?)");
        $stmt->execute([$id, current_user()['id'], $reason, $booking['total_price']]);
    }
    $pdo->commit();
    set_flash('success', $requestRefund
        ? 'Booking cancelled. Refund request sent to admin for review. No money has been transferred.'
        : 'Booking cancelled.');
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', 'Could not cancel booking. Please try again.');
}
go('my_bookings.php');
