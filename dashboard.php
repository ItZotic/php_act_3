<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

$success = "";
$errors = [];
$userData = [];
$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(["id" => $_SESSION["user_id"]]);
    $userData = $stmt->fetch();

    if (!$userData) {
        throw new RuntimeException("Unable to load your profile details.");
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // CSRF Validation
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $errors[] = "Invalid security token. Please refresh and try again.";
        } else {
            $fullName = sanitizeInput($_POST["full_name"] ?? "");
            $email    = sanitizeInput($_POST["email"] ?? "");
            $phone    = sanitizeInput($_POST["phone"] ?? "");
            $skills   = sanitizeInput($_POST["skills"] ?? "");
            $education = sanitizeInput($_POST["education"] ?? "");
            $bio      = sanitizeInput($_POST["bio"] ?? "");

            // Validation
            if (empty($fullName)) {
                $errors[] = "Full name is required.";
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "A valid email address is required.";
            }

            if (empty($phone)) {
                $errors[] = "Phone number is required.";
            }

            if (empty($skills)) {
                $errors[] = "Please enter at least one skill.";
            }

            if (empty($education)) {
                $errors[] = "Education information is required.";
            }

            if (empty($bio)) {
                $errors[] = "Bio section cannot be empty.";
            }

            if (empty($errors)) {
                $update = $db->prepare(
                    "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, 
                     skills = :skills, education = :education, bio = :bio, updated_at = NOW() 
                     WHERE id = :id"
                );
                $update->execute([
                    "full_name" => $fullName,
                    "email" => $email,
                    "phone" => $phone,
                    "skills" => $skills,
                    "education" => $education,
                    "bio" => $bio,
                    "id" => $_SESSION["user_id"]
                ]);

                $success = "Your resume has been updated.";
                
                $stmt->execute(["id" => $_SESSION["user_id"]]);
                $userData = $stmt->fetch();
            }
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $errors[] = "An error occurred. Please try again later.";
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Welcome, <?php echo htmlspecialchars($userData["username"] ?? ""); ?></h1>
    <p>Update your resume information and share your public profile.</p>
  </header>

  <div class="dashboard-container">
    <?php if ($success): ?>
      <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="error">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="dashboard.php" method="POST" class="resume-form">
      <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData["full_name"] ?? ""); ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData["email"] ?? ""); ?>" required>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($userData["phone"] ?? ""); ?>" required>
      </div>

      <div class="form-group">
        <label for="skills">Skills</label>
        <textarea id="skills" name="skills" rows="4" required><?php echo htmlspecialchars($userData["skills"] ?? ""); ?></textarea>
      </div>

      <div class="form-group">
        <label for="education">Education</label>
        <textarea id="education" name="education" rows="4" required><?php echo htmlspecialchars($userData["education"] ?? ""); ?></textarea>
      </div>

      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="5" required><?php echo htmlspecialchars($userData["bio"] ?? ""); ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit">Save Changes</button>
        <a class="btn secondary" href="portfolio.php">View Private Resume</a>
        <a class="btn secondary" href="public_resume.php?id=<?php echo urlencode($userData["id"]); ?>" target="_blank">View Public Resume</a>
        <a class="logout-btn" href="logout.php">Logout</a>
      </div>
    </form>
  </div>
</body>
</html>
