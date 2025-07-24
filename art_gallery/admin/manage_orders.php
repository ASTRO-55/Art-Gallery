<?php 
session_start();
require '../config/db_connect.php';

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all orders with related user, artwork, and artist info
$orders_stmt = $conn->prepare("
    SELECT 
        o.id as order_id, o.status, o.order_date, o.contact_number, o.shipping_address,
        u.name as buyer_name, u.email as buyer_email,
        a.title as artwork_title, a.price, a.image as artwork_image,
        ar.name as artist_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN artworks a ON o.artwork_id = a.id
    JOIN artists ar ON a.artist_id = ar.id
    ORDER BY o.order_date DESC
");
$orders_stmt->execute();
$result = $orders_stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Handle order status update or cancellation if POST request is sent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];
        $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $order_id]);
        header("Location: manage_orders.php");
        exit;
    } elseif (isset($_POST['cancel_order'])) {
        $order_id = $_POST['order_id'];
        $cancel_stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
        $cancel_stmt->execute([$order_id]);
        header("Location: manage_orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            padding: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0,0,0,0.6);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #444;
            color: #eee;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: rgba(255,255,255,0.1);
        }
        tr:hover {
            background: rgba(255,255,255,0.05);
        }
        .artwork-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        select, button {
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-update {
            background-color: #28a745;
            color: white;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .address-cell {
            max-width: 200px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="navbar">
    <div class="nav-title">
    Art Gallery Admin
</div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_artists.php">artists</a>
        <a href="logout.php">Logout</a>
    </div>
</div>
<div class="container">
    <h1>Manage Orders</h1>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Contact Number</th>
                <th>Address</th>
                <th>Artwork</th>
                <th>Artist</th>
                <th>Price (₹)</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Update Status</th>
                <th>Cancel Order</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) === 0): ?>
                <tr><td colspan="11" style="text-align:center;">No orders found.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_id']) ?></td>
                        <td>
                            <?= htmlspecialchars($order['buyer_name']) ?><br>
                            <small><?= htmlspecialchars($order['buyer_email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($order['contact_number'] ?? 'N/A') ?></td>
                        <td class="address-cell"><?= htmlspecialchars($order['shipping_address'] ?? 'N/A') ?></td>
                        <td>
                            <img src="../uploads/<?= htmlspecialchars($order['artwork_image']) ?>" alt="Artwork" class="artwork-image"><br>
                            <?= htmlspecialchars($order['artwork_title']) ?>
                        </td>
                        <td><?= htmlspecialchars($order['artist_name']) ?></td>
                        <td><?= number_format($order['price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <select name="status" required>
                                    <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Update</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($order['status'] !== 'Cancelled'): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <button type="submit" name="cancel_order" class="btn-cancel">Cancel</button>
                            </form>
                            <?php else: ?>
                                <em>Cancelled</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
