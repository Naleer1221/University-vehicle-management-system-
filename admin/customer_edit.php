<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
$id=(int)($_GET['id'] ?? 0);
$stmt=$pdo->prepare("SELECT * FROM users WHERE id=? AND role='customer'");$stmt->execute([$id]);$customer=$stmt->fetch();
if(!$customer)not_found('Customer not found.');
$pageTitle='Edit customer';$errors=[];
if(is_post()) {
    check_csrf();
    $name=trim($_POST['full_name'] ?? '');$email=strtolower(trim($_POST['email'] ?? ''));
    $phone=trim($_POST['phone'] ?? '');$license=trim($_POST['license_no'] ?? '');
    $active=isset($_POST['is_active']) && $_POST['is_active']==='1' ? 1:0;
    if(strlen($name)<2 || strlen($name)>120)$errors[]='Name must be 2 to 120 characters.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Invalid email.';
    if(strlen($phone)>30 || strlen($license)>60)$errors[]='Phone or licence number too long.';
    $stmt=$pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?');$stmt->execute([$email,$id]);
    if($stmt->fetch())$errors[]='Email already used.';
    if(!$active && (int)$customer['is_active']) {
        $stmt=$pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status IN ('pending','approved')");$stmt->execute([$id]);
        if($stmt->fetchColumn())$errors[]='Cannot disable customer with active bookings.';
    }
    if(!$errors){$stmt=$pdo->prepare('UPDATE users SET full_name=?,email=?,phone=?,license_no=?,is_active=? WHERE id=?');
        $stmt->execute([$name,$email,$phone,$license,$active,$id]);set_flash('success','Customer updated.');go('admin/customers.php');}
}
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Edit customer</h1><?php display_errors($errors); ?>
<form method="post" data-validate><?php csrf_field(); ?>
<div class="field"><label for="full_name">Name</label><input name="full_name" id="full_name" required value="<?= field('full_name',$customer['full_name']) ?>"></div>
<div class="field"><label for="email">Email</label><input type="email" name="email" id="email" required value="<?= field('email',$customer['email']) ?>"></div>
<div class="form-row"><div class="field"><label for="phone">Phone</label><input name="phone" id="phone" value="<?= field('phone',$customer['phone']) ?>"></div>
<div class="field"><label for="license_no">Licence</label><input name="license_no" id="license_no" value="<?= field('license_no',$customer['license_no']) ?>"></div></div>
<div class="field"><label for="is_active">Account status</label><select name="is_active" id="is_active"><option value="1" <?= (int)($_POST['is_active'] ?? $customer['is_active'])===1?'selected':'' ?>>Active</option>
<option value="0" <?= (int)($_POST['is_active'] ?? $customer['is_active'])===0?'selected':'' ?>>Disabled</option></select></div>
<button class="btn">Save customer</button> <a class="btn outline" href="<?= h(url('admin/customers.php')) ?>">Cancel</a>
</form></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
