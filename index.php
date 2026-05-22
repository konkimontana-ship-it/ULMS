<?php
session_start();

// Auto-redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
} elseif (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to ULMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('images/bookshelf.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .welcome-box {
            background: rgba(255, 255, 255, 0.92);
            padding: 50px 60px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.25);
            backdrop-filter: blur(6px);
        }
        h1 {
            font-weight: bold;
            color: #1f2937;
        }
        p {
            color: #555;
        }
        .btn {
            margin: 10px;
            font-size: 18px;
            padding: 12px 25px;
            border-radius: 8px;
        }
        footer {
            position: absolute;
            bottom: 15px;
            width: 100%;
            text-align: center;
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        }
        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 24px;
            text-decoration: none;
        }
        .social-icons a:hover {
            color: #FFD700;
        }
    </style>
</head>
<body>

    <div class="welcome-box">
        <h1>📚 Welcome to ULMS</h1>
        <p>Urafiki Library Management System</p>
        <a href="user_login.php" class="btn btn-primary">👤 User Login</a>
        <a href="admin_login.php" class="btn btn-dark">🛠️ Admin Login</a>
    </div>

    <footer>
        <div class="social-icons">
            <a href="https://twitter.com/" target="_blank"><i class="bi bi-twitter"></i></a>
            <a href="https://instagram.com/" target="_blank"><i class="bi bi-instagram"></i></a>
            <a href="https://facebook.com/" target="_blank"><i class="bi bi-facebook"></i></a>
        </div>
        <p>© 2025 ULMS | Designed by Konki & Ace</p>
    </footer>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</body>
</html>
