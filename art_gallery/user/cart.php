<?php
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = '../cart.php';
    header('Location: user/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT c.id AS cart_id, a.id AS artwork_id, a.title, a.price, a.image, a.available
FROM cart c
JOIN artworks a ON c.artwork_id = a.id
WHERE c.user_id = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total price of available artworks only
$total_price = 0;
foreach ($cart_items as $item) {
    if ($item['available']) {
        $total_price += $item['price'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        $cart_id = (int)$_POST['remove'];
        $del_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        if (!$del_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $del_stmt->bind_param("ii", $cart_id, $user_id);
        $del_stmt->execute();
        $del_stmt->close();

        header("Location: cart.php");
        exit;
    } elseif (isset($_POST['checkout'])) {
        header("Location: checkout.php");
        exit;
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Your Cart - Art Gallery</title>
<link rel="stylesheet" href="../css/style.css" />
<style>

.navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }
  table {
    width: 100%;
    border-collapse: collapse;
    color: white;
    background: rgba(0,0,0,0.6);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0,0,0,0.7);
  }
  th, td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    text-align: center;
  }
  th {
    background: rgba(0,0,0,0.8);
  }
  img.artwork-thumb {
    width: 80px;
    height: auto;
    border-radius: 6px;
  }
  .checkout-btn {
    background-color: #1e90ff;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    cursor: pointer;
    margin-top: 20px;
    font-weight: bold;
    transition: background-color 0.3s;
  }
  .checkout-btn:hover {
    background-color: #187bcd;
  }
  .total-price {
    text-align: right;
    font-size: 1.3rem;
    margin-top: 20px;
    font-weight: bold;
    color: #aaffaa;
  }
  p {
    background: rgba(0,0,0,0.6);
    padding: 15px;
    border-radius: 12px;
    max-width: 600px;
    margin-top: 40px;
  }

  @media (max-width: 600px) {
      .navbar {
          flex-direction: column;
          align-items: center;
      }

      .navbar .nav-links {
          margin-top: 10px;
      }

      table, th, td {
          font-size: 14px;
      }

      .checkout-btn {
          width: 100%;
      }
  }
</style>
</head>
<body>

<div class="navbar">
  <a href="../index.php" class="logo">🎨 Art Gallery</a>
  <div class="nav-links">
    <a href="/art_gallery/user/orders.php">Orders</a> <!-- Add this page if it exists -->
    <a href="/art_gallery/user/profile.php">Profile</a>   <!-- Optional: for account settings -->
    <a href="/art_gallery/user/logout.php">Logout</a>
  </div>
</div>


<div style="max-width:1200px; margin:80px auto 40px;">
  <h1>Your Shopping Cart</h1>

  <?php if ($cart_items): ?>
    <form action="cart.php" method="post">
      <table>
        <thead>
          <tr>
            <th>Artwork</th>
            <th>Title</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Remove</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart_items as $item): ?>
          <tr>
            <td><img src="../uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="artwork-thumb" /></td>
            <td><?php echo htmlspecialchars($item['title']); ?></td>
            <td>₹<?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo $item['available'] ? 'Available' : '<span style="color:#c62828;">Unavailable</span>'; ?></td>
            <td>
              <button type="submit" name="remove" value="<?php echo $item['cart_id']; ?>" style="background:none; border:none; color:#c62828; cursor:pointer;" title="Remove from cart">✖</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="total-price">Total Price: ₹<?php echo number_format($total_price, 2); ?></div>

      <button type="submit" name="checkout" class="checkout-btn" <?php echo $total_price > 0 ? '' : 'disabled'; ?>>
        Proceed to Checkout
      </button>
    </form>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>

</div>

</body>
</html>
