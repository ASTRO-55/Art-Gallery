<?php
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['artist_id'])) {
    header('Location: manage_artists.php');
    exit;
}

$artist_id = $_POST['artist_id'];

// Delete related artworks first
$stmt1 = $conn->prepare("DELETE FROM artworks WHERE artist_id = ?");
$stmt1->execute([$artist_id]);

// Then delete artist
$stmt2 = $conn->prepare("DELETE FROM artists WHERE id = ?");
$stmt2->execute([$artist_id]);

header("Location: manage_artists.php");
exit;
