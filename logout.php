<?php
// logout.php — shared for both admin and user
session_start();

// Clear all session data
session_unset();
session_destroy();

// Redirect based on where they came from
if (isset($_GET['type']) && $_GET['type'] === 'user') {
    header('Location: user_login.php');
} else {
    header('Location: admin_login.php');
}
exit;
?>
