<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ===================== ADD BOOK =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author_id = intval($_POST['author_id']);
    $category_id = intval($_POST['category_id']);
    $isbn = trim($_POST['isbn']);
    $copies = intval($_POST['copies']);
    $price = floatval($_POST['price']);

    if ($title && $author_id && $category_id && $copies > 0) {
        $stmt = $conn->prepare("INSERT INTO books (title, author_id, category_id, isbn, copies, price, added_on) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("siisid", $title, $author_id, $category_id, $isbn, $copies, $price);
        $stmt->execute();
        $stmt->close();
        $message = "✅ Book added successfully!";
    } else {
        $message = "⚠️ Please fill all required fields.";
    }
}

// ===================== DELETE BOOK =====================
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    $conn->query("DELETE FROM books WHERE book_id = $book_id");
    $message = "🗑️ Book deleted successfully!";
}

// ===================== FETCH DATA =====================
$authors = $conn->query("SELECT * FROM authors ORDER BY author_name ASC");
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

$books = $conn->query("
    SELECT b.book_id, b.title, a.author_name, c.category_name, b.isbn, b.copies, b.price, b.added_on
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN categories c ON b.category_id = c.category_id
    ORDER BY b.book_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - ULMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="admin_dashboard.php">ULMS Dashboard</a>
    <div>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <!-- Add Book Form -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Add New Book</h4>
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info py-2"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label">Book Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Author</label>
                            <select name="author_id" class="form-select" required>
                                <option value="">Select Author</option>
                                <?php while ($row = $authors->fetch_assoc()): ?>
                                    <option value="<?= $row['author_id'] ?>"><?= htmlspecialchars($row['author_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php while ($row = $categories->fetch_assoc()): ?>
                                    <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Copies</label>
                            <input type="number" name="copies" class="form-control" required min="1">
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Price (optional)</label>
                            <input type="number" step="0.01" name="price" class="form-control">
                        </div>

                        <button type="submit" name="add_book" class="btn btn-primary w-100 mt-2">Add Book</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Book List -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">All Books</h4>

                    <?php if ($books->num_rows > 0): ?>
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>ISBN</th>
                                    <th>Copies</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($book = $books->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $book['book_id'] ?></td>
                                        <td><?= htmlspecialchars($book['title']) ?></td>
                                        <td><?= htmlspecialchars($book['author_name']) ?></td>
                                        <td><?= htmlspecialchars($book['category_name']) ?></td>
                                        <td><?= htmlspecialchars($book['isbn']) ?></td>
                                        <td><?= $book['copies'] ?></td>
                                        <td><?= number_format($book['price'], 2) ?></td>
                                        <td>
                                            <a href="edit_book.php?id=<?= $book['book_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?delete=<?= $book['book_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No books found. Add one using the form.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
