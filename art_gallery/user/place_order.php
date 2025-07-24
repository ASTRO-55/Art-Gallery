<?php
session_start();
include '../includes/db_connect.php'; // assumes $conn is defined here

// Check if buyer is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_id'])) {
    $user_id = $_SESSION['user_id'];
    $artwork_id = intval($_POST['artwork_id']);

    // Fetch artwork details
    $stmt = $conn->prepare("SELECT * FROM artworks WHERE id = ?");
    $stmt->bind_param("i", $artwork_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artwork = $result->fetch_assoc();

    if (!$artwork) {
        echo "Invalid artwork.";
        exit();
    }

    // Check if already sold (you can add a 'sold' flag in DB if needed)
    // For now, proceed with order

    // Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, artwork_id, status, order_date) VALUES (?, ?, 'Placed', NOW())");
    $stmt->bind_param("ii", $user_id, $artwork_id);
    if ($stmt->execute()) {
        echo "<script>alert('Order placed successfully!'); window.location.href='cart.php';</script>";
    } else {
        echo "<script>alert('Order failed. Try again.'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
