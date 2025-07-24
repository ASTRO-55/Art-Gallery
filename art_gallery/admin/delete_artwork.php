<?php
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['admin_id']) || !isset($_POST['artwork_id'])) {
    header('Location: manage_artists.php');
    exit;
}

$artwork_id = $_POST['artwork_id'];
$stmt = $conn->prepare("DELETE FROM artworks WHERE id = ?");
$stmt->execute([$artwork_id]);

header("Location: manage_artists.php");
exit;
