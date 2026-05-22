<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect to login if admin not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Admin info
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ULMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1920&q=80') no-repeat center center/cover;
            color: #fff;
            height: 100vh;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.65);
            position: absolute;
            inset: 0;
        }

        .dashboard-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav.navbar {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            color: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .btn-custom {
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1.1em;
            width: 230px;
            transition: 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            opacity: 0.9;
        }

        footer {
            background: rgba(0, 0, 0, 0.75);
            padding: 15px;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            color: #ddd;
        }

        footer a {
            color: #fff;
            margin: 0 8px;
            text-decoration: none;
        }

        footer a:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="dashboard-container">
        <nav class="navbar navbar-dark px-3">
            <a class="navbar-brand" href="#">📚 ULMS Admin Dashboard</a>
            <div>
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($admin_name) ?> 👋</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </nav>

        <div class="container mt-5 text-center">
            <div class="card p-4 shadow-lg">
                <h2 class="mb-3 fw-bold">Welcome to Your ULMS System</h2>
                <p class="text-light mb-4">Use the buttons below to manage your library effectively 📖</p>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="manage_books.php" class="btn btn-primary btn-custom">📘 Manage Books</a>
                    <a href="manage_users.php" class="btn btn-success btn-custom">👥 Manage Users</a>
                    <a href="manage_categories.php" class="btn btn-info btn-custom">🏷 Manage Categories</a>
                    <a href="issue_book.php" class="btn btn-warning btn-custom">📖 Issue Book</a>
                    <a href="issued_books.php" class="btn btn-danger btn-custom">🗂 Issued Books</a>
                    <a href="return_book.php" class="btn btn-secondary btn-custom">🔁 Return Book</a>
                </div>
            </div>
        </div>

        <footer>
            <p>📚 ULMS Admin Panel &copy; <?= date('Y') ?> | Follow us:
                <a href="https://twitter.com" target="_blank">Twitter</a> |
                <a href="https://instagram.com" target="_blank">Instagram</a> |
                <a href="https://facebook.com" target="_blank">Facebook</a>
            </p>
        </footer>
    </div>
</body>
</html>
