<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
$pageTitle='Manage vehicles';
$vehicles=$pdo->query('SELECT v.*,c.name AS category FROM vehicles v JOIN categories c ON v.category_id=c.id WHERE v.is_deleted=0 ORDER BY v.id DESC')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><div class="section-heading"><h1>Manage vehicles</h1><a class="btn" href="<?= h(url('admin/vehicle_add.php')) ?>">+ Add vehicle</a></div>
<?php require __DIR__.'/nav.php'; ?>
<div class="table-wrap admin-table"><table><thead><tr><th>ID</th><th>Image</th><th>Vehicle</th><th>Category</th><th>Registration</th><th>Price / day</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($vehicles as $v): ?><tr><td><?= h($v['id']) ?></td><td><img class="admin-vehicle-thumb" src="<?= h(vehicle_image($v['image'])) ?>" alt="" loading="lazy"></td><td><?= h($v['name']) ?></td><td><?= h($v['category']) ?></td><td><?= h($v['registration_no']) ?></td><td><?= h(money($v['price_per_day'])) ?></td>
<td><span class="tag <?= h($v['status']) ?>"><?= h($v['status']) ?></span></td><td><div class="actions">
<a class="btn small" href="<?= h(url('admin/vehicle_edit.php?id='.$v['id'])) ?>">Edit</a>
<form method="post" action="<?= h(url('admin/vehicle_delete.php')) ?>" data-confirm="Delete this vehicle? It will be hidden from the website.">
<?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($v['id']) ?>"><button class="btn small danger">Delete</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
