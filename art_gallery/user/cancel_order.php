<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to login with redirect back to my_orders.php
    $_SESSION['redirect_after_login'] = '/art_gallery/user/orders.php';
    header("Location: /art_gallery/user/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $user_id = $_SESSION['user_id'];
    $order_id = intval($_POST['order_id']);

    include '../includes/db_connect.php'; // Adjust path if needed

    // First verify order belongs to user and is still pending
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $order = $result->fetch_assoc();
        if (strtolower($order['status']) === 'pending') {
            // Update order status to cancelled
            $update_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $update_stmt->bind_param("i", $order_id);
            if ($update_stmt->execute()) {
                $_SESSION['cancel_msg'] = "Order #$order_id has been cancelled successfully.";
            } else {
                $_SESSION['cancel_msg'] = "Failed to cancel the order. Please try again.";
            }
            $update_stmt->close();
        } else {
            $_SESSION['cancel_msg'] = "Order cannot be cancelled because it is already {$order['status']}.";
        }
    } else {
        $_SESSION['cancel_msg'] = "Order not found or you don't have permission to cancel this order.";
    }

    $stmt->close();
    $conn->close();

    header("Location: /art_gallery/user/orders.php");
    exit();
} else {
    // Invalid request, redirect back
    header("Location: /art_gallery/user/orders.php");
    exit();
}
