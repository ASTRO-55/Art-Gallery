<?php
session_start();

// Clear only artist session variables
if (isset($_SESSION['artist_id'])) {
    unset($_SESSION['artist_id']);
    // Optionally clear other artist session keys if any
}

// Destroy session if no other roles are logged in
if (empty($_SESSION)) {
    session_destroy();
}

// Redirect with success message
header("Location: ../artist/login.php?logout=success");
exit();
