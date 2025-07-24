<?php
session_start();
require '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Please fill in both email and password.";
    header('Location: login.php');
    exit;
}

// Get user from DB by email
$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['login_error'] = "Invalid email or password.";
    header('Location: login.php');
    exit;
}

// Bind result variables and fetch
$stmt->bind_result($id, $name, $fetched_email, $hashed_password);
$stmt->fetch();

// Verify password
if (!password_verify($password, $hashed_password)) {
    $_SESSION['login_error'] = "Invalid email or password.";
    header('Location: login.php');
    exit;
}

// Login success: set session variables
$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $name;

// Redirect to saved page or homepage
$redirect = $_SESSION['redirect_after_login'] ?? '/art_gallery/index.php';
unset($_SESSION['redirect_after_login']);
header("Location: $redirect");
exit;
