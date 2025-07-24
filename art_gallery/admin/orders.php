<?php
// admin/orders.php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle cancel action
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $cancel_id = intval($_GET['cancel']);
    $conn->query("UPDATE orders SET status = 'Cancelled' WHERE id = $cancel_id");
    header('Location: orders.php');
    exit();
}

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h1>All Orders</h1>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['fullname']) ?></td>
            <td><?= htmlspecialchars($order['email']) ?></td>
            <td>₹<?= number_format($order['total_price'], 2) ?></td>
            <td><?= $order['status'] ?></td>
            <td><?= $order['created_at'] ?></td>
            <td>
                <?php if ($order['status'] != 'Cancelled'): ?>
                <a href="orders.php?cancel=<?= $order['id'] ?>" onclick="return confirm('Cancel this order?');">Cancel</a>
                <?php else: ?>Cancelled<?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
