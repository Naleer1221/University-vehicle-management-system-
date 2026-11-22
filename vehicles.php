<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Browse vehicles';
$search = trim($_GET['q'] ?? '');
$category = (int) ($_GET['category'] ?? 0);
$seats = max(0, (int) ($_GET['seats'] ?? 0));
$maxPrice = (float) ($_GET['max_price'] ?? 0);
$pickup = trim($_GET['pickup_date'] ?? '');
$return = trim($_GET['return_date'] ?? '');
$errors = [];
if ($pickup !== '' || $return !== '') {
    if ($pickup === '' || $return === '') $errors[] = 'Provide both dates to check availability.';
    elseif ($message = validate_dates($pickup, $return)) $errors[] = $message;
}
$sql = "SELECT v.*,c.name AS category FROM vehicles v JOIN categories c ON c.id=v.category_id
        WHERE v.is_deleted=0 AND v.status='available'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (v.name LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)';
    for ($i=0; $i<3; $i++) $params[] = '%' . $search . '%';
}
if ($category > 0) { $sql .= ' AND v.category_id=?'; $params[] = $category; }
if ($seats > 0) { $sql .= ' AND v.seats>=?'; $params[] = $seats; }
if ($maxPrice > 0) { $sql .= ' AND v.price_per_day<=?'; $params[] = $maxPrice; }
if (!$errors && $pickup !== '' && $return !== '') {
    $sql .= " AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.vehicle_id=v.id
        AND b.status IN ('pending','approved') AND b.pickup_date<=? AND b.return_date>=?)";
    $params[] = $return; $params[] = $pickup;
}
$sql .= ' ORDER BY v.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
$allCategories = categories($pdo);
$categoryIcons = ['Car'=>'🚘','SUV'=>'🚙','Van'=>'🚐','Motorbike'=>'🏍️','Pickup'=>'🛻','Luxury'=>'✨','Three-wheeler'=>'🛺','Scooter'=>'🛵'];
require __DIR__ . '/includes/header.php';
?>
<main>
  <section class="catalog-heading"><div class="container"><span class="overline">FIND YOUR WAY</span><h1>Choose a ride that fits.</h1><p>Cars, SUVs, vans, scooters, motorbikes and three-wheelers — all in one place.</p><span class="catalog-ghost" aria-hidden="true">EXPLORE</span></div></section>
  <div class="container section catalog-main">
    <div class="category-tabs" aria-label="Vehicle categories">
      <a class="category-tab <?= $category === 0 ? 'selected' : '' ?>" href="<?= h(url('vehicles.php')) ?>">✦ All vehicles</a>
      <?php foreach ($allCategories as $cat): ?><a class="category-tab <?= $category === (int)$cat['id'] ? 'selected' : '' ?>" href="<?= h(url('vehicles.php?category=' . $cat['id'])) ?>"><?= h($categoryIcons[$cat['name']] ?? '🚗') ?> <?= h($cat['name']) ?></a><?php endforeach; ?>
    </div>
    <form class="filter-box" method="get" data-validate>
      <div class="filter-grid">
        <div><label for="q">Search vehicles</label><input id="q" name="q" value="<?= h($search) ?>" placeholder="Model, brand, name..."></div>
        <div><label for="category">Type</label><select id="category" name="category"><option value="">All types</option><?php foreach ($allCategories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option><?php endforeach; ?></select></div>
        <div><label for="seats">Minimum seats</label><input id="seats" type="number" min="0" name="seats" value="<?= $seats ?: '' ?>" placeholder="Any"></div>
        <div><label for="max_price">Max price (LKR/day)</label><input id="max_price" type="number" min="0" name="max_price" value="<?= $maxPrice ?: '' ?>" placeholder="Any"></div>
      </div>
      <div class="filter-bottom"><div class="form-row"><div class="field"><label for="pickup_date">Pickup date</label><input type="date" id="pickup_date" name="pickup_date" value="<?= h($pickup) ?>" min="<?= date('Y-m-d') ?>"></div><div class="field"><label for="return_date">Return date</label><input type="date" id="return_date" name="return_date" value="<?= h($return) ?>"></div></div>
      <div class="filter-actions"><button class="btn" type="submit">Show results →</button><a class="btn outline" href="<?= h(url('vehicles.php')) ?>">Clear</a></div></div>
    </form>
    <?php display_errors($errors); ?>
    <div class="section-heading results-heading"><div><span class="overline">THE FLEET</span><h2><?= count($vehicles) ?> matching vehicles</h2></div><span class="muted">Illustrative demo pictures • Prices per day</span></div>
    <?php if (!$vehicles): ?><div class="empty">No matching vehicles. Change your search, dates or category.</div><?php endif; ?>
    <div class="grid cards">
      <?php foreach ($vehicles as $v): ?><article class="card vehicle-card">
        <a class="card-image-link" href="<?= h(url('vehicle_details.php?id=' . $v['id'])) ?>" aria-label="Details for <?= h($v['name']) ?>"><img class="vehicle-img" src="<?= h(vehicle_image($v['image'])) ?>" alt="Illustrative photo for <?= h($v['category']) ?> rental" loading="lazy"><span class="image-category"><?= h($v['category']) ?></span></a>
        <div class="card-content"><div class="card-small">DRIVEEASY COLLECTION • <?= h($v['year']) ?></div><h3><?= h($v['name']) ?></h3><p class="card-specs"><span>♙ <?= h($v['seats']) ?> seats</span><span>⚙ <?= h($v['transmission']) ?></span><span>◈ <?= h($v['fuel_type']) ?></span></p>
        <div class="card-bottom"><p class="price"><?= h(money($v['price_per_day'])) ?><small> / day</small></p><a class="round-arrow" href="<?= h(url('vehicle_details.php?id=' . $v['id'])) ?>" aria-label="View <?= h($v['name']) ?>">↗</a></div></div>
      </article><?php endforeach; ?>
    </div>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
