<?php
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orderPlaced = false;
$error = '';

// Fetch user's available cart items and total price to confirm
$stmt = $conn->prepare("
    SELECT a.id, a.title, a.price, a.available
    FROM cart c
    JOIN artworks a ON c.artwork_id = a.id
    WHERE c.user_id = ?
");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$available_items = array_filter($cart_items, fn($item) => $item['available']);
$total_price = 0;
foreach ($available_items as $item) {
    $total_price += $item['price'];
}

if ($total_price == 0) {
    $error = "Your cart has no available artworks to checkout.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$orderPlaced && !$error) {
    $address = trim($_POST['address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';

    if (empty($address) || empty($contact_number)) {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^[0-9]{10}$/', $contact_number)) {
        $error = "Please enter a valid 10-digit contact number.";
    } elseif ($total_price <= 0) {
        $error = "No available items in your cart to place order.";
    } else {
        // Insert orders for each available artwork
        $insert = $conn->prepare("INSERT INTO orders (user_id, artwork_id, shipping_address, contact_number, payment_method) VALUES (?, ?, ?, ?, ?)");
        foreach ($available_items as $item) {
            $insert->execute([$user_id, $item['id'], $address, $contact_number, $payment_method]);

            // Optionally: mark artwork as unavailable after order
            $updateArtwork = $conn->prepare("UPDATE artworks SET available = 0 WHERE id = ?");
            $updateArtwork->execute([$item['id']]);
        }

        // Clear cart items for user
        $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $delete->execute([$user_id]);

        $orderPlaced = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Checkout</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
          .navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }
    .checkout-container {
      max-width: 800px;
      margin: 50px auto;
      background-color: rgba(30, 30, 30, 0.95);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.6);
    }
    .checkout-container h2 {
      text-align: center;
      margin-bottom: 25px;
    }
    textarea, input[type="text"], select {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      margin-bottom: 20px;
      border: none;
      border-radius: 6px;
      background-color: #222;
      color: #fff;
    }
    .button {
      background-color: #444;
      color: white;
      padding: 12px 20px;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      width: 100%;
      font-size: 16px;
      transition: background-color 0.3s;
    }
    .button:hover {
      background-color: #333;
    }
    .message, .error {
      margin-top: 15px;
      padding: 10px;
      border-radius: 6px;
      font-weight: bold;
    }
    .message {
      background-color: #2d662d;
      color: #aaffaa;
    }
    .error {
      background-color: #662d2d;
      color: #ffaaaa;
    }
    .order-summary {
      margin-bottom: 20px;
      border-bottom: 1px solid #444;
      padding-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="navbar">
    <a href="../index.php" class="logo">🎨 Art Gallery</a>
    <div class="nav-links">
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="checkout-container">
  <h2>Checkout</h2>

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if ($orderPlaced): ?>
    <div class="message">
      Thank you for your order! Your transaction has been successfully processed.
      <br />
      <a href="index.php" style="color:#aaffaa;">Continue Shopping</a>
    </div>
  <?php else: ?>
    <?php if (!$available_items): ?>
      <p>Your cart has no available artworks for purchase.</p>
      <?php else: ?>
      <div class="order-summary">
        <h3>Order Summary</h3>
        <ul>
          <?php foreach ($available_items as $item): ?>
            <li><?php echo htmlspecialchars($item['title']); ?> — $<?php echo number_format($item['price'], 2); ?></li>
          <?php endforeach; ?>
        </ul>
        <strong>Total: $<?php echo number_format($total_price, 2); ?></strong>
      </div>

      <form method="POST" action="checkout.php">
        <label for="address">Shipping Address *</label>
        <textarea name="address" id="address" rows="4" required></textarea>

        <label for="contact_number">Contact Number *</label>
        <input type="text" id="contact_number" name="contact_number" placeholder="10-digit phone number" pattern="\d{10}" required />

        <label for="payment_method">Payment Method</label>
        <select id="payment_method" name="payment_method">
          <option value="Cash on Delivery">Cash on Delivery</option>
          <option value="UPI">UPI</option>
          <option value="Card">Card</option>
        </select>

        <button type="submit" class="button">Place Order</button>
      </form>
    <?php endif; ?>
  <?php endif; ?>

</div>

</body>
</html>
