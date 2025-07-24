<?php 
session_start();

// Ensure only logged-in artists can access this page
if (!isset($_SESSION['artist_id'])) {
    header("Location: login.php");
    exit();
}

// DB Connection
$conn = new mysqli("localhost", "root", "", "art_gallery");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $type = trim($_POST['type']);
    $artist_id = $_SESSION['artist_id'];

    // File upload check
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $image_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Allow only specific image formats
        if (in_array($image_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $stmt = $conn->prepare("INSERT INTO artworks (artist_id, title, description, price, image, type, available) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->bind_param("issdss", $artist_id, $title, $description, $price, $image_name, $type);
                if ($stmt->execute()) {
                    $message = "✅ Artwork added successfully!";
                } else {
                    $message = "❌ Database error: " . $stmt->error;
                }
            } else {
                $message = "❌ Failed to upload image.";
            }
        } else {
            $message = "❌ Invalid image format. Only JPG, JPEG, PNG, and GIF allowed.";
        }
    } else {
        $message = "❌ Please upload an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Artwork</title>
    <link rel="stylesheet" href="/art_gallery/css/style.css">
    <style>
        
        .form-container {
            max-width: 600px;
            margin: 120px auto;
            background: rgba(20, 20, 20, 0.75);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label {
            margin-top: 15px;
            font-weight: bold;
        }

        .form-container input,
        .form-container textarea,
        .form-container select {
            margin-top: 5px;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }

        .form-container input[type="submit"] {
            margin-top: 25px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            margin-top: 20px;
            text-align: center;
            font-size: 1rem;
            padding: 10px;
            border-radius: 10px;
            background-color: rgba(255,255,255,0.08);
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title">
        🎨 Add Artwork
    </div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_artwork.php">Add Artwork</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="form-container">
    <h2>Upload New Artwork</h2>
    <?php if (!empty($message)) echo '<div class="message">' . htmlspecialchars($message) . '</div>'; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="title">Artwork Title</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4" required></textarea>

        <label for="price">Price (₹)</label>
        <input type="number" name="price" id="price" step="0.01" required>

        <label for="type">Type</label>
        <select name="type" id="type" required>
            <option value="">Select Type</option>
            <option value="Painting">Painting</option>
            <option value="Drawing">Drawing</option>
            <option value="Photography">Photography</option>
            <option value="Digital Art">Digital Art</option>
            <option value="Other">Other</option>
        </select>

        <label for="image">Artwork Image</label>
        <input type="file" name="image" id="image" accept="image/*" required>

        <input type="submit" value="Add Artwork">
    </form>
</div>

</body>
</html>
