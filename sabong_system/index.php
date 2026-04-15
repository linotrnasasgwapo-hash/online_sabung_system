<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Saraet Cockpit Arena — Himamaylan City</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blood:#8b1a1a;--blood2:#6b0f0f;--blood-light:#c0392b;
  --gold:#d4a843;--gold2:#b8922e;--gold-light:#f0c96a;
  --dark:#0a0a0a;--dark2:#111;--dark3:#1a1a1a;--dark4:#222;
  --text:#e8e0d0;--muted:#9a9080;--border:rgba(212,168,67,.2);
}
body{font-family:'Crimson Pro',serif;background:var(--dark);color:var(--text);min-height:100vh;}

/* NAV */
nav{background:rgba(10,10,10,.95);backdrop-filter:blur(10px);padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;border-bottom:1px solid var(--border);}
.nav-brand{display:flex;align-items:center;gap:.75rem;text-decoration:none;}
.brand-icon{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--blood),var(--gold));display:flex;align-items:center;justify-content:center;font-size:1.3rem;}
.brand-text h1{font-family:'Cinzel',serif;font-size:.95rem;font-weight:700;color:var(--gold);letter-spacing:.08em;}
.brand-text p{font-size:.62rem;color:var(--muted);letter-spacing:.06em;text-transform:uppercase;}
.nav-links{display:flex;align-items:center;gap:.5rem;}
.nav-link{padding:.45rem .9rem;color:var(--muted);font-size:.85rem;text-decoration:none;border-radius:6px;transition:color .2s;}
.nav-link:hover{color:var(--gold);}
.nav-cta{padding:.5rem 1.25rem;background:linear-gradient(135deg,var(--blood),var(--blood2));color:#fff;border-radius:8px;font-size:.85rem;font-weight:600;font-family:'Cinzel',serif;letter-spacing:.05em;text-decoration:none;border:1px solid rgba(212,168,67,.3);transition:all .2s;}
.nav-cta:hover{background:linear-gradient(135deg,var(--blood-light),var(--blood));box-shadow:0 4px 20px rgba(139,26,26,.4);}

/* HERO */
.hero{min-height:92vh;display:flex;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden;padding:4rem 2rem;}
.hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse at 50% 60%,rgba(139,26,26,.25) 0%,transparent 70%),radial-gradient(ellipse at 80% 20%,rgba(212,168,67,.08) 0%,transparent 50%),linear-gradient(180deg,var(--dark) 0%,var(--dark2) 100%);}
.hero-pattern{position:absolute;inset:0;opacity:.04;background-image:repeating-linear-gradient(45deg,var(--gold) 0,var(--gold) 1px,transparent 0,transparent 50%);background-size:20px 20px;}
.hero-content{position:relative;z-index:1;max-width:800px;}
.hero-badge{display:inline-block;padding:.35rem 1.1rem;background:rgba(212,168,67,.1);border:1px solid rgba(212,168,67,.3);border-radius:20px;font-size:.72rem;font-weight:600;color:var(--gold);letter-spacing:.12em;text-transform:uppercase;margin-bottom:1.75rem;font-family:'Cinzel',serif;}
.hero h1{font-family:'Cinzel',serif;font-size:clamp(2.2rem,6vw,4.5rem);font-weight:900;color:#fff;line-height:1.1;margin-bottom:.5rem;text-shadow:0 0 40px rgba(212,168,67,.2);}
.hero h1 span{color:var(--gold);display:block;}
.hero-sub{font-size:1.15rem;color:var(--muted);max-width:500px;margin:.75rem auto 2.5rem;line-height:1.7;font-style:italic;}
.hero-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
.btn-hero{padding:.9rem 2.25rem;border-radius:10px;font-family:'Cinzel',serif;font-size:.9rem;font-weight:700;letter-spacing:.06em;text-decoration:none;transition:all .2s;}
.btn-primary{background:linear-gradient(135deg,var(--blood),var(--blood2));color:#fff;border:1px solid rgba(212,168,67,.3);box-shadow:0 4px 24px rgba(139,26,26,.3);}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(139,26,26,.5);}
.btn-secondary{background:rgba(212,168,67,.08);color:var(--gold);border:1px solid var(--border);}
.btn-secondary:hover{background:rgba(212,168,67,.15);}

/* STATS */
.stats{background:var(--dark3);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:2rem;}
.stats-inner{max-width:900px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;text-align:center;}
.stat-item .num{font-family:'Cinzel',serif;font-size:2rem;font-weight:900;color:var(--gold);}
.stat-item .lbl{font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-top:.2rem;}

/* UPCOMING DERBIES */
.section{padding:5rem 2rem;max-width:1000px;margin:0 auto;}
.section-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.14em;color:var(--blood-light);margin-bottom:.5rem;font-family:'Cinzel',serif;}
.section h2{font-family:'Cinzel',serif;font-size:2rem;font-weight:700;color:var(--gold);margin-bottom:.6rem;}
.section .sub{color:var(--muted);font-size:.95rem;line-height:1.7;margin-bottom:2.5rem;font-style:italic;}
.derby-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;}
.derby-card{background:var(--dark3);border:1px solid var(--border);border-radius:16px;padding:1.5rem;position:relative;overflow:hidden;transition:border-color .2s,transform .2s;}
.derby-card:hover{border-color:rgba(212,168,67,.5);transform:translateY(-3px);}
.derby-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--blood),var(--gold));}
.dc-type{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gold);font-family:'Cinzel',serif;margin-bottom:.75rem;}
.dc-name{font-family:'Cinzel',serif;font-size:1rem;font-weight:700;color:#fff;margin-bottom:.75rem;line-height:1.3;}
.dc-meta{display:flex;flex-direction:column;gap:.35rem;}
.dc-row{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--muted);}
.dc-row span:first-child{font-size:.9rem;}
.dc-status{display:inline-block;padding:.22rem .7rem;border-radius:20px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-top:.75rem;}
.s-open{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.2);}
.s-upcoming{background:rgba(212,168,67,.1);color:var(--gold);border:1px solid var(--border);}
.s-completed{background:rgba(100,100,100,.1);color:#666;border:1px solid rgba(100,100,100,.2);}
.s-ongoing{background:rgba(139,26,26,.2);color:#f87171;border:1px solid rgba(139,26,26,.3);}
.dc-prize{font-family:'Cinzel',serif;font-size:1.1rem;font-weight:700;color:var(--gold-light);margin-top:.75rem;}

/* HOW IT WORKS */
.how{background:var(--dark2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:5rem 2rem;}
.how-inner{max-width:900px;margin:0 auto;}
.how-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-top:2.5rem;}
.how-card{background:var(--dark3);border:1px solid var(--border);border-radius:14px;padding:1.5rem;text-align:center;}
.how-num{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--blood),var(--blood2));display:flex;align-items:center;justify-content:center;font-family:'Cinzel',serif;font-weight:900;color:var(--gold);font-size:.9rem;margin:0 auto .9rem;}
.how-card h4{font-family:'Cinzel',serif;font-size:.88rem;font-weight:700;color:#fff;margin-bottom:.4rem;}
.how-card p{font-size:.8rem;color:var(--muted);line-height:1.6;}

/* FOOTER */
footer{background:var(--dark);border-top:1px solid var(--border);padding:2rem;text-align:center;}
footer p{color:var(--muted);font-size:.8rem;}
footer a{color:var(--gold);text-decoration:none;}
</style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-brand">
    <div class="brand-icon">🐓</div>
    <div class="brand-text">
      <h1>Saraet Cockpit Arena</h1>
      <p>Himamaylan City</p>
    </div>
  </a>
  <div class="nav-links">
    <a href="#derbies" class="nav-link">Derbies</a>
    <a href="#how" class="nav-link">How It Works</a>
    <a href="admin/login.php" class="nav-cta">Staff Login</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-pattern"></div>
  <div class="hero-content">
    <div class="hero-badge">🐓 Official Arena Management System</div>
    <h1>Saraet Cockpit Arena<span>Management System</span></h1>
    <p class="hero-sub">Complete cockpit arena operations — derby registration, match management, rooster records, and arena scheduling in one powerful platform.</p>
    <div class="hero-cta">
      <a href="admin/login.php" class="btn-hero btn-primary">Enter Arena Dashboard</a>
      <a href="#derbies" class="btn-hero btn-secondary">View Upcoming Derbies</a>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats">
  <div class="stats-inner">
    <?php
    require_once 'config/db.php';
    $db = getDB();
    $total_derbies  = qs($db,"SELECT COUNT(*) FROM derbies");
    $total_roosters = qs($db,"SELECT COUNT(*) FROM roosters WHERE status='active'");
    $total_owners   = qs($db,"SELECT COUNT(*) FROM owners WHERE is_active=1");
    $total_matches  = qs($db,"SELECT COUNT(*) FROM matches");
    ?>
    <div class="stat-item"><div class="num"><?= $total_derbies ?></div><div class="lbl">Total Derbies</div></div>
    <div class="stat-item"><div class="num"><?= $total_roosters ?></div><div class="lbl">Active Roosters</div></div>
    <div class="stat-item"><div class="num"><?= $total_owners ?></div><div class="lbl">Registered Owners</div></div>
    <div class="stat-item"><div class="num"><?= $total_matches ?></div><div class="lbl">Total Fights</div></div>
  </div>
</div>

<!-- UPCOMING DERBIES -->
<div class="section" id="derbies">
  <div class="section-label">Events</div>
  <h2>Upcoming Derbies</h2>
  <p class="sub">Register your rooster and join the competition. All derbies are managed and officiated by certified arena staff.</p>
  <div class="derby-grid">
    <?php
    $derbies = $db->query("SELECT * FROM derbies ORDER BY event_date ASC LIMIT 6");
    $smap = ['registration_open'=>'s-open','upcoming'=>'s-upcoming','completed'=>'s-completed','ongoing'=>'s-ongoing','cancelled'=>'s-completed'];
    $tmap = ['open_derby'=>'Open Derby','local_derby'=>'Local Derby','invitational'=>'Invitational','special_derby'=>'Special Derby','fiesta_derby'=>'Fiesta Derby'];
    while($d=$derbies->fetch_assoc()):
    ?>
    <div class="derby-card">
      <div class="dc-type"><?= $tmap[$d['derby_type']]??$d['derby_type'] ?></div>
      <div class="dc-name"><?= htmlspecialchars($d['derby_name']) ?></div>
      <div class="dc-meta">
        <div class="dc-row"><span>📅</span><?= date('F j, Y', strtotime($d['event_date'])) ?></div>
        <div class="dc-row"><span>🕐</span><?= date('h:i A', strtotime($d['event_time'])) ?></div>
        <div class="dc-row"><span>📍</span><?= htmlspecialchars($d['venue']) ?></div>
        <div class="dc-row"><span>🐓</span><?= $d['current_entries'] ?>/<?= $d['max_entries'] ?> entries</div>
      </div>
      <?php if($d['prize_pool']>0): ?>
      <div class="dc-prize">Prize Pool: ₱<?= number_format($d['prize_pool'],0) ?></div>
      <?php endif; ?>
      <div><span class="dc-status <?= $smap[$d['status']]??'s-upcoming' ?>"><?= ucfirst(str_replace('_',' ',$d['status'])) ?></span></div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- HOW IT WORKS -->
<div class="how" id="how">
  <div class="how-inner">
    <div style="text-align:center">
      <div class="section-label" style="justify-content:center;display:flex">Process</div>
      <h2 style="font-family:'Cinzel',serif;font-size:2rem;color:var(--gold);margin-top:.3rem">How It Works</h2>
    </div>
    <div class="how-grid">
      <div class="how-card"><div class="how-num">1</div><h4>Register Owners</h4><p>Add cockfighter owners and their contact details to the system registry.</p></div>
      <div class="how-card"><div class="how-num">2</div><h4>Add Roosters</h4><p>Register each rooster with breed, weight, color, and win/loss record.</p></div>
      <div class="how-card"><div class="how-num">3</div><h4>Create Derby</h4><p>Set up a derby event with date, entry fee, prize pool, and max entries.</p></div>
      <div class="how-card"><div class="how-num">4</div><h4>Open Registration</h4><p>Accept rooster entries and assign them as Meron or Wala for each fight.</p></div>
      <div class="how-card"><div class="how-num">5</div><h4>Schedule Matches</h4><p>Pair entries into fights with assigned referees and fight numbers.</p></div>
      <div class="how-card"><div class="how-num">6</div><h4>Record Results</h4><p>Enter fight results live and track wins/losses for all roosters and owners.</p></div>
    </div>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> Saraet Cockpit Arena — Himamaylan City, Negros Occidental &nbsp;|&nbsp; <a href="admin/login.php">Staff Portal</a></p>
</footer>

</body>
</html>
