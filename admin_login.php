<?php
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "⚠️ Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT admin_id, full_name, password FROM admins WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header('Location: admin_dashboard.php');
                exit;
            } elseif ($password === $admin['password']) {
                // Upgrade plain password to hashed
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
                $upd->bind_param('si', $newHash, $admin['admin_id']);
                $upd->execute();

                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = "❌ Invalid email or password.";
            }
        } else {
            $error = "❌ Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - ULMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1400&q=80') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            font-family: 'Poppins', sans-serif;
        }

        /* Dark overlay for readability */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px 35px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            width: 400px;
            transition: 0.3s ease-in-out;
        }

        .login-container:hover {
            transform: translateY(-5px);
        }

        .login-container h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #1e3c72;
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
        }

        .btn-primary {
            background: #1e3c72;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: #162f59;
            transform: scale(1.03);
        }

        .input-group-text {
            background: #1e3c72;
            color: #fff;
            border: none;
        }

        .alert {
            text-align: center;
            border-radius: 10px;
        }

        .footer {
            text-align: center;
            font-size: 0.9em;
            margin-top: 20px;
            color: #333;
        }

        .social-icons {
            margin-top: 10px;
            text-align: center;
        }

        .social-icons a {
            text-decoration: none;
            color: #1e3c72;
            margin: 0 10px;
            font-size: 1.3em;
            transition: 0.3s;
        }

        .social-icons a:hover {
            color: #2a5298;
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3><i class="fa-solid fa-lock"></i> Admin Login</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </form>

        <div class="footer mt-3">
            <small>📚 Urafiki Library Management System</small>
            <div class="social-icons">
                <a href="https://twitter.com/yourhandle" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/yourhandle" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://facebook.com/yourhandle" target="_blank"><i class="fab fa-facebook"></i></a>
            </div>
        </div>
    </div>
</body>
</html>
