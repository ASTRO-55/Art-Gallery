<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

// Handle update
$update_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $new_name, $new_email, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        $update_message = "Profile updated successfully.";
        $name = $new_name;
        $email = $new_email;
    } else {
        $update_message = "Error updating profile.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
            .navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }

        .container {
            background-color: rgba(0, 0, 0, 0.6);
            max-width: 500px;
            margin: 80px auto 40px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px #000;
            backdrop-filter: blur(12px);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type=text], input[type=email], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            background: #222;
            color: #fff;
            border: 1px solid #444;
            border-radius: 10px;
        }

        input[type=submit] {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
        }

        .message {
            text-align: center;
            margin-bottom: 10px;
            color: lightgreen;
        }

        a.back-link {
            color: #ccc;
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="../index.php" class="logo">🎨 Art Gallery</a>
    <div class="nav-links">
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>User Profile</h2>

    <?php if ($update_message): ?>
        <div class="message"><?= htmlspecialchars($update_message) ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="password">

        <input type="submit" value="Update Profile">
    </form>

    
</div>

</body>
</html>
