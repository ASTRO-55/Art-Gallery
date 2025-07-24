<?php 
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User Login - Art Gallery</title>
  <link rel="stylesheet" href="../css/style.css" />
  <style>
  
    .login-box {
      max-width: 380px;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(12px);
      padding: 35px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0,0,0,0.8);
      position: absolute;
      top: 55%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    h2 {
      margin-bottom: 25px;
      text-align: center;
      color: #fff;
    }

    input[type="email"], input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 18px;
      border: none;
      border-radius: 8px;
      background: rgba(255,255,255,0.1);
      color: white;
    }

    input::placeholder {
      color: #aaa;
    }

    button {
      width: 100%;
      padding: 12px;
      background: linear-gradient(45deg, #00c6ff, #0072ff);
      border: none;
      border-radius: 8px;
      color: white;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: linear-gradient(45deg, #0072ff, #00c6ff);
    }

    .error-message {
      color: #ff7676;
      background: rgba(255, 0, 0, 0.15);
      padding: 10px;
      border-radius: 6px;
      text-align: center;
      margin-bottom: 15px;
      font-weight: bold;
    }

    .signup-link {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #ccc;
    }

    .signup-link a {
      color: #aaffaa;
      text-decoration: none;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .login-box {
        width: 90%;
        padding: 25px;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <a href="../index.php" class="logo">🎨 Art Gallery</a>
  <div class="nav-links">
    <a href="../admin/login.php">Admin Login</a>
    <a href="../artist/login.php">Artist Login</a>
  </div>
</div>

<!-- Login Box -->
<div class="login-box">
  <h2>User Login</h2>

  <?php if ($error): ?>
    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form action="process_login.php" method="post">
    <input type="email" name="email" placeholder="Email address" required autofocus />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Log In</button>
  </form>

  <div class="signup-link">
    <p>Don't have an account? <a href="register.php">Sign up here</a></p>
  </div>
</div>

</body>
</html>
