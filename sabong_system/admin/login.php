<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
if (isAdmin()) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db    = getDB();
    $email = clean($db,$_POST['email']??'');
    $pass  = $_POST['password']??'';
    $stmt  = $db->prepare("SELECT * FROM admins WHERE email=? AND is_active=1 LIMIT 1");
    $stmt->bind_param('s',$email); $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($pass,$admin['password'])) {
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff Login — Saraet Cockpit Arena</title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--blood:#8b1a1a;--blood2:#6b0f0f;--gold:#d4a843;--gold2:#b8922e;--dark:#0a0a0a;--dark2:#111;--dark3:#1a1a1a;--text:#e8e0d0;--muted:#9a9080;--border:rgba(212,168,67,.2);--red:#ef4444;}
body{font-family:'Crimson Pro',serif;background:var(--dark);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;position:relative;overflow:hidden;}
body::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 50%,rgba(139,26,26,.2) 0%,transparent 70%);}
body::after{content:'';position:absolute;inset:0;opacity:.03;background-image:repeating-linear-gradient(45deg,var(--gold) 0,var(--gold) 1px,transparent 0,transparent 50%);background-size:20px 20px;}
.wrap{width:100%;max-width:420px;position:relative;z-index:1;}
.card{background:var(--dark3);border:1px solid var(--border);border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5);}
.card-top{background:linear-gradient(145deg,var(--blood2) 0%,var(--blood) 50%,#3a0808 100%);padding:2.5rem 2rem 2rem;text-align:center;position:relative;overflow:hidden;}
.card-top::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(45deg,rgba(212,168,67,.04) 0,rgba(212,168,67,.04) 1px,transparent 0,transparent 12px);}
.emblem{width:72px;height:72px;border-radius:50%;background:rgba(212,168,67,.12);border:2px solid rgba(212,168,67,.3);display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto .9rem;position:relative;z-index:1;}
.card-top h1{font-family:'Cinzel',serif;color:var(--gold);font-size:1.25rem;font-weight:700;letter-spacing:.06em;position:relative;z-index:1;}
.card-top p{color:rgba(255,255,255,.5);font-size:.75rem;margin-top:.3rem;letter-spacing:.04em;text-transform:uppercase;position:relative;z-index:1;}
.card-body{padding:2rem;}
label{display:block;font-size:.72rem;font-weight:600;color:var(--muted);margin-bottom:.38rem;text-transform:uppercase;letter-spacing:.08em;font-family:'Cinzel',serif;}
input{width:100%;padding:.82rem 1rem;background:var(--dark2);border:1px solid rgba(212,168,67,.2);border-radius:10px;font-size:.92rem;font-family:inherit;outline:none;color:var(--text);transition:border-color .2s;}
input:focus{border-color:rgba(212,168,67,.5);box-shadow:0 0 0 3px rgba(212,168,67,.06);}
.field{margin-bottom:1.1rem;}
.btn{width:100%;padding:.88rem;background:linear-gradient(135deg,var(--blood),var(--blood2));color:#fff;border:1px solid rgba(212,168,67,.3);border-radius:11px;font-size:.88rem;font-weight:700;font-family:'Cinzel',serif;cursor:pointer;letter-spacing:.06em;transition:all .2s;margin-top:.25rem;}
.btn:hover{background:linear-gradient(135deg,#a01f1f,var(--blood));box-shadow:0 4px 20px rgba(139,26,26,.4);}
.error{background:rgba(239,68,68,.08);color:#f87171;border:1px solid rgba(239,68,68,.2);border-radius:9px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1rem;}
.hint{background:rgba(212,168,67,.06);border:1px solid var(--border);border-radius:9px;padding:.7rem 1rem;font-size:.76rem;color:var(--muted);margin-bottom:1rem;line-height:1.6;}
.hint strong{color:var(--gold);}
.hint code{background:rgba(212,168,67,.1);padding:.1rem .3rem;border-radius:4px;color:var(--gold);}
.footer-link{text-align:center;margin-top:1.2rem;font-size:.8rem;color:var(--muted);}
.footer-link a{color:var(--gold);text-decoration:none;}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="card-top">
      <div class="emblem">🐓</div>
      <h1>Saraet Cockpit Arena</h1>
      <p>Staff Management Portal</p>
    </div>
    <div class="card-body">
      <div class="hint">
        <strong>Demo Login:</strong><br>
        Email: <code>owner@arena.com</code> &nbsp; Password: <code>Admin@1234</code>
      </div>
      <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="field"><label>Email Address</label><input type="email" name="email" required placeholder="staff@arena.com" autocomplete="email"></div>
        <div class="field"><label>Password</label><input type="password" name="password" required placeholder="••••••••" autocomplete="current-password"></div>
        <button class="btn" type="submit">Enter the Arena</button>
      </form>
      <div class="footer-link"><a href="../index.php">← Back to Arena Home</a></div>
    </div>
  </div>
</div>
</body>
</html>
