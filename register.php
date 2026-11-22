<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) go(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'my_bookings.php');
$pageTitle = 'Register';
$errors = [];
if (is_post()) {
    check_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $license = trim($_POST['license_no'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($name) < 2 || strlen($name) > 120) $errors[] = 'Name must contain 2 to 120 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== ($_POST['confirm_password'] ?? '')) $errors[] = 'Passwords do not match.';
    if (strlen($phone) > 30 || strlen($license) > 60) $errors[] = 'Phone or licence number is too long.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'This email is already registered.';
        else {
            $stmt = $pdo->prepare('INSERT INTO users (full_name,email,phone,password_hash,role,license_no) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT),'customer',$license]);
            set_flash('success', 'Account created. You can now log in.');
            go('login.php');
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Create an account</h1><p class="muted">Register as a customer to book a vehicle.</p>
<?php display_errors($errors); ?>
<form method="post" data-validate>
  <?php csrf_field(); ?>
  <div class="field"><label for="full_name">Full name *</label><input id="full_name" name="full_name" required minlength="2" maxlength="120" value="<?= field('full_name') ?>"></div>
  <div class="form-row"><div class="field"><label for="email">Email *</label><input id="email" name="email" type="email" required value="<?= field('email') ?>"></div>
  <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" maxlength="30" value="<?= field('phone') ?>"></div></div>
  <div class="field"><label for="license_no">Driving licence number</label><input id="license_no" name="license_no" maxlength="60" value="<?= field('license_no') ?>"></div>
  <div class="form-row"><div class="field"><label for="password">Password *</label><input type="password" id="password" name="password" minlength="8" required></div>
  <div class="field"><label for="confirm_password">Confirm password *</label><input type="password" id="confirm_password" name="confirm_password" minlength="8" required></div></div>
  <button class="btn" type="submit">Create account</button><p>Already registered? <a href="<?= h(url('login.php')) ?>">Log in</a>.</p>
</form></div></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
