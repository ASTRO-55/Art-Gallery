<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: /art_gallery/user/login.php");
    exit();
}

include '../includes/db_connect.php'; // Adjust path as needed
$user_id = $_SESSION['user_id'];

// Handle cancel success message (optional)
$cancel_msg = '';
if (isset($_SESSION['cancel_msg'])) {
    $cancel_msg = $_SESSION['cancel_msg'];
    unset($_SESSION['cancel_msg']);
}

$query = "SELECT o.id, o.order_date, o.status, a.title, a.price, a.image 
          FROM orders o
          JOIN artworks a ON o.artwork_id = a.id
          WHERE o.user_id = ?
          ORDER BY o.order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <style>
            .navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }

        .orders-container {
            max-width: 900px;
            margin: 100px auto;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .order-details {
            flex: 1;
        }
        .order-details h4 {
            margin: 0;
            color: #fff;
        }
        .order-details p {
            margin: 5px 0;
            color: #ccc;
        }
        .btn-cancel {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .btn-cancel:hover {
            background-color: #c0392b;
        }
        .message {
            max-width: 900px;
            margin: 20px auto;
            padding: 10px;
            background: #27ae60;
            color: white;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="navbar">
    <a href="../index.php" class="logo">🎨 Art Gallery</a>
    <div class="nav-links">
        <a href="cart.php">Cart</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

    <div class="orders-container">
        <h2 style="color:white;">My Orders</h2>

        <?php if ($cancel_msg): ?>
            <div class="message"><?php echo htmlspecialchars($cancel_msg); ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="order-item">
                    <img src="/art_gallery/uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Artwork">
                    <div class="order-details">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p>Price: ₹<?php echo htmlspecialchars($row['price']); ?></p>
                        <p>Ordered on: <?php echo date("d M Y, h:i A", strtotime($row['order_date'])); ?></p>
                        <p>Status: <strong><?php echo htmlspecialchars(ucfirst($row['status'])); ?></strong></p>

                        <?php if (strtolower($row['status']) === 'pending'): ?>
                            <form method="POST" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-cancel">Cancel Order</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: white;">You haven't placed any orders yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
