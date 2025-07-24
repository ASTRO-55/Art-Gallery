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

// Get artworks by artist
$resArtworks = $conn->query("
    SELECT * FROM artworks 
    WHERE artist_id = $artist_id
    ORDER BY id DESC
");

// Calculate total earnings for this artist
$resEarnings = $conn->query("
    SELECT SUM(a.price) AS total 
    FROM orders o 
    JOIN artworks a ON o.artwork_id = a.id 
    WHERE a.artist_id = $artist_id
")->fetch_assoc();

$totalEarnings = $resEarnings['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Artist Dashboard - Art Gallery</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    .container {
      background-color: rgba(0, 0, 0, 0.6);
      max-width: 1000px;
      margin: 120px auto;
      padding: 30px;
      border-radius: 20px;
      backdrop-filter: blur(10px);
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    h2, h3 {
      text-align: center;
      margin-bottom: 20px;
    }

    .earnings {
      text-align: center;
      font-size: 1.2rem;
      color: #00ffcc;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255, 255, 255, 0.05);
      color: #fff;
    }

    th, td {
      padding: 10px 12px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    th {
      background-color: rgba(0, 255, 255, 0.1);
    }

    tr:last-child td {
      border-bottom: none;
    }

    .btn {
      background: #00e6e6;
      color: #000;
      padding: 6px 10px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      font-size: 0.9rem;
    }

    .btn:hover {
      background-color: #00bcbc;
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
  <h2>Artist Dashboard</h2>

  <div class="earnings">
    <strong>Total Earnings:</strong> ₹<?= $totalEarnings ?>
  </div>

  <h3>Your Artworks</h3>
  <table>
    <tr>
      <th>Title</th>
      <th>Description</th>
      <th>Price</th>
      <th>Available</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $resArtworks->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['title']) ?></td>
      <td><?= htmlspecialchars($row['description']) ?></td>
      <td>₹<?= $row['price'] ?></td>
      <td><?= $row['available'] ? 'Yes' : 'No' ?></td>
      <td>
        <a class="btn" href="edit_artwork.php?id=<?= $row['id'] ?>">Edit</a>
        <a class="btn" style="background:#e60000;color:white;" href="delete_artwork.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

</body>
</html>
