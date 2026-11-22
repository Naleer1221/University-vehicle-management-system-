<?php
require_once __DIR__.'/../includes/auth.php';
function vehicle_fields($source) {
    return [
        'category_id'=>(int)($source['category_id'] ?? 0),
        'name'=>trim($source['name'] ?? ''),
        'brand'=>trim($source['brand'] ?? ''),
        'model'=>trim($source['model'] ?? ''),
        'year'=>(int)($source['year'] ?? 0),
        'registration_no'=>strtoupper(trim($source['registration_no'] ?? '')),
        'seats'=>(int)($source['seats'] ?? 0),
        'transmission'=>trim($source['transmission'] ?? 'Automatic'),
        'fuel_type'=>trim($source['fuel_type'] ?? 'Petrol'),
        'price_per_day'=>(float)($source['price_per_day'] ?? 0),
        'status'=>trim($source['status'] ?? 'available'),
        'description'=>trim($source['description'] ?? '')
    ];
}
function validate_vehicle(PDO $pdo, $v, $excludeId=0) {
    $errors=[];
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM categories WHERE id=?');$stmt->execute([$v['category_id']]);
    if(!$stmt->fetchColumn()) $errors[]='Select a valid category.';
    foreach(['name','brand','model','registration_no'] as $key) {
        if($v[$key]==='' || strlen($v[$key])>120) $errors[]='Invalid '.str_replace('_',' ',$key).'.';
    }
    if($v['year']<1980 || $v['year']>(int)date('Y')+1)$errors[]='Invalid vehicle year.';
    if($v['seats']<1 || $v['seats']>60)$errors[]='Seats must be between 1 and 60.';
    if($v['price_per_day']<=0 || $v['price_per_day']>10000000)$errors[]='Enter a valid daily price.';
    if(!in_array($v['transmission'],['Automatic','Manual'],true))$errors[]='Invalid transmission.';
    if(!in_array($v['fuel_type'],['Petrol','Diesel','Electric','Hybrid'],true))$errors[]='Invalid fuel type.';
    if(!in_array($v['status'],['available','maintenance','unavailable'],true))$errors[]='Invalid status.';
    if(strlen($v['description'])>5000)$errors[]='Description is too long.';
    $stmt=$pdo->prepare('SELECT id FROM vehicles WHERE registration_no=? AND id<>?');
    $stmt->execute([$v['registration_no'],$excludeId]);
    if($stmt->fetch()) $errors[]='Registration number already exists.';
    return $errors;
}
function upload_vehicle_image($file) {
    if(!$file || $file['error']===UPLOAD_ERR_NO_FILE)return null;
    if($file['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Image upload failed.');
    if($file['size']>2*1024*1024)throw new RuntimeException('Image must be 2 MB or less.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($types[$mime]))throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
    $dir=__DIR__.'/../uploads/vehicles/';
    if(!is_dir($dir) || !is_writable($dir))throw new RuntimeException('Uploads folder is not writable.');
    $name=bin2hex(random_bytes(16)).'.'.$types[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.$name))throw new RuntimeException('Could not save image.');
    return $name;
}
