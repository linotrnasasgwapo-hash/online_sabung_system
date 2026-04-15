<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

// Save/Update derby
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_derby'])) {
    $id   = (int)($_POST['derby_id']??0);
    $name = clean($db,$_POST['derby_name']??'');
    $type = clean($db,$_POST['derby_type']??'open_derby');
    $venue= clean($db,$_POST['venue']??'');
    $date = clean($db,$_POST['event_date']??'');
    $time = clean($db,$_POST['event_time']??'14:00');
    $fee  = (float)$_POST['entry_fee'];
    $pool = (float)$_POST['prize_pool'];
    $max  = (int)$_POST['max_entries'];
    $status= clean($db,$_POST['status']??'upcoming');
    $desc = clean($db,$_POST['description']??'');
    $rules= clean($db,$_POST['rules']??'');
    $aid  = $_SESSION['admin_id'];
    if ($id) {
        $db->query("UPDATE derbies SET derby_name='$name',derby_type='$type',venue='$venue',event_date='$date',event_time='$time',entry_fee=$fee,prize_pool=$pool,max_entries=$max,status='$status',description='$desc',rules='$rules' WHERE derby_id=$id");
        $msg = 'Derby updated successfully.';
    } else {
        $db->query("INSERT INTO derbies (derby_name,derby_type,venue,event_date,event_time,entry_fee,prize_pool,max_entries,status,description,rules,created_by) VALUES('$name','$type','$venue','$date','$time',$fee,$pool,$max,'$status','$desc','$rules',$aid)");
        $msg = 'Derby created successfully.';
    }
}

// Delete
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $db->query("DELETE FROM derbies WHERE derby_id=$did");
    header('Location: derbies.php?msg=deleted'); exit;
}

$filter = clean($db,$_GET['status']??'');
$where  = $filter ? "WHERE status='$filter'" : '';
$derbies= $db->query("SELECT d.*, (SELECT COUNT(*) FROM derby_entries WHERE derby_id=d.derby_id) entry_count FROM derbies d $where ORDER BY event_date DESC");

$types   = ['open_derby'=>'Open Derby','local_derby'=>'Local Derby','invitational'=>'Invitational','special_derby'=>'Special Derby','fiesta_derby'=>'Fiesta Derby'];
$statuses= ['upcoming','registration_open','ongoing','completed','cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Derby Events — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd">
    <h1>🏆 Derby Events</h1>
    <button class="btn-primary btn-gold" onclick="openModal(0)">+ Create Derby</button>
  </div>

  <?php if($msg||isset($_GET['msg'])): ?><div class="msg-success">✅ <?= htmlspecialchars($msg?:'Done.') ?></div><?php endif; ?>

  <form method="GET" class="filter-bar">
    <select name="status" onchange="this.form.submit()">
      <option value="">All Status</option>
      <?php foreach($statuses as $st): ?><option <?=$filter===$st?'selected':''?> value="<?=$st?>"><?=ucfirst(str_replace('_',' ',$st))?></option><?php endforeach; ?>
    </select>
    <?php if($filter): ?><a href="derbies.php" style="font-size:.78rem;color:var(--muted);text-decoration:none;">Clear</a><?php endif; ?>
  </form>

  <div class="card">
    <div class="card-hd"><h3>All Derbies</h3><span style="font-size:.75rem;color:var(--muted)"><?= $derbies->num_rows ?> records</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Derby Name</th><th>Type</th><th>Date & Time</th><th>Venue</th><th>Entry Fee</th><th>Prize Pool</th><th>Entries</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php
        $smap=['registration_open'=>'b-green','upcoming'=>'b-gold','ongoing'=>'b-blood','completed'=>'b-gray','cancelled'=>'b-red'];
        while($d=$derbies->fetch_assoc()):
        ?>
        <tr>
          <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">#<?=$d['derby_id']?></td>
          <td><div class="t-name"><?=htmlspecialchars($d['derby_name'])?></div><div class="t-sub"><?=$d['description']?htmlspecialchars(substr($d['description'],0,40)).'...':'—'?></div></td>
          <td class="t-sub"><?=$types[$d['derby_type']]??$d['derby_type']?></td>
          <td><div class="t-name"><?=date('M j, Y',strtotime($d['event_date']))?></div><div class="t-sub"><?=date('h:i A',strtotime($d['event_time']))?></div></td>
          <td class="t-sub"><?=htmlspecialchars($d['venue'])?></td>
          <td style="font-family:'Cinzel',serif;color:var(--gold-light)">₱<?=number_format($d['entry_fee'],0)?></td>
          <td style="font-family:'Cinzel',serif;color:var(--gold)">₱<?=number_format($d['prize_pool'],0)?></td>
          <td><span style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$d['entry_count']?>/<?=$d['max_entries']?></span></td>
          <td><span class="badge <?=$smap[$d['status']]??'b-gray'?>"><?=ucfirst(str_replace('_',' ',$d['status']))?></span></td>
          <td style="display:flex;gap:.3rem;flex-wrap:wrap">
            <button class="btn-xs bx-edit" onclick="openModal(<?=$d['derby_id']?>,'<?=addslashes($d['derby_name'])?>','<?=$d['derby_type']?>','<?=addslashes($d['venue'])?>','<?=$d['event_date']?>','<?=$d['event_time']?>','<?=$d['entry_fee']?>','<?=$d['prize_pool']?>','<?=$d['max_entries']?>','<?=$d['status']?>','<?=addslashes($d['description']??'')?>','<?=addslashes($d['rules']??'')?>')">Edit</button>
            <a href="entries.php?derby_id=<?=$d['derby_id']?>" class="btn-xs bx-view">Entries</a>
            <a href="matches.php?derby_id=<?=$d['derby_id']?>" class="btn-xs bx-add">Fights</a>
            <a href="derbies.php?delete=<?=$d['derby_id']?>" class="btn-xs bx-delete" onclick="return confirm('Delete this derby? All entries and matches will be removed.')">Del</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- DERBY MODAL -->
<div class="overlay" id="derbyModal">
  <div class="modal">
    <h3 id="m-title">Create Derby Event</h3>
    <form method="POST">
      <input type="hidden" name="save_derby" value="1">
      <input type="hidden" name="derby_id" id="m-id">
      <div class="field"><label>Derby Name *</label><input name="derby_name" id="m-name" required placeholder="e.g. Himamaylan City Fiesta Derby 2026"></div>
      <div class="row2">
        <div class="field"><label>Derby Type</label>
          <select name="derby_type" id="m-type">
            <?php foreach($types as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Status</label>
          <select name="status" id="m-status">
            <?php foreach($statuses as $st): ?><option value="<?=$st?>"><?=ucfirst(str_replace('_',' ',$st))?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field"><label>Venue</label><input name="venue" id="m-venue" placeholder="Arena name and location"></div>
      <div class="row2">
        <div class="field"><label>Event Date *</label><input type="date" name="event_date" id="m-date" required></div>
        <div class="field"><label>Event Time</label><input type="time" name="event_time" id="m-time" value="14:00"></div>
      </div>
      <div class="row3">
        <div class="field"><label>Entry Fee (₱)</label><input type="number" name="entry_fee" id="m-fee" value="0" min="0" step="0.01"></div>
        <div class="field"><label>Prize Pool (₱)</label><input type="number" name="prize_pool" id="m-pool" value="0" min="0" step="0.01"></div>
        <div class="field"><label>Max Entries</label><input type="number" name="max_entries" id="m-max" value="20" min="2" max="200"></div>
      </div>
      <div class="field"><label>Description</label><textarea name="description" id="m-desc" placeholder="Brief description of this derby..."></textarea></div>
      <div class="field"><label>Rules & Conditions</label><textarea name="rules" id="m-rules" placeholder="Rules, conditions, and special notes..."></textarea></div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('derbyModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Derby</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id,name='',type='open_derby',venue='',date='',time='14:00',fee='0',pool='0',max='20',status='upcoming',desc='',rules='') {
  document.getElementById('m-title').textContent = id ? 'Edit Derby Event' : 'Create Derby Event';
  document.getElementById('m-id').value     = id;
  document.getElementById('m-name').value   = name;
  document.getElementById('m-type').value   = type;
  document.getElementById('m-venue').value  = venue;
  document.getElementById('m-date').value   = date;
  document.getElementById('m-time').value   = time.substring(0,5);
  document.getElementById('m-fee').value    = fee;
  document.getElementById('m-pool').value   = pool;
  document.getElementById('m-max').value    = max;
  document.getElementById('m-status').value = status;
  document.getElementById('m-desc').value   = desc;
  document.getElementById('m-rules').value  = rules;
  document.getElementById('derbyModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body>
</html>
