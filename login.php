<?php
require_once 'config.php';
session_start();

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
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <?php if($error): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
  <!-- Add this line -->
  <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
  
  <label for="username">Username:</label>
  <input type="text" name="username" required>

  <label for="password">Password:</label>
  <input type="password" name="password" required>

  <button type="submit">Login</button>
</form>
    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
  </div>
</body>
</html>
