<?php
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get all artists
$artists = [];
$artists_stmt = $conn->prepare("SELECT id, name, email FROM artists");
$artists_stmt->execute();
$artists_result = $artists_stmt->get_result();
while ($row = $artists_result->fetch_assoc()) {
    $artists[] = $row;
}

// Helper functions
function getArtistEarnings($conn, $artist_id) {
    $stmt = $conn->prepare("
        SELECT SUM(a.price) AS earnings
        FROM orders o
        JOIN artworks a ON o.artwork_id = a.id
        WHERE a.artist_id = ?
    ");
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $stmt->bind_result($earnings);
    $stmt->fetch();
    $stmt->close();
    return $earnings ?? 0;
}

function getArtistOrderCount($conn, $artist_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS order_count
        FROM orders o
        JOIN artworks a ON o.artwork_id = a.id
        WHERE a.artist_id = ?
    ");
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $stmt->bind_result($order_count);
    $stmt->fetch();
    $stmt->close();
    return $order_count ?? 0;
}

function getArtistArtworks($conn, $artist_id) {
    $stmt = $conn->prepare("SELECT * FROM artworks WHERE artist_id = ?");
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artworks = [];
    while ($row = $result->fetch_assoc()) {
        $artworks[] = $row;
    }
    $stmt->close();
    return $artworks;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Artists</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Inside your <head> tag -->
<style>

    .container {
        max-width: 900px;
        margin: 60px auto;
        padding: 20px;
        background: rgba(25, 25, 25, 0.85);
        border-radius: 12px;
        box-shadow: 0 0 8px rgba(0,0,0,0.4);
    }

    h1 {
        text-align: center;
        font-size: 22px;
        margin-bottom: 30px;
    }

    .artist-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .artist-card h2 {
        font-size: 16px;
        margin: 0 0 8px 0;
    }

    .artist-card p {
        margin: 3px 0;
    }

    .artworks-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 8px;
    }

    .artwork {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 6px;
        padding: 8px;
        width: 140px;
        text-align: center;
        font-size: 12px;
        color: #ccc;
    }

    .artwork img {
        max-width: 100%;
        height: 90px;
        border-radius: 4px;
        object-fit: cover;
        margin-bottom: 6px;
    }

    .btn-delete {
        background: #f44336;
        color: white;
        padding: 4px 10px;
        margin-top: 6px;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
    }

    .btn-delete:hover {
        background: #d32f2f;
    }

    .btn-inline {
        display: inline-block;
        margin-left: 6px;
    }

    form.inline-form {
        display: inline;
    }

    #earningsChartContainer {
        max-width: 700px;
        margin: 40px auto;
        background: rgba(0,0,0,0.65);
        padding: 15px;
        border-radius: 10px;
    }

    canvas {
        width: 100% !important;
        height: 300px !important;
    }

    
</style>

</head>
<body>
<div class="navbar">
    <div class="nav-title">Art Gallery Admin</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_orders.php">Orders</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h1>Manage Artists</h1>

    <?php foreach ($artists as $artist): ?>
        <div class="artist-card">
            <h2><?= htmlspecialchars($artist['name']) ?> (<?= htmlspecialchars($artist['email']) ?>)</h2>
            <p><strong>Total Orders:</strong> <?= getArtistOrderCount($conn, $artist['id']) ?></p>
            <p><strong>Total Earnings:</strong> ₹<?= getArtistEarnings($conn, $artist['id']) ?></p>
            <?php $artworks = getArtistArtworks($conn, $artist['id']); ?>
            <p><strong>Total Artworks:</strong> <?= count($artworks) ?></p>

            <h3>Artworks:</h3>
            <?php if (empty($artworks)): ?>
                <p>No artworks found.</p>
            <?php else: ?>
                <div class="artworks-container">
                    <?php foreach ($artworks as $art): ?>
                        <div class="artwork">
                            <?php if (!empty($art['image']) && file_exists('../uploads/' . $art['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($art['image']) ?>" alt="<?= htmlspecialchars($art['title']) ?>">
                            <?php else: ?>
                                <img src="../assets/no-image.png" alt="No Image">
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($art['title']) ?></strong>
                            <p>₹<?= $art['price'] ?></p>
                            <p><?= $art['available'] ? '<span style="color:#4caf50;">Available</span>' : '<span style="color:#f44336;">Sold</span>' ?></p>
                            <form method="POST" action="delete_artwork.php" class="inline-form" onsubmit="return confirm('Delete this artwork?')">
                                <input type="hidden" name="artwork_id" value="<?= $art['id'] ?>">
                                <button type="submit" class="btn-delete btn-inline">🗑️</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="delete_artist.php" onsubmit="return confirm('Delete this artist and all artworks?')">
                <input type="hidden" name="artist_id" value="<?= $artist['id'] ?>">
                <button type="submit" class="btn-delete">Delete Artist</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- Chart Container -->
<div id="earningsChartContainer">
    <h2>Artist Earnings Chart</h2>
    <canvas id="earningsChart" width="900" height="400"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const artists = <?= json_encode(array_map(fn($a) => $a['name'], $artists)) ?>;
    const earnings = <?= json_encode(array_map(fn($a) => getArtistEarnings($conn, $a['id']), $artists)) ?>;

    const ctx = document.getElementById('earningsChart').getContext('2d');
    const earningsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: artists,
            datasets: [{
                label: 'Earnings (₹)',
                data: earnings,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#eee' }
                },
                x: {
                    ticks: { color: '#eee' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#eee' }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => `₹${ctx.parsed.y}`
                    }
                }
            }
        }
    });
</script>
</body>
</html>
