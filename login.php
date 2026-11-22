<?php
require_once __DIR__ . '/includes/auth.php';
if (current_user()) go(current_user()['role'] === 'admin' ? 'admin/dashboard.php' : 'my_bookings.php');
$pageTitle = 'Log in';
$errors = [];
if (is_post()) {
    check_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $stmt = $pdo->prepare('SELECT id,full_name,email,password_hash,role,is_active FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !(int)$user['is_active'] || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        $errors[] = 'Invalid email or password.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['full_name'],'role'=>$user['role']];
        set_flash('success', 'Welcome, ' . $user['full_name'] . '!');
        go($user['role'] === 'admin' ? 'admin/dashboard.php' : 'my_bookings.php');
    }
}
require __DIR__ . '/includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Log in</h1><p class="muted">Enter your registered email and password.</p>
<?php display_errors($errors); ?>
<form method="post" data-validate>
  <?php csrf_field(); ?>
  <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= field('email') ?>" required></div>
  <div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
  <button class="btn" type="submit">Log in</button>
</form><p>New customer? <a href="<?= h(url('register.php')) ?>">Register here</a>.</p></div></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
