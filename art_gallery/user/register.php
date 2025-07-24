<?php
session_start();
require '../config/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm_password) {
        $error = "Please fill all the fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered. Try logging in.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->execute([$name, $email, $hashed_password]);
            $success = "Registration successful! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - Art Gallery</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    .navbar .logo {
      font-size: 1.2rem;
      font-weight: bold;
      color: #fff;
      text-decoration: none;
    }

    .register-wrapper {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding-top: 70px; /* to push content below navbar */
    }

    .register-box {
      backdrop-filter: blur(15px);
      background: rgba(0, 0, 0, 0.6);
      border-radius: 15px;
      padding: 35px 30px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.8);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 1.8rem;
    }

    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: none;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    input::placeholder {
      color: #ccc;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #2980b9;
      border: none;
      border-radius: 8px;
      color: white;
      font-size: 1rem;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #1a5d8f;
    }

    .message {
      margin-bottom: 15px;
      text-align: center;
    }

    .error-message {
      color: #ff4c4c;
      font-weight: bold;
    }

    .success-message {
      color: #aaffaa;
    }

    .login-link {
      text-align: center;
      margin-top: 10px;
    }

    .login-link a {
      color: #aaffaa;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="navbar">
  <a href="../index.php" class="logo">🎨 Art Gallery</a>  <div class="nav-links">
        <a href="login.php">Login</a>
    </div>
</div>

<div class="register-wrapper">
  <div class="register-box">
    <h2>Create Account</h2>

    <?php if ($error): ?>
      <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="message success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" action="register.php">
      <input type="text" name="name" placeholder="Full Name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" />
      <input type="email" name="email" placeholder="Email address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
      <input type="password" name="password" placeholder="Password" required />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit">Register</button>
    </form>

    <div class="login-link">
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
  </div>
</div>

</body>
</html>
