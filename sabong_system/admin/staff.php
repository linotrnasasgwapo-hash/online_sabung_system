<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../config/db.php'; $db=getDB(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save_staff'])){$fn=clean($db,$_POST['full_name']??'');$em=clean($db,$_POST['email']??'');$rl=clean($db,$_POST['role']??'encoder');$pw=password_hash($_POST['password']??'Staff@1234',PASSWORD_BCRYPT);$stmt=$db->prepare("INSERT INTO admins(full_name,email,password,role)VALUES(?,?,?,?)");$stmt->bind_param('ssss',$fn,$em,$pw,$rl);$stmt->execute()?$msg='Staff added.':$msg='Error: email may already exist.';}
if(isset($_GET['toggle'])){$tid=(int)$_GET['toggle'];$db->query("UPDATE admins SET is_active=NOT is_active WHERE admin_id=$tid AND admin_id!={$_SESSION['admin_id']}");header('Location: staff.php');exit;}
$staff=$db->query("SELECT * FROM admins ORDER BY role,full_name");
$roles=['owner','manager','referee','cashier','encoder'];
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Staff — Saraet Cockpit Arena</title><link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Crimson+Pro:wght@400;600&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/admin.css"></head>
<body><?php include '../includes/sidebar.php';?>
<main class="main">
  <div class="page-hd"><h1>🔐 Staff Accounts</h1><button class="btn-primary btn-gold" onclick="document.getElementById('staffModal').classList.add('open')">+ Add Staff</button></div>
  <?php if($msg):?><div class="msg-success">✅ <?=htmlspecialchars($msg)?></div><?php endif;?>
  <div class="card"><div class="card-hd"><h3>Arena Staff</h3></div><div class="tbl-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead><tbody>
  <?php $rmap=['owner'=>'b-gold','manager'=>'b-green','referee'=>'b-amber','cashier'=>'b-meron','encoder'=>'b-gray']; while($s=$staff->fetch_assoc()):?>
  <tr><td style="font-family:'Cinzel',serif;font-weight:700;color:var(--gold)">#<?=$s['admin_id']?></td><td class="t-name"><?=htmlspecialchars($s['full_name'])?></td><td class="t-sub"><?=htmlspecialchars($s['email'])?></td><td><span class="badge <?=$rmap[$s['role']]??'b-gray'?>"><?=ucfirst($s['role'])?></span></td><td><?=$s['is_active']?'<span class="badge b-green">Active</span>':'<span class="badge b-red">Inactive</span>'?></td><td class="t-sub"><?=date('M j, Y',strtotime($s['created_at']))?></td><td><?php if($s['admin_id']!=$_SESSION['admin_id']):?><a href="staff.php?toggle=<?=$s['admin_id']?>" class="btn-xs bx-edit"><?=$s['is_active']?'Disable':'Enable'?></a><?php else:?><span class="t-sub">(you)</span><?php endif;?></td></tr>
  <?php endwhile;?>
  </tbody></table></div></div>
</main>
<div class="overlay" id="staffModal"><div class="modal"><h3>Add Staff Account</h3><form method="POST"><input type="hidden" name="save_staff" value="1"><div class="field"><label>Full Name *</label><input name="full_name" required></div><div class="field"><label>Email *</label><input type="email" name="email" required></div><div class="row2"><div class="field"><label>Role</label><select name="role"><?php foreach($roles as $r):?><option value="<?=$r?>"><?=ucfirst($r)?></option><?php endforeach;?></select></div><div class="field"><label>Password</label><input type="password" name="password" placeholder="Staff@1234"></div></div><div class="modal-btns"><button type="button" class="modal-cancel" onclick="document.getElementById('staffModal').classList.remove('open')">Cancel</button><button type="submit" class="modal-save">Create Account</button></div></form></div></div>
<script>document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));</script>
</body></html>
