<?php
require_once __DIR__ . '/includes/auth.php';
require_customer();
$pageTitle = 'My bookings';
$stmt = $pdo->prepare('SELECT b.*,v.name AS vehicle_name,v.image,
  r.id AS refund_id,r.status AS refund_status,r.reason AS refund_reason,
  r.admin_note,r.requested_amount,r.approved_amount,r.refund_reference
  FROM bookings b JOIN vehicles v ON b.vehicle_id=v.id
  LEFT JOIN refund_requests r ON r.booking_id=b.id
  WHERE b.user_id=? ORDER BY b.created_at DESC,b.id DESC');
$stmt->execute([current_user()['id']]);
$bookings = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<main class="container section">
  <div class="section-heading"><h1>My bookings</h1><a class="btn" href="<?= h(url('vehicles.php')) ?>">Book another vehicle</a></div>
  <?php if (!$bookings): ?>
    <div class="empty">You have no bookings yet. <a href="<?= h(url('vehicles.php')) ?>">Browse available vehicles</a>.</div>
  <?php else: ?>
    <div class="table-wrap"><table><thead><tr>
      <th>ID / Vehicle</th><th>Dates</th><th>Cost</th><th>Booking</th><th>Refund request</th><th>Actions</th>
    </tr></thead><tbody>
      <?php foreach ($bookings as $b): ?>
        <tr>
          <td>#<?= h($b['id']) ?><br><strong><?= h($b['vehicle_name']) ?></strong></td>
          <td><?= h($b['pickup_date']) ?> to <?= h($b['return_date']) ?><br><small><?= h($b['total_days']) ?> days</small></td>
          <td><?= h(money($b['total_price'])) ?></td>
          <td><span class="tag <?= h($b['status']) ?>"><?= h(ucfirst($b['status'])) ?></span></td>
          <td>
            <?php if ($b['refund_id']): ?>
              <span class="tag <?= h($b['refund_status']) ?>"><?= h(ucfirst($b['refund_status'])) ?></span>
              <div class="form-note">Requested: <?= h(money($b['requested_amount'])) ?></div>
              <?php if ($b['approved_amount'] !== null): ?><div class="form-note">Approved: <?= h(money($b['approved_amount'])) ?></div><?php endif; ?>
              <?php if ($b['admin_note']): ?><div class="form-note"><strong>Admin:</strong> <?= h($b['admin_note']) ?></div><?php endif; ?>
              <?php if ($b['refund_reference']): ?><div class="form-note">Reference: <?= h($b['refund_reference']) ?></div><?php endif; ?>
            <?php else: ?>
              <small class="muted">No request</small>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($b['status'] === 'pending'): ?>
              <div class="actions"><a class="btn small" href="<?= h(url('edit_booking.php?id='.$b['id'])) ?>">Edit dates</a></div>
              <form method="post" action="<?= h(url('cancel_booking.php')) ?>" data-confirm="Cancel this booking?" class="cancel-box">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= h($b['id']) ?>">
                <label for="reason_<?= h($b['id']) ?>">Cancellation reason</label>
                <textarea id="reason_<?= h($b['id']) ?>" name="reason" required minlength="10" maxlength="1000" rows="2" placeholder="Explain why you are cancelling"></textarea>
                <label class="checkbox-label"><input type="checkbox" name="request_refund" value="1"> Also request a refund (admin reviews)</label>
                <button class="btn small danger" type="submit">Cancel booking</button>
              </form>
            <?php elseif (!$b['refund_id'] && in_array($b['status'], ['approved','cancelled'], true)): ?>
              <a class="btn small" href="<?= h(url('refund_request.php?id='.$b['id'])) ?>"><?= $b['status']==='approved' ? 'Request cancellation &amp; refund' : 'Request refund' ?></a>
            <?php else: ?>
              <small class="muted">No further action</small>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody></table></div>
    <p class="form-note" style="margin-top:16px">Refund requests are reviewed manually. An approved request does not mean money was transferred; "Refunded" means admin recorded a completed manual refund.</p>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
