<?php
session_start();

$host = "localhost";
$db   = "portfolio_db";
$user = "postgres";  
$pass = "1234567890";

$error = "";
$success = "";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        if ($username === "" || $password === "") {
            $error = "All fields are required!";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(["username" => $username]);

            if ($stmt->fetch()) {
                $error = "Username already taken!";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, skills, education, bio) VALUES (:username, :password, '', '', '', '', '', '')");
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
    $error = "Database connection failed: " . $e->getMessage();
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
