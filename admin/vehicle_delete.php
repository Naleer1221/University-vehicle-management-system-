<?php
require_once __DIR__.'/../includes/auth.php';require_admin();check_csrf();
$id=(int)($_POST['id'] ?? 0);
$pdo->beginTransaction();
try {
    $stmt=$pdo->prepare('SELECT id FROM vehicles WHERE id=? FOR UPDATE');$stmt->execute([$id]);
    if(!$stmt->fetch())throw new RuntimeException('Vehicle not found.');
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vehicle_id=? AND status IN ('pending','approved')");
    $stmt->execute([$id]);
    if($stmt->fetchColumn())throw new RuntimeException('Cannot delete vehicle with active bookings.');
    $stmt=$pdo->prepare('UPDATE vehicles SET is_deleted=1 WHERE id=?');$stmt->execute([$id]);
    $pdo->commit();set_flash('success','Vehicle hidden from catalogue. Booking history kept.');
}catch(RuntimeException $error){if($pdo->inTransaction())$pdo->rollBack();set_flash('error',$error->getMessage());}
go('admin/vehicles.php');
