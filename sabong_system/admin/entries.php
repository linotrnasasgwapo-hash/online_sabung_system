<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

// Register entry
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_entry'])) {
    $eid   = (int)($_POST['entry_id']??0);
    $did   = (int)$_POST['derby_id'];
    $oid   = (int)$_POST['owner_id'];
    $rid   = (int)$_POST['rooster_id'];
    $weight= (float)$_POST['weight_at_entry'];
    $side  = clean($db,$_POST['side']??'pending');
    $st    = clean($db,$_POST['status']??'registered');
    $paid  = isset($_POST['entry_fee_paid'])?1:0;
    $notes = clean($db,$_POST['notes']??'');
    $aid   = $_SESSION['admin_id'];

    if ($eid) {
        $db->query("UPDATE derby_entries SET owner_id=$oid,rooster_id=$rid,weight_at_entry=$weight,side='$side',status='$st',entry_fee_paid=$paid,notes='$notes' WHERE entry_id=$eid");
        $msg = 'Entry updated.';
    } else {
        // Get next entry number for this derby
        $en = qs($db,"SELECT COALESCE(MAX(entry_number),0)+1 FROM derby_entries WHERE derby_id=$did");
        $db->query("INSERT INTO derby_entries (derby_id,owner_id,rooster_id,entry_number,weight_at_entry,side,status,entry_fee_paid,notes,registered_by) VALUES($did,$oid,$rid,$en,$weight,'$side','$st',$paid,'$notes',$aid)");
        $db->query("UPDATE derbies SET current_entries=current_entries+1 WHERE derby_id=$did");
        $msg = 'Entry registered successfully. Entry #'.$en;
    }
}

// Delete entry
if (isset($_GET['delete'])) {
    $eid = (int)$_GET['delete'];
    $r   = $db->query("SELECT derby_id FROM derby_entries WHERE entry_id=$eid");
    $did = $r?$r->fetch_row()[0]:0;
    $db->query("DELETE FROM derby_entries WHERE entry_id=$eid");
    if($did) $db->query("UPDATE derbies SET current_entries=GREATEST(0,current_entries-1) WHERE derby_id=$did");
    header('Location: entries.php'.($did?"?derby_id=$did":'').'&msg=deleted'); exit;
}

$fdid   = (int)($_GET['derby_id']??0);
$fsearch= clean($db,$_GET['q']??'');
$fside  = clean($db,$_GET['side']??'');

$where = 'WHERE 1=1';
if ($fdid)    $where .= " AND de.derby_id=$fdid";
if ($fsearch) $where .= " AND (o.full_name LIKE '%$fsearch%' OR r.name LIKE '%$fsearch%')";
if ($fside)   $where .= " AND de.side='$fside'";

$entries = $db->query("
    SELECT de.*, d.derby_name, d.entry_fee d_fee,
           o.full_name owner_name, o.nickname, o.phone,
           r.name rooster_name, r.breed, r.weight_kg, r.color,
           r.wins r_wins, r.losses r_losses
    FROM derby_entries de
    JOIN derbies d  ON de.derby_id=d.derby_id
    JOIN owners o   ON de.owner_id=o.owner_id
    JOIN roosters r ON de.rooster_id=r.rooster_id
    $where ORDER BY de.derby_id DESC, de.entry_number ASC
");

$derbies = $db->query("SELECT derby_id,derby_name,status FROM derbies ORDER BY event_date DESC");
$owners  = $db->query("SELECT owner_id,full_name,nickname FROM owners WHERE is_active=1 ORDER BY full_name");
$roosters= $db->query("SELECT r.*,o.full_name owner_name FROM roosters r JOIN owners o ON r.owner_id=o.owner_id WHERE r.status='active' ORDER BY r.name");

$sides   = ['pending','meron','wala'];
$estatuses=['registered','confirmed','scratched','disqualified'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Derby Entries — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd">
    <h1>📋 Derby Entries</h1>
    <button class="btn-primary btn-gold" onclick="openEntry(0)">+ Register Entry</button>
  </div>

  <?php if($msg||isset($_GET['msg'])): ?><div class="msg-success">✅ <?= htmlspecialchars($msg?:'Done.') ?></div><?php endif; ?>

  <form method="GET" class="filter-bar">
    <select name="derby_id" onchange="this.form.submit()">
      <option value="">All Derbies</option>
      <?php $derbies->data_seek(0); while($d=$derbies->fetch_assoc()): ?>
      <option value="<?=$d['derby_id']?>" <?=$fdid==$d['derby_id']?'selected':''?>><?=htmlspecialchars($d['derby_name'])?></option>
      <?php endwhile; ?>
    </select>
    <select name="side" onchange="this.form.submit()">
      <option value="">All Sides</option>
      <option value="meron" <?=$fside==='meron'?'selected':''?>>Meron</option>
      <option value="wala"  <?=$fside==='wala'?'selected':''?>>Wala</option>
      <option value="pending" <?=$fside==='pending'?'selected':''?>>TBD</option>
    </select>
    <input type="text" name="q" placeholder="Search owner or rooster..." value="<?=htmlspecialchars($fsearch)?>" style="min-width:200px">
    <?php if($fdid): ?><input type="hidden" name="derby_id" value="<?=$fdid?>"> <?php endif; ?>
    <button type="submit">Filter</button>
    <a href="entries.php" style="font-size:.76rem;color:var(--muted);text-decoration:none">Reset</a>
  </form>

  <div class="card">
    <div class="card-hd"><h3>Registered Entries</h3><span style="font-size:.75rem;color:var(--muted)"><?=$entries->num_rows?> entries</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Entry #</th><th>Derby</th><th>Owner</th><th>Rooster</th><th>Breed</th><th>Entry Wt.</th><th>Side</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php
        $emap=['registered'=>'b-gold','confirmed'=>'b-green','scratched'=>'b-gray','disqualified'=>'b-red'];
        while($e=$entries->fetch_assoc()):
        ?>
        <tr>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">E-<?=str_pad($e['entry_number'],3,'0',STR_PAD_LEFT)?></td>
          <td><div class="t-name" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($e['derby_name'])?></div></td>
          <td><div class="t-name"><?=htmlspecialchars($e['full_name']??$e['owner_name'])?></div><div class="t-sub"><?=htmlspecialchars($e['phone']??'')?></div></td>
          <td><div class="t-name"><?=htmlspecialchars($e['rooster_name'])?></div><div class="t-sub"><?=$e['r_wins']?>W - <?=$e['r_losses']?>L</div></td>
          <td class="t-sub"><?=htmlspecialchars($e['breed']??'—')?></td>
          <td class="t-sub"><?=$e['weight_at_entry']??$e['weight_kg']?> kg</td>
          <td>
            <?php if($e['side']==='meron'): ?><span class="badge b-meron">MERON</span>
            <?php elseif($e['side']==='wala'): ?><span class="badge b-wala">WALA</span>
            <?php else: ?><span class="t-sub">TBD</span><?php endif; ?>
          </td>
          <td><?= $e['entry_fee_paid'] ? '<span class="badge b-green">Paid</span>' : '<span class="badge b-red">Unpaid</span>' ?></td>
          <td><span class="badge <?=$emap[$e['status']]??'b-gold'?>"><?=ucfirst($e['status'])?></span></td>
          <td style="display:flex;gap:.3rem">
            <button class="btn-xs bx-edit" onclick="openEntry(<?=$e['entry_id']?>,<?=$e['derby_id']?>,<?=$e['owner_id']?>,<?=$e['rooster_id']?>,'<?=$e['weight_at_entry']??$e['weight_kg']?>','<?=$e['side']?>','<?=$e['status']?>',<?=$e['entry_fee_paid']?>,'<?=addslashes($e['notes']??'')?>')">Edit</button>
            <a href="entries.php?delete=<?=$e['entry_id']?><?=$fdid?"&derby_id=$fdid":''?>" class="btn-xs bx-delete" onclick="return confirm('Remove this entry?')">Del</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="entryModal">
  <div class="modal">
    <h3 id="em-title">Register Derby Entry</h3>
    <form method="POST">
      <input type="hidden" name="save_entry" value="1">
      <input type="hidden" name="entry_id" id="em-id">
      <div class="field"><label>Derby *</label>
        <select name="derby_id" id="em-derby" required>
          <option value="">— Select derby —</option>
          <?php $derbies->data_seek(0); while($d=$derbies->fetch_assoc()): ?>
          <option value="<?=$d['derby_id']?>" <?=$fdid==$d['derby_id']?'selected':''?>><?=htmlspecialchars($d['derby_name'])?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="row2">
        <div class="field"><label>Owner / Handler *</label>
          <select name="owner_id" id="em-owner" required onchange="filterRoosters(this.value)">
            <option value="">— Select owner —</option>
            <?php $owners->data_seek(0); while($o=$owners->fetch_assoc()): ?>
            <option value="<?=$o['owner_id']?>"><?=htmlspecialchars($o['full_name'].($o['nickname']?' ('.$o['nickname'].')':''))?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Rooster *</label>
          <select name="rooster_id" id="em-rooster" required>
            <option value="">— Select owner first —</option>
            <?php $roosters->data_seek(0); while($r=$roosters->fetch_assoc()): ?>
            <option value="<?=$r['rooster_id']?>" data-owner="<?=$r['owner_id']?>" data-weight="<?=$r['weight_kg']?>"><?=htmlspecialchars($r['name'].' ('.$r['breed'].')')?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="row3">
        <div class="field"><label>Weight at Entry (kg)</label><input type="number" name="weight_at_entry" id="em-weight" step="0.01" min="1" max="5" placeholder="2.10"></div>
        <div class="field"><label>Side</label>
          <select name="side" id="em-side">
            <option value="pending">TBD</option>
            <option value="meron">Meron</option>
            <option value="wala">Wala</option>
          </select>
        </div>
        <div class="field"><label>Status</label>
          <select name="status" id="em-status">
            <?php foreach($estatuses as $st): ?><option value="<?=$st?>"><?=ucfirst($st)?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field" style="display:flex;align-items:center;gap:.75rem">
        <input type="checkbox" name="entry_fee_paid" id="em-paid" value="1" style="width:auto">
        <label for="em-paid" style="font-size:.82rem;text-transform:none;letter-spacing:0;cursor:pointer;margin-bottom:0">Entry Fee Paid</label>
      </div>
      <div class="field"><label>Notes</label><textarea name="notes" id="em-notes" placeholder="Additional notes..."></textarea></div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('entryModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Entry</button>
      </div>
    </form>
  </div>
</div>

<script>
// Store rooster data
const roosters = {};
<?php $roosters->data_seek(0); while($r=$roosters->fetch_assoc()): ?>
roosters[<?=$r['rooster_id']?>] = {owner: <?=$r['owner_id']?>, weight: '<?=$r['weight_kg']?>'};
<?php endwhile; ?>

function filterRoosters(ownerId) {
  const sel = document.getElementById('em-rooster');
  Array.from(sel.options).forEach(opt => {
    if (!opt.value) return;
    opt.style.display = (!ownerId || roosters[opt.value]?.owner == ownerId) ? '' : 'none';
  });
  sel.value = '';
}

function openEntry(id, did=0, oid=0, rid=0, wt='', side='pending', status='registered', paid=0, notes='') {
  document.getElementById('em-title').textContent = id ? 'Edit Derby Entry' : 'Register Derby Entry';
  document.getElementById('em-id').value      = id;
  document.getElementById('em-derby').value   = did || <?= $fdid ?: 0 ?>;
  document.getElementById('em-owner').value   = oid;
  document.getElementById('em-weight').value  = wt;
  document.getElementById('em-side').value    = side;
  document.getElementById('em-status').value  = status;
  document.getElementById('em-paid').checked  = !!paid;
  document.getElementById('em-notes').value   = notes;
  if (oid) filterRoosters(oid);
  setTimeout(() => { document.getElementById('em-rooster').value = rid; }, 50);
  document.getElementById('entryModal').classList.add('open');
}
document.getElementById('em-rooster').addEventListener('change', function() {
  const r = roosters[this.value];
  if (r && !document.getElementById('em-weight').value) document.getElementById('em-weight').value = r.weight;
});
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body>
</html>
