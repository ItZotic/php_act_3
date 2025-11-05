<?php
require_once 'config.php';
session_start();

$error = "";
$success = "";
$db = Database::getInstance()->getConnection();

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = sanitizeInput($_POST["username"]);
        $password = $_POST["password"];

        if (empty($username) || empty($password)) {
            $error = "All fields are required!";
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(["username" => $username]);

            if ($stmt->fetch()) {
                $error = "Username already taken!";
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

                $success = "Account created! You can now log in.";
                header("Location: login.php");
                exit;
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "An error occurred. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <h2>Sign Up</h2>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>
    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    
    <form action="signup.php" method="POST">
      <label for="username">Username:</label>
      <input type="text" name="username" required>

      <label for="password">Password:</label>
      <input type="password" name="password" required>

      <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
