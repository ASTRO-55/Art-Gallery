<?php
session_start();
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /art_gallery/login.php');
    exit();
}

// Get artwork ID from query string
if (!isset($_GET['artwork_id'])) {
    die("Artwork not specified.");
}
$artwork_id = intval($_GET['artwork_id']);

// Fetch artwork details from DB
$stmt = $conn->prepare("SELECT title, description, price, image FROM artworks WHERE id = ?");
$stmt->bind_param("i", $artwork_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Artwork not found.");
}

$artwork = $result->fetch_assoc();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = htmlspecialchars(trim($_POST['area']));
    $pincode = htmlspecialchars(trim($_POST['pincode']));
    $city = htmlspecialchars(trim($_POST['city']));
    $user_id = $_SESSION['user_id'];

    if (!$area || !$pincode || !$city) {
        $error = "Please fill in all address fields.";
    } else {
        // Insert order into DB (assuming you have an orders table)
        $stmt = $conn->prepare("INSERT INTO orders (user_id, artwork_id, area, pincode, city, order_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $user_id, $artwork_id, $area, $pincode, $city);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Failed to process order: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Buy Artwork - Art Gallery</title>
<link rel="stylesheet" href="/art_gallery/css/style.css" />
<style>
  body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    color: #fff;
    background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat top center;
    background-size: cover;
    min-height: 100vh;
  }
  .buy-container {
    max-width: 900px;
    margin: 120px auto 50px;
    background: rgba(0,0,0,0.6);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.5);
    color: #fff;
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
    justify-content: center;
  }
  .artwork-card {
    flex: 1 1 400px;
    background: rgba(255,255,255,0.1);
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.3);
    overflow: hidden;
    color: #fff;
    cursor: default;
  }
  .artwork-card img {
    width: 100%;
    height: auto;
    display: block;
    object-fit: contain;
  }
  .artwork-info {
    padding: 20px;
  }
  .artwork-info h3 {
    margin: 0 0 10px;
    font-size: 2rem;
  }
  .artwork-info p {
    font-size: 1.1rem;
    margin-bottom: 10px;
  }
  .artwork-info .price {
    font-weight: 700;
    font-size: 1.4rem;
    color: #6a11cb;
  }
  form.address-form {
    flex: 1 1 400px;
    display: flex;
    flex-direction: column;
  }
  form.address-form label {
    margin-top: 15px;
    font-weight: 600;
  }
  form.address-form input {
    padding: 10px;
    margin-top: 5px;
    border-radius: 6px;
    border: none;
    font-size: 1rem;
  }
  form.address-form input[type="submit"] {
    margin-top: 30px;
    background: linear-gradient(45deg, #6a11cb, #2575fc);
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 30px;
    padding: 15px;
    cursor: pointer;
    transition: background 0.3s ease;
  }
  form.address-form input[type="submit"]:hover {
    background: linear-gradient(45deg, #2575fc, #6a11cb);
  }
  .success {
    color: #4caf50;
    margin-top: 15px;
    font-weight: bold;
  }
  .error {
    color: #ff5555;
    margin-top: 15px;
    font-weight: bold;
  }
</style>
</head>
<body>

<!-- Navbar (reuse your existing navbar or add here) -->
<div class="navbar" id="navbar">
  <h1 id="navTitle" class="nav-title hidden">Art Gallery</h1>
  <div id="navButtons" class="nav-links hidden">
    <a href="/art_gallery/index.php" class="button">Home</a>
    <a href="/art_gallery/user/dashboard.php" class="button">Dashboard</a>
    <a href="/art_gallery/logout.php" class="button">Logout</a>
  </div>
</div>

<div class="buy-container">
  <div class="artwork-card">
    <img src="/art_gallery/uploads/<?php echo htmlspecialchars($artwork['image']); ?>" alt="<?php echo htmlspecialchars($artwork['title']); ?>" />
    <div class="artwork-info">
      <h3><?php echo htmlspecialchars($artwork['title']); ?></h3>
      <p><?php echo nl2br(htmlspecialchars($artwork['description'])); ?></p>
      <p class="price">₹ <?php echo number_format($artwork['price'], 2); ?></p>
    </div>
  </div>

  <form method="post" class="address-form">
    <h2>Delivery Address</h2>
    <?php if ($success): ?>
      <p class="success">✅ Order placed successfully!</p>
    <?php elseif ($error): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <label for="area">Area / Locality</label>
    <input type="text" id="area" name="area" required />

    <label for="pincode">Pin Code</label>
    <input type="text" id="pincode" name="pincode" required pattern="\d{6}" title="6-digit pin code" />

    <label for="city">City</label>
    <input type="text" id="city" name="city" required />

    <input type="submit" value="Buy Now" />
  </form>
</div>

<script>
const navbar = document.getElementById("navbar");
const navTitle = document.getElementById("navTitle");
const navButtons = document.getElementById("navButtons");

window.addEventListener("scroll", () => {
  if (window.scrollY > 100) {
    navbar.classList.add("visible");
    navTitle.classList.remove("hidden");
    navButtons.classList.remove("hidden");
  } else {
    navbar.classList.remove("visible");
    navTitle.classList.add("hidden");
    navButtons.classList.add("hidden");
  }
});
</script>

</body>
</html>
