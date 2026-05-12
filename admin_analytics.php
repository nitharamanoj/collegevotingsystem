<?php
session_start();
include 'config.php';

// must be logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$election_id = isset($_GET['election_id']) ? (int)$_GET['election_id'] : 0;
$elections = $conn->query("SELECT election_id, title FROM elections");

// fetch analytics grouped by position
$analytics = [];
if ($election_id > 0) {
    $result = $conn->query("
        SELECT c.candidate_id, c.name, pos.position_name, COUNT(v.id) AS total_votes
        FROM candidates c
        LEFT JOIN votes v ON c.candidate_id = v.candidate_id
        LEFT JOIN positions pos ON c.position_id = pos.position_id
        WHERE c.election_id = $election_id
        GROUP BY c.candidate_id, c.name, pos.position_name
        ORDER BY pos.position_name, total_votes DESC
    ");
    while ($row = $result->fetch_assoc()) {
        $analytics[$row['position_name']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Election Analytics - VoteEase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* General page layout */
    :root {
        --primary: #4a6cf7;
        --secondary: #6c757d;
        --success: #28a745;
        --danger: #dc3545;
        --bg-body: #f5f6fa;
        --card-bg: #ffffff;
        --border-color: #e5e7eb;
        --text-color: #111827;
        --text-muted: #6b7280;
    }

    body {
        margin: 0;
        background-color: var(--bg-body);
        font-family: "Inter", sans-serif;
        font-size: 14px;
        color: var(--text-color);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        display: flex;
    }
    
    /* Main content area */
    .main-content {
        flex-grow: 1;
        padding: 3rem 2rem;
    }
    
    .main-content h2 {
        font-weight: 700;
        margin-bottom: 2rem;
        color: var(--text-color);
    }

    /* Card styling */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        background-color: var(--card-bg);
        padding: 2rem;
        margin-bottom: 1.5rem;
    }
    
    /* Form and table styling */
    .form-control, .form-select {
        border-radius: 0.5rem;
        border-color: var(--border-color);
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.25);
        border-color: var(--primary);
    }
    .table {
        border-radius: 1rem;
        overflow: hidden;
    }
    .table thead th {
        font-weight: 600;
        color: var(--text-muted);
        background-color: #f8fafc;
    }
    .table tbody tr {
        transition: background-color 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: #f1f5f9;
    }

    /* Button styling */
    .btn-primary {
        background-color: var(--primary);
        border: none;
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        background-color: #3b59d8;
        transform: translateY(-1px);
    }

  </style>
</head>
<body>

<?php 
include 'sidebar.php'; 
?>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Election Analytics</h2>
    </div>

    <form method="GET" action="">
        <div class="card">
            <div class="d-flex align-items-center gap-3">
                <label for="election_id" class="form-label fw-bold mb-0">Select Election:</label>
                <select name="election_id" id="election_id" onchange="this.form.submit()" class="form-select w-auto">
                    <option value="">-- Choose --</option>
                    <?php while ($row = $elections->fetch_assoc()) { ?>
                        <option value="<?= $row['election_id'] ?>" <?= ($election_id == $row['election_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['title']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($election_id > 0 && !empty($analytics)) { ?>
        <?php foreach ($analytics as $position => $candidates) { ?>
            <div class="card">
                <h3 class="fw-bold mb-3"><?= htmlspecialchars($position) ?> Results</h3>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Candidate</th>
                                <th>Total Votes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $row) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= $row['total_votes'] ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="chart-container">
                    <canvas id="chart_<?= md5($position) ?>"></canvas>
                </div>

                <script>
                const ctx_<?= md5($position) ?> = document.getElementById('chart_<?= md5($position) ?>').getContext('2d');
                new Chart(ctx_<?= md5($position) ?>, {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode(array_column($candidates, 'name')) ?>,
                        datasets: [{
                            label: 'Votes',
                            data: <?= json_encode(array_column($candidates, 'total_votes')) ?>,
                            backgroundColor: '#4a6cf7',
                            borderColor: '#3b59d8',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: '<?= addslashes($position) ?>'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
                </script>
            </div>
        <?php } ?>
    <?php } elseif ($election_id > 0) { ?>
        <p class="text-center text-muted mt-5">No votes have been cast for this election yet.</p>
    <?php } ?>
</div>

</body>
</html>
