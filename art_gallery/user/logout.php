<?php
session_start();

// Clear only user session variables
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
    // Optionally clear other user session keys if any
}

// Destroy session if no other roles are logged in
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect with success message
header("Location: ../user/login.php?logout=success");
exit();
