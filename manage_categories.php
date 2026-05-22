<?php
session_start();
include __DIR__ . '/db_connect.php'; // Database connection

// Redirect to login if admin not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Add Category
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name'] ?? '');

    if ($category_name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->close();
        $message = "✅ Category added successfully!";
    } else {
        $message = "⚠️ Please enter a category name.";
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE category_id = $id");
    $message = "🗑️ Category deleted successfully!";
}

// Handle Edit Category
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_category'])) {
    $id = intval($_POST['category_id']);
    $new_name = trim($_POST['category_name'] ?? '');

    if ($new_name !== '') {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->bind_param("si", $new_name, $id);
        $stmt->execute();
        $stmt->close();
        $message = "✏️ Category updated successfully!";
    } else {
        $message = "⚠️ Please enter a category name.";
    }
}

// Fetch all categories
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - ULMS</title>
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
        <!-- Add/Edit Category Form -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Add / Edit Category</h4>

                    <?php if (!empty($message)): ?>
                        <div class="alert alert-info py-2"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="category_id" id="category_id">
                        <div class="mb-2">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="category_name" id="category_name" class="form-control" required>
                        </div>

                        <button type="submit" name="add_category" id="addBtn" class="btn btn-primary w-100 mt-2">Add Category</button>
                        <button type="submit" name="edit_category" id="editBtn" class="btn btn-warning w-100 mt-2 d-none">Update Category</button>
                        <button type="button" onclick="resetForm()" class="btn btn-secondary w-100 mt-2 d-none" id="cancelBtn">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">All Categories</h4>

                    <?php if (count($categories) > 0): ?>
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $cat): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editCategory(<?= $cat['category_id'] ?>, '<?= htmlspecialchars($cat['category_name']) ?>')">Edit</button>
                                            <a href="?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No categories found. Add one using the form.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editCategory(id, name) {
    document.getElementById('category_id').value = id;
    document.getElementById('category_name').value = name;
    document.getElementById('addBtn').classList.add('d-none');
    document.getElementById('editBtn').classList.remove('d-none');
    document.getElementById('cancelBtn').classList.remove('d-none');
}

function resetForm() {
    document.getElementById('category_id').value = '';
    document.getElementById('category_name').value = '';
    document.getElementById('addBtn').classList.remove('d-none');
    document.getElementById('editBtn').classList.add('d-none');
    document.getElementById('cancelBtn').classList.add('d-none');
}
</script>

</body>
</html>

