<?php
http_response_code(404);
require_once __DIR__.'/includes/functions.php';
$pageTitle='Page not found';require __DIR__.'/includes/header.php';
?>
<main class="container section"><h1>404 — Page not found</h1><p>We could not find that page.</p><a class="btn" href="<?= h(url()) ?>">Back to home</a></main>
<?php require __DIR__.'/includes/footer.php'; ?>
