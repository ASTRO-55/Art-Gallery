<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_id'], $_POST['comment'])) {
    $user_id = $_SESSION['user_id'];
    $artwork_id = intval($_POST['artwork_id']);
    $comment = trim($_POST['comment']);

    if ($comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, artwork_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $artwork_id, $comment);
        $stmt->execute();
    }

    header("Location: index.php");
    exit();
}
?>
