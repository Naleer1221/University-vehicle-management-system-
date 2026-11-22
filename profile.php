<?php
require_once __DIR__.'/includes/auth.php';require_customer();
$pageTitle='My profile';$errors=[];
// Load the currently signed-in customer's record from MySQL.
$stmt = $pdo->prepare('SELECT id, full_name, email, phone, license_no, password_hash FROM users WHERE id = ? LIMIT 1');
$stmt->execute([(int) current_user()['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the account cannot be found, prevent empty/invalid profile fields.
if (!$user) {
    unset($_SESSION['user']);
    set_flash('error', 'Your account was not found. Please log in again.');
    go('login.php');
}
if(is_post()) {
    check_csrf();
    $name=trim($_POST['full_name'] ?? '');$email=strtolower(trim($_POST['email'] ?? ''));
    $phone=trim($_POST['phone'] ?? '');$license=trim($_POST['license_no'] ?? '');
    $oldPassword=$_POST['old_password'] ?? '';$newPassword=$_POST['new_password'] ?? '';
    if(strlen($name)<2 || strlen($name)>120)$errors[]='Name must have 2 to 120 characters.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Invalid email address.';
    if(strlen($phone)>30 || strlen($license)>60)$errors[]='Phone or licence number too long.';
    if($newPassword!=='' && (strlen($newPassword)<8 || !password_verify($oldPassword,$user['password_hash'])))$errors[]='To change your password, enter the current password correctly and choose at least 8 characters.';
    if($newPassword!==($_POST['confirm_password'] ?? ''))$errors[]='New password confirmation does not match.';
    $stmt=$pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?');$stmt->execute([$email,$user['id']]);
    if($stmt->fetch())$errors[]='Email is already taken.';
    if(!$errors) {
        if($newPassword!=='') {
            $stmt=$pdo->prepare('UPDATE users SET full_name=?,email=?,phone=?,license_no=?,password_hash=? WHERE id=?');
            $stmt->execute([$name,$email,$phone,$license,password_hash($newPassword,PASSWORD_DEFAULT),$user['id']]);
        } else {
            $stmt=$pdo->prepare('UPDATE users SET full_name=?,email=?,phone=?,license_no=? WHERE id=?');
            $stmt->execute([$name,$email,$phone,$license,$user['id']]);
        }
        $_SESSION['user']['name']=$name;
        set_flash('success','Profile updated.');go('profile.php');
    }
}
require __DIR__.'/includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>My profile</h1><?php display_errors($errors); ?>
<form method="post" data-validate><?php csrf_field(); ?>
<div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" required minlength="2" value="<?= field('full_name', $user['full_name'] ?? '') ?>"></div>
<div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required value="<?= field('email', $user['email'] ?? '') ?>"></div>
<div class="form-row"><div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= field('phone', $user['phone'] ?? '') ?>"></div>
<div class="field"><label for="license_no">Licence number</label><input id="license_no" name="license_no" value="<?= field('license_no', $user['license_no'] ?? '') ?>"></div></div>
<hr class="divider"><h3>Change password (optional)</h3>
<div class="field"><label for="old_password">Current password</label><input id="old_password" type="password" name="old_password"></div>
<div class="form-row"><div class="field"><label for="new_password">New password</label><input id="new_password" type="password" name="new_password" minlength="8"></div>
<div class="field"><label for="confirm_password">Confirm new password</label><input id="confirm_password" type="password" name="confirm_password"></div></div>
<button class="btn">Save profile</button></form></div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
