<?php
session_start();
include 'config.php';
// Automatically update election statuses every time the admin visits the dashboard.
include 'update_election_status.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php"); exit();
}

$admin_id = (int)$_SESSION['admin_id'];
$admin_stmt = $conn->prepare("SELECT id, name, email FROM admins WHERE id=?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();
$admin_stmt->close();

$total_elections  = ($conn->query("SELECT COUNT(*) AS c FROM elections")->fetch_assoc()['c'] ?? 0);
$active_elections = ($conn->query("SELECT COUNT(*) AS c FROM elections WHERE status='Ongoing'")->fetch_assoc()['c'] ?? 0);
$total_candidates = ($conn->query("SELECT COUNT(*) AS c FROM candidates")->fetch_assoc()['c'] ?? 0);
$total_votes      = ($conn->query("SELECT COUNT(*) AS c FROM votes")->fetch_assoc()['c'] ?? 0);
$total_students   = ($conn->query("SELECT COUNT(*) AS c FROM regestration")->fetch_assoc()['c'] ?? 0);
$avg_turnout = $total_students > 0 ? round(($total_votes / $total_students) * 100, 1) : 0;

$recent_elections = $conn->query("SELECT election_id, title, description, start_date, end_date, status FROM elections ORDER BY start_date DESC LIMIT 1");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard - VoteEase</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
  --bg:#f6f7fb;
  --card:#ffffff;
  --muted:#73777f;
  --border:#e9edf1;
  --primary:#4a6cf7;
  --blue-100: #eef3ff;
  --green-100:#ecfbef;
  --purple-100:#f6eefb;
  --amber-100:#fff6ea;
  --orange-100:#fff3ea;
  --success:#27ae60;
  --shadow: 0 6px 18px rgba(17,24,39,0.06);
  font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
}
*{box-sizing:border-box}
html,body{height:100%}
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
  box-shadow: 0 6px 18px rgba(74,108,247,0.18);
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

/* Content area */
.content{ flex:1; padding:28px 36px; overflow:auto; }

/* Top stats boxes row */
.stats-row{
  display:flex; gap:18px; align-items:stretch; margin-bottom:22px; flex-wrap:wrap;
}

/* Individual stat card */
.stat-card{
  background:var(--card);
  border-radius:12px;
  padding:16px 18px;
  width:220px;
  border:1px solid var(--border);
  box-shadow:var(--shadow);
  display:flex; align-items:center; justify-content:space-between;
}
.stat-left{ display:flex; flex-direction:column; gap:6px; }
.stat-left .title{ font-size:13px; color:var(--muted); font-weight:600; }
.stat-left .num{ font-size:26px; font-weight:800; color:#111827; }

/* icon container on right of stat card */
.stat-icon{
  width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center;
  border:1px solid transparent;
}

/* color variants */
.stat-blue{ background:linear-gradient(180deg,#f1f5ff,#fff); }
.stat-blue .stat-icon{ background:var(--blue-100); border-color: rgba(74,108,247,0.12); }
.stat-green{ background:linear-gradient(180deg,#f8fff6,#fff); }
.stat-green .stat-icon{ background:var(--green-100); border-color: rgba(34,197,94,0.08); }
.stat-purple{ background:linear-gradient(180deg,#fbf6ff,#fff); }
.stat-purple .stat-icon{ background:var(--purple-100); border-color: rgba(156,39,176,0.08); }
.stat-amber{ background:linear-gradient(180deg,#fffaf4,#fff); }
.stat-amber .stat-icon{ background:var(--amber-100); border-color: rgba(212,161,55,0.08); }
.stat-orange{ background:linear-gradient(180deg,#fff8f2,#fff); }
.stat-orange .stat-icon{ background:var(--orange-100); border-color: rgba(253,126,20,0.08); }

/* sections */
.section{ margin-top:18px; background:transparent; }
.section .heading{
  font-size:18px; font-weight:800; margin:2px 0 14px 0;
}

/* Recent elections container (outer) */
.recent-wrap{
  background:var(--card);
  border-radius:12px;
  border:1px solid var(--border);
  padding:18px;
  box-shadow:var(--shadow);
  margin-bottom:22px;
}

/* inner election card (pale) */
.recent-inner{
  background:#fbfdff;
  border-radius:10px; padding:18px;
  border:1px solid rgba(233,237,241,0.7);
  display:flex; align-items:center; justify-content:space-between;
}
.recent-left{ max-width:78%; }
.recent-left h3{ margin:0; font-size:16px; font-weight:800; color:#111827; }
.recent-left p{ margin:8px 0 10px 0; color:var(--muted); line-height:1.45; font-size:13px; }
.recent-meta{ color: #8b94a0; font-size:12px; }

/* pill */
.pill{
  display:inline-block; padding:6px 12px; border-radius:999px; font-weight:700; font-size:13px;
}
.pill.green{ background:#e9f7ee; color: #23843a; border:1px solid rgba(34,197,94,0.08); }
.pill.orange{ background:#fff3e0; color: #e65100; border:1px solid rgba(251,140,0,0.08); }
.pill.grey{ background:#f1f3f5; color: #5f6368; border:1px solid rgba(154,160,166,0.08); }


/* Quick actions container */
.actions-wrap{
  background:var(--card);
  border-radius:12px; border:1px solid var(--border);
  padding:20px; box-shadow:var(--shadow);
}
.actions-grid{
  display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-top:8px;
}

/* each action (pastel box) */
.action{
  background: #fff;
  border-radius:10px;
  padding:18px;
  min-height:110px;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  border:1px solid rgba(226,230,235,0.7);
  text-align:center;
  cursor:pointer;
  transition: transform .12s ease, box-shadow .12s ease;
}
.action:hover{ transform:translateY(-6px) }
.action .act-icon{ width:54px; height:54px; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:12px; border:1px solid rgba(0,0,0,0.03); }
.action .act-title{ font-weight:700; margin-bottom:6px; }
.action .act-sub{ font-size:12px; color:var(--muted) }

/* pastel variants */
.action.blue .act-icon{ background:#eef4ff; border-color: rgba(74,108,247,0.12); color:var(--primary) }
.action.green .act-icon{ background:#f2fbf4; border-color: rgba(34,197,94,0.08); color:#3aa34a }
.action.purple .act-icon{ background:#fbf6ff; border-color: rgba(156,39,176,0.08); color:#8e44d6 }
.action.orange .act-icon{ background:#fff6ef; border-color: rgba(253,126,20,0.06); color:#d97706 }

/* small 'made in bolt' badge bottom right like screenshot */
.badge {
  position:fixed; right:18px; bottom:18px;
  background:#fff; border-radius:10px; padding:8px 10px; border:1px solid var(--border);
  box-shadow: 0 6px 18px rgba(17,24,39,0.06); font-weight:700; font-size:12px;
}

/* responsive */
@media (max-width:1080px){
  .actions-grid{ grid-template-columns:repeat(2,1fr) }
  .stats-row{ gap:12px; }
}
@media (max-width:720px){
  .sidebar{ display:none }
  .content{ padding:18px }
  .actions-grid{ grid-template-columns:1fr }
  .stat-card{ width:100% }
}
</style>
</head>
<body>


<?php include 'sidebar.php'; ?>




<div class="content">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:18px;">
    <div>
      <h1 style="margin:0; font-size:22px; font-weight:800;">Dashboard</h1>
      <div style="color:var(--muted); margin-top:8px;">Welcome back, <?= htmlspecialchars($admin['name']); ?> </div>
    </div>
  </div>

  <!-- stats row -->
  <div class="stats-row" style="margin-top:18px;">
    <div class="stat-card stat-blue">
      <div class="stat-left">
        <div class="title">Total Elections</div>
        <div class="num"><?= $total_elections ?></div>
      </div>
      <div class="stat-icon">
        <!-- calendar icon -->
        <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#4a6cf7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/></svg>
      </div>
    </div>

    <div class="stat-card stat-green">
      <div class="stat-left">
        <div class="title">Active Elections</div>
        <div class="num"><?= $active_elections ?></div>
      </div>
      <div class="stat-icon">
        <!-- check square -->
        <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#2ea44f" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
    </div>

    <div class="stat-card stat-purple">
      <div class="stat-left">
        <div class="title">Total Candidates</div>
        <div class="num"><?= $total_candidates ?></div>
      </div>
      <div class="stat-icon">
        <!-- users icon -->
        <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#8a47e6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2"/><circle cx="9" cy="7" r="4"/><path d="M20 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
      </div>
    </div>

    <div class="stat-card stat-amber">
      <div class="stat-left">
        <div class="title">Total Votes</div>
        <div class="num"><?= $total_votes ?></div>
      </div>
      <div class="stat-icon">
        <!-- trophy -->
        <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#c79a2e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8M12 17v4M7 3h10v4a5 5 0 0 1-10 0V3z"/></svg>
      </div>
    </div>

    <div class="stat-card stat-orange">
      <div class="stat-left">
        <div class="title">Avg Turnout</div>
        <div class="num"><?= $avg_turnout ?>%</div>
      </div>
      <div class="stat-icon">
        <!-- trend up -->
        <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ef7a3a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/></svg>
      </div>
    </div>
  </div>

  <!-- Recent Elections -->
  <div class="section">
    <div class="heading">Recent Elections</div>
    <div class="recent-wrap">
      <?php if ($recent_elections && $recent_elections->num_rows): ?>
        <?php while ($row = $recent_elections->fetch_assoc()): 
            $eid = (int)$row['election_id'];
            // get positions and votes counts for that election if tables exist
            $pos_count = $conn->query("SELECT COUNT(DISTINCT position_id) AS c FROM candidates WHERE election_id = $eid")->fetch_assoc()['c'] ?? 0;
            $votes_count = $conn->query("SELECT COUNT(*) AS c FROM votes WHERE election_id = $eid")->fetch_assoc()['c'] ?? 0;
            $start = !empty($row['start_date']) ? date('n/j/Y', strtotime($row['start_date'])) : '';
            $end = !empty($row['end_date']) ? date('n/j/Y', strtotime($row['end_date'])) : '';
        ?>
          <div class="recent-inner">
            <div class="recent-left">
              <h3><?= htmlspecialchars($row['title']); ?></h3>
              <p><?= htmlspecialchars($row['description'] ?? 'No description available.'); ?></p>
              <div class="recent-meta">Positions: <?= (int)$pos_count ?> &nbsp; &nbsp; Votes: <?= (int)$votes_count ?> &nbsp; &nbsp; <?= $start ?> - <?= $end ?></div>
            </div>
            <div>
              <?php 
                $status_class = 'grey'; // Default
                if ($row['status'] === 'Ongoing') {
                    $status_class = 'green';
                } elseif ($row['status'] === 'Upcoming') {
                    $status_class = 'orange';
                }
              ?>
                <div class="pill <?= $status_class ?>"><?= htmlspecialchars($row['status']) ?></div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="recent-inner"><div style="color:var(--muted)">No elections found.</div></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="section">
    <div class="heading">Quick Actions</div>
    <div class="actions-wrap">
      <div class="actions-grid">
        <div class="action blue" onclick="location.href='add_election.php'">
          <div class="act-icon">
            <!-- create icon -->
            <svg viewBox="0 0 24 24" width="26" height="26" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M8 11h8M12 7v8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="act-title">Create Election</div>
          <div class="act-sub">Start a new voting process</div>
        </div>

        <div class="action green" onclick="location.href='add_candidate.php'">
          <div class="act-icon">
            <!-- people -->
            <svg viewBox="0 0 24 24" width="26" height="26" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 11c1.657 0 3-1.567 3-3.5S17.657 4 16 4s-3 1.567-3 3.5S14.343 11 16 11zM8 11c1.657 0 3-1.567 3-3.5S9.657 4 8 4 5 5.567 5 7.5 6.343 11 8 11zM2 20c0-2.5 4-4.5 8-4.5s8 2 8 4.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="act-title">Add Candidates</div>
          <div class="act-sub">Register new candidates</div>
        </div>

        <div class="action purple" onclick="location.href='admin_analytics.php'">
          <div class="act-icon">
            <!-- analytics -->
            <svg viewBox="0 0 24 24" width="26" height="26" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 3v18h18" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 13l3-3 4 6 3-8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="act-title">View Results</div>
          <div class="act-sub">Election insights & reports</div>
        </div>
        
        <div class="action orange" onclick="location.href='admin_students.php'">
          <div class="act-icon">
            <!-- student icon -->
            <svg viewBox="0 0 24 24" width="26" height="26" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
            </svg>
          </div>
          <div class="act-title">Manage Students</div>
          <div class="act-sub">Add or edit student accounts</div>
        </div>

      </div>
    </div>
  </div>
</div>
</body>
</html>
