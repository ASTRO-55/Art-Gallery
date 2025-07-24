<?php
session_start();
include '../config/db_connect.php';

$email = $password = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM artists WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION["artist_id"] = $id;
                $_SESSION["artist_name"] = $name;
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Artist Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
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
        input[type="email"], input[type="password"] {
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
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            transition: 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #ff4b2b, #ff416c);
            cursor: pointer;
        }
        .error {
            background: rgba(255, 0, 0, 0.2);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            color: #ff9e9e;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #ccc;
        }
        .register-link a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="../index.php" class="logo">🎨 Art Gallery</a>
        <div class="nav-links">
            <?php if (isset($_SESSION['artist_id'])): ?>
                <a href="logout.php">🚪 Logout</a>
            <?php else: ?>
                <a href="logout.php" style="pointer-events: none; opacity: 0.5;">🚪 Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="login-container">
        <h2>Artist Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="register-link">
            New artist? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
