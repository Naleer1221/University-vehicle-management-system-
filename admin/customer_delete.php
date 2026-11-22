<?php
require_once __DIR__.'/../includes/auth.php';require_admin();check_csrf();
$id=(int)($_POST['id'] ?? 0);
try {
    $pdo->beginTransaction();
    $stmt=$pdo->prepare("SELECT id FROM users WHERE id=? AND role='customer' AND is_active=1 FOR UPDATE");
    $stmt->execute([$id]);if(!$stmt->fetch())throw new RuntimeException('Customer not found or already disabled.');
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status IN ('pending','approved')");$stmt->execute([$id]);
    if($stmt->fetchColumn())throw new RuntimeException('Customer has active bookings; disable is blocked.');
    $stmt=$pdo->prepare('UPDATE users SET is_active=0 WHERE id=?');$stmt->execute([$id]);
    $pdo->commit();set_flash('success','Customer account disabled; booking history preserved.');
}catch(RuntimeException $error){if($pdo->inTransaction())$pdo->rollBack();set_flash('error',$error->getMessage());}
go('admin/customers.php');
