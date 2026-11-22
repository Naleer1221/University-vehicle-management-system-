<?php
require_once __DIR__.'/../includes/auth.php';require_admin();check_csrf();
$id=(int)($_POST['id'] ?? 0);$status=$_POST['status'] ?? '';
$allowed=['pending','approved','rejected','cancelled','completed'];
if(!in_array($status,$allowed,true)) {set_flash('error','Invalid booking status.');go('admin/bookings.php');}
try {
    // Read vehicle id, then lock vehicle before booking to avoid booking race conditions.
    $stmt=$pdo->prepare('SELECT vehicle_id FROM bookings WHERE id=?');$stmt->execute([$id]);$vehicleId=$stmt->fetchColumn();
    if(!$vehicleId)throw new RuntimeException('Booking not found.');
    $pdo->beginTransaction();
    $stmt=$pdo->prepare('SELECT status,is_deleted FROM vehicles WHERE id=? FOR UPDATE');$stmt->execute([$vehicleId]);$vehicle=$stmt->fetch();
    $stmt=$pdo->prepare('SELECT * FROM bookings WHERE id=? FOR UPDATE');$stmt->execute([$id]);$booking=$stmt->fetch();
    if(!$booking)throw new RuntimeException('Booking not found.');
    // Do not overwrite a booking while a refund is pending or already approved.
    $stmt=$pdo->prepare("SELECT status FROM refund_requests WHERE booking_id=?");
    $stmt->execute([$id]);$refundStatus=$stmt->fetchColumn();
    if($refundStatus && in_array($refundStatus,['pending','approved','refunded'],true) && $status!==$booking['status']) {
        throw new RuntimeException('Refund is under review or approved. Use Refund requests to manage this booking.');
    }
    if(in_array($status,['pending','approved'],true)) {
        if(!$vehicle || $vehicle['status']!=='available' || (int)$vehicle['is_deleted'])throw new RuntimeException('Vehicle is not available.');
        if(booking_conflicts($pdo,$vehicleId,$booking['pickup_date'],$booking['return_date'],$id))throw new RuntimeException('Cannot approve: booking dates overlap.');
    }
    $stmt=$pdo->prepare('UPDATE bookings SET status=? WHERE id=?');$stmt->execute([$status,$id]);
    $pdo->commit();set_flash('success','Booking status updated.');
}catch(RuntimeException $error){if($pdo->inTransaction())$pdo->rollBack();set_flash('error',$error->getMessage());}
catch(PDOException $error){if($pdo->inTransaction())$pdo->rollBack();set_flash('error','Could not update booking.');}
go('admin/bookings.php');
