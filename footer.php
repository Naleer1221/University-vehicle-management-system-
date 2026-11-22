<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-intro"><a class="footer-brand" href="<?= h(url()) ?>">drive<span>easy.</span></a><p>Find your next ride and make booking requests with confidence.</p><span class="footer-note">University demonstration • Sri Lanka • LKR</span></div>
    <div class="footer-links"><strong>EXPLORE</strong><a href="<?= h(url('vehicles.php')) ?>">All vehicles</a><a href="<?= h(url('contact.php')) ?>">Contact</a></div>
    <div class="footer-links"><strong>YOUR ACCOUNT</strong><a href="<?= h(url('login.php')) ?>">Log in</a><a href="<?= h(url('my_bookings.php')) ?>">My bookings</a></div>
  </div>
  <div class="container footer-bottom"><span>&copy; <?= date('Y') ?> DriveEasy. Student project.</span><span>Demo vehicles and illustrative pictures • No online payment processing</span></div>
</footer>
<script src="<?= h(url('assets/js/main.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/main.js')) ?>" defer></script>
<script src="<?= h(url('assets/js/validation.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/validation.js')) ?>" defer></script>
<script src="<?= h(url('assets/js/booking.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/booking.js')) ?>" defer></script>
</body>
</html>
