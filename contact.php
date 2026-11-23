<?php
require_once __DIR__.'/includes/auth.php';
$pageTitle='Contact';$errors=[];
if(is_post()) {
  check_csrf();
  $name=trim($_POST['name'] ?? '');$email=trim($_POST['email'] ?? '');
  $subject=trim($_POST['subject'] ?? '');$message=trim($_POST['message'] ?? '');
  if(strlen($name)<2 || strlen($name)>120)$errors[]='Enter your name (2–120 characters).';
  if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email address.';
  if(strlen($subject)<2 || strlen($subject)>200)$errors[]='Enter a subject (2–200 characters).';
  if(strlen($message)<10 || strlen($message)>5000)$errors[]='Message must contain 10–5000 characters.';
  if(!$errors){$stmt=$pdo->prepare('INSERT INTO messages(name,email,subject,message) VALUES (?,?,?,?)');
    $stmt->execute([$name,$email,$subject,$message]);set_flash('success','Message saved. Thank you!');go('contact.php');}
}
require __DIR__.'/includes/header.php';
?>
<main class="container section"><div class="form-card"><h1>Contact us</h1><p class="muted">Your message will appear in the admin inbox.</p>
<?php display_errors($errors); ?>
<form method="post" data-validate><?php csrf_field(); ?>
<div class="form-row"><div class="field"><label for="name">Name</label><input name="name" id="name" required value="<?= field('name') ?>"></div>
<div class="field"><label for="email">Email</label><input type="email" name="email" id="email" required value="<?= field('email') ?>"></div></div>
<div class="field"><label for="subject">Subject</label><input name="subject" id="subject" required maxlength="200" value="<?= field('subject') ?>"></div>
<div class="field"><label for="message">Message</label><textarea name="message" id="message" required minlength="10"><?= field('message') ?></textarea></div>
<button class="btn" type="submit">Send message</button></form></div></main>
<?php require __DIR__.'/includes/footer.php'; ?>
