<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
$pageTitle='Admin dashboard';
$vehicles=(int)$pdo->query('SELECT COUNT(*) FROM vehicles WHERE is_deleted=0')->fetchColumn();
$customers=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer' AND is_active=1")->fetchColumn();
$bookings=(int)$pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn();
$revenue=(float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE status IN ('approved','completed')")->fetchColumn();
$pendingRefunds=(int)$pdo->query("SELECT COUNT(*) FROM refund_requests WHERE status='pending'")->fetchColumn();
$recordedRefunds=(float)$pdo->query("SELECT COALESCE(SUM(approved_amount),0) FROM refund_requests WHERE status='refunded'")->fetchColumn();
$statusTotals=$pdo->query('SELECT status,COUNT(*) AS total FROM bookings GROUP BY status ORDER BY status')->fetchAll();
$recent=$pdo->query('SELECT b.*,u.full_name,v.name AS vehicle_name FROM bookings b JOIN users u ON b.user_id=u.id JOIN vehicles v ON b.vehicle_id=v.id ORDER BY b.created_at DESC,b.id DESC LIMIT 8')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><div class="admin-heading"><h1>Admin dashboard</h1><p>Manage vehicles, customers, and bookings.</p></div>
<?php require __DIR__.'/nav.php'; ?>
<div class="grid stats"><div class="stat"><span>Vehicles</span><strong><?= $vehicles ?></strong></div>
<div class="stat"><span>Customers</span><strong><?= $customers ?></strong></div>
<div class="stat"><span>Bookings</span><strong><?= $bookings ?></strong></div>
<div class="stat"><span>Booking value (approved + completed)</span><strong><?= h(money($revenue)) ?></strong></div></div>
<p style="margin-top:18px"><a class="btn" href="<?= h(url('admin/refunds.php')) ?>">Review refund requests (<?= h($pendingRefunds) ?> pending)</a></p>
<p class="form-note">Manual refunds recorded: <?= h(money($recordedRefunds)) ?>. These values are booking records, not proof of actual payments.</p>
<h2 style="margin-top:35px">Recent bookings</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Price</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $b): ?><tr><td>#<?= h($b['id']) ?></td><td><?= h($b['full_name']) ?></td>
<td><?= h($b['vehicle_name']) ?></td><td><?= h($b['pickup_date']) ?></td><td><?= h(money($b['total_price'])) ?></td>
<td><span class="tag <?= h($b['status']) ?>"><?= h($b['status']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div>
<h2 style="margin-top:34px">Booking status report</h2>
<div class="grid stats">
<?php foreach($statusTotals as $result): ?><div class="stat"><span><?= h(ucfirst($result['status'])) ?></span><strong><?= h($result['total']) ?></strong></div><?php endforeach; ?>
</div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
