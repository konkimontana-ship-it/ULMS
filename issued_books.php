<?php
session_start();
include __DIR__ . '/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Helper: get column list for a table
function get_columns($conn, $table) {
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $cols[] = $r['Field'];
        }
    }
    return $cols;
}

// Tables
$tblIssued = 'issued_books';
$tblUsers  = 'users';
$tblBooks  = 'books';

// Get columns for each table
$issuedCols = get_columns($conn, $tblIssued);
$usersCols  = get_columns($conn, $tblUsers);
$booksCols  = get_columns($conn, $tblBooks);

// Candidate names (common variations)
$issueIdCandidates    = ['id','issue_id','issued_id','s_no'];
$issueUserIdCandidates= ['user_id','student_id','uid'];
$issueBookIdCandidates= ['book_id','book_no','b_id'];
$issueDateCandidates  = ['issue_date','issued_on','created_at','date_issued'];
$returnDateCandidates = ['return_date','due_date','return_on'];
$statusCandidates     = ['status','state'];

$userIdCandidates     = ['id','user_id','userId'];
$userNameCandidates   = ['full_name','name','username','user_name','email'];

$bookIdCandidates     = ['id','book_id','bookId'];
$bookTitleCandidates  = ['title','book_name','name'];

// Detection function
function detect($cols, $candidates) {
    foreach ($candidates as $c) {
        if (in_array($c, $cols)) return $c;
    }
    return null;
}

// Detect columns
$issued_id_col   = detect($issuedCols, $issueIdCandidates);
$issued_user_col = detect($issuedCols, $issueUserIdCandidates);
$issued_book_col = detect($issuedCols, $issueBookIdCandidates);
$issued_issue_date_col = detect($issuedCols, $issueDateCandidates);
$issued_return_date_col = detect($issuedCols, $returnDateCandidates);
$issued_status_col = detect($issuedCols, $statusCandidates);

$user_id_col = detect($usersCols, $userIdCandidates);
$user_name_col = detect($usersCols, $userNameCandidates);

$book_id_col = detect($booksCols, $bookIdCandidates);
$book_title_col = detect($booksCols, $bookTitleCandidates);

// Validate we have the minimum mapping
$missing = [];
if (!$issued_id_col) $missing[] = 'issued id (issued_books)';
if (!$issued_user_col) $missing[] = 'issued user id (issued_books)';
if (!$issued_book_col) $missing[] = 'issued book id (issued_books)';
if (!$user_id_col) $missing[] = 'user id (users)';
if (!$user_name_col) $missing[] = 'user name (users)';
if (!$book_id_col) $missing[] = 'book id (books)';
if (!$book_title_col) $missing[] = 'book title (books)';

if (!empty($missing)) {
    // Show clear error and helpful info
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Issued Books - Error</title>";
    echo "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'>";
    echo "</head><body class='bg-light'><div class='container mt-5'>";
    echo "<div class='alert alert-danger'><strong>Column mapping failed.</strong> Could not detect these required columns:<ul>";
    foreach ($missing as $m) echo "<li>" . htmlspecialchars($m) . "</li>";
    echo "</ul></div>";
    echo "<div class='card'><div class='card-body'><h5>Detected columns</h5>";
    echo "<p><strong>issued_books:</strong> " . implode(', ', $issuedCols ?: ['<none>']) . "</p>";
    echo "<p><strong>users:</strong> " . implode(', ', $usersCols ?: ['<none>']) . "</p>";
    echo "<p><strong>books:</strong> " . implode(', ', $booksCols ?: ['<none>']) . "</p>";
    echo "<p class='text-muted'>If column names are different, either rename them in the DB or tell me the columns and I'll adjust the code.</p>";
    echo "<a class='btn btn-primary' href='admin_dashboard.php'>Back to Dashboard</a>";
    echo "</div></div></div></body></html>";
    exit();
}

// Build SELECT safely with detected names (use aliases)
$issueId   = "ib.`{$issued_id_col}` AS issue_id";
$userName  = "u.`{$user_name_col}` AS user_name";
$bookTitle = "b.`{$book_title_col}` AS book_title";

$issueDate = $issued_issue_date_col ? "ib.`{$issued_issue_date_col}` AS issue_date" : "NULL AS issue_date";
$returnDate= $issued_return_date_col  ? "ib.`{$issued_return_date_col}` AS return_date" : "NULL AS return_date";
$statusCol = $issued_status_col ? "ib.`{$issued_status_col}` AS status" : "NULL AS status";

// Build join ON parts
$joinUserOn = "ib.`{$issued_user_col}` = u.`{$user_id_col}`";
$joinBookOn = "ib.`{$issued_book_col}` = b.`{$book_id_col}`";

// Final query
$sql = "SELECT $issueId, $userName, $bookTitle, $issueDate, $returnDate, $statusCol
        FROM `{$tblIssued}` ib
        LEFT JOIN `{$tblUsers}` u ON $joinUserOn
        LEFT JOIN `{$tblBooks}` b ON $joinBookOn
        ORDER BY ib.`{$issued_id_col}` DESC";

$result = $conn->query($sql);

if ($result === false) {
    die("<!doctype html><html><head><meta charset='utf-8'><title>Query Error</title>"
        . "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'></head><body class='bg-light'><div class='container mt-5'>"
        . "<div class='alert alert-danger'><strong>SQL Error:</strong> " . htmlspecialchars($conn->error) . "</div>"
        . "<a class='btn btn-primary' href='admin_dashboard.php'>Back to Dashboard</a></div></body></html>");
}

// Fetch rows
$issuedBooks = [];
while ($r = $result->fetch_assoc()) {
    $issuedBooks[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issued Books - ULMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="admin_dashboard.php">ULMS Dashboard</a>
    <div>
        <a href="issue_book.php" class="btn btn-outline-light btn-sm">Issue Book</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-4">🗂️ Issued Books</h4>

            <?php if (count($issuedBooks) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Book</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($issuedBooks as $i => $ib): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($ib['user_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($ib['book_title'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($ib['issue_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($ib['return_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($ib['status'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No issued books found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
