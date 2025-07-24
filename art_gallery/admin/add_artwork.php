<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = false;

// Fetch artists for dropdown
$artists = [];
$result = $conn->query("SELECT id, name FROM artists ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $artists[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artist_id = intval($_POST['artist_id']);
    $title = trim($_POST['title']);
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (empty($title) || empty($description) || $price <= 0 || !$artist_id || empty($type)) {
        $error = "Please fill all fields correctly.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please upload a valid image.";
    } else {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_info = getimagesize($image_tmp);

        if ($image_info === false) {
            $error = "Uploaded file is not a valid image.";
        } else {
            $image_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid('art_', true) . '.' . $image_ext;
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($image_tmp, $upload_path)) {
               $stmt = $conn->prepare("INSERT INTO artworks (artist_id, title, description, price, image, is_available, art_type) VALUES (?, ?, ?, ?, ?, 1, ?)");

                $stmt->bind_param("isssds", $artist_id, $title, $type, $description, $price, $new_filename);

                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Database error: " . $stmt->error;
                    unlink($upload_path);
                }
                $stmt->close();
            } else {
                $error = "Failed to upload image.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Artwork - Admin</title>
    <link rel="stylesheet" href="/art_gallery/css/style.css" />
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat center top;
            background-size: cover;
            min-height: 100vh;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 12px 20px;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999;
        }

        .nav-title {
            font-size: 1.8rem;
            color: #fff;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .button {
            padding: 10px 18px;
            border-radius: 20px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: #fff;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .button:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
        }

        .form-container {
            background: rgba(0, 0, 0, 0.65);
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: 120px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        .form-container input,
        .form-container select,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
        }

        .form-container input[type="submit"] {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            font-weight: bold;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
        }

        .success { color: #0f0; text-align: center; }
        .error { color: #f44; text-align: center; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title">Admin Panel</div>
    <div class="nav-links">
        <a href="/art_gallery/admin/dashboard.php" class="button">Dashboard</a>
        <a href="/art_gallery/index.php" class="button">Home</a>
        <a href="/art_gallery/admin/logout.php" class="button">Logout</a>
    </div>
</div>

<div class="form-container">
    <h2>Add New Artwork</h2>

    <?php if ($success): ?>
        <p class="success">✅ Artwork added successfully!</p>
    <?php elseif (!empty($error)): ?>
        <p class="error">❌ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <select name="artist_id" required>
            <option value="">-- Select Artist --</option>
            <?php foreach ($artists as $artist): ?>
                <option value="<?= $artist['id'] ?>"><?= htmlspecialchars($artist['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="title" placeholder="Artwork Title" required>

        <select name="type" required>
            <option value="">-- Select Art Type --</option>
            <option value="Painting">Painting</option>
            <option value="Sculpture">Sculpture</option>
            <option value="Photography">Photography</option>
            <option value="Digital Art">Digital Art</option>
            <option value="Mixed Media">Mixed Media</option>
            <option value="Other">Other</option>
        </select>

        <textarea name="description" placeholder="Description" rows="4" required></textarea>
        <input type="number" name="price" step="0.01" placeholder="Price in ₹" required>
        <input type="file" name="image" accept="image/*" required>
        <input type="submit" value="Add Artwork">
    </form>
</div>

</body>
</html>
