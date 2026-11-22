<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
check_csrf();
$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$note = trim($_POST['note'] ?? '');
$reference = trim($_POST['refund_reference'] ?? '');

if ($id <= 0 || !in_array($action, ['approve','reject','refunded'], true)) {
    set_flash('error', 'Invalid refund action.');
    go('admin/refunds.php');
}
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT r.*,b.status AS booking_status,b.total_price
                           FROM refund_requests r JOIN bookings b ON b.id=r.booking_id
                           WHERE r.id=? FOR UPDATE');
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) throw new RuntimeException('Refund request not found.');

    if ($action === 'approve') {
        if ($request['status'] !== 'pending') throw new RuntimeException('This request was already reviewed.');
        if (!in_array($request['booking_status'], ['approved','cancelled'], true)) {
            throw new RuntimeException('Booking status is not eligible for refund approval.');
        }
        $amountText = trim((string)($_POST['amount'] ?? ''));
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $amountText)) {
            throw new RuntimeException('Enter a valid refund amount (maximum two decimal places).');
        }
        $amount = (float)$amountText;
        if ($amount <= 0 || $amount > (float)$request['requested_amount']) {
            throw new RuntimeException('Refund amount must be greater than zero and cannot exceed requested amount.');
        }
        if (strlen($note) > 1000) throw new RuntimeException('Admin note is too long.');
        // Approval cancels the booking. It DOES NOT transfer any money.
        $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled',
                                cancellation_reason=CASE WHEN cancellation_reason='' THEN ? ELSE cancellation_reason END WHERE id=?");
        $stmt->execute([$request['reason'], $request['booking_id']]);
        $stmt = $pdo->prepare("UPDATE refund_requests SET status='approved',approved_amount=?,admin_note=?,reviewed_at=NOW() WHERE id=?");
        $stmt->execute([$amount, $note, $id]);
        $message = 'Refund approved for manual processing. No money was sent.';
    } elseif ($action === 'reject') {
        if ($request['status'] !== 'pending') throw new RuntimeException('This request was already reviewed.');
        if (strlen($note) < 5 || strlen($note) > 1000) {
            throw new RuntimeException('Explain rejection in 5 to 1000 characters.');
        }
        $stmt = $pdo->prepare("UPDATE refund_requests SET status='rejected',admin_note=?,reviewed_at=NOW() WHERE id=?");
        $stmt->execute([$note, $id]);
        $message = 'Refund rejected. Customer can see the reason.';
    } else {
        if ($request['status'] !== 'approved') throw new RuntimeException('Approve the request first.');
        if (strlen($reference) < 3 || strlen($reference) > 120) {
            throw new RuntimeException('Enter a valid reference from the completed manual refund (3 to 120 characters).');
        }
        $stmt = $pdo->prepare("UPDATE refund_requests SET status='refunded',refund_reference=?,refunded_at=NOW() WHERE id=?");
        $stmt->execute([$reference, $id]);
        $message = 'Manual refund marked as completed.';
    }
    $pdo->commit();
    set_flash('success', $message);
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', 'Could not update refund request.');
}
go('admin/refunds.php');
