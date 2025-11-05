<?php
require_once 'config.php';
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";
$db = Database::getInstance()->getConnection();

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = sanitizeInput($_POST["username"]);
        $password = $_POST["password"];
        $confirmPassword = $_POST["confirm_password"] ?? "";

        // Validation
        if (empty($username) || empty($password)) {
            $error = "All fields are required!";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match!";
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(["username" => $username]);

            if ($stmt->fetch()) {
                $error = "Username already taken! Please choose another.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $db->prepare(
                    "INSERT INTO users (username, password, full_name, email, phone, skills, education, bio, created_at) 
                     VALUES (:username, :password, '', '', '', '', '', '', NOW())"
                );
                $stmt->execute([
                    "username" => $username,
                    "password" => $hashedPassword
                ]);

                $success = "Account created successfully! Redirecting to login...";
                header("refresh:2;url=login.php");
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred during registration. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <div class="avatar">📝</div>
    <h2>Create Account</h2>
    <p style="margin-bottom: 20px; color: #666;">Join us to create your portfolio</p>
    
    <?php if($error): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <?php if($success): ?>
      <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    
    <form action="signup.php" method="POST">
      <label for="username">Username:</label>
      <input type="text" name="username" id="username" required minlength="3" 
             placeholder="At least 3 characters" autocomplete="username">

      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required minlength="6" 
             placeholder="At least 6 characters" autocomplete="new-password">
      
      <label for="confirm_password">Confirm Password:</label>
      <input type="password" name="confirm_password" id="confirm_password" required minlength="6" 
             placeholder="Re-enter password" autocomplete="new-password">

      <button type="submit">Sign Up</button>
    </form>
    
    <p style="margin-top: 20px;">Already have an account? <a href="login.php" style="color: #4285f4; font-weight: 600;">Login here</a></p>
  </div>
</body>
</html>