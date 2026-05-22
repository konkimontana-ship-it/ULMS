<?php
session_start();
require_once 'config.php';

// Redirect to login if admin not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get book ID from URL
$book_id = (int)($_GET['id'] ?? 0);
if (!$book_id) {
    header('Location: manage_books.php');
    exit;
}

// Fetch the book from the database
$stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    header('Location: manage_books.php');
    exit;
}

$message = '';
$error = '';

// Update book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);

    if ($title && $author && $category && $quantity > 0) {
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, quantity=? WHERE book_id=?");
        $stmt->bind_param("sssii", $title, $author, $category, $quantity, $book_id);
        $stmt->execute();
        $stmt->close();
        $message = "Book updated successfully!";
        // Update $book array to reflect changes in the form
        $book['title'] = $title;
        $book['author'] = $author;
        $book['category'] = $category;
        $book['quantity'] = $quantity;
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book - ULMS</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f3f4f6; margin:0; padding:0; }
        .container { max-width:600px; margin:50px auto; background:white; padding:30px; border-radius:10px; box-shadow:0 3px 8px rgba(0,0,0,0.1); }
        input[type="text"], input[type="number"] { width:100%; padding:8px; margin:6px 0; }
        button { padding:10px 14px; background:#2563eb; color:white; border:none; border-radius:6px; cursor:pointer; }
        button:hover { opacity:0.9; }
        .msg { color:green; margin-top:10px; }
        .error { color:red; margin-top:10px; }
        a { text-decoration:none; color:#2563eb; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Book</h2>
    <form method="POST" action="">
        <input type="text" name="title" placeholder="Book Title" value="<?= htmlspecialchars($book['title']) ?>" required>
        <input type="text" name="author" placeholder="Author Name" value="<?= htmlspecialchars($book['author']) ?>" required>
        <input type="text" name="category" placeholder="Category" value="<?= htmlspecialchars($book['category']) ?>" required>
        <input type="number" name="quantity" placeholder="Quantity" min="1" value="<?= htmlspecialchars($book['quantity']) ?>" required>
        <button type="submit" name="update_book">Update Book</button>
    </form>
    <?php if ($message) echo "<p class='msg'>$message</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <p><a href="manage_books.php">← Back to Manage Books</a></p>
</div>
</body>
</html>
