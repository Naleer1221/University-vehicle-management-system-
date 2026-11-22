<?php
require_once __DIR__ . '/includes/auth.php';
require_customer();
check_csrf();
$vehicleId=(int)($_POST['vehicle_id'] ?? 0);
$pickup=trim($_POST['pickup_date'] ?? '');
$return=trim($_POST['return_date'] ?? '');
$notes=trim($_POST['notes'] ?? '');
$error=validate_dates($pickup,$return);
if (strlen($notes)>1000) $error='Notes cannot exceed 1000 characters.';
if ($vehicleId<=0) $error='Invalid vehicle.';
if ($error) {set_flash('error',$error);go('vehicle_details.php?id='.$vehicleId);}
try {
    // Lock the vehicle row: two customers cannot book this car simultaneously.
    $pdo->beginTransaction();
    $stmt=$pdo->prepare('SELECT id,price_per_day,status,is_deleted FROM vehicles WHERE id=? FOR UPDATE');
    $stmt->execute([$vehicleId]);$vehicle=$stmt->fetch();
    if (!$vehicle || $vehicle['status']!=='available' || (int)$vehicle['is_deleted']) {
        throw new RuntimeException('Vehicle is unavailable.');
    }
    if (booking_conflicts($pdo,$vehicleId,$pickup,$return)) {
        throw new RuntimeException('These dates overlap another pending or approved booking.');
    }
    $days=total_days($pickup,$return);
    $price=round($days*(float)$vehicle['price_per_day'],2);
    $stmt=$pdo->prepare("INSERT INTO bookings (user_id,vehicle_id,pickup_date,return_date,total_days,total_price,status,notes)
        VALUES (?,?,?,?,?,?,'pending',?)");
    $stmt->execute([current_user()['id'],$vehicleId,$pickup,$return,$days,$price,$notes]);
    $pdo->commit();
    set_flash('success','Booking requested. View its status below.');
    go('my_bookings.php');
} catch (RuntimeException $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error',$error->getMessage());
    go('vehicle_details.php?id='.$vehicleId);
} catch (PDOException $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error','Could not save booking. Please retry.');
    go('vehicle_details.php?id='.$vehicleId);
}
