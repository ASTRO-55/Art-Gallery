<?php
session_start();

$conn = new mysqli("localhost", "root", "", "art_gallery");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Total sales count and earnings
$sales = $conn->query("
    SELECT COUNT(o.id) AS count, IFNULL(SUM(a.price), 0) AS total
    FROM orders o
    JOIN artworks a ON o.artwork_id = a.id
")->fetch_assoc();

// Today's earnings
$todaysEarnings = $conn->query("
    SELECT IFNULL(SUM(a.price), 0) AS total
    FROM orders o
    JOIN artworks a ON o.artwork_id = a.id
    WHERE DATE(o.order_date) = CURDATE()
")->fetch_assoc()['total'] ?? 0;

// Recent orders
$resOrders = $conn->query("
    SELECT 
        o.id, 
        a.title AS artwork_title, 
        ar.name AS artist_name, 
        u.name AS buyer_name, 
        a.price, 
        o.order_date, 
        o.address,
        o.status
    FROM orders o
    JOIN artworks a ON o.artwork_id = a.id
    JOIN users u ON o.user_id = u.id
    JOIN artists ar ON a.artist_id = ar.id
    ORDER BY o.order_date DESC 
    LIMIT 10
");


// Fetch all artists for management
$resArtists = $conn->query("SELECT * FROM artists ORDER BY id DESC");

// Fetch all artworks for management (joining artist name for display)
$resArtworks = $conn->query("
    SELECT aw.*, ar.name AS artist_name 
    FROM artworks aw
    JOIN artists ar ON aw.artist_id = ar.id
    ORDER BY aw.id DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Art Gallery</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
       
        .container {
            background-color: rgba(0, 0, 0, 0.6);
            max-width: 1100px;
            margin: 120px auto 40px;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(12px);
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 30px;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.07);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            width: 30%;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            min-width: 250px;
        }

        .card h3 {
            margin-bottom: 10px;
            color: #00e6e6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 40px;
        }

        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: rgba(0, 255, 255, 0.1);
            color: #00ffff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .btn {
            display: inline-block;
            background: #00e6e6;
            color: #000;
            padding: 6px 12px;
            margin: 0 4px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #00bcbc;
            color: #fff;
        }

        .btn-danger {
            background-color: #e60000;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #b30000;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
                align-items: center;
            }

            .navbar {
                flex-direction: column;
                align-items: center;
            }

            .navbar .nav-links {
                margin-top: 10px;
            }

            .card {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title">
    Art Gallery Admin - Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
</div>

    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_artists.php">Artists</a>
        <a href="manage_orders.php">Orders</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Admin Dashboard</h2>

    <div class="stats">
        <div class="card">
            <h3>Total Orders</h3>
            <p><?= $sales['count'] ?></p>
        </div>
        <div class="card">
            <h3>Total Earnings</h3>
            <p>₹<?= $sales['total'] ?></p>
        </div>
        <div class="card">
            <h3>Today's Earnings</h3>
            <p>₹<?= $todaysEarnings ?></p>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="recent-orders">
        <h3>Recent Orders</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Artwork</th>
                <th>Artist</th>
                <th>Buyer</th>
                <th>Price</th>
                <th>Date</th>
                
            </tr>
            <?php while($order = $resOrders->fetch_assoc()): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['artwork_title'] ?? '') ?></td>
                <td><?= htmlspecialchars($order['artist_name']) ?></td>
                <td><?= htmlspecialchars($order['buyer_name']) ?></td>
                <td>₹<?= $order['price'] ?></td>
                <td><?= $order['order_date'] ?></td>
                
                    



            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Manage Artists -->
    <div class="manage-artists">
        <div class="section-header">
            <h3>Manage Artists</h3>
            <a href="add_artist.php" class="btn">Add New Artist</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while($artist = $resArtists->fetch_assoc()): ?>
            <tr>
                <td><?= $artist['id'] ?></td>
                <td><?= htmlspecialchars($artist['name']) ?></td>
                <td><?= htmlspecialchars($artist['email']) ?></td>
                <td>
                    <a href="edit_artist.php?id=<?= $artist['id'] ?>" class="btn">Edit</a>
                    <a href="delete_artist.php?id=<?= $artist['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure to delete this artist?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Manage Artworks -->
    <div class="manage-artworks">
        <div class="section-header">
            <h3>Manage Artworks</h3>
            <a href="add_artwork.php" class="btn">Add New Artwork</a>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Artist</th>
                <th>Price</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
            <?php while($artwork = $resArtworks->fetch_assoc()): ?>
            <tr>
                <td><?= $artwork['id'] ?></td>
                <td><?= htmlspecialchars($artwork['title']) ?></td>
                <td><?= htmlspecialchars($artwork['artist_name']) ?></td>
                <td>₹<?= $artwork['price'] ?></td>
                <td><?= $artwork['available'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="edit_artwork.php?id=<?= $artwork['id'] ?>" class="btn">Edit</a>
                    <a href="delete_artwork.php?id=<?= $artwork['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure to delete this artwork?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
