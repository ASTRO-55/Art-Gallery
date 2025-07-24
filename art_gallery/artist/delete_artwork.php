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
$artwork_id = $_GET['id'] ?? null;

if ($artwork_id) {
    $stmt = $conn->prepare("DELETE FROM artworks WHERE id = ? AND artist_id = ?");
    $stmt->bind_param("ii", $artwork_id, $artist_id);
    $stmt->execute();
}

header("Location: dashboard.php");
exit();
?>
