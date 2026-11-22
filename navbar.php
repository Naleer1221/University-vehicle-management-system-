<?php $user = current_user(); ?>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="<?= h(url()) ?>" aria-label="DriveEasy home">
      <svg class="brand-icon" width="44" height="30" viewBox="0 0 52 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4 20h4l5-11h24l8 11h3v4h-4a5 5 0 0 0-10 0H19a5 5 0 0 0-10 0H4v-4Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M14 9 19 4h13l5 5M13 19h30" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/><circle cx="14" cy="24" r="3" fill="currentColor"/><circle cx="39" cy="24" r="3" fill="currentColor"/></svg>
      <span>drive<span class="brand-accent">easy.</span><small>RENT • RIDE • EXPLORE</small></span>
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">☰ Menu</button>
    <nav class="site-nav" id="site-nav" aria-label="Main navigation">
      <a href="<?= h(url()) ?>">Home</a>
      <a href="<?= h(url('vehicles.php')) ?>">Our vehicles</a>
      <a href="<?= h(url('contact.php')) ?>">Contact</a>
      <?php if (!$user): ?>
        <a href="<?= h(url('login.php')) ?>">Log in</a>
        <a class="nav-pill" href="<?= h(url('register.php')) ?>">Get started ↗</a>
      <?php elseif ($user['role'] === 'admin'): ?>
        <a class="nav-pill" href="<?= h(url('admin/dashboard.php')) ?>">Admin panel ↗</a>
        <a href="<?= h(url('logout.php')) ?>">Log out</a>
      <?php else: ?>
        <a href="<?= h(url('my_bookings.php')) ?>">My bookings</a>
        <a href="<?= h(url('profile.php')) ?>">My profile</a>
        <a class="nav-pill" href="<?= h(url('logout.php')) ?>">Log out ↗</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
