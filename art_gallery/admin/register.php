<?php
session_start();
include '../config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username already exists in `admin` table
    $check = $conn->prepare("SELECT id FROM admin WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username already taken.";
    } else {
        $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $success = "Admin registered successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="/art_gallery/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat center top;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 12px 30px;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: space-between;
            z-index: 1000;
        }
        .nav-title {
            font-size: 1.8rem;
        }
        .nav-links a {
            text-decoration: none;
            color: #fff;
            margin-left: 15px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            padding: 10px 15px;
            border-radius: 25px;
            font-weight: bold;
        }
        .form-container {
            background: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
            max-width: 450px;
            margin: 120px auto;
            color: #fff;
        }
        .form-container input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 6px;
            border: none;
        }
        .form-container input[type="submit"] {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .form-container input[type="submit"]:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
        }
        .message {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }
        .success { color: lightgreen; }
        .error { color: salmon; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="nav-title">Art Gallery Admin</div>
    <div class="nav-links">
        <a href="/art_gallery/admin/login.php">Login</a>
        <a href="/art_gallery/index.php">Home</a>
    </div>
</div>

<div class="form-container">
    <h2>Register Admin</h2>

    <?php if (!empty($success)): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="submit" value="Register Admin">
    </form>
</div>

</body>
</html>
