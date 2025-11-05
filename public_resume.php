<?php
require_once 'config.php';

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$userData = null;
$error = '';
$db = Database::getInstance()->getConnection();

if ($userId <= 0) {
    $error = 'Invalid resume link.';
} else {
    try {
        $stmt = $db->prepare("SELECT username, full_name, email, phone, skills, education, bio FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $userData = $stmt->fetch();

        if (!$userData) {
            $error = 'The requested resume could not be found.';
        } elseif (empty($userData['full_name'])) {
            $error = 'This user has not completed their profile yet.';
            $userData = null;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Unable to load resume. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $userData ? htmlspecialchars($userData['full_name']) . ' - Resume' : 'Resume Not Found'; ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    .public-resume {
      animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .public-resume section {
      margin-bottom: 30px;
    }
    .public-resume h2 {
      color: #2163aa;
      border-bottom: 2px solid #4dadfc;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="public-resume">
    <?php if ($error): ?>
      <div class="error" style="text-align: center; padding: 40px;">
        <h2 style="margin-bottom: 10px;">⚠️ <?php echo htmlspecialchars($error); ?></h2>
        <p>Please check the URL or contact the portfolio owner.</p>
        <a class="btn" href="login.php" style="margin-top: 20px;">Go to Login</a>
      </div>
    <?php else: ?>
      <header style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;">
          <?php echo htmlspecialchars($userData['full_name']); ?>
        </h1>
        <p style="font-size: 1.1rem; color: #666; font-style: italic;">
          <?php echo htmlspecialchars($userData['bio']); ?>
        </p>
      </header>

      <section>
        <h2>📞 Contact Information</h2>
        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($userData['email']); ?>"><?php echo htmlspecialchars($userData['email']); ?></a></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($userData['phone']); ?></p>
      </section>

      <section>
        <h2>💼 Skills</h2>
        <p style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($userData['skills'])); ?></p>
      </section>

      <section>
        <h2>🎓 Education</h2>
        <p style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($userData['education'])); ?></p>
      </section>

      <footer class="resume-footer" style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;">
        <p style="color: #666; margin-bottom: 15px;">
          This is the public portfolio of <strong><?php echo htmlspecialchars($userData['username']); ?></strong>
        </p>
        <a class="btn" href="browse.php" style="margin-right: 10px;">Browse More Portfolios</a>
        <a class="btn secondary" href="login.php">Create Your Own Portfolio</a>
      </footer>
    <?php endif; ?>
  </div>
</body>
</html>
