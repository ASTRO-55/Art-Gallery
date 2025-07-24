<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid artwork ID.");
}

$artwork_id = $_GET['id'];
$success = false;
$error = '';

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_artist_id = $_POST['artist_id'];
    $new_title = $_POST['title'];
    $new_description = $_POST['description'];
    $new_price = $_POST['price'];
    $new_image = $_POST['image'];

    try {
        $stmt = $conn->prepare("UPDATE artworks SET artist_id = ?, title = ?, description = ?, price = ?, image = ? WHERE id = ?");
        $success = $stmt->execute([$new_artist_id, $new_title, $new_description, $new_price, $new_image, $artwork_id]);

        if (!$success) {
            $error = "Database error: " . implode(" | ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch current artwork details
try {
    $stmt = $conn->prepare("SELECT artist_id, title, description, price, image FROM artworks WHERE id = ?");
    $stmt->execute([$artwork_id]);
    $artwork = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artwork) {
        die("Artwork not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$artist_id = $artwork['artist_id'];
$title = $artwork['title'];
$description = $artwork['description'];
$price = $artwork['price'];
$image = $artwork['image'];

// Fetch all artists for the dropdown
try {
    $artist_stmt = $conn->query("SELECT id, name FROM artists");
    $artists = $artist_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch artists: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Artwork</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-form-container {
            max-width: 600px;
            margin: 80px auto;
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 15px;
            color: #fff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }
        label, input, textarea, select {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        input, textarea, select {
            padding: 8px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        input[type="submit"] {
            background-color: #5cb85c;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #3c763d;
        }
        .error {
            background-color: #a94442;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="edit-form-container">
        <h2>Edit Artwork</h2>

        <?php if ($success): ?>
            <div class="message success">Artwork updated successfully.</div>
        <?php elseif ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="artist_id">Artist</label>
            <select name="artist_id" required>
                <?php foreach ($artists as $artist): ?>
                    <option value="<?= $artist['id'] ?>" <?= $artist['id'] == $artist_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($artist['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>

            <label for="description">Description</label>
            <textarea name="description" required><?= htmlspecialchars($description) ?></textarea>

            <label for="price">Price</label>
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" required>

            <label for="image">Image Filename</label>
            <input type="text" name="image" value="<?= htmlspecialchars($image) ?>" required>

            <input type="submit" value="Update Artwork">
        </form>
    </div>
</body>
</html>
