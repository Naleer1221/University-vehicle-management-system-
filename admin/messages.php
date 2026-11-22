<?php
require_once __DIR__.'/../includes/auth.php';require_admin();
if(is_post()) {
    check_csrf();$id=(int)($_POST['id'] ?? 0);$action=$_POST['action'] ?? '';
    if($action==='read'){$stmt=$pdo->prepare('UPDATE messages SET is_read=1 WHERE id=?');$stmt->execute([$id]);set_flash('success','Message marked as read.');}
    elseif($action==='delete'){$stmt=$pdo->prepare('DELETE FROM messages WHERE id=?');$stmt->execute([$id]);set_flash('success','Message deleted.');}
    else set_flash('error','Unknown action.');
    go('admin/messages.php');
}
$pageTitle='Messages';$messages=$pdo->query('SELECT * FROM messages ORDER BY created_at DESC,id DESC')->fetchAll();
require __DIR__.'/../includes/header.php';
?>
<main class="container section"><h1>Contact messages</h1><?php require __DIR__.'/nav.php'; ?>
<?php if(!$messages): ?><div class="empty">No messages yet.</div><?php else: ?><div class="table-wrap admin-table"><table><thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead><tbody>
<?php foreach($messages as $m): ?><tr><td><?= h($m['name']) ?><br><small><?= h($m['email']) ?></small></td>
<td><?= h($m['subject']) ?> <?php if(!(int)$m['is_read']): ?><span class="tag pending">Unread</span><?php endif; ?></td>
<td><?= nl2br(h($m['message'])) ?></td><td><?= h($m['created_at']) ?></td><td><div class="actions">
<?php if(!(int)$m['is_read']): ?><form method="post"><?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($m['id']) ?>"><input type="hidden" name="action" value="read"><button class="btn small">Mark read</button></form><?php endif; ?>
<form method="post" data-confirm="Delete this message?"><?php csrf_field(); ?><input type="hidden" name="id" value="<?= h($m['id']) ?>"><input type="hidden" name="action" value="delete"><button class="btn small danger">Delete</button></form>
</div></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
