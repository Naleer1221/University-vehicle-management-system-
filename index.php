<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Find your next ride';
$allCategories = categories($pdo);
$vehicles = $pdo->query("SELECT v.*, c.name AS category FROM vehicles v
    JOIN categories c ON v.category_id = c.id
    WHERE v.is_deleted = 0 AND v.status = 'available'
    ORDER BY CASE WHEN v.image IS NULL THEN 1 ELSE 0 END, v.id DESC LIMIT 8")->fetchAll();
$totalVehicles = (int) $pdo->query("SELECT COUNT(*) FROM vehicles WHERE is_deleted=0 AND status='available'")->fetchColumn();
$categoryIcons = ['Car'=>'🚘','SUV'=>'🚙','Van'=>'🚐','Motorbike'=>'🏍️','Pickup'=>'🛻','Luxury'=>'✨','Three-wheeler'=>'🛺','Scooter'=>'🛵'];
require __DIR__ . '/includes/header.php';
?>
<main>
  <!-- Large, airy hero inspired by the uploaded white product-photo design. -->
  <section class="hero">
    <div class="container hero-inner">
      <div class="hero-copy">
        <span class="eyebrow"><span class="pulse-dot"></span> YOUR ROAD. YOUR CHOICE.</span>
        <h1>Every journey<br>starts with a <em>ride.</em></h1>
        <p>Find your fit, from city cars and SUVs to motorbikes and three-wheelers. Choose dates, request a booking and travel your way.</p>
        <div class="hero-actions">
          <a class="btn hero-btn" href="<?= h(url('vehicles.php')) ?>">Explore vehicles <span aria-hidden="true">↗</span></a>
          <a class="btn light" href="#how-it-works">How it works <span aria-hidden="true">↓</span></a>
        </div>
        <div class="hero-mini"><span class="mini-line"></span> CARS <span>·</span> BIKES <span>·</span> AUTOS <span>·</span> VANS</div>
      </div>
      <div class="hero-visual-note" aria-hidden="true"><span>01 / THE OPEN ROAD</span><strong>DRIVE DIFFERENT.</strong></div>
    </div>
  </section>

  <section class="search-band" aria-label="Find a vehicle">
    <div class="container">
      <form class="quick-search" method="get" action="<?= h(url('vehicles.php')) ?>">
        <div class="search-title"><span class="search-icon">⌕</span><div><strong>Find your ride</strong><small>What are you looking for?</small></div></div>
        <div class="field"><label for="quick_q">Vehicle or brand</label><input id="quick_q" name="q" placeholder="Toyota, Honda, Bajaj..."></div>
        <div class="field"><label for="quick_category">Vehicle type</label><select name="category" id="quick_category"><option value="">All vehicles</option>
          <?php foreach ($allCategories as $cat): ?><option value="<?= (int)$cat['id'] ?>"><?= h($cat['name']) ?></option><?php endforeach; ?>
        </select></div>
        <button class="btn" type="submit">Search now <span aria-hidden="true">→</span></button>
      </form>
    </div>
  </section>

  <section class="container section categories-section" id="categories">
    <div class="section-heading"><div><span class="overline">FIND YOUR MATCH</span><h2>What moves you?</h2><p class="muted">Choose a category and explore rides at your own pace.</p></div><a class="text-link" href="<?= h(url('vehicles.php')) ?>">See all vehicles <span aria-hidden="true">↗</span></a></div>
    <div class="category-grid">
      <?php foreach ($allCategories as $cat): ?>
      <a class="category-card" href="<?= h(url('vehicles.php?category=' . $cat['id'])) ?>"><span class="category-icon" aria-hidden="true"><?= h($categoryIcons[$cat['name']] ?? '🚗') ?></span><strong><?= h($cat['name']) ?></strong><small>Explore <span aria-hidden="true">↗</span></small></a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="featured-zone"><div class="container section">
    <div class="section-heading"><div><span class="overline">THE COLLECTION</span><h2>Popular picks, made for you.</h2><p class="muted"><?= $totalVehicles ?> available vehicles in the demo catalogue. Actual date availability is checked during booking.</p></div><a class="text-link" href="<?= h(url('vehicles.php')) ?>">Browse all <span aria-hidden="true">↗</span></a></div>
    <div class="grid cards">
      <?php foreach ($vehicles as $vehicle): ?>
      <article class="card vehicle-card">
        <a class="card-image-link" href="<?= h(url('vehicle_details.php?id=' . $vehicle['id'])) ?>" aria-label="View <?= h($vehicle['name']) ?>">
          <img class="vehicle-img" src="<?= h(vehicle_image($vehicle['image'])) ?>" alt="Illustrative photo for <?= h($vehicle['category']) ?> rental" loading="lazy">
          <span class="image-category"><?= h($vehicle['category']) ?></span>
        </a>
        <div class="card-content"><div class="card-small">RENTAL VEHICLE <span>•</span> <?= h($vehicle['year']) ?></div><h3><?= h($vehicle['name']) ?></h3>
          <p class="card-specs"><span>♙ <?= h($vehicle['seats']) ?> seats</span><span>⚙ <?= h($vehicle['transmission']) ?></span><span>◈ <?= h($vehicle['fuel_type']) ?></span></p>
          <div class="card-bottom"><p class="price"><?= h(money($vehicle['price_per_day'])) ?><small> / day</small></p><a class="round-arrow" href="<?= h(url('vehicle_details.php?id=' . $vehicle['id'])) ?>" aria-label="View details of <?= h($vehicle['name']) ?>">↗</a></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div></section>

  <section class="container section" id="how-it-works">
    <div class="section-heading"><div><span class="overline">NO COMPLICATED STEPS</span><h2>Your ride in three steps.</h2><p class="muted">Simple requests, clear booking statuses, and no confusing forms.</p></div></div>
    <div class="grid steps">
      <div class="step"><span class="step-n">01</span><span class="step-icon">⌕</span><h3>Discover your vehicle</h3><p>Browse photos, compare daily prices and filter by type, seats or travel dates.</p></div>
      <div class="step"><span class="step-n">02</span><span class="step-icon">▦</span><h3>Choose your dates</h3><p>Pick a start and end date. The system checks overlapping bookings.</p></div>
      <div class="step"><span class="step-n">03</span><span class="step-icon">✓</span><h3>Manage your booking</h3><p>Track your request or submit a cancellation and refund reason for admin review.</p></div>
    </div>
  </section>
  <section class="container experience-banner"><div><span class="overline">THE FREEDOM TO EXPLORE</span><h2>Different journeys.<br>One easy place to book.</h2><p>Find a car for the weekend or a motorbike for a short city ride.</p><a class="btn light" href="<?= h(url('vehicles.php')) ?>">Find your vehicle ↗</a></div><span class="experience-text" aria-hidden="true">GO</span></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
