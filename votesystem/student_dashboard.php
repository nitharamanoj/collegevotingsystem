<?php
session_start();
include 'config.php';
// Automatically update election statuses every time a student visits their dashboard.
include 'update_election_status.php';

// ✅ Allow only logged in students
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Student info
$stmt = $conn->prepare("SELECT * FROM regestration WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all elections
$elections_sql = "SELECT * FROM elections ORDER BY start_date DESC, start_time DESC";
$elections_result = $conn->query($elections_sql);

// Stats
$total_voters = $conn->query("SELECT COUNT(*) AS total FROM regestration")->fetch_assoc()['total'];
$votes_cast   = $conn->query("SELECT COUNT(*) AS total FROM votes")->fetch_assoc()['total'];
$positions    = $conn->query("SELECT COUNT(*) AS total FROM positions")->fetch_assoc()['total'];
$candidates   = $conn->query("SELECT COUNT(*) AS total FROM candidates")->fetch_assoc()['total'];

// Find the first available completed election for the "Quick Action" button
$completed_election_for_quick_action = $conn->query("SELECT election_id FROM elections WHERE status = 'Completed' ORDER BY end_date DESC, end_time DESC LIMIT 1")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Portal - College Voting System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { font-family:"Segoe UI",sans-serif; background:#f5f7fb; margin:0; }
    header { background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.05); padding:15px 30px; display:flex; justify-content:space-between; align-items:center; }
    header .logo { font-weight:bold; color:#1a3fa0; font-size:1.2rem; }
    nav a { margin-left:20px; text-decoration:none; color:#333; font-weight:500; }
    nav a.login { background:#1a3fa0; color:#fff; padding:8px 16px; border-radius:6px; }
    .container { max-width:1000px; margin:30px auto; padding:0 20px; }
    .card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); margin-bottom:20px; }
    .welcome { font-size:1.2rem; font-weight:bold; }
    .subtext { font-size:0.9rem; color:#666; }
    .election-details { display:flex; justify-content:space-between; align-items:center; }
    .btn { background:#3b6ef6; color:white; padding:10px 18px; border-radius:6px; text-decoration:none; font-weight:bold; }
    .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; text-align:center; }
    .stats .item { background:#f9fafc; padding:20px; border-radius:12px; }
    .stats .number { font-size:1.5rem; font-weight:bold; }
    .stats .label { font-size:0.9rem; color:#555; }
    .quick-actions { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
    .quick-actions a { display:block; text-align:center; padding:20px; border-radius:12px; text-decoration:none; font-weight:bold; }
    .quick-actions .vote { background:#e6edff; color:#1a3fa0; }
    .quick-actions .results { background:#e7f8ed; color:#218838; }
    .quick-actions .profile { background:#e9ecef; color:#333; }
    .disabled { pointer-events: none; opacity: 0.6; background-color: #e9ecef !important; color: #6c757d !important; }
    .status-badge { padding: .35em .65em; font-size: .75em; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }

    /* --- Loading Overlay --- */
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(245, 247, 251, 0.8); /* Match body bg */
        backdrop-filter: blur(5px);
        display: none; /* Hidden by default */
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
        color: #1a3fa0; /* Match theme */
        font-size: 1.1rem;
        font-weight: 500;
        text-align: center;
    }

    .loader {
        border: 8px solid #e6edff; /* Light blue */
        border-top: 8px solid #1a3fa0; /* Dark blue */
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    /* --- End Loading Overlay --- */
</style>
</head>
<body>

<!-- ==== LOADING OVERLAY ==== -->
<div id="loading-overlay">
    <div class="loader"></div>
    <p>Generating secure OTP... <br> Please wait while we send your email.</p>
</div>
<!-- ======================= -->

    <header>
        <div class="logo">College Vote <span style="font-weight:normal;">| Online Voting System</span></div>
        <nav>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="logout.php" class="login">Logout</a>
        </nav>
    </header>

    <div class="container">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div class="welcome">Welcome, <?= htmlspecialchars($student['name']); ?>!</div>
                    <div class="subtext"><?= htmlspecialchars($student['department']); ?> - Year <?= htmlspecialchars($student['yearr']); ?> | <?= htmlspecialchars($student['email']); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Election Statistics</h3>
            <div class="stats">
                <div class="item"><div class="number" style="color:#3b6ef6;"><?= $total_voters; ?></div><div class="label">Total Voters</div></div>
                <div class="item"><div class="number" style="color:#28a745;"><?= $votes_cast; ?></div><div class="label">Votes Cast</div></div>
                <div class="item"><div class="number" style="color:#6f42c1;"><?= $positions; ?></div><div class="label">Positions</div></div>
                <div class="item"><div class="number" style="color:#fd7e14;"><?= $candidates; ?></div><div class="label">Candidates</div></div>
            </div>
        </div>

        
        
        <h3 class="fw-bold mb-4">Available Elections</h3>
        <?php
        if ($elections_result->num_rows > 0):
            $elections_result->data_seek(0); // Reset pointer
            while ($election = $elections_result->fetch_assoc()):
                // Check if the student has already voted in this election
                $voted_check_stmt = $conn->prepare("SELECT COUNT(*) AS c FROM votes WHERE student_id = ? AND election_id = ?");
                $voted_check_stmt->bind_param("ii", $student_id, $election['election_id']);
                $voted_check_stmt->execute();
                $is_voted = $voted_check_stmt->get_result()->fetch_assoc()['c'] > 0;
                $voted_check_stmt->close();
            ?>
                <div class="card mb-3">
                    <div class="election-details">
                        <div>
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($election['title']); ?></h5>
                            <small class="text-muted">
                                From: <?= date('M d, Y h:i A', strtotime($election['start_date'] . ' ' . $election['start_time'])) ?><br>
                                To: <?= date('M d, Y h:i A', strtotime($election['end_date'] . ' ' . $election['end_time'])) ?>
                            </small>
                        </div>
                        <div class="text-end d-flex flex-column align-items-end">
                            <?php if ($election['status'] == 'Ongoing'): ?>
                                <?php if ($is_voted): ?>
                                    <span class="status-badge bg-secondary text-white mb-2">Voted</span>
                                    <button class="btn btn-secondary btn-sm disabled">Vote Cast</button>
                                <?php else: ?>
                                    <span class="status-badge bg-success text-white mb-2">Ongoing</span>
                                    <!-- Added 'vote-now-btn' class here -->
                                    <a href="vote.php?election_id=<?= $election['election_id']; ?>" class="btn btn-success btn-sm vote-now-btn">Vote Now</a>
                                <?php endif; ?>
                            <?php elseif ($election['status'] == 'Upcoming'): ?>
                                <span class="status-badge bg-info text-dark mb-2">Upcoming</span>
                                <button class="btn btn-info btn-sm disabled">Not Started</button>
                            <?php elseif ($election['status'] == 'Completed'): ?>
                                <span class="status-badge bg-secondary text-white mb-2">Completed</span>
                                <a href="student_results.php?election_id=<?= $election['election_id']; ?>" class="btn btn-primary btn-sm">View Results</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card p-4 text-center text-muted">No elections are scheduled at this time.</div>
        <?php endif; ?>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find all "Vote Now" buttons
    const voteButtons = document.querySelectorAll('.vote-now-btn');
    const loadingOverlay = document.getElementById('loading-overlay');

    // Add click listener to each button
    voteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Show the loading overlay when a vote button is clicked
            loadingOverlay.style.display = 'flex';
        });
    });
});
</script>

</body>
</html>