<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Fetch user’s issued books
$sql = "SELECT ib.issue_id, b.title, ib.issue_date, ib.return_date, ib.status 
        FROM issued_books ib
        JOIN books b ON ib.book_id = b.book_id
        WHERE ib.user_id = ?
        ORDER BY ib.issue_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - ULMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .overlay {
            background-color: rgba(255,255,255,0.9);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .navbar {
            background: #1e3a8a;
        }
        .navbar a.navbar-brand {
            color: #fff;
            font-weight: bold;
        }
        .navbar .btn-logout {
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 12px;
        }
        .navbar .btn-logout:hover {
            background: #b91c1c;
        }
        .card {
            border: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .card-header {
            background: #2563eb;
            color: white;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            background: rgba(0,0,0,0.05);
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="overlay">
    <nav class="navbar navbar-dark px-3">
        <a class="navbar-brand" href="#">📚 ULMS User Dashboard</a>
        <div>
            <span class="text-white me-3">Welcome, <?= htmlspecialchars($user_name) ?> 👋</span>
            <a href="user_logout.php" class="btn btn-logout btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Your Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars($user_name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>📖 Your Issued Books</h5>
            </div>
            <div class="card-body">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book Title</th>
                                <th>Issue Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['issue_date']) ?></td>
                                    <td><?= htmlspecialchars($row['return_date'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'returned'): ?>
                                            <span class="badge bg-success">Returned</span>
                                        <?php elseif ($row['status'] === 'issued'): ?>
                                            <span class="badge bg-warning text-dark">Issued</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Overdue</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No books issued yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>📚 ULMS System &copy; 2025 | Designed by Konki & Ace 💻</p>
    </footer>
</div>
</body>
</html>
