<?php
require_once __DIR__.'/vehicle_helpers.php';require_admin();
$pageTitle='Add vehicle';$errors=[];$v=[];
if(is_post()) {
    check_csrf();$v=vehicle_fields($_POST);$errors=validate_vehicle($pdo,$v);
    if(!$errors) {
        $image=null;
        try {
            $image=upload_vehicle_image($_FILES['image'] ?? null);
            $stmt=$pdo->prepare('INSERT INTO vehicles(category_id,name,brand,model,year,registration_no,seats,transmission,fuel_type,price_per_day,image,status,description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$v['category_id'],$v['name'],$v['brand'],$v['model'],$v['year'],$v['registration_no'],$v['seats'],$v['transmission'],$v['fuel_type'],$v['price_per_day'],$image,$v['status'],$v['description']]);
            set_flash('success','Vehicle added.');go('admin/vehicles.php');
        }catch(RuntimeException|PDOException $error){if($image && is_file(__DIR__.'/../uploads/vehicles/'.$image))unlink(__DIR__.'/../uploads/vehicles/'.$image);$errors[]='Cannot save vehicle. Check input, registration number and upload permissions.';}
    }
}
$buttonLabel='Add vehicle';require __DIR__.'/../includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Add vehicle</h1><?php display_errors($errors);require __DIR__.'/vehicle_form.php'; ?></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
