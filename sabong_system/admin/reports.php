<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../config/db.php'; $db=getDB();
$stats=['total_derbies'=>qs($db,"SELECT COUNT(*) FROM derbies"),'total_matches'=>qs($db,"SELECT COUNT(*) FROM matches"),'total_roosters'=>qs($db,"SELECT COUNT(*) FROM roosters"),'total_owners'=>qs($db,"SELECT COUNT(*) FROM owners"),'meron_wins'=>qs($db,"SELECT COUNT(*) FROM matches WHERE result='meron'"),'wala_wins'=>qs($db,"SELECT COUNT(*) FROM matches WHERE result='wala'"),'draws'=>qs($db,"SELECT COUNT(*) FROM matches WHERE result='draw'"),'total_entries'=>qs($db,"SELECT COUNT(*) FROM derby_entries")];
$top_roosters=$db->query("SELECT r.name,r.breed,r.wins,r.losses,r.draws,o.full_name owner_name FROM roosters r JOIN owners o ON r.owner_id=o.owner_id ORDER BY r.wins DESC LIMIT 10");
$top_owners=$db->query("SELECT full_name,team_name,wins,losses,draws FROM owners ORDER BY wins DESC LIMIT 10");
$derby_summary=$db->query("SELECT d.derby_name,d.derby_type,d.event_date,d.status,(SELECT COUNT(*) FROM derby_entries WHERE derby_id=d.derby_id) entries,(SELECT COUNT(*) FROM matches WHERE derby_id=d.derby_id) fights FROM derbies d ORDER BY d.event_date DESC LIMIT 10");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reports — Saraet Cockpit Arena</title><link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/admin.css"></head>
<body><?php include '../includes/sidebar.php';?>
<main class="main">
  <div class="page-hd"><h1>📊 Reports & Statistics</h1></div>
  <div class="stats" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card stat-gold"><div class="lbl">Total Derbies</div><div class="val"><?=$stats['total_derbies']?></div></div>
    <div class="stat-card stat-blood"><div class="lbl">Total Fights</div><div class="val"><?=$stats['total_matches']?></div></div>
    <div class="stat-card stat-green"><div class="lbl">Total Roosters</div><div class="val"><?=$stats['total_roosters']?></div></div>
    <div class="stat-card stat-amber"><div class="lbl">Registered Owners</div><div class="val"><?=$stats['total_owners']?></div></div>
  </div>

  <!-- Win distribution -->
  <div class="card" style="margin-bottom:1.5rem">
    <div class="card-hd"><h3>Fight Result Distribution</h3></div>
    <div style="padding:1.25rem;display:flex;gap:2rem;flex-wrap:wrap">
      <?php
      $total = $stats['meron_wins']+$stats['wala_wins']+$stats['draws'];
      $total = max(1,$total);
      $items=[['Meron Wins',$stats['meron_wins'],'#f87171'],['Wala Wins',$stats['wala_wins'],'#93c5fd'],['Draws',$stats['draws'],'#fbbf24']];
      foreach($items as [$lbl,$cnt,$clr]):
        $pct = round($cnt/$total*100);
      ?>
      <div style="flex:1;min-width:160px">
        <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.4rem"><span style="color:var(--text);font-weight:600"><?=$lbl?></span><span style="color:var(--muted)"><?=$cnt?> (<?=$pct?>%)</span></div>
        <div style="background:rgba(255,255,255,.06);border-radius:6px;height:10px"><div style="background:<?=$clr?>;height:10px;border-radius:6px;width:<?=$pct?>%"></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="grid2">
    <div class="card"><div class="card-hd"><h3>🐓 Top Roosters (by Wins)</h3></div>
    <div class="tbl-wrap"><table><thead><tr><th>Rank</th><th>Rooster</th><th>Owner</th><th>Breed</th><th>W/L/D</th></tr></thead><tbody>
    <?php $rank=1; while($r=$top_roosters->fetch_assoc()):?>
    <tr><td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)"><?=$rank++?></td><td class="t-name"><?=htmlspecialchars($r['name'])?></td><td class="t-sub"><?=htmlspecialchars($r['owner_name'])?></td><td class="t-sub"><?=htmlspecialchars($r['breed']??'—')?></td><td><span style="color:#4ade80"><?=$r['wins']?>W</span>·<span style="color:#f87171"><?=$r['losses']?>L</span>·<span style="color:var(--gold-light)"><?=$r['draws']?>D</span></td></tr>
    <?php endwhile;?>
    </tbody></table></div></div>

    <div class="card"><div class="card-hd"><h3>👤 Top Owners (by Wins)</h3></div>
    <div class="tbl-wrap"><table><thead><tr><th>Rank</th><th>Owner</th><th>Team</th><th>W/L/D</th></tr></thead><tbody>
    <?php $rank=1; while($o=$top_owners->fetch_assoc()):?>
    <tr><td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)"><?=$rank++?></td><td class="t-name"><?=htmlspecialchars($o['full_name'])?></td><td class="t-sub"><?=htmlspecialchars($o['team_name']??'—')?></td><td><span style="color:#4ade80"><?=$o['wins']?>W</span>·<span style="color:#f87171"><?=$o['losses']?>L</span>·<span style="color:var(--gold-light)"><?=$o['draws']?>D</span></td></tr>
    <?php endwhile;?>
    </tbody></table></div></div>

    <div class="card full"><div class="card-hd"><h3>🏆 Derby Summary</h3></div>
    <div class="tbl-wrap"><table><thead><tr><th>Derby</th><th>Type</th><th>Date</th><th>Entries</th><th>Fights</th><th>Status</th></tr></thead><tbody>
    <?php $smap=['registration_open'=>'b-green','upcoming'=>'b-gold','ongoing'=>'b-blood','completed'=>'b-gray','cancelled'=>'b-red']; while($d=$derby_summary->fetch_assoc()):?>
    <tr><td class="t-name"><?=htmlspecialchars($d['derby_name'])?></td><td class="t-sub"><?=ucwords(str_replace('_',' ',$d['derby_type']))?></td><td class="t-sub"><?=date('M j, Y',strtotime($d['event_date']))?></td><td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)"><?=$d['entries']?></td><td style="font-family:'Cinzel',serif;font-weight:700;color:var(--text)"><?=$d['fights']?></td><td><span class="badge <?=$smap[$d['status']]??'b-gray'?>"><?=ucfirst(str_replace('_',' ',$d['status']))?></span></td></tr>
    <?php endwhile;?>
    </tbody></table></div></div>
  </div>
</main>
</body></html>
