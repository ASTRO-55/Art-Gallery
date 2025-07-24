<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_id'])) {
    $user_id = $_SESSION['user_id'];
    $artwork_id = intval($_POST['artwork_id']);

    // Prevent duplicate likes
    $check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND artwork_id = ?");
    $check->bind_param("ii", $user_id, $artwork_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO likes (user_id, artwork_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $artwork_id);
        $stmt->execute();
    }

    header("Location: index.php");
    exit();
}
?>
