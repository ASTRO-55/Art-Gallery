<?php
session_start();

if (!isset($_SESSION['buyer_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$artwork_id = intval($_POST['artwork_id']);

if (isset($_SESSION['cart']) && in_array($artwork_id, $_SESSION['cart'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($id) => $id != $artwork_id);
}

header("Location: cart.php");
exit();
