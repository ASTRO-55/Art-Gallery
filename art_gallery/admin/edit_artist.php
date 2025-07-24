<?php
session_start();
include('../includes/db.php');

$id = $_GET['id'] ?? 0;
$query = "SELECT * FROM artists WHERE id=$id";
$result = mysqli_query($conn, $query);
$artist = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $artist['password'];

    $update = "UPDATE artists SET name='$name', email='$email', password='$password' WHERE id=$id";
    mysqli_query($conn, $update);
    header('Location: manage_artists.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Artist</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container glass-card mt-5">
    <h2 class="text-center mb-4">Edit Artist</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-white">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($artist['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($artist['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label text-white">Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Update Artist</button>
        <a href="manage_artists.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
