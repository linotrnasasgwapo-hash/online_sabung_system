<?php
// admin/owners.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_owner'])) {
    $id  = (int)($_POST['owner_id']??0);
    $fn  = clean($db,$_POST['full_name']??'');
    $nn  = clean($db,$_POST['nickname']??'');
    $ph  = clean($db,$_POST['phone']??'');
    $add = clean($db,$_POST['address']??'');
    $tm  = clean($db,$_POST['team_name']??'');
    if ($id) { $db->query("UPDATE owners SET full_name='$fn',nickname='$nn',phone='$ph',address='$add',team_name='$tm' WHERE owner_id=$id"); $msg='Owner updated.'; }
    else { $db->query("INSERT INTO owners (full_name,nickname,phone,address,team_name) VALUES('$fn','$nn','$ph','$add','$tm')"); $msg='Owner added.'; }
}
if (isset($_GET['delete'])) { $db->query("DELETE FROM owners WHERE owner_id=".(int)$_GET['delete']); header('Location: owners.php?msg=deleted'); exit; }
$search = clean($db,$_GET['q']??'');
$where  = $search ? "WHERE o.full_name LIKE '%$search%' OR o.nickname LIKE '%$search%' OR o.phone LIKE '%$search%'" : '';
$rows   = $db->query("SELECT o.*, (SELECT COUNT(*) FROM roosters WHERE owner_id=o.owner_id AND status='active') rooster_count, (SELECT COUNT(*) FROM derby_entries WHERE owner_id=o.owner_id) entry_count FROM owners o $where ORDER BY o.wins DESC, o.full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Owners — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css"></head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd"><h1>👤 Owners / Handlers</h1><button class="btn-primary btn-gold" onclick="openModal(0)">+ Add Owner</button></div>
  <?php if($msg||isset($_GET['msg'])): ?><div class="msg-success">✅ <?=htmlspecialchars($msg?:'Done.')?></div><?php endif; ?>
  <form method="GET" class="filter-bar">
    <input type="text" name="q" placeholder="🔍 Search by name, nickname, or phone..." value="<?=htmlspecialchars($search)?>" style="min-width:260px">
    <button type="submit">Search</button>
    <?php if($search): ?><a href="owners.php" style="font-size:.76rem;color:var(--muted);text-decoration:none">Clear</a><?php endif; ?>
  </form>
  <div class="card">
    <div class="card-hd"><h3>Registered Owners</h3><span style="font-size:.75rem;color:var(--muted)"><?=$rows->num_rows?> owners</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Team</th><th>Contact</th><th>Roosters</th><th>Entries</th><th>W / L / D</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($o=$rows->fetch_assoc()): ?>
        <tr>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">#<?=$o['owner_id']?></td>
          <td><div class="t-name"><?=htmlspecialchars($o['full_name'])?></div><div class="t-sub"><?=$o['nickname']?'"'.htmlspecialchars($o['nickname']).'"':''?></div></td>
          <td class="t-sub"><?=htmlspecialchars($o['team_name']??'—')?></td>
          <td class="t-sub"><?=htmlspecialchars($o['phone']??'—')?></td>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$o['rooster_count']?></td>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$o['entry_count']?></td>
          <td><span style="color:#4ade80;font-weight:700"><?=$o['wins']?>W</span> <span style="color:var(--muted)">·</span> <span style="color:#f87171;font-weight:700"><?=$o['losses']?>L</span> <span style="color:var(--muted)">·</span> <span style="color:var(--gold-light);font-weight:700"><?=$o['draws']?>D</span></td>
          <td class="t-sub"><?=date('M j, Y',strtotime($o['created_at']))?></td>
          <td style="display:flex;gap:.3rem">
            <button class="btn-xs bx-edit" onclick="openModal(<?=$o['owner_id']?>,'<?=addslashes($o['full_name'])?>','<?=addslashes($o['nickname']??'')?>','<?=addslashes($o['phone']??'')?>','<?=addslashes($o['address']??'')?>','<?=addslashes($o['team_name']??'')?>')">Edit</button>
            <a href="roosters.php?owner_id=<?=$o['owner_id']?>" class="btn-xs bx-view">Roosters</a>
            <a href="owners.php?delete=<?=$o['owner_id']?>" class="btn-xs bx-delete" onclick="return confirm('Delete this owner?')">Del</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<div class="overlay" id="ownerModal">
  <div class="modal"><h3 id="om-title">Add Owner</h3>
    <form method="POST"><input type="hidden" name="save_owner" value="1"><input type="hidden" name="owner_id" id="om-id">
      <div class="row2"><div class="field"><label>Full Name *</label><input name="full_name" id="om-fn" required></div><div class="field"><label>Nickname / Alias</label><input name="nickname" id="om-nn"></div></div>
      <div class="row2"><div class="field"><label>Phone</label><input name="phone" id="om-ph"></div><div class="field"><label>Team / Farm Name</label><input name="team_name" id="om-tm"></div></div>
      <div class="field"><label>Address</label><textarea name="address" id="om-add" rows="2"></textarea></div>
      <div class="modal-btns"><button type="button" class="modal-cancel" onclick="document.getElementById('ownerModal').classList.remove('open')">Cancel</button><button type="submit" class="modal-save">Save</button></div>
    </form>
  </div>
</div>
<script>
function openModal(id,fn='',nn='',ph='',add='',tm=''){
  document.getElementById('om-title').textContent=id?'Edit Owner':'Add Owner';
  document.getElementById('om-id').value=id;document.getElementById('om-fn').value=fn;
  document.getElementById('om-nn').value=nn;document.getElementById('om-ph').value=ph;
  document.getElementById('om-add').value=add;document.getElementById('om-tm').value=tm;
  document.getElementById('ownerModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body></html>
