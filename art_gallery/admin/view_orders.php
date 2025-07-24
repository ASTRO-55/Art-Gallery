<?php
session_start();
include '../config/db.php';

// Check if admin logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch all sales with artwork & artist info
$sql = "
    SELECT s.sale_date, s.quantity, s.total_price, a.title, ar.name AS artist_name 
    FROM sales s
    JOIN artworks a ON s.artwork_id = a.id
    JOIN artists ar ON a.artist_id = ar.id
    ORDER BY s.sale_date DESC
";
$result = $conn->query($sql);

$sales = [];
while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}

// Prepare data for charts
$sales_per_artist = [];
$sales_per_month = [];

foreach ($sales as $sale) {
    // Sum sales per artist
    $artist = $sale['artist_name'];
    $sales_per_artist[$artist] = ($sales_per_artist[$artist] ?? 0) + $sale['total_price'];

    // Sum sales per month (YYYY-MM)
    $month = date('Y-m', strtotime($sale['sale_date']));
    $sales_per_month[$month] = ($sales_per_month[$month] ?? 0) + $sale['total_price'];
}

// Sort sales per month chronologically
ksort($sales_per_month);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Sales Overview - Art Gallery</title>
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
        max-width: 800px;
        margin: 0 auto 50px;
    }
    .chart-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        gap: 40px;
    }
    .chart-box {
        flex: 1 1 350px;
        background: rgba(0,0,0,0.6);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.8);
    }
</style>
</head>
<body>

<h1>Overall Sales Overview</h1>

<?php if (count($sales) === 0): ?>
    <p style="text-align:center; font-size:1.2rem;">No sales yet.</p>
<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Artwork</th>
            <th>Artist</th>
            <th>Quantity</th>
            <th>Total Price (₹)</th>
            <th>Sale Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $sale): ?>
        <tr>
            <td><?php echo htmlspecialchars($sale['title']); ?></td>
            <td><?php echo htmlspecialchars($sale['artist_name']); ?></td>
            <td><?php echo intval($sale['quantity']); ?></td>
            <td>₹ <?php echo number_format($sale['total_price'], 2); ?></td>
            <td><?php echo date('d M Y, H:i', strtotime($sale['sale_date'])); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="chart-wrapper">

    <div class="chart-box">
        <h3 style="text-align:center; color:#6a11cb;">Sales per Artist</h3>
        <canvas id="salesPieChart"></canvas>
    </div>

    <div class="chart-box">
        <h3 style="text-align:center; color:#6a11cb;">Monthly Sales (₹)</h3>
        <canvas id="salesLineChart"></canvas>
    </div>

</div>

<script>
const pieCtx = document.getElementById('salesPieChart').getContext('2d');
const pieData = {
    labels: <?php echo json_encode(array_keys($sales_per_artist)); ?>,
    datasets: [{
        data: <?php echo json_encode(array_values($sales_per_artist)); ?>,
        backgroundColor: [
            '#6a11cb', '#2575fc', '#e91e63', '#ff9800', '#00bcd4', '#4caf50', '#ffc107'
        ],
        borderWidth: 1,
        hoverOffset: 15
    }]
};
new Chart(pieCtx, {
    type: 'doughnut',
    data: pieData,
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: {color: 'white'} }
        }
    }
});

const lineCtx = document.getElementById('salesLineChart').getContext('2d');
const lineData = {
    labels: <?php echo json_encode(array_keys($sales_per_month)); ?>,
    datasets: [{
        label: 'Sales (₹)',
        data: <?php echo json_encode(array_values($sales_per_month)); ?>,
        fill: true,
        borderColor: '#6a11cb',
        backgroundColor: 'rgba(106, 17, 203, 0.4)',
        tension: 0.3,
        pointBackgroundColor: '#2575fc',
        pointRadius: 6
    }]
};
new Chart(lineCtx, {
    type: 'line',
    data: lineData,
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        },
        plugins: {
            legend: { labels: {color: 'white'} }
        }
    }
});
</script>

<?php endif; ?>

</body>
</html>
