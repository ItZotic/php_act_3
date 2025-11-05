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

$success = "";
$errors = [];
$userData = [];

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(["id" => $_SESSION["user_id"]]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        throw new RuntimeException("Unable to load your profile details.");
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $fullName = trim($_POST["full_name"] ?? "");
        $email    = trim($_POST["email"] ?? "");
        $phone    = trim($_POST["phone"] ?? "");
        $skills   = trim($_POST["skills"] ?? "");
        $education = trim($_POST["education"] ?? "");
        $bio      = trim($_POST["bio"] ?? "");

        if ($fullName === "") {
            $errors[] = "Full name is required.";
        }

        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "A valid email address is required.";
        }

        if ($phone === "") {
            $errors[] = "Phone number is required.";
        }

        if ($skills === "") {
            $errors[] = "Please enter at least one skill.";
        }

        if ($education === "") {
            $errors[] = "Education information is required.";
        }

        if ($bio === "") {
            $errors[] = "Bio section cannot be empty.";
        }

        if (!$errors) {
            $update = $pdo->prepare(
                "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, skills = :skills, education = :education, bio = :bio WHERE id = :id"
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
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}
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
