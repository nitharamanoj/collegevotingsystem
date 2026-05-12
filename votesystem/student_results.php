<?php
session_start();
include 'config.php';
include 'update_election_status.php';


// ✅ Must be logged in as a student
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$election_id = isset($_GET['election_id']) ? (int)$_GET['election_id'] : 0;
$results = [];
$election_title = "Election Results";
$can_view_results = false;

if ($election_id > 0) {
    // Get election title and status
    $stmt = $conn->prepare("SELECT title, status FROM elections WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $election_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($election_data) {
        $election_title = $election_data['title'];
        // ✅ Only allow viewing if the election is 'Completed'
        if ($election_data['status'] === 'Completed') {
            $can_view_results = true;

            // Fetch and count votes grouped by position
            $sql = "
                SELECT 
                    c.name AS candidate_name, 
                    c.image AS candidate_image,
                    p.position_name,
                    COUNT(v.id) AS total_votes
                FROM candidates c
                LEFT JOIN votes v ON c.candidate_id = v.candidate_id
                LEFT JOIN positions p ON c.position_id = p.position_id
                WHERE c.election_id = ?
                GROUP BY c.candidate_id, p.position_name
                ORDER BY p.position_name, total_votes DESC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $election_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $results[$row['position_name']][] = $row;
                }
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($election_title) ?> Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family:"Inter",sans-serif; background:#f5f7fb; margin:0; }
        .container { max-width: 900px; }
        .card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.05); margin-bottom:20px; }
        .btn-primary { background:#3b6ef6; border-color:#3b6ef6; }
        .btn-secondary { background:#6c757d; border-color:#6c757d; }
        .winner-card { background-color: #e6edff; border: 2px solid #3b6ef6; padding: 1.5rem; border-radius: 12px; text-align: center; margin-bottom: 2rem; position: relative; }
        .winner-card h4 { font-weight: 700; color: #1a3fa0; }
        .winner-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .winner-name { font-size: 1.5rem; font-weight: 800; color: #111827; margin-top: 1rem; }
        .winner-votes { font-size: 1rem; color: #6c757d; }
        .list-group-item { display: flex; justify-content: space-between; align-items: center; border: none; padding: 1rem 1.25rem; border-bottom: 1px solid #e9ecef; }
        .list-group-item:last-child { border-bottom: none; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?= htmlspecialchars($election_title) ?></h2>
        <a href="student_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <?php if ($election_id === 0): ?>
        <div class="card p-4 text-center">
            <h5 class="text-muted">Please select an election from your dashboard to view results.</h5>
        </div>
    <?php elseif (!$can_view_results): ?>
        <div class="card p-4 text-center">
            <i class="bi bi-hourglass-split" style="font-size: 3rem; color: #6c757d;"></i>
            <h5 class="text-muted mt-3">Results for this election are not yet available.</h5>
            <p>Please check back after the election has concluded.</p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="card p-4 text-center">
            <h5 class="text-muted">No votes were cast in this election.</h5>
        </div>
    <?php else: ?>
        <?php foreach ($results as $position_name => $candidates): ?>
            <div class="card mb-4">
                <h4 class="fw-bold mb-3"><?= htmlspecialchars($position_name) ?></h4>
                <?php if (!empty($candidates)): 
                    $winner = $candidates[0]; ?>
                    <div class="winner-card">
                        <img src="<?= htmlspecialchars($winner['candidate_image'] ?? 'placeholder.png') ?>" alt="<?= htmlspecialchars($winner['candidate_name']) ?>" class="winner-img">
                        <div class="winner-name"><?= htmlspecialchars($winner['candidate_name']) ?></div>
                        <div class="winner-votes"><strong>Winner</strong> with <?= $winner['total_votes'] ?> Votes</div>
                    </div>
                
                    <?php if (count($candidates) > 1): ?>
                        <h5 class="fw-bold mt-4 mb-3">Other Candidates</h5>
                        <ul class="list-group list-group-flush">
                            <?php for ($i = 1; $i < count($candidates); $i++): 
                                $candidate = $candidates[$i]; ?>
                                <li class="list-group-item">
                                    <span class="fw-bold"><?= htmlspecialchars($candidate['candidate_name']) ?></span>
                                    <span class="text-primary fw-bold"><?= $candidate['total_votes'] ?> Votes</span>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
