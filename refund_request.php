<?php
require_once __DIR__ . '/includes/auth.php';
require_customer();

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT b.*, v.name AS vehicle_name FROM bookings b
                       JOIN vehicles v ON v.id=b.vehicle_id
                       WHERE b.id=? AND b.user_id=?');
$stmt->execute([$id, current_user()['id']]);
$booking = $stmt->fetch();
if (!$booking || !in_array($booking['status'], ['approved','cancelled'], true)) {
    set_flash('error', 'Refund requests are available for your approved or cancelled bookings.');
    go('my_bookings.php');
}
$stmt = $pdo->prepare('SELECT id FROM refund_requests WHERE booking_id=?');
$stmt->execute([$id]);
if ($stmt->fetch()) {
    set_flash('error', 'A refund request already exists for this booking.');
    go('my_bookings.php');
}

$errors = [];
if (is_post()) {
    check_csrf();
    $reason = trim($_POST['reason'] ?? '');
    if (strlen($reason) < 10 || strlen($reason) > 1000) {
        $errors[] = 'Please explain your reason in 10 to 1000 characters.';
    }
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Recheck ownership/status inside the transaction.
            $stmt = $pdo->prepare('SELECT id,status,total_price FROM bookings WHERE id=? AND user_id=? FOR UPDATE');
            $stmt->execute([$id, current_user()['id']]);
            $locked = $stmt->fetch();
            if (!$locked || !in_array($locked['status'], ['approved','cancelled'], true)) {
                throw new RuntimeException('Booking is no longer eligible.');
            }
            $stmt = $pdo->prepare('INSERT INTO refund_requests (booking_id,user_id,reason,requested_amount)
                                   VALUES (?,?,?,?)');
            $stmt->execute([$id, current_user()['id'], $reason, $locked['total_price']]);
            $pdo->commit();
            set_flash('success', 'Your refund request was sent to admin. Check My bookings for updates.');
            go('my_bookings.php');
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $e->getMessage();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Could not submit request. It might already exist.';
        }
    }
}
$pageTitle = 'Request a refund';
require __DIR__ . '/includes/header.php';
?>
<main class="container section">
  <div class="form-card">
    <h1>Request cancellation / refund</h1>
    <p><strong>Booking #<?= h($booking['id']) ?></strong> — <?= h($booking['vehicle_name']) ?></p>
    <p>Booking amount: <strong><?= h(money($booking['total_price'])) ?></strong></p>
    <?php if ($booking['status'] === 'approved'): ?>
      <p class="form-note">Your approved booking stays active while admin reviews the request. If admin approves, the booking will be cancelled.</p>
    <?php else: ?>
      <p class="form-note">This booking is already cancelled. Admin will check whether any payment was received.</p>
    <?php endif; ?>
    <p class="form-note">This website has no online payment system. Approval is not a money transfer. Admin must verify your payment and process any eligible refund manually.</p>
    <?php display_errors($errors); ?>
    <form method="post" data-validate>
      <?php csrf_field(); ?>
      <input type="hidden" name="id" value="<?= h($id) ?>">
      <div class="field"><label for="reason">Why are you requesting a refund?</label>
        <textarea name="reason" id="reason" minlength="10" maxlength="1000" required placeholder="Please explain why you are cancelling the booking or asking for a refund."><?= field('reason') ?></textarea>
      </div>
      <button class="btn" type="submit">Send request to admin</button>
      <a class="btn outline" href="<?= h(url('my_bookings.php')) ?>">Back</a>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
