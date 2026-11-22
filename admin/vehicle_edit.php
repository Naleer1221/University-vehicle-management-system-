<?php
require_once __DIR__.'/vehicle_helpers.php';require_admin();
$id=(int)($_GET['id'] ?? 0);
$stmt=$pdo->prepare('SELECT * FROM vehicles WHERE id=? AND is_deleted=0');$stmt->execute([$id]);$v=$stmt->fetch();
if(!$v)not_found('Vehicle not found.');
$pageTitle='Edit vehicle';$errors=[];
if(is_post()) {
    check_csrf();$new=vehicle_fields($_POST);$errors=validate_vehicle($pdo,$new,$id);
    if(!$errors) {
        $image=null;
        try {
            $image=upload_vehicle_image($_FILES['image'] ?? null);
            // Keep previous image when no new image was uploaded.
            $stmt=$pdo->prepare('UPDATE vehicles SET category_id=?,name=?,brand=?,model=?,year=?,registration_no=?,seats=?,transmission=?,fuel_type=?,price_per_day=?,image=?,status=?,description=? WHERE id=? AND is_deleted=0');
            $stmt->execute([$new['category_id'],$new['name'],$new['brand'],$new['model'],$new['year'],$new['registration_no'],$new['seats'],$new['transmission'],$new['fuel_type'],$new['price_per_day'],$image ?? $v['image'],$new['status'],$new['description'],$id]);
            set_flash('success','Vehicle updated.');go('admin/vehicles.php');
        }catch(RuntimeException|PDOException $error){if($image && is_file(__DIR__.'/../uploads/vehicles/'.$image))unlink(__DIR__.'/../uploads/vehicles/'.$image);$errors[]='Cannot update vehicle. Check input and image.';}
    }
}
$buttonLabel='Save changes';require __DIR__.'/../includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Edit vehicle</h1><?php display_errors($errors);require __DIR__.'/vehicle_form.php'; ?></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
