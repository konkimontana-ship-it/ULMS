<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect if admin not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle book issue form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['issue_book'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $issue_date = date("Y-m-d");
    $return_date = $_POST['return_date'] ?? null;

    if ($user_id > 0 && $book_id > 0) {
        // Check if book is available
        $book_check = $conn->query("SELECT quantity FROM books WHERE book_id = $book_id");
        if ($book_check && $book_check->num_rows > 0) {
            $book = $book_check->fetch_assoc();
            if ($book['quantity'] > 0) {
                // Issue the book
                $stmt = $conn->prepare("INSERT INTO issued_books (user_id, book_id, issue_date, return_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $user_id, $book_id, $issue_date, $return_date);
                if ($stmt->execute()) {
                    // Reduce book quantity
                    $conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");
                    $message = "✅ Book issued successfully!";
                } else {
                    $message = "❌ Failed to issue book.";
                }
                $stmt->close();
            } else {
                $message = "⚠️ Book not available in stock.";
            }
        } else {
            $message = "⚠️ Book not found.";
        }
    } else {
        $message = "⚠️ Please select a user and a book.";
    }
}

// Fetch users
$users = [];
$userResult = $conn->query("SELECT user_id, name FROM users ORDER BY name ASC");
if ($userResult && $userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch available books
$books = [];
$bookResult = $conn->query("SELECT book_id, title FROM books WHERE quantity > 0 ORDER BY title ASC");
if ($bookResult && $bookResult->num_rows > 0) {
    while ($row = $bookResult->fetch_assoc()) {
        $books[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue Book - ULMS</title>
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
            <h3 class="mb-4">📖 Issue a Book</h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Select User</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Choose User --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Book</label>
                    <select name="book_id" class="form-select" required>
                        <option value="">-- Choose Book --</option>
                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['book_id'] ?>"><?= htmlspecialchars($book['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Return Date</label>
                    <input type="date" name="return_date" class="form-control" required>
                </div>

                <button type="submit" name="issue_book" class="btn btn-success w-100">Issue Book</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

