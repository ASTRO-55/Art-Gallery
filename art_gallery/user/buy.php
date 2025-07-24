<?php
session_start();
include '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /art_gallery/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get artwork ID from GET parameter
if (!isset($_GET['art_id']) || empty($_GET['art_id'])) {
    echo "Artwork not specified.";
    exit();
}

$art_id = intval($_GET['art_id']);

// Fetch artwork details
$stmt = $conn->prepare("SELECT * FROM artworks WHERE id = ?");
$stmt->bind_param("i", $art_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Artwork not found.";
    exit();
}

$artwork = $result->fetch_assoc();

$buy_success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate address inputs
    $area = trim($_POST['area'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if ($area === '' || $pincode === '' || $city === '') {
        $error = "Please fill in all address fields.";
    } else {
        // Here you would add code to save order/purchase info in DB
        // For now, just show success message
        $buy_success = true;
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
            background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat top center;
            background-size: cover;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 12px 30px;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
        }
        .nav-title {
            font-size: 1.8rem;
            color: #fff;
        }
        .nav-links a {
            color: #fff;
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            padding: 8px 15px;
            border-radius: 30px;
            transition: 0.3s ease;
        }
        .nav-links a:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
        }
        .container {
            max-width: 900px;
            margin: 120px auto 50px;
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.8);
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .artwork-image {
            flex: 1 1 400px;
            max-width: 450px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.7);
        }
        .artwork-image img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
        }
        .artwork-details {
            flex: 1 1 350px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .artwork-details h2 {
            margin-top: 0;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .artwork-details p.description {
            font-size: 1rem;
            margin-bottom: 15px;
            min-height: 70px;
        }
        .artwork-details p.price {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: #6a11cb;
        }
        form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 1rem;
        }
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
        }
        form input[type="submit"] {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            padding: 12px;
            border-radius: 30px;
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        form input[type="submit"]:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
        }
        .success {
            color: #0f0;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }
        .error {
            color: #f66;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 120px 20px 50px;
            }
            .artwork-image, .artwork-details {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title">Art Gallery</div>
    <div class="nav-links">
        <a href="/art_gallery/index.php">Home</a>
        <a href="/art_gallery/user/dashboard.php">Dashboard</a>
        <a href="/art_gallery/user/logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="artwork-image">
        <img src="/art_gallery/uploads/<?php echo htmlspecialchars($artwork['image']); ?>" alt="<?php echo htmlspecialchars($artwork['title']); ?>">
    </div>

    <div class="artwork-details">
        <h2><?php echo htmlspecialchars($artwork['title']); ?></h2>
        <p class="description"><?php echo nl2br(htmlspecialchars($artwork['description'])); ?></p>
        <p class="price">₹ <?php echo number_format($artwork['price'], 2); ?></p>

        <?php if ($buy_success): ?>
            <p class="success">Thank you for your purchase! Your order is being processed.</p>
        <?php else: ?>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="post" action="">
                <label for="area">Area:</label>
                <input type="text" id="area" name="area" required />

                <label for="pincode">Pincode:</label>
                <input type="number" id="pincode" name="pincode" required />

                <label for="city">City:</label>
                <input type="text" id="city" name="city" required />

                <input type="submit" value="Buy Now" />
            </form>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
