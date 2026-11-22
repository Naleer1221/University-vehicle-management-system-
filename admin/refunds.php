<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$pageTitle = 'Refund requests';
$requests = $pdo->query('SELECT r.*,b.status AS booking_status,b.total_price,
  u.full_name,u.email,v.name AS vehicle_name FROM refund_requests r
  JOIN bookings b ON b.id=r.booking_id
  JOIN users u ON u.id=r.user_id
  JOIN vehicles v ON v.id=b.vehicle_id
  ORDER BY (r.status = \'pending\') DESC, r.created_at DESC, r.id DESC')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<main class="container section">
  <h1>Customer refund requests</h1>
  <?php require __DIR__ . '/nav.php'; ?>
  <p class="form-note">Read the customer's reason and check evidence of payment and the cancellation policy before approving. This site cannot send money. Record "Refunded" only after you have paid the customer outside the site.</p>
  <?php if (!$requests): ?>
    <div class="empty">No refund requests yet.</div>
  <?php else: ?>
  <div class="table-wrap admin-table"><table><thead><tr>
    <th>Request / booking</th><th>Customer</th><th>Reason</th><th>Amount</th><th>Decision / notes</th><th>Admin action</th>
  </tr></thead><tbody>
  <?php foreach ($requests as $r): ?>
    <tr>
      <td>#<?= h($r['id']) ?> / Booking #<?= h($r['booking_id']) ?><br><?= h($r['vehicle_name']) ?><br><small>Booking: <?= h($r['booking_status']) ?></small></td>
      <td><?= h($r['full_name']) ?><br><small><?= h($r['email']) ?></small></td>
      <td><?= nl2br(h($r['reason'])) ?></td>
      <td>Requested:<br><?= h(money($r['requested_amount'])) ?>
        <?php if ($r['approved_amount'] !== null): ?><br>Approved:<br><?= h(money($r['approved_amount'])) ?><?php endif; ?></td>
      <td><span class="tag <?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span>
        <?php if ($r['admin_note']): ?><p class="form-note">Admin note: <?= h($r['admin_note']) ?></p><?php endif; ?>
        <?php if ($r['refund_reference']): ?><p class="form-note">Payment ref: <?= h($r['refund_reference']) ?></p><?php endif; ?>
      </td>
      <td>
        <?php if ($r['status'] === 'pending'): ?>
          <form method="post" action="<?= h(url('admin/refund_update.php')) ?>" class="refund-admin-form">
            <?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($r['id']) ?>">
            <label for="amount_<?= h($r['id']) ?>">Approved refund (LKR)</label>
            <input id="amount_<?= h($r['id']) ?>" type="number" name="amount" min="0.01" max="<?= h($r['requested_amount']) ?>" step="0.01" value="<?= h($r['requested_amount']) ?>" required>
            <label for="note_<?= h($r['id']) ?>">Admin note (required to reject)</label>
            <textarea id="note_<?= h($r['id']) ?>" name="note" maxlength="1000" placeholder="Reason for decision / payment verification"></textarea>
            <div class="actions"><button class="btn small" type="submit" name="action" value="approve">Approve</button>
              <button class="btn small danger" type="submit" name="action" value="reject">Reject</button></div>
          </form>
        <?php elseif ($r['status'] === 'approved'): ?>
          <form method="post" action="<?= h(url('admin/refund_update.php')) ?>" class="refund-admin-form" data-confirm="Confirm you already transferred the refund outside the website?">
            <?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($r['id']) ?>">
            <input type="hidden" name="action" value="refunded">
            <label for="reference_<?= h($r['id']) ?>">Manual payment / transfer reference</label>
            <input id="reference_<?= h($r['id']) ?>" name="refund_reference" maxlength="120" required placeholder="Bank reference or receipt number">
            <button class="btn small" type="submit">Mark refunded</button>
          </form>
        <?php else: ?><small class="muted">Review finished</small><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
