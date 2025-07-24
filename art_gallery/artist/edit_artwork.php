<?php
session_start();
if (!isset($_SESSION['artist_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "art_gallery");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$artist_id = $_SESSION['artist_id'];
$artwork_id = $_GET['id'] ?? null;

if (!$artwork_id) {
    die("Artwork ID missing.");
}

// Fetch existing artwork
$stmt = $conn->prepare("SELECT * FROM artworks WHERE id = ? AND artist_id = ?");
$stmt->bind_param("ii", $artwork_id, $artist_id);
$stmt->execute();
$result = $stmt->get_result();
$artwork = $result->fetch_assoc();

if (!$artwork) {
    die("Artwork not found or access denied.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE artworks SET title = ?, description = ?, price = ?, available = ? WHERE id = ? AND artist_id = ?");
    $stmt->bind_param("ssdiii", $title, $description, $price, $available, $artwork_id, $artist_id);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Artwork - Art Gallery</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    .container {
      background-color: rgba(0, 0, 0, 0.6);
      max-width: 600px;
      margin: 120px auto;
      padding: 30px;
      border-radius: 20px;
      backdrop-filter: blur(10px);
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
      color: #fff;
    }
    label {
      display: block;
      margin: 15px 0 5px;
    }
    input, textarea {
      width: 100%;
      padding: 10px;
      background: rgba(255,255,255,0.1);
      border: none;
      border-radius: 8px;
      color: #fff;
    }
    input[type="submit"] {
      background: #00e6e6;
      color: #000;
      font-weight: bold;
      margin-top: 20px;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background: #00bcbc;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div class="nav-title">Welcome, <?= htmlspecialchars($_SESSION['artist_name'] ?? 'Artist') ?></div>
  <div class="nav-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_artwork.php">Add Artwork</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <h2>Edit Artwork</h2>
  <form method="POST">
    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($artwork['title']) ?>" required>

    <label>Description:</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($artwork['description']) ?></textarea>

    <label>Price (₹):</label>
    <input type="number" name="price" step="0.01" value="<?= $artwork['price'] ?>" required>

    <label>
      <input type="checkbox" name="available" <?= $artwork['available'] ? 'checked' : '' ?>> Available for Sale
    </label>

    <input type="submit" value="Update Artwork">
  </form>
</div>

</body>
</html>
