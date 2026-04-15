<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_rooster'])) {
    $id  = (int)($_POST['rooster_id']??0);
    $oid = (int)$_POST['owner_id'];
    $nm  = clean($db,$_POST['name']??'');
    $br  = clean($db,$_POST['breed']??'');
    $cl  = clean($db,$_POST['color']??'');
    $wt  = (float)$_POST['weight_kg'];
    $lg  = clean($db,$_POST['leg_color']??'');
    $st  = clean($db,$_POST['status']??'active');
    $nt  = clean($db,$_POST['notes']??'');
    if ($id) { $db->query("UPDATE roosters SET owner_id=$oid,name='$nm',breed='$br',color='$cl',weight_kg=$wt,leg_color='$lg',status='$st',notes='$nt' WHERE rooster_id=$id"); $msg='Rooster updated.'; }
    else { $db->query("INSERT INTO roosters (owner_id,name,breed,color,weight_kg,leg_color,status,notes) VALUES($oid,'$nm','$br','$cl',$wt,'$lg','$st','$nt')"); $msg='Rooster added.'; }
}
if (isset($_GET['delete'])) { $db->query("DELETE FROM roosters WHERE rooster_id=".(int)$_GET['delete']); header('Location: roosters.php?msg=deleted'); exit; }

$foid    = (int)($_GET['owner_id']??0);
$fsearch = clean($db,$_GET['q']??'');
$where   = 'WHERE 1=1';
if ($foid)    $where .= " AND r.owner_id=$foid";
if ($fsearch) $where .= " AND (r.name LIKE '%$fsearch%' OR r.breed LIKE '%$fsearch%')";
$rows    = $db->query("SELECT r.*,o.full_name owner_name,(SELECT COUNT(*) FROM derby_entries WHERE rooster_id=r.rooster_id) entry_count FROM roosters r JOIN owners o ON r.owner_id=o.owner_id $where ORDER BY r.wins DESC,r.name");
$owners  = $db->query("SELECT owner_id,full_name FROM owners WHERE is_active=1 ORDER BY full_name");
$breeds  = $db->query("SELECT breed_name FROM breeds ORDER BY breed_name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Roosters — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css"></head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd"><h1>🐓 Rooster Registry</h1><button class="btn-primary btn-gold" onclick="openModal(0)">+ Add Rooster</button></div>
  <?php if($msg||isset($_GET['msg'])): ?><div class="msg-success">✅ <?=htmlspecialchars($msg?:'Done.')?></div><?php endif; ?>
  <form method="GET" class="filter-bar">
    <select name="owner_id" onchange="this.form.submit()">
      <option value="">All Owners</option>
      <?php $owners->data_seek(0); while($o=$owners->fetch_assoc()): ?>
      <option value="<?=$o['owner_id']?>" <?=$foid==$o['owner_id']?'selected':''?>><?=htmlspecialchars($o['full_name'])?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="q" placeholder="Search name or breed..." value="<?=htmlspecialchars($fsearch)?>" style="min-width:200px">
    <button type="submit">Search</button>
    <a href="roosters.php" style="font-size:.76rem;color:var(--muted);text-decoration:none">Reset</a>
  </form>
  <div class="card">
    <div class="card-hd"><h3>Registered Roosters</h3><span style="font-size:.75rem;color:var(--muted)"><?=$rows->num_rows?> roosters</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Owner</th><th>Breed</th><th>Color</th><th>Weight</th><th>Leg Color</th><th>W / L / D</th><th>Entries</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($r=$rows->fetch_assoc()): $smap=['active'=>'b-green','retired'=>'b-amber','deceased'=>'b-gray']; ?>
        <tr>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">#<?=$r['rooster_id']?></td>
          <td><div class="t-name"><?=htmlspecialchars($r['name'])?></div></td>
          <td class="t-sub"><?=htmlspecialchars($r['owner_name'])?></td>
          <td class="t-sub"><?=htmlspecialchars($r['breed']??'—')?></td>
          <td class="t-sub"><?=htmlspecialchars($r['color']??'—')?></td>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$r['weight_kg']?> kg</td>
          <td class="t-sub"><?=htmlspecialchars($r['leg_color']??'—')?></td>
          <td><span style="color:#4ade80;font-weight:700"><?=$r['wins']?>W</span> · <span style="color:#f87171;font-weight:700"><?=$r['losses']?>L</span> · <span style="color:var(--gold-light);font-weight:700"><?=$r['draws']?>D</span></td>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$r['entry_count']?></td>
          <td><span class="badge <?=$smap[$r['status']]??'b-gray'?>"><?=ucfirst($r['status'])?></span></td>
          <td style="display:flex;gap:.3rem">
            <button class="btn-xs bx-edit" onclick="openModal(<?=$r['rooster_id']?>,<?=$r['owner_id']?>,'<?=addslashes($r['name'])?>','<?=addslashes($r['breed']??'')?>','<?=addslashes($r['color']??'')?>','<?=$r['weight_kg']?>','<?=addslashes($r['leg_color']??'')?>','<?=$r['status']?>','<?=addslashes($r['notes']??'')?>')">Edit</button>
            <a href="roosters.php?delete=<?=$r['rooster_id']?>" class="btn-xs bx-delete" onclick="return confirm('Delete this rooster?')">Del</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<div class="overlay" id="roosterModal">
  <div class="modal"><h3 id="rm-title">Add Rooster</h3>
    <form method="POST"><input type="hidden" name="save_rooster" value="1"><input type="hidden" name="rooster_id" id="rm-id">
      <div class="row2">
        <div class="field"><label>Owner *</label><select name="owner_id" id="rm-owner" required><option value="">— Select —</option><?php $owners->data_seek(0); while($o=$owners->fetch_assoc()): ?><option value="<?=$o['owner_id']?>"><?=htmlspecialchars($o['full_name'])?></option><?php endwhile; ?></select></div>
        <div class="field"><label>Rooster Name *</label><input name="name" id="rm-name" required placeholder="e.g. Agila"></div>
      </div>
      <div class="row2">
        <div class="field"><label>Breed</label><input name="breed" id="rm-breed" placeholder="e.g. Sweater" list="breed-list"><datalist id="breed-list"><?php $breeds->data_seek(0); while($b=$breeds->fetch_row()): ?><option value="<?=htmlspecialchars($b[0])?>"><?php endwhile; ?></datalist></div>
        <div class="field"><label>Color / Plumage</label><input name="color" id="rm-color" placeholder="e.g. Red, Black"></div>
      </div>
      <div class="row3">
        <div class="field"><label>Weight (kg)</label><input type="number" name="weight_kg" id="rm-wt" step="0.01" min="0.5" max="5" placeholder="2.10"></div>
        <div class="field"><label>Leg Color</label><input name="leg_color" id="rm-lg" placeholder="Yellow, White..."></div>
        <div class="field"><label>Status</label><select name="status" id="rm-st"><option value="active">Active</option><option value="retired">Retired</option><option value="deceased">Deceased</option></select></div>
      </div>
      <div class="field"><label>Notes</label><textarea name="notes" id="rm-notes" rows="2" placeholder="Additional notes..."></textarea></div>
      <div class="modal-btns"><button type="button" class="modal-cancel" onclick="document.getElementById('roosterModal').classList.remove('open')">Cancel</button><button type="submit" class="modal-save">Save Rooster</button></div>
    </form>
  </div>
</div>
<script>
function openModal(id,oid=0,nm='',br='',cl='',wt='',lg='',st='active',nt=''){
  document.getElementById('rm-title').textContent=id?'Edit Rooster':'Add Rooster';
  document.getElementById('rm-id').value=id; document.getElementById('rm-owner').value=oid;
  document.getElementById('rm-name').value=nm; document.getElementById('rm-breed').value=br;
  document.getElementById('rm-color').value=cl; document.getElementById('rm-wt').value=wt;
  document.getElementById('rm-lg').value=lg; document.getElementById('rm-st').value=st;
  document.getElementById('rm-notes').value=nt;
  document.getElementById('roosterModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body></html>
