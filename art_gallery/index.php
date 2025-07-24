<?php 
session_start();
require_once 'includes/db_connect.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['name'] ?? '';
$cart_count = 0;
if ($is_logged_in) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}


$filter_artist = $_GET['artist'] ?? '';
$filter_type = $_GET['type'] ?? '';

$artist_result = $conn->query("SELECT id, name FROM artists");

// Build artworks query
$sql = "
  SELECT a.id, a.title, a.description, a.type, a.price, a.image, ar.name AS artist_name
  FROM artworks a
  JOIN artists ar ON a.artist_id = ar.id
  WHERE a.available = 1
";
$params = [];
$types = '';

if ($filter_artist !== '') {
    $sql .= " AND ar.id = ?";
    $params[] = $filter_artist;
    $types .= 'i';
}
if ($filter_type !== '') {
    $sql .= " AND a.type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

$sql .= " ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$type_result = $conn->query("SELECT DISTINCT type FROM artworks WHERE available = 1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Art Gallery</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .hero {
  max-width: 1200px;
  margin: 100px auto 40px;
  text-align: center;
}

.filter-container {
  position: fixed;
  top: 80px;
  right: 20px;
  background: rgba(30, 30, 30, 0.85);
  padding: 10px 12px;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(100, 100, 255, 0.15);
  z-index: 1100;
  width: 140px;
  font-size: 11px;
}

.filter-container label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #aaa;
}

.filter-container select,
.filter-container button {
  width: 100%;
  padding: 8px 10px;
  margin-bottom: 15px;
  border-radius: 6px;
  border: none;
  background: #444;
  color: #eee;
  font-size: 14px;
}

.filter-container button {
  background: linear-gradient(45deg, #6a11cb, #2575fc);
  font-weight: 700;
}

.artwork-container {
  max-width: 1200px;
  margin: 50px auto 100px;
  padding: 0 15px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;
}

.artwork-card {
  position: relative; /* Added */
  background: rgba(30, 30, 30, 0.85);
  border-radius: 12px;
  box-shadow: 0 0 15px rgba(100, 100, 255, 0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  color: #eee;
  transition: transform 0.3s, box-shadow 0.3s;
  padding-bottom: 70px; /* Make space for buttons */
}

.artwork-card:hover {
  transform: scale(1.03);
  box-shadow: 0 0 25px rgba(120, 120, 255, 0.4);
  cursor: pointer;
}

.artwork-card img {
  width: 100%;
  height: 320px;
  object-fit: cover;
  border-bottom: 1px solid #444;
}

.artwork-card h3 {
  margin: 15px;
  font-size: 20px;
}

.artwork-card p {
  font-size: 13px;
  line-height: 1.4;
  margin: 0 15px 10px;
  color: #bbb;
  display: block;
  max-height: 80px;
  overflow: hidden;
  text-overflow: ellipsis;
}

.price {
  font-weight: 700;
  margin: 0 15px 15px;
  color: #6a11cb;
}

.artist {
  margin: 0 15px 10px;
  color: #999;
  font-style: italic;
  font-size: 14px;
}
.nav-links {
  display: flex;
  align-items: center;
}


.artwork-actions {
  position: absolute;
  bottom: 15px;
  left: 15px;
  right: 15px;
  display: flex;
  gap: 10px;
  height: 45px;
  display: flex;
  gap: 8px;
  margin: 0 15px 15px;
}
.artwork-actions .button {
  flex: 1;
}
/* Shared base button style */
.button {
  flex: 1;
  height: 38px;
  padding: 0;
  border: none;
  border-radius: 6px;
  color: white;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: background 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Specific background for each button */
.btn-add-cart {
  background: linear-gradient(45deg, #6a11cb, #2575fc);
}

.btn-buy-now {
  background: linear-gradient(45deg, #c62828, #ff5252);
}

/* Optional hover effect for both */
.btn-add-cart:hover,
.btn-buy-now:hover {
  filter: brightness(1.1);
}



#popup {
  display: none;
  position: fixed;
  top: 20px;
  right: 20px;
  background: #333;
  color: #fff;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
  z-index: 2000;
  font-size: 16px;
}

  </style>
</head>

<body>

<div id="popup">Item added to cart!</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const popup = document.getElementById('popup');
  const cartButtons = document.querySelectorAll('.btn-add-cart');
  const cartLink = document.querySelector('.nav-links a[href="user/cart.php"]');

  function showPopup(message) {
    popup.textContent = message;
    popup.style.display = 'block';
    setTimeout(() => { popup.style.display = 'none'; }, 2000);
  }

  function updateCartCount(count) {
    if (cartLink) {
      cartLink.textContent = `🛒 Cart (${count})`;
    }
  }

  cartButtons.forEach(button => {
    button.addEventListener('click', () => {
      const artworkId = button.getAttribute('data-artwork-id');

      fetch('user/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `artwork_id=${encodeURIComponent(artworkId)}`
      })
      .then(res => res.text())  // Get raw response text first
      .then(text => {
        try {
          const data = JSON.parse(text);
          if (data.success) {
            showPopup("Item added to cart!");
            updateCartCount(data.cart_count);
          } else if (data.message === 'Login required') {
            window.location.href = 'user/login.php';
          } else {
            showPopup("Error adding to cart.");
          }
        } catch (e) {
          console.error("Invalid JSON response:", text);
          showPopup("Network error.");
        }
      })
      .catch(err => {
        console.error(err);
        showPopup("Network error.");
      });
    });
  });
});

</script>

<!-- Navbar -->
<!-- Navbar -->
<div class="navbar">
  <div>
    <a href="index.php" class="logo">🎨 Art Gallery</a>
    <?php if ($is_logged_in): ?>
      <span class="welcome-msg" style="margin-right: 10px;">Welcome</span>
      <span style="color: #6a11cb; font-weight: bold;"><?= htmlspecialchars($user_name) ?></span>
    <?php endif; ?>
  </div>
  <div class="nav-links">
    <?php if ($is_logged_in): ?>
      <a href="user/logout.php" class="button">Logout</a>
    <?php else: ?>
      <a href="user/login.php">Login</a>
      <a href="user/register.php" class="button">Register</a>
    <?php endif; ?>
    <?php if ($is_logged_in): ?>
  <a href="user/cart.php" class="button">🛒 Cart (<?= $cart_count ?>)</a>
<?php else: ?>
  <a href="user/login.php" class="button">🛒 Cart</a>
<?php endif; ?>

  </div>
</div>

<!-- Filters -->
<div class="filter-container">
  <form method="GET" action="index.php">
    <label for="artist">Filter by Artist:</label>
    <select name="artist" id="artist">
      <option value="">All Artists</option>
      <?php while ($artist = $artist_result->fetch_assoc()): ?>
        <option value="<?= $artist['id'] ?>" <?= ($artist['id'] == $filter_artist) ? 'selected' : '' ?>>
          <?= htmlspecialchars($artist['name']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <label for="type">Filter by Type:</label>
    <select name="type" id="type">
      <option value="">All Types</option>
      <?php while ($typeRow = $type_result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($typeRow['type']) ?>" <?= ($typeRow['type'] == $filter_type) ? 'selected' : '' ?>>
          <?= htmlspecialchars(ucfirst($typeRow['type'])) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <button type="submit">Apply Filters</button>
  </form>
</div>

<!-- Hero -->
<div class="hero">
  <h1>Discover Stunning Artworks</h1>
  <p>Explore and buy artworks from talented artists worldwide.</p>
</div>

<!-- Artworks -->
<div class="artwork-container">
  <?php if ($result->num_rows === 0): ?>
    <p style="text-align:center; color:#ccc; font-size:18px;">No artworks found matching your filters.</p>
  <?php endif; ?>
  <?php while ($artwork = $result->fetch_assoc()): ?>
    <div class="artwork-card">
      <img src="uploads/<?= htmlspecialchars($artwork['image']) ?>" alt="<?= htmlspecialchars($artwork['title']) ?>">
      <h3><?= htmlspecialchars($artwork['title']) ?></h3>
      <p class="artist">By: <?= htmlspecialchars($artwork['artist_name']) ?></p>
      <p><?= htmlspecialchars($artwork['description']) ?></p>
      <p class="price">₹<?= number_format($artwork['price'], 2) ?></p>
      <div class="artwork-actions">
  <form method="POST" action="user/checkout.php" style="display: flex; gap: 8px; flex: 1;">
    <input type="hidden" name="artwork_id" value="<?= $artwork['id'] ?>">
    <button type="button" class="button btn-add-cart" data-artwork-id="<?= $artwork['id'] ?>">Add to Cart</button>
    <button type="submit" name="buy_now" class="button btn-buy-now">Purchase</button>
  </form>
</div>

    </div>
  <?php endwhile; ?>
</div>

</body>
</html>
