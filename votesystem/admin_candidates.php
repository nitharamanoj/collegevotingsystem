<?php
session_start();
include 'config.php';

// 🔐 Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch candidates with position and election, ordered for grouping
// The custom order is specified using the SQL FIELD() function
$sql = "
    SELECT c.candidate_id, c.name, c.department, c.year,
           p.position_name, e.title AS election_title
    FROM candidates c
    LEFT JOIN positions p ON c.position_id = p.position_id
    LEFT JOIN elections e ON c.election_id = e.election_id
    ORDER BY FIELD(p.position_name, 
        'Chairman/Chairperson',
        'Vice Chairperson (FE)',
        'General Secretary',
        'UUC - University Union Councillor',
        'Arts Club Secretary',
        'Magazine Editor',
        'Lady Representative',
        '2nd PG Rep',
        '1st PG Rep',
        '3rd UG Rep',
        '2nd UG Rep',
        '1st UG Rep'
    ), c.name
";
$result = $conn->query($sql);

$candidates_by_position = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $position_name = $row['position_name'] ?? 'Unassigned Position';
        $candidates_by_position[$position_name][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Candidates - VoteEase</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .btn-outline-primary, .btn-outline-danger {
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            color: var(--card-bg);
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
        }
        .btn-outline-danger:hover {
            color: var(--card-bg);
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        /* Table styling */
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
        
        /* Badge styling */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Alert styling */
        .alert {
            border: none;
            border-radius: 0.75rem;
            font-weight: 500;
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
        <h2 class="fw-bold">Manage Candidates</h2>
        <a href="add_candidate.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Candidate</a>
    </div>

    <?php if (empty($candidates_by_position)): ?>
        <div class="card p-4">
            <p class="text-center text-muted m-0">No candidates found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($candidates_by_position as $position_name => $candidates): ?>
            <div class="mb-5">
                <h3 class="mb-3 fw-bold"><?= htmlspecialchars($position_name) ?></h3>
                <div class="card p-4">
                    <table class="table align-middle m-0">
                        <thead>
                            <tr class="table-light">
                                <th>ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Election</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td><?= htmlspecialchars($candidate['candidate_id']) ?></td>
                                    <td><?= htmlspecialchars($candidate['name']) ?></td>
                                    <td><?= htmlspecialchars($candidate['department']) ?></td>
                                    <td><?= htmlspecialchars($candidate['year']) ?></td>
                                    <td><?= htmlspecialchars($candidate['election_title'] ?? 'N/A') ?></td>
                                    <td class="text-end">
                                        <a href="edit_candidate.php?id=<?= $candidate['candidate_id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete_candidate.php?id=<?= $candidate['candidate_id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this candidate?')"
                                           class="btn btn-sm btn-outline-danger">
                                           <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
