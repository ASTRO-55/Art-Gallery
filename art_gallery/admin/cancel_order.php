<?php
session_start();
include '../config/db.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // Update the order status to canceled
    $stmt = $conn->prepare("UPDATE sales SET status = 'canceled' WHERE id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Order #$order_id has been canceled.";
    } else {
        $_SESSION['message'] = "Failed to cancel order #$order_id.";
    }

    $stmt->close();
}

header('Location: dashboard.php');
exit();
