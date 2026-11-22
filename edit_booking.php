<?php
require_once __DIR__ . '/includes/auth.php';
require_customer();
$id=(int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt=$pdo->prepare('SELECT b.*,v.name AS vehicle_name,v.price_per_day,v.status AS vehicle_status,v.is_deleted FROM bookings b JOIN vehicles v ON b.vehicle_id=v.id WHERE b.id=? AND b.user_id=?');
$stmt->execute([$id,current_user()['id']]);$booking=$stmt->fetch();
if (!$booking) not_found('Booking not found.');
$errors=[];
if ($booking['status']!=='pending') $errors[]='Only pending bookings can be edited.';
if (is_post()) {
    check_csrf();
    $pickup=trim($_POST['pickup_date'] ?? '');$return=trim($_POST['return_date'] ?? '');
    if ($error=validate_dates($pickup,$return)) $errors[]=$error;
    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Always lock vehicle first, then booking: same order as new booking.
            $lock=$pdo->prepare('SELECT id,status,is_deleted,price_per_day FROM vehicles WHERE id=? FOR UPDATE');
            $lock->execute([$booking['vehicle_id']]);$v=$lock->fetch();
            $lock=$pdo->prepare('SELECT status FROM bookings WHERE id=? AND user_id=? FOR UPDATE');
            $lock->execute([$id,current_user()['id']]);$live=$lock->fetch();
            if (!$live || $live['status']!=='pending' || !$v || $v['status']!=='available' || (int)$v['is_deleted']) throw new RuntimeException('This booking can no longer be edited.');
            if (booking_conflicts($pdo,$booking['vehicle_id'],$pickup,$return,$id)) throw new RuntimeException('Selected dates overlap another booking.');
            $days=total_days($pickup,$return);$total=round($days*(float)$v['price_per_day'],2);
            $save=$pdo->prepare('UPDATE bookings SET pickup_date=?,return_date=?,total_days=?,total_price=? WHERE id=?');
            $save->execute([$pickup,$return,$days,$total,$id]);
            $pdo->commit();set_flash('success','Booking dates updated.');go('my_bookings.php');
        } catch (RuntimeException $error) {
            if($pdo->inTransaction())$pdo->rollBack();$errors[]=$error->getMessage();
        } catch (PDOException $error) {
            if($pdo->inTransaction())$pdo->rollBack();$errors[]='Unable to edit booking. Try again.';
        }
    }
}
$pageTitle='Edit booking';require __DIR__.'/includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Edit booking #<?= h($id) ?></h1><p>Vehicle: <strong><?= h($booking['vehicle_name']) ?></strong></p>
<?php display_errors($errors); ?>
<?php if($booking['status']==='pending'): ?><form method="post" data-booking-calculator data-price="<?= h($booking['price_per_day']) ?>" data-validate>
<?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($id) ?>">
<div class="form-row"><div class="field"><label for="pickup_date">Pickup date</label><input type="date" name="pickup_date" id="pickup_date" required value="<?= field('pickup_date',$booking['pickup_date']) ?>"></div>
<div class="field"><label for="return_date">Return date</label><input type="date" name="return_date" id="return_date" required value="<?= field('return_date',$booking['return_date']) ?>"></div></div>
<p class="form-note" data-total></p><button type="submit" class="btn">Save dates</button>
</form><?php endif; ?><p><a href="<?= h(url('my_bookings.php')) ?>">← Back to bookings</a></p></div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
