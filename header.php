<?php
require_once __DIR__ . '/csrf.php';
$pageTitle = $pageTitle ?? 'Vehicle Booking';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="DriveEasy vehicle rental: cars, motorbikes, scooters, autos and vans">
  <title><?= h($pageTitle) ?> | DriveEasy</title>
  <link rel="icon" href="<?= h(url('assets/images/vehicle.svg')) ?>" type="image/svg+xml">
  <link rel="stylesheet" href="<?= h(url('assets/css/style.css') . '?v=' . filemtime(__DIR__ . '/../assets/css/style.css')) ?>">
  <?php if (str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/')): ?>
    <link rel="stylesheet" href="<?= h(url('assets/css/admin.css') . '?v=' . filemtime(__DIR__ . '/../assets/css/admin.css')) ?>">
  <?php endif; ?>
</head>
<body>
<?php require __DIR__ . '/navbar.php'; ?>
<?php $flash = get_flash(); if ($flash): ?>
  <div class="container alert <?= h($flash['type']) ?>" role="status" data-flash><?= h($flash['message']) ?></div>
<?php endif; ?>
