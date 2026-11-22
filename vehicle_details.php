<?php
require_once __DIR__ . '/includes/functions.php';
$id=(int)($_GET['id'] ?? 0);
$stmt=$pdo->prepare('SELECT v.*,c.name AS category FROM vehicles v JOIN categories c ON c.id=v.category_id WHERE v.id=? AND v.is_deleted=0');
$stmt->execute([$id]); $vehicle=$stmt->fetch();
if (!$vehicle) not_found('That vehicle is not available in our catalogue.');
$pageTitle=$vehicle['name'];
require __DIR__ . '/includes/header.php';
?>
<main class="container section vehicle-details-page">
  <a class="breadcrumb" href="<?= h(url('vehicles.php')) ?>">← Back to vehicles</a>
  <div class="detail-head"><div><span class="overline">THE DRIVE EASY FLEET / <?= h(strtoupper($vehicle['category'])) ?></span><h1><?= h($vehicle['name']) ?></h1><p class="muted"><?= h($vehicle['year']) ?> · <?= h($vehicle['category']) ?> · <?= h($vehicle['brand']) ?></p></div><span class="tag <?= h($vehicle['status']) ?>"><?= h(ucfirst($vehicle['status'])) ?></span></div>
  <div class="details">
    <div>
      <div class="detail-photo"><img class="details-image" src="<?= h(vehicle_image($vehicle['image'])) ?>" alt="Illustrative <?= h($vehicle['category']) ?> photo"><span>DEMO FLEET IMAGE</span></div>
      <div class="panel detail-description"><span class="overline">GET TO KNOW YOUR RIDE</span><h2>Vehicle details</h2><p><?= nl2br(h($vehicle['description'])) ?></p>
        <div class="spec-grid"><div><small>Brand</small><strong><?= h($vehicle['brand']) ?></strong></div><div><small>Model</small><strong><?= h($vehicle['model']) ?></strong></div><div><small>Year</small><strong><?= h($vehicle['year']) ?></strong></div><div><small>Seats</small><strong><?= h($vehicle['seats']) ?></strong></div><div><small>Transmission</small><strong><?= h($vehicle['transmission']) ?></strong></div><div><small>Fuel type</small><strong><?= h($vehicle['fuel_type']) ?></strong></div></div>
      </div>
    </div>
    <div class="panel booking-panel"><span class="overline">READY WHEN YOU ARE</span><h2>Reserve this ride.</h2><p class="price"><?= h(money($vehicle['price_per_day'])) ?> <small>/ day</small></p><p class="muted">Choose your dates and submit a booking request.</p>
      <?php if($vehicle['status']!=='available'): ?><p class="alert error">Vehicle cannot be booked at the moment.</p>
      <?php elseif(!current_user()): ?><p>Log in as a customer to book this vehicle.</p><a class="btn" href="<?= h(url('login.php')) ?>">Log in to book →</a>
      <?php elseif(current_user()['role']==='admin'): ?><p>Administrators can manage bookings in the admin panel. Use a customer account to book.</p>
      <?php else: ?>
      <form method="post" action="<?= h(url('book.php')) ?>" data-booking-calculator data-price="<?= h($vehicle['price_per_day']) ?>" data-validate>
        <?php csrf_field(); ?><input type="hidden" name="vehicle_id" value="<?= h($vehicle['id']) ?>">
        <div class="field"><label for="pickup_date">Pickup date</label><input type="date" id="pickup_date" name="pickup_date" min="<?= date('Y-m-d') ?>" required></div>
        <div class="field"><label for="return_date">Return date</label><input type="date" id="return_date" name="return_date" required></div>
        <div class="field"><label for="notes">Notes (optional)</label><textarea id="notes" name="notes" maxlength="1000" placeholder="Your pickup preferences"></textarea></div>
        <p class="total-preview" data-total>Choose pickup and return dates.</p><button type="submit" class="btn btn-block">Request booking →</button>
      </form><?php endif; ?>
      <p class="booking-disclaimer">No online payment is collected by this demo website. Cancellation/refund requests require admin review.</p>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
