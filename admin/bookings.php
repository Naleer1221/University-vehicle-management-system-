<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
$pageTitle='Manage bookings';
$bookings=$pdo->query('SELECT b.*,v.name AS vehicle_name,u.full_name,u.email FROM bookings b JOIN vehicles v ON b.vehicle_id=v.id JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC,b.id DESC')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><h1>Manage bookings</h1><?php require __DIR__.'/nav.php'; ?>
<div class="table-wrap admin-table"><table><thead><tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Dates</th><th>Total</th><th>Status</th><th>Update / delete</th></tr></thead><tbody>
<?php foreach($bookings as $b): ?><tr><td>#<?= h($b['id']) ?></td><td><?= h($b['full_name']) ?><br><small><?= h($b['email']) ?></small></td>
<td><?= h($b['vehicle_name']) ?></td><td><?= h($b['pickup_date']) ?> to <?= h($b['return_date']) ?></td><td><?= h(money($b['total_price'])) ?></td>
<td><span class="tag <?= h($b['status']) ?>"><?= h($b['status']) ?></span></td>
<td><div class="actions"><form class="inline-form" method="post" action="<?= h(url('admin/booking_update.php')) ?>">
<?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($b['id']) ?>"><label class="visually-hidden" for="status_<?= h($b['id']) ?>">Change status</label>
<select id="status_<?= h($b['id']) ?>" name="status"><?php foreach(['pending','approved','rejected','cancelled','completed'] as $status): ?><option value="<?= h($status) ?>" <?= $b['status']===$status?'selected':'' ?>><?= h(ucfirst($status)) ?></option><?php endforeach; ?></select>
<button class="btn small">Save</button></form>
<form class="inline-form" method="post" action="<?= h(url('admin/booking_delete.php')) ?>" data-confirm="Permanently delete this booking?">
<?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($b['id']) ?>"><button class="btn danger small">Delete</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
