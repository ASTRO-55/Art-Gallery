<?php
session_start();
include '../config/db_connect.php'; // Assumes this creates $conn as a MySQLi object

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = "❌ Please enter username and password.";
    } else {
        // Use prepared statement (MySQLi style)
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($admin = $result->fetch_assoc()) {
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "❌ Invalid password.";
            }
        } else {
            $error = "❌ Admin not found.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login - Art Gallery</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #000 url('../assets/a man on the moon looking at Earth.png') no-repeat center top;
      background-size: cover;
      color: #fff;
      margin: 0;
    }

    .login-container {
      max-width: 400px;
      margin: 140px auto;
      background: rgba(255, 255, 255, 0.07);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.5);
      text-align: center;
      backdrop-filter: blur(8px);
    }

    .login-container h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }

    .login-container input {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
    }

    .login-container input[type="submit"] {
      background: linear-gradient(45deg, #6a11cb, #2575fc);
      color: white;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .login-container input[type="submit"]:hover {
      background: linear-gradient(45deg, #2575fc, #6a11cb);
    }

    .error {
      color: #ff6666;
      margin-bottom: 15px;
    }

           .navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }
  </style>
</head>
<body>

  <div class="navbar">
    <a href="../index.php" class="logo">🎨 Art Gallery</a>
    <div class="nav-links">
      <a href="logout.php" style="pointer-events: none; opacity: 0.5;">🚪 Logout</a>
    </div>
  </div>

  <div class="login-container">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="username" placeholder="Username" required autofocus>
      <input type="password" name="password" placeholder="Password" required>
      <input type="submit" value="Login">
    </form>
    
  </div>

</body>
</html>
