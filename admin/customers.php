<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
$pageTitle='Manage customers';
$customers=$pdo->query("SELECT u.id,u.full_name,u.email,u.phone,u.license_no,u.is_active,u.created_at,COUNT(b.id) AS bookings
    FROM users u LEFT JOIN bookings b ON b.user_id=u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.id DESC")->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><h1>Manage customers</h1><?php require __DIR__.'/nav.php'; ?>
<div class="table-wrap admin-table"><table><thead><tr><th>ID</th><th>Name</th><th>Email / phone</th><th>Bookings</th><th>Account</th><th>Actions</th></tr></thead><tbody>
<?php foreach($customers as $c): ?><tr><td><?= h($c['id']) ?></td><td><?= h($c['full_name']) ?></td><td><?= h($c['email']) ?><br><small><?= h($c['phone']) ?></small></td>
<td><?= h($c['bookings']) ?></td><td><span class="tag <?= (int)$c['is_active']?'approved':'cancelled' ?>"><?= (int)$c['is_active']?'Active':'Disabled' ?></span></td>
<td><div class="actions"><a class="btn small" href="<?= h(url('admin/customer_edit.php?id='.$c['id'])) ?>">Edit</a>
<?php if((int)$c['is_active']): ?><form method="post" action="<?= h(url('admin/customer_delete.php')) ?>" data-confirm="Disable this customer account?">
<?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($c['id']) ?>"><button class="btn danger small">Disable</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
</tbody></table></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
