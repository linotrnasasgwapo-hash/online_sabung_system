<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_match'])) {
    $mid   = (int)($_POST['match_id']??0);
    $did   = (int)$_POST['derby_id'];
    $fnum  = (int)$_POST['fight_number'];
    $meid  = (int)$_POST['meron_entry_id'];
    $weid  = (int)$_POST['wala_entry_id'];
    $mw    = (float)$_POST['meron_weight'];
    $ww    = (float)$_POST['wala_weight'];
    $ref   = (int)($_POST['referee_id']??0) ?: 'NULL';
    $mdate = clean($db,$_POST['match_date']??date('Y-m-d'));
    $mtime = clean($db,$_POST['match_time']??'14:00');
    $status= clean($db,$_POST['status']??'scheduled');
    $result= clean($db,$_POST['result']??'');
    $notes = clean($db,$_POST['notes']??'');
    $resultVal = $result ? "'$result'" : 'NULL';
    $durVal = 0;

    if ($mid) {
        $db->query("UPDATE matches SET derby_id=$did,fight_number=$fnum,meron_entry_id=$meid,wala_entry_id=$weid,meron_weight=$mw,wala_weight=$ww,referee_id=$ref,match_date='$mdate',match_time='$mtime',status='$status',result=$resultVal,notes='$notes' WHERE match_id=$mid");
        // Update rooster and owner records on result
        if ($result && in_array($result,['meron','wala','draw'])) {
            $me = $db->query("SELECT r.rooster_id,o.owner_id FROM derby_entries de JOIN roosters r ON de.rooster_id=r.rooster_id JOIN owners o ON r.owner_id=o.owner_id WHERE de.entry_id=$meid")->fetch_assoc();
            $we = $db->query("SELECT r.rooster_id,o.owner_id FROM derby_entries de JOIN roosters r ON de.rooster_id=r.rooster_id JOIN owners o ON r.owner_id=o.owner_id WHERE de.entry_id=$weid")->fetch_assoc();
            if ($me && $we) {
                if ($result==='meron') {
                    $db->query("UPDATE roosters SET wins=wins+1 WHERE rooster_id={$me['rooster_id']}");
                    $db->query("UPDATE roosters SET losses=losses+1 WHERE rooster_id={$we['rooster_id']}");
                    $db->query("UPDATE owners SET wins=wins+1 WHERE owner_id={$me['owner_id']}");
                    $db->query("UPDATE owners SET losses=losses+1 WHERE owner_id={$we['owner_id']}");
                } elseif ($result==='wala') {
                    $db->query("UPDATE roosters SET wins=wins+1 WHERE rooster_id={$we['rooster_id']}");
                    $db->query("UPDATE roosters SET losses=losses+1 WHERE rooster_id={$me['rooster_id']}");
                    $db->query("UPDATE owners SET wins=wins+1 WHERE owner_id={$we['owner_id']}");
                    $db->query("UPDATE owners SET losses=losses+1 WHERE owner_id={$me['owner_id']}");
                } else {
                    $db->query("UPDATE roosters SET draws=draws+1 WHERE rooster_id IN ({$me['rooster_id']},{$we['rooster_id']})");
                    $db->query("UPDATE owners SET draws=draws+1 WHERE owner_id IN ({$me['owner_id']},{$we['owner_id']})");
                }
            }
        }
        $msg = 'Fight updated.';
    } else {
        $db->query("INSERT INTO matches (derby_id,fight_number,meron_entry_id,wala_entry_id,meron_weight,wala_weight,referee_id,match_date,match_time,status,result,notes) VALUES($did,$fnum,$meid,$weid,$mw,$ww,$ref,'$mdate','$mtime','$status',$resultVal,'$notes')");
        $msg = 'Fight scheduled.';
    }
}

if (isset($_GET['delete'])) {
    $mid=(int)$_GET['delete'];
    $db->query("DELETE FROM matches WHERE match_id=$mid");
    header('Location: matches.php?msg=deleted'); exit;
}

$fdid   = (int)($_GET['derby_id']??0);
$where  = $fdid ? "WHERE m.derby_id=$fdid" : '';
$matches= $db->query("
    SELECT m.*, d.derby_name,
           me_r.name meron_name, we_r.name wala_name,
           me_o.full_name meron_owner, we_o.full_name wala_owner,
           ref.full_name ref_name
    FROM matches m
    LEFT JOIN derbies d ON m.derby_id=d.derby_id
    LEFT JOIN derby_entries me ON m.meron_entry_id=me.entry_id
    LEFT JOIN roosters me_r ON me.rooster_id=me_r.rooster_id
    LEFT JOIN owners me_o ON me.owner_id=me_o.owner_id
    LEFT JOIN derby_entries we ON m.wala_entry_id=we.entry_id
    LEFT JOIN roosters we_r ON we.rooster_id=we_r.rooster_id
    LEFT JOIN owners we_o ON we.owner_id=we_o.owner_id
    LEFT JOIN admins ref ON m.referee_id=ref.admin_id
    $where ORDER BY m.derby_id DESC, m.fight_number ASC
");

$derbies  = $db->query("SELECT derby_id,derby_name FROM derbies ORDER BY event_date DESC");
$all_entries = $db->query("SELECT de.entry_id,de.derby_id,de.entry_number,de.side,r.name rooster_name,o.full_name owner_name FROM derby_entries de JOIN roosters r ON de.rooster_id=r.rooster_id JOIN owners o ON de.owner_id=o.owner_id WHERE de.status NOT IN ('scratched','disqualified') ORDER BY de.derby_id,de.entry_number");
$referees = $db->query("SELECT admin_id,full_name FROM admins WHERE role='referee' AND is_active=1");
$results  = ['','meron','wala','draw','cancelled','no_contest'];
$mstatuses= ['scheduled','ongoing','completed','cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Matches — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd">
    <h1>⚔️ Matches / Fights</h1>
    <button class="btn-primary btn-gold" onclick="openMatch(0)">+ Schedule Fight</button>
  </div>
  <?php if($msg||isset($_GET['msg'])): ?><div class="msg-success">✅ <?=htmlspecialchars($msg?:'Done.')?></div><?php endif; ?>

  <form method="GET" class="filter-bar">
    <select name="derby_id" onchange="this.form.submit()">
      <option value="">All Derbies</option>
      <?php $derbies->data_seek(0); while($d=$derbies->fetch_assoc()): ?>
      <option value="<?=$d['derby_id']?>" <?=$fdid==$d['derby_id']?'selected':''?>><?=htmlspecialchars($d['derby_name'])?></option>
      <?php endwhile; ?>
    </select>
    <a href="matches.php" style="font-size:.76rem;color:var(--muted);text-decoration:none">Reset</a>
  </form>

  <div class="card">
    <div class="card-hd"><h3>All Fights</h3><span style="font-size:.75rem;color:var(--muted)"><?=$matches->num_rows?> fights</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Fight</th><th>Derby</th><th>Meron</th><th>Wala</th><th>Date</th><th>Referee</th><th>Status</th><th>Result</th><th>Actions</th></tr></thead>
        <tbody>
        <?php
        $rmap=['meron'=>'b-meron','wala'=>'b-wala','draw'=>'b-amber','cancelled'=>'b-gray','no_contest'=>'b-gray'];
        $smap2=['scheduled'=>'b-gold','ongoing'=>'b-blood','completed'=>'b-green','cancelled'=>'b-gray'];
        while($m=$matches->fetch_assoc()):
        ?>
        <tr>
          <td style="font-family:'Cinzel',serif;font-weight:900;color:var(--gold);font-size:1.1rem">F-<?=str_pad($m['fight_number'],3,'0',STR_PAD_LEFT)?></td>
          <td class="t-sub" style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($m['derby_name']??'—')?></td>
          <td>
            <div class="t-name"><?=htmlspecialchars($m['meron_name']??'—')?></div>
            <div class="t-sub"><?=htmlspecialchars($m['meron_owner']??'')?>  <?=$m['meron_weight']?'· '.$m['meron_weight'].'kg':''?></div>
          </td>
          <td>
            <div class="t-name"><?=htmlspecialchars($m['wala_name']??'—')?></div>
            <div class="t-sub"><?=htmlspecialchars($m['wala_owner']??'')?> <?=$m['wala_weight']?'· '.$m['wala_weight'].'kg':''?></div>
          </td>
          <td class="t-sub"><?=date('M j',strtotime($m['match_date']))?><br><?=date('h:i A',strtotime($m['match_time']))?></td>
          <td class="t-sub"><?=htmlspecialchars($m['ref_name']??'—')?></td>
          <td><span class="badge <?=$smap2[$m['status']]??'b-gray'?>"><?=ucfirst($m['status'])?></span></td>
          <td><?php if($m['result']): ?><span class="badge <?=$rmap[$m['result']]??'b-gray'?>"><?=strtoupper($m['result'])==='NO_CONTEST'?'NC':strtoupper($m['result'])?></span><?php else: ?><span class="t-sub">—</span><?php endif; ?></td>
          <td style="display:flex;gap:.3rem">
            <button class="btn-xs bx-edit" onclick="openMatch(<?=$m['match_id']?>,<?=$m['derby_id']?>,<?=$m['fight_number']?>,<?=$m['meron_entry_id']??0?>,<?=$m['wala_entry_id']??0?>,'<?=$m['meron_weight']?>','<?=$m['wala_weight']?>',<?=$m['referee_id']??0?>,'<?=$m['match_date']?>','<?=substr($m['match_time'],0,5)?>','<?=$m['status']?>','<?=$m['result']??''?>','<?=addslashes($m['notes']??'')?>')">Edit</button>
            <a href="matches.php?delete=<?=$m['match_id']?>" class="btn-xs bx-delete" onclick="return confirm('Delete this fight?')">Del</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="matchModal">
  <div class="modal" style="max-width:580px">
    <h3 id="mm-title">Schedule Fight</h3>
    <form method="POST">
      <input type="hidden" name="save_match" value="1">
      <input type="hidden" name="match_id" id="mm-id">
      <div class="row2">
        <div class="field"><label>Derby *</label>
          <select name="derby_id" id="mm-derby" required onchange="filterEntries(this.value)">
            <option value="">— Select derby —</option>
            <?php $derbies->data_seek(0); while($d=$derbies->fetch_assoc()): ?>
            <option value="<?=$d['derby_id']?>" <?=$fdid==$d['derby_id']?'selected':''?>><?=htmlspecialchars($d['derby_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Fight Number *</label><input type="number" name="fight_number" id="mm-fnum" required min="1" placeholder="1"></div>
      </div>
      <div class="row2">
        <div class="field"><label>Meron (Red Corner) *</label>
          <select name="meron_entry_id" id="mm-meron" required>
            <option value="">— Select derby first —</option>
            <?php $all_entries->data_seek(0); while($en=$all_entries->fetch_assoc()): ?>
            <option value="<?=$en['entry_id']?>" data-derby="<?=$en['derby_id']?>">[E-<?=str_pad($en['entry_number'],3,'0',STR_PAD_LEFT)?>] <?=htmlspecialchars($en['rooster_name'])?> (<?=htmlspecialchars($en['owner_name'])?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field"><label>Wala (Blue Corner) *</label>
          <select name="wala_entry_id" id="mm-wala" required>
            <option value="">— Select derby first —</option>
            <?php $all_entries->data_seek(0); while($en=$all_entries->fetch_assoc()): ?>
            <option value="<?=$en['entry_id']?>" data-derby="<?=$en['derby_id']?>">[E-<?=str_pad($en['entry_number'],3,'0',STR_PAD_LEFT)?>] <?=htmlspecialchars($en['rooster_name'])?> (<?=htmlspecialchars($en['owner_name'])?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="row2">
        <div class="field"><label>Meron Weight (kg)</label><input type="number" name="meron_weight" id="mm-mw" step="0.01" min="1" max="5" placeholder="2.10"></div>
        <div class="field"><label>Wala Weight (kg)</label><input type="number" name="wala_weight" id="mm-ww" step="0.01" min="1" max="5" placeholder="2.10"></div>
      </div>
      <div class="row3">
        <div class="field"><label>Match Date</label><input type="date" name="match_date" id="mm-date" value="<?=date('Y-m-d')?>"></div>
        <div class="field"><label>Match Time</label><input type="time" name="match_time" id="mm-time" value="14:00"></div>
        <div class="field"><label>Referee</label>
          <select name="referee_id" id="mm-ref">
            <option value="">— Assign —</option>
            <?php $referees->data_seek(0); while($r=$referees->fetch_assoc()): ?>
            <option value="<?=$r['admin_id']?>"><?=htmlspecialchars($r['full_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="row2">
        <div class="field"><label>Status</label>
          <select name="status" id="mm-status">
            <?php foreach($mstatuses as $st): ?><option value="<?=$st?>"><?=ucfirst($st)?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Result</label>
          <select name="result" id="mm-result">
            <option value="">— No result yet —</option>
            <option value="meron">Meron Wins</option>
            <option value="wala">Wala Wins</option>
            <option value="draw">Draw</option>
            <option value="cancelled">Cancelled</option>
            <option value="no_contest">No Contest</option>
          </select>
        </div>
      </div>
      <div class="field"><label>Notes</label><textarea name="notes" id="mm-notes" placeholder="Fight notes..."></textarea></div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('matchModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Fight</button>
      </div>
    </form>
  </div>
</div>

<script>
function filterEntries(did) {
  ['mm-meron','mm-wala'].forEach(id => {
    const sel = document.getElementById(id);
    Array.from(sel.options).forEach(opt => {
      if (!opt.value) return;
      opt.style.display = (!did || opt.dataset.derby == did) ? '' : 'none';
    });
    sel.value = '';
  });
}
function openMatch(id,did=0,fn=1,me=0,we=0,mw='',ww='',ref=0,date='',time='14:00',status='scheduled',result='',notes='') {
  document.getElementById('mm-title').textContent = id ? 'Edit Fight' : 'Schedule Fight';
  document.getElementById('mm-id').value      = id;
  document.getElementById('mm-fnum').value    = fn;
  document.getElementById('mm-mw').value      = mw;
  document.getElementById('mm-ww').value      = ww;
  document.getElementById('mm-date').value    = date || '<?=date("Y-m-d")?>';
  document.getElementById('mm-time').value    = time;
  document.getElementById('mm-status').value  = status;
  document.getElementById('mm-result').value  = result;
  document.getElementById('mm-ref').value     = ref;
  document.getElementById('mm-notes').value   = notes;
  if (did) {
    document.getElementById('mm-derby').value = did;
    filterEntries(did);
    setTimeout(() => {
      document.getElementById('mm-meron').value = me;
      document.getElementById('mm-wala').value  = we;
    }, 50);
  }
  document.getElementById('matchModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body>
</html>
