<?php // includes/sidebar.php — included in all admin pages ?>
<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">🐓</div>
    <div>
      <h2>Saraet Cockpit</h2>
      <p>Arena Management</p>
    </div>
  </div>
  <nav class="sb-nav">
    <div class="sb-sec">Main</div>
    <a href="dashboard.php"  class="sb-item <?= basename($_SERVER['PHP_SELF'])==='dashboard.php'?'active':'' ?>"><span>🏠</span>Dashboard</a>
    <div class="sb-sec">Derbies</div>
    <a href="derbies.php"    class="sb-item <?= basename($_SERVER['PHP_SELF'])==='derbies.php'?'active':'' ?>"><span>🏆</span>Derby Events</a>
    <a href="entries.php"    class="sb-item <?= basename($_SERVER['PHP_SELF'])==='entries.php'?'active':'' ?>"><span>📋</span>Derby Entries</a>
    <a href="matches.php"    class="sb-item <?= basename($_SERVER['PHP_SELF'])==='matches.php'?'active':'' ?>"><span>⚔️</span>Matches / Fights</a>
    <div class="sb-sec">Registry</div>
    <a href="owners.php"     class="sb-item <?= basename($_SERVER['PHP_SELF'])==='owners.php'?'active':'' ?>"><span>👤</span>Owners / Handlers</a>
    <a href="roosters.php"   class="sb-item <?= basename($_SERVER['PHP_SELF'])==='roosters.php'?'active':'' ?>"><span>🐓</span>Roosters</a>
    <a href="breeds.php"     class="sb-item <?= basename($_SERVER['PHP_SELF'])==='breeds.php'?'active':'' ?>"><span>📖</span>Breeds Reference</a>
    <div class="sb-sec">System</div>
    <a href="reports.php"    class="sb-item <?= basename($_SERVER['PHP_SELF'])==='reports.php'?'active':'' ?>"><span>📊</span>Reports</a>
    <a href="staff.php"      class="sb-item <?= basename($_SERVER['PHP_SELF'])==='staff.php'?'active':'' ?>"><span>🔐</span>Staff Accounts</a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-av"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div>
      <div>
        <p><?= htmlspecialchars($_SESSION['admin_name']) ?></p>
        <span><?= ucfirst(str_replace('_',' ',$_SESSION['admin_role'])) ?></span>
      </div>
    </div>
    <a href="logout.php" class="sb-logout">Sign Out</a>
  </div>
</aside>
