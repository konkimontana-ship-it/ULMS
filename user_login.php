<?php
session_start();
include __DIR__ . '/db_connect.php';

// Redirect logged-in users
if (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Prepare query based on your database columns
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];

                header("Location: user_dashboard.php");
                exit();
            } else {
                $error = "❌ Incorrect email or password.";
            }

            $stmt->close();
        } else {
            $error = "⚠️ Database error. Please check your query.";
        }
    } else {
        $error = "⚠️ Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - ULMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-container {
            background: rgba(255,255,255,0.9);
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            margin: 100px auto;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        .btn-login {
            background: #2563eb;
            color: #fff;
            font-weight: bold;
            border: none;
        }
        .btn-login:hover {
            background: #1d4ed8;
        }
        .social-icons {
            text-align: center;
            margin-top: 20px;
        }
        .social-icons a {
            color: #333;
            margin: 0 8px;
            text-decoration: none;
            font-size: 1.2rem;
        }
        .social-icons a:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>📚 ULMS User Login</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-login w-100">Login</button>
        </form>

        <div class="social-icons">
            <p class="mt-3 mb-1">Connect with us:</p>
            <a href="https://twitter.com/" target="_blank"><i class="bi bi-twitter"></i>🐦</a>
            <a href="https://instagram.com/" target="_blank"><i class="bi bi-instagram"></i>📸</a>
            <a href="https://facebook.com/" target="_blank"><i class="bi bi-facebook"></i>📘</a>
        </div>

        <p class="text-center mt-3">
            <a href="index.php" class="text-decoration-none">⬅ Back to Home</a>
        </p>
    </div>
</body>
</html>
