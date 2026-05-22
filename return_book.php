<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect if admin not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle return action
if (isset($_POST['return_id'])) {
    $issue_id = intval($_POST['return_id']);

    // Get book info
    $get_issue = $conn->query("SELECT book_id FROM issued_books WHERE issue_id = $issue_id");
    if ($get_issue && $get_issue->num_rows > 0) {
        $issue = $get_issue->fetch_assoc();
        $book_id = $issue['book_id'];

        // Update return_date to today
        $conn->query("UPDATE issued_books SET return_date = CURDATE() WHERE issue_id = $issue_id");

        // Increase book quantity
        $conn->query("UPDATE books SET quantity = quantity + 1 WHERE book_id = $book_id");

        $message = "✅ Book returned successfully!";
    } else {
        $message = "❌ Invalid issue record.";
    }
}

// Fetch all issued books that are not yet returned
$sql = "
SELECT 
    ib.issue_id,
    u.name AS user_name,
    b.title AS book_title,
    ib.issue_date
FROM issued_books ib
JOIN users u ON ib.user_id = u.user_id
JOIN books b ON ib.book_id = b.book_id
WHERE ib.return_date IS NULL
ORDER BY ib.issue_date DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Book - ULMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="admin_dashboard.php">⬅️ Back to Dashboard</a>
    <div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4">🔄 Return Issued Books</h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= htmlspecialchars($row['book_title']) ?></td>
                            <td><?= htmlspecialchars($row['issue_date']) ?></td>
                            <td>
                                <form method="POST" action="return_book.php" style="display:inline;">
                                    <input type="hidden" name="return_id" value="<?= $row['issue_id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Return</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No pending returns.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
