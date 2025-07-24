<?php
session_start();
include '../config/db_connect.php';

$name = $email = $password = $confirm_password = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Basic validation
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM artists WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO artists (name, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $name, $email, $hashed_password);
            if ($insert->execute()) {
                $_SESSION['artist_id'] = $insert->insert_id;
                $_SESSION['artist_name'] = $name;
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Artist Registration</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        
        .register-container {
            max-width: 450px;
            margin: 80px auto;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0,0,0,0.7);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #eee;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            outline: none;
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        input::placeholder {
            color: #bbb;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            transition: 0.3s ease;
        }
        .btn-register:hover {
            background: linear-gradient(45deg, #2575fc, #6a11cb);
            cursor: pointer;
        }
        .error {
            background: rgba(255, 0, 0, 0.2);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #ff9e9e;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #ccc;
        }
        .login-link a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
    <a href="../index.php" class="logo">🎨 Art Gallery</a>
    <div class="nav-links">
      <a href="login.php" class="button">Login</a>
      <a href="logout.php" style="pointer-events: none; opacity: 0.5;">🚪 Logout</a>
    </div>
  </div>
    <div class="register-container">
        <h2>Artist Registration</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" class="btn-register">Register</button>
        </form>

        <div class="login-link">
            Already registered? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>
