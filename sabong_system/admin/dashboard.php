<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();

$today = date('Y-m-d');
$s['total_derbies']   = qs($db,"SELECT COUNT(*) FROM derbies");
$s['open_derbies']    = qs($db,"SELECT COUNT(*) FROM derbies WHERE status='registration_open'");
$s['total_roosters']  = qs($db,"SELECT COUNT(*) FROM roosters WHERE status='active'");
$s['total_owners']    = qs($db,"SELECT COUNT(*) FROM owners WHERE is_active=1");
$s['total_matches']   = qs($db,"SELECT COUNT(*) FROM matches");
$s['pending_entries'] = qs($db,"SELECT COUNT(*) FROM derby_entries WHERE status='registered'");

$upcoming = $db->query("SELECT d.*,(SELECT COUNT(*) FROM derby_entries WHERE derby_id=d.derby_id) ec FROM derbies d WHERE d.status IN ('registration_open','upcoming') ORDER BY d.event_date ASC LIMIT 4");
$recent_entries = $db->query("SELECT de.*,d.derby_name,o.full_name owner_name,r.name rooster_name FROM derby_entries de JOIN derbies d ON de.derby_id=d.derby_id JOIN owners o ON de.owner_id=o.owner_id JOIN roosters r ON de.rooster_id=r.rooster_id ORDER BY de.created_at DESC LIMIT 8");
$recent_matches = $db->query("SELECT m.*,d.derby_name, me_r.name meron_name, we_r.name wala_name FROM matches m LEFT JOIN derbies d ON m.derby_id=d.derby_id LEFT JOIN derby_entries me ON m.meron_entry_id=me.entry_id LEFT JOIN roosters me_r ON me.rooster_id=me_r.rooster_id LEFT JOIN derby_entries we ON m.wala_entry_id=we.entry_id LEFT JOIN roosters we_r ON we.rooster_id=we_r.rooster_id ORDER BY m.created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="page-hd">
    <h1>🏟️ Arena Dashboard</h1>
    <div class="page-hd-right">
      <span style="font-size:.78rem;color:var(--muted);background:var(--dark3);padding:.4rem .9rem;border-radius:20px;border:1px solid var(--border2)">📅 <?= date('l, F j, Y') ?></span>
      <a href="derbies.php" class="btn-primary btn-gold">+ New Derby</a>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat-card stat-gold"><div class="lbl">Total Derbies</div><div class="val"><?= $s['total_derbies'] ?></div><div class="sub">All time</div></div>
    <div class="stat-card stat-green"><div class="lbl">Open Registration</div><div class="val"><?= $s['open_derbies'] ?></div><div class="sub">Accepting entries</div></div>
    <div class="stat-card stat-amber"><div class="lbl">Active Roosters</div><div class="val"><?= $s['total_roosters'] ?></div><div class="sub">Registered</div></div>
    <div class="stat-card"><div class="lbl">Owners / Handlers</div><div class="val" style="color:var(--text)"><?= $s['total_owners'] ?></div><div class="sub">Active</div></div>
    <div class="stat-card stat-blood"><div class="lbl">Pending Entries</div><div class="val"><?= $s['pending_entries'] ?></div><div class="sub">Unconfirmed</div></div>
  </div>

  <div class="grid2">

    <!-- UPCOMING DERBIES -->
    <div class="card">
      <div class="card-hd"><h3>🏆 Upcoming Derbies</h3><a href="derbies.php" class="btn-ghost" style="font-size:.72rem">View all →</a></div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Derby</th><th>Date</th><th>Entries</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php while($d=$upcoming->fetch_assoc()):
            $smap=['registration_open'=>'b-green','upcoming'=>'b-gold','ongoing'=>'b-blood','completed'=>'b-gray'];
          ?>
          <tr>
            <td><div class="t-name"><?= htmlspecialchars($d['derby_name']) ?></div><div class="t-sub"><?= ucwords(str_replace('_',' ',$d['derby_type'])) ?></div></td>
            <td class="t-sub"><?= date('M j, Y',strtotime($d['event_date'])) ?></td>
            <td><span style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)"><?= $d['ec'] ?>/<?= $d['max_entries'] ?></span></td>
            <td><span class="badge <?= $smap[$d['status']]??'b-gray' ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span></td>
            <td><a href="entries.php?derby_id=<?= $d['derby_id'] ?>" class="btn-xs bx-view">Entries</a></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RECENT MATCHES -->
    <div class="card">
      <div class="card-hd"><h3>⚔️ Recent Fights</h3><a href="matches.php" class="btn-ghost" style="font-size:.72rem">View all →</a></div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>Fight #</th><th>Meron</th><th>Wala</th><th>Result</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($m=$recent_matches->fetch_assoc()):
            $rmap=['meron'=>'b-meron','wala'=>'b-wala','draw'=>'b-amber','cancelled'=>'b-gray','no_contest'=>'b-gray'];
          ?>
          <tr>
            <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">F-<?= str_pad($m['fight_number'],3,'0',STR_PAD_LEFT) ?></td>
            <td class="t-name"><?= htmlspecialchars($m['meron_name']??'—') ?></td>
            <td class="t-name"><?= htmlspecialchars($m['wala_name']??'—') ?></td>
            <td><?php if($m['result']): ?><span class="badge <?= $rmap[$m['result']]??'b-gray' ?>"><?= strtoupper($m['result']) ?></span><?php else: ?>—<?php endif; ?></td>
            <td><span class="badge <?= $m['status']==='completed'?'b-green':($m['status']==='ongoing'?'b-blood':'b-gold') ?>"><?= ucfirst($m['status']) ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RECENT ENTRIES -->
    <div class="card full">
      <div class="card-hd"><h3>📋 Recent Derby Entries</h3><a href="entries.php" class="btn-ghost" style="font-size:.72rem">View all →</a></div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>#</th><th>Derby</th><th>Owner</th><th>Rooster</th><th>Side</th><th>Fee Paid</th><th>Status</th><th>Registered</th></tr></thead>
          <tbody>
          <?php while($e=$recent_entries->fetch_assoc()):
            $emap=['registered'=>'b-gold','confirmed'=>'b-green','scratched'=>'b-gray','disqualified'=>'b-red'];
          ?>
          <tr>
            <td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">#<?= $e['entry_id'] ?></td>
            <td><div class="t-name"><?= htmlspecialchars($e['derby_name']) ?></div></td>
            <td class="t-name"><?= htmlspecialchars($e['owner_name']) ?></td>
            <td class="t-name"><?= htmlspecialchars($e['rooster_name']) ?></td>
            <td><?php if($e['side']!=='pending'): ?><span class="badge <?= $e['side']==='meron'?'b-meron':'b-wala' ?>"><?= strtoupper($e['side']) ?></span><?php else: ?><span class="t-sub">TBD</span><?php endif; ?></td>
            <td><?= $e['entry_fee_paid'] ? '<span class="badge b-green">Paid</span>' : '<span class="badge b-red">Unpaid</span>' ?></td>
            <td><span class="badge <?= $emap[$e['status']]??'b-gold' ?>"><?= ucfirst($e['status']) ?></span></td>
            <td class="t-sub"><?= date('M j, Y',strtotime($e['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>
</body>
</html>
