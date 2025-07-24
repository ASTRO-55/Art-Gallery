<?php
session_start();
include '../config/db.php';

// Check if artist logged in
if (!isset($_SESSION['artist_id'])) {
    header('Location: ../login.php');
    exit();
}
$artist_id = $_SESSION['artist_id'];

// Fetch sales data for this artist
$sql = "
    SELECT s.sale_date, s.quantity, s.total_price, a.title 
    FROM sales s 
    JOIN artworks a ON s.artwork_id = a.id
    WHERE a.artist_id = ?
    ORDER BY s.sale_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}

// Prepare data for charts (sales per artwork title)
$sales_per_artwork = [];
foreach ($sales as $sale) {
    $title = $sale['title'];
    $sales_per_artwork[$title] = ($sales_per_artwork[$title] ?? 0) + $sale['total_price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Artist Sales - Art Gallery</title>
<link rel="stylesheet" href="/art_gallery/css/style.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat top center;
        background-size: cover;
        color: #fff;
        font-family: 'Segoe UI', sans-serif;
        padding: 20px;
    }
    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #6a11cb;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
    }
    th, td {
        padding: 10px;
        border-bottom: 1px solid #444;
        text-align: left;
    }
    th {
        background: linear-gradient(45deg, #6a11cb, #2575fc);
        color: white;
    }
    .chart-container {
        max-width: 700px;
        margin: 0 auto;
    }
</style>
</head>
<body>

<h1>Your Sales Overview</h1>

<?php if (count($sales) === 0): ?>
    <p style="text-align:center; font-size:1.2rem;">No sales yet.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Artwork</th>
            <th>Quantity</th>
            <th>Total Price (₹)</th>
            <th>Sale Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $sale): ?>
        <tr>
            <td><?php echo htmlspecialchars($sale['title']); ?></td>
            <td><?php echo intval($sale['quantity']); ?></td>
            <td>₹ <?php echo number_format($sale['total_price'], 2); ?></td>
            <td><?php echo date('d M Y, H:i', strtotime($sale['sale_date'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="chart-container">
    <canvas id="salesBarChart"></canvas>
</div>

<script>
const ctx = document.getElementById('salesBarChart').getContext('2d');
const salesData = <?php echo json_encode(array_values($sales_per_artwork)); ?>;
const labels = <?php echo json_encode(array_keys($sales_per_artwork)); ?>;

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Sales Amount (₹)',
            data: salesData,
            backgroundColor: 'rgba(106, 17, 203, 0.7)',
            borderColor: 'rgba(37, 117, 252, 1)',
            borderWidth: 2,
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(37, 117, 252, 0.9)'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php endif; ?>

</body>
</html>
