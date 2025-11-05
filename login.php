<?php
require_once 'config.php';
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$db = Database::getInstance()->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Validation
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please refresh and try again.";
    } else {
        checkRateLimit('login_' . $_SERVER['REMOTE_ADDR']);
        
        $username = sanitizeInput($_POST["username"]);
        $password = $_POST["password"];
        
        if (empty($username) || empty($password)) {
            $error = "All fields are required!";
        } else {
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->execute(["username" => $username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user["password"])) {
                    session_regenerate_id(true);
                    
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["username"] = $user["username"];
                    
                    // Update last_login
                    $updateLogin = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $updateLogin->execute(["id" => $user["id"]]);
                    
                    unset($_SESSION['rate_limit_login_' . $_SERVER['REMOTE_ADDR']]);
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Incorrect username or password.";
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = "An error occurred. Please try again later.";
            }
        }
    }
} 
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Portfolio System</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .browse-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      padding: 12px;
      background: #f0f0f0;
      border-radius: 8px;
      text-decoration: none;
      color: #2163aa;
      font-weight: 500;
      transition: background 0.3s;
    }
    .browse-link:hover {
      background: #e0e0e0;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="avatar">👤</div>
    <h2>Welcome Back</h2>
    <p style="margin-bottom: 20px; color: #666;">Please login to access your portfolio</p>
    
    <?php if($error): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
      
      <label for="username">Username:</label>
      <input type="text" name="username" id="username" required autocomplete="username">

      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required autocomplete="current-password">

      <button type="submit">Login</button>
    </form>
    
    <p style="margin-top: 20px;">Don't have an account? <a href="signup.php" style="color: #4285f4; font-weight: 600;">Sign Up</a></p>
    
    <a href="browse.php" class="browse-link">🔍 Browse Student Portfolios</a>
  </div>
</body>
</html>
