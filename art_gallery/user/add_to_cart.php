<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_id'])) {
    $user_id = $_SESSION['user_id'];
    $artwork_id = intval($_POST['artwork_id']);

    // Check if artwork is available
    $stmt = $conn->prepare("SELECT id FROM artworks WHERE id = ? AND available = 1");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Artwork not available']);
        exit;
    }

    // Prevent duplicate entries
    $check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND artwork_id = ?");
    $check->bind_param("ii", $user_id, $artwork_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO cart (user_id, artwork_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $artwork_id);
        $insert->execute();
    }

    // Count items in user's cart
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_stmt->bind_result($cart_count);
    $count_stmt->fetch();

    echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
