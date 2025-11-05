<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$host = "localhost";
$db   = "portfolio_db";
$user = "postgres";
$pass = "1234567890";

$error = '';
$userData = [];

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT username, full_name, email, phone, skills, education, bio FROM users WHERE id = :id");
    $stmt->execute(["id" => $_SESSION["user_id"]]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        throw new RuntimeException("Unable to load your resume details.");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Resume</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Private Resume View</h1>
    <p>Only visible to you while logged in.</p>
  </header>

  <div class="resume-container">
    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
      <h2 class="center-text"><?php echo htmlspecialchars($userData['full_name']); ?></h2>
      <p class="center-text"><?php echo htmlspecialchars($userData['bio']); ?></p>
      <p class="center-text"><?php echo htmlspecialchars($userData['email']); ?> | <?php echo htmlspecialchars($userData['phone']); ?></p>

      <hr>

      <h3>Skills</h3>
      <p><?php echo nl2br(htmlspecialchars($userData['skills'])); ?></p>

      <h3>Education</h3>
      <p><?php echo nl2br(htmlspecialchars($userData['education'])); ?></p>

      <div class="center-text">
        <a href="dashboard.php" class="btn">Edit Resume</a>
        <a href="public_resume.php?id=<?php echo urlencode($_SESSION['user_id']); ?>" class="btn secondary" target="_blank">Share Public Link</a>
        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
