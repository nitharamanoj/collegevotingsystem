<?php
if (session_status() === PHP_SESSION_NONE) {
     session_start();
}
include 'config.php';

// fetch admin info if not already available
if (isset($_SESSION['admin_id'])) {
    $admin_id = (int)$_SESSION['admin_id'];
    $admin_stmt = $conn->prepare("SELECT id, name, email FROM admins WHERE id=?");
    $admin_stmt->bind_param("i", $admin_id);
    $admin_stmt->execute();
    $admin = $admin_stmt->get_result()->fetch_assoc();
 $admin_stmt->close();
} else {
    header("Location: index.php");
    exit();
}

// detect current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<html>
  <style>
  body{
  margin:0;
  background:var(--bg);
  color:#111827;
  display:flex;
  font-size:14px;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

/* Sidebar */
.sidebar{
  width:260px;
  background:var(--card);
  border-right:1px solid var(--border);
  height:100vh;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
  position:sticky;
  top:0;
  padding:22px 16px;
}
.brand{
  display:flex;
  align-items:center;
  gap:14px;
  padding-bottom:6px;
}
.logo {
  width:42px; height:42px; border-radius:8px;
  background: linear-gradient(180deg,#4a6cf7,#6a85ff);
  display:flex; align-items:center; justify-content:center;
  box-shadow: 0 6px 18px rgba(241, 241, 241, 0.18);
}
.logo svg{ width:20px; height:20px; color:white; fill:none; stroke:white; stroke-width:1.5; }

.brand-title{ font-weight:700; font-size:18px; color:#111827; }

/* Menu */
.menu{ margin-top:18px; display:flex; flex-direction:column; gap:6px; padding-right:8px; }
.menu a{
  display:flex; align-items:center; gap:12px;
  padding:12px 12px; text-decoration:none; color:#333;
  border-radius:10px; margin:2px 6px;
  font-weight:600;
  transition: all .18s ease;
}
.menu a svg{ width:18px; height:18px; opacity:.95; }
.menu a.active{
  background: #eef5ff;
  color:var(--primary);
  box-shadow: inset 0 0 0 1px rgba(74,108,247,0.06);
}
.menu a:hover{ background:#fbfbff; transform:translateY(-1px); }

/* Sidebar footer */
.sidebar-footer{
  padding:12px 12px;
  color:var(--muted);
}
.logout{
  display:inline-flex; gap:8px; align-items:center; margin-top:8px; color:#d9534f; text-decoration:none; font-weight:700;
}
.logout svg{ width:16px;height:16px }
</style>
<body>
<!-- Sidebar -->
<div class="sidebar">
  <div>
    <div class="brand">
     <div class="logo"><img src="logo.png" height="50" width="50">
         <!-- ballot envelope icon -->
         <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
           <rect x="3" y="5" width="18" height="14" rx="3" stroke="none" fill="rgba(255,255,255,0.06)"></rect>
           <path d="M7 9l3 2 5-4" stroke="#fff" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
         </svg>
      </div>
      <div class="brand-title">Voting system</div>
    </div>

    <nav class="menu" aria-label="Main Navigation">
      <a href="admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">
  <!-- dashboard icon -->
  Dashboard
</a>

<a href="admin_elections.php" class="<?= $current_page == 'admin_elections.php' ? 'active' : '' ?>">
  Elections
</a>

<a href="admin_candidates.php" class="<?= $current_page == 'admin_candidates.php' ? 'active' : '' ?>">
  Candidates
</a>

<a href="admin_analytics.php" class="<?= $current_page == 'admin_analytics.php' ? 'active' : '' ?>">
  Analytics
</a>

<a href="admin_settings.php" class="<?= $current_page == 'admin_settings.php' ? 'active' : '' ?>">
  Settings
</a>

    </nav>
  </div>

  <div class="sidebar-footer">
    <div style="font-weight:700;color:#111827"><?= htmlspecialchars($admin['name']); ?></div>
    <div style="font-size:12px;color:var(--muted); margin-top:4px;"><?= htmlspecialchars($admin['email']); ?></div>
    <a class="logout" href="logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M16 17l5-5-5-5" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12H9" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M13 5v-1a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h5a2 2 0 0 0 2-2v-1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Logout
    </a>
  </div>
</div>
</body>
</html>
