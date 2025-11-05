<?php
$host = "localhost";
$db   = "portfolio_db";
$user = "postgres";
$pass = "1234567890";

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userData = null;
$error = '';

if ($userId <= 0) {
    $error = 'Invalid resume link.';
} else {
    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT username, full_name, email, phone, skills, education, bio FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            $error = 'The requested resume could not be found.';
        }
    } catch (PDOException $e) {
        $error = 'Unable to load resume: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Public Resume</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="public-resume">
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
      <header>
        <h1><?php echo htmlspecialchars($userData['full_name']); ?></h1>
        <p><?php echo htmlspecialchars($userData['bio']); ?></p>
      </header>

      <section>
        <h2>Contact Information</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($userData['phone']); ?></p>
      </section>

      <section>
        <h2>Skills</h2>
        <p><?php echo nl2br(htmlspecialchars($userData['skills'])); ?></p>
      </section>

      <section>
        <h2>Education</h2>
        <p><?php echo nl2br(htmlspecialchars($userData['education'])); ?></p>
      </section>

      <footer class="resume-footer">
        <p>Profile owned by <?php echo htmlspecialchars($userData['username']); ?>.</p>
        <a class="btn" href="login.php">Login to edit your profile</a>
      </footer>
    <?php endif; ?>
  </div>
</body>
</html>
