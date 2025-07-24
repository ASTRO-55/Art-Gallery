<?php
session_start();

// Clear only admin session variables
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
    // Optionally clear other admin session keys if any
}

// Destroy session if no other roles are logged in
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect with success message
header("Location: ../admin/login.php?logout=success");
exit();
