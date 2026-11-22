<?php // Shared form is included from vehicle_add.php or vehicle_edit.php.
$v=$v ?? [];
?>
<form method="post" enctype="multipart/form-data" data-validate>
<?php csrf_field(); ?>
<div class="form-row"><div class="field"><label for="name">Vehicle name *</label><input name="name" id="name" maxlength="120" required value="<?= field('name',$v['name'] ?? '') ?>"></div>
<div class="field"><label for="category_id">Category *</label><select name="category_id" id="category_id" required><option value="">Choose</option>
<?php foreach(categories($pdo) as $cat): ?><option value="<?= h($cat['id']) ?>" <?= (int)($_POST['category_id'] ?? $v['category_id'] ?? 0)===(int)$cat['id']?'selected':'' ?>><?= h($cat['name']) ?></option><?php endforeach; ?></select></div></div>
<div class="form-row"><div class="field"><label for="brand">Brand *</label><input name="brand" id="brand" required value="<?= field('brand',$v['brand'] ?? '') ?>"></div>
<div class="field"><label for="model">Model *</label><input name="model" id="model" required value="<?= field('model',$v['model'] ?? '') ?>"></div></div>
<div class="form-row"><div class="field"><label for="year">Year *</label><input type="number" name="year" id="year" required min="1980" max="<?= date('Y')+1 ?>" value="<?= field('year',$v['year'] ?? date('Y')) ?>"></div>
<div class="field"><label for="registration_no">Registration number *</label><input name="registration_no" id="registration_no" required value="<?= field('registration_no',$v['registration_no'] ?? '') ?>"></div></div>
<div class="form-row"><div class="field"><label for="seats">Seats *</label><input type="number" name="seats" id="seats" min="1" max="60" required value="<?= field('seats',$v['seats'] ?? 4) ?>"></div>
<div class="field"><label for="price_per_day">LKR per day *</label><input type="number" step="0.01" min="1" name="price_per_day" id="price_per_day" required value="<?= field('price_per_day',$v['price_per_day'] ?? '') ?>"></div></div>
<div class="form-row"><div class="field"><label for="transmission">Transmission *</label><select name="transmission" id="transmission">
<?php foreach(['Automatic','Manual'] as $choice): ?><option <?= ($_POST['transmission'] ?? $v['transmission'] ?? 'Automatic')===$choice?'selected':'' ?>><?= h($choice) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="fuel_type">Fuel type *</label><select name="fuel_type" id="fuel_type">
<?php foreach(['Petrol','Diesel','Electric','Hybrid'] as $choice): ?><option <?= ($_POST['fuel_type'] ?? $v['fuel_type'] ?? 'Petrol')===$choice?'selected':'' ?>><?= h($choice) ?></option><?php endforeach; ?></select></div></div>
<div class="field"><label for="status">Status *</label><select name="status" id="status">
<?php foreach(['available','maintenance','unavailable'] as $choice): ?><option value="<?= h($choice) ?>" <?= ($_POST['status'] ?? $v['status'] ?? 'available')===$choice?'selected':'' ?>><?= h(ucfirst($choice)) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="description">Description</label><textarea name="description" id="description" maxlength="5000"><?= field('description',$v['description'] ?? '') ?></textarea></div>
<div class="field"><label for="image">Vehicle image (JPG/PNG/WebP, max 2 MB)</label><input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"></div>
<?php if(!empty($v['image'])): ?><p><img src="<?= h(vehicle_image($v['image'])) ?>" alt="Current vehicle image" style="max-width:210px;border-radius:10px"></p><?php endif; ?>
<button class="btn" type="submit"><?= h($buttonLabel ?? 'Save vehicle') ?></button> <a class="btn outline" href="<?= h(url('admin/vehicles.php')) ?>">Cancel</a>
</form>
