<?php
require_once 'config.php';

$db = Database::getInstance()->getConnection();
$users = [];
$searchQuery = "";

try {
    // Check if there's a search query
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $searchQuery = sanitizeInput($_GET['search']);
        
        // Search by name, username, or skills
        $stmt = $db->prepare("
            SELECT id, username, full_name, email, skills, education, bio 
            FROM users 
            WHERE (full_name ILIKE :search OR username ILIKE :search OR skills ILIKE :search OR education ILIKE :search)
            AND full_name != '' 
            ORDER BY full_name ASC
        ");
        $stmt->execute(['search' => '%' . $searchQuery . '%']);
    } else {
        // Get all users with completed profiles
        $stmt = $db->prepare("
            SELECT id, username, full_name, email, skills, education, bio 
            FROM users 
            WHERE full_name != '' AND email != ''
            ORDER BY full_name ASC
        ");
        $stmt->execute();
    }
    
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Unable to load portfolios. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Student Portfolios</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .browse-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .browse-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .browse-header h1 {
      font-size: 2rem;
      color: #222;
      margin-bottom: 10px;
    }
    
    .search-box {
      display: flex;
      gap: 10px;
      max-width: 500px;
      margin: 20px auto;
      justify-content: center;
      align-items: center;
    }
    
    .search-box input {
      flex: 1;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }
    
    .search-box button {
      padding: 12px 24px;
      background: #34a853;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s;
    }
    
    .search-box button:hover {
      background: #2d8e47;
    }
    
    .portfolio-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    
    .portfolio-card {
      background: #f9f9f9;
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
      cursor: pointer;
    }
    
    .portfolio-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .avatar-circle {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      color: white;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    .portfolio-card h3 {
      margin: 10px 0 5px 0;
      color: #222;
      font-size: 1.3rem;
    }
    
    .portfolio-card .program {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 8px;
    }
    
    .portfolio-card .student-id {
      color: #999;
      font-size: 0.85rem;
      margin-bottom: 15px;
    }
    
    .portfolio-card .bio-preview {
      color: #555;
      font-size: 0.9rem;
      line-height: 1.4;
      margin-bottom: 15px;
      max-height: 60px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
    }
    
    .view-portfolio-btn {
      display: inline-block;
      padding: 8px 20px;
      background: #2163aa;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-size: 0.9rem;
      transition: background 0.3s;
    }
    
    .view-portfolio-btn:hover {
      background: #174a7a;
    }
    
    .back-btn {
      display: inline-block;
      padding: 10px 20px;
      background: #dc3545;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      margin-bottom: 20px;
      transition: background 0.3s;
    }
    
    .back-btn:hover {
      background: #c82333;
    }
    
    .no-results {
      text-align: center;
      padding: 40px;
      color: #666;
    }
    
    .no-results h3 {
      margin-bottom: 10px;
      color: #999;
    }
    
    .results-count {
      text-align: center;
      color: #666;
      margin: 20px 0;
      font-size: 0.95rem;
    }
  </style>
</head>
<body>
  <div class="browse-container">
    <a href="login.php" class="back-btn">← Back</a>
    
    <div class="browse-header">
      <h1>Browse Student Portfolios</h1>
      <p style="color: #666;">Discover talented students and their work</p>
    </div>
    
    <form method="GET" action="browse.php" class="search-box">
      <input type="text" name="search" placeholder="Search by name or program..." 
             value="<?php echo htmlspecialchars($searchQuery); ?>">
      <button type="submit">Search</button>
    </form>
    
    <?php if (isset($error)): ?>
      <div class="error" style="text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif (empty($users)): ?>
      <div class="no-results">
        <h3>No portfolios found</h3>
        <p>
          <?php if ($searchQuery): ?>
            No results for "<?php echo htmlspecialchars($searchQuery); ?>". Try a different search term.
          <?php else: ?>
            No students have completed their profiles yet. Be the first to create one!
          <?php endif; ?>
        </p>
        <a href="signup.php" class="btn" style="margin-top: 15px;">Create Your Portfolio</a>
      </div>
    <?php else: ?>
      <div class="results-count">
        <?php 
        $count = count($users);
        echo $searchQuery 
          ? "Found $count " . ($count === 1 ? 'portfolio' : 'portfolios') . " matching \"" . htmlspecialchars($searchQuery) . "\""
          : "Showing $count " . ($count === 1 ? 'portfolio' : 'portfolios');
        ?>
      </div>
      
      <div class="portfolio-grid">
        <?php foreach ($users as $user): ?>
          <div class="portfolio-card" onclick="window.location.href='public_resume.php?id=<?php echo $user['id']; ?>'">
            <div class="avatar-circle">
              <?php 
              // Get initials from full name
              $nameParts = explode(' ', $user['full_name']);
              $initials = '';
              if (count($nameParts) >= 2) {
                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1);
              } else {
                $initials = substr($user['full_name'], 0, 2);
              }
              echo htmlspecialchars($initials);
              ?>
            </div>
            
            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
            
            <div class="program">
              <?php 
              // Extract degree from education (first line usually)
              $educationLines = explode("\n", $user['education']);
              echo htmlspecialchars($educationLines[0]);
              ?>
            </div>
            
            <div class="student-id">
              <?php echo htmlspecialchars($user['email']); ?>
            </div>
            
            <?php if (!empty($user['bio'])): ?>
              <div class="bio-preview">
                <?php echo htmlspecialchars($user['bio']); ?>
              </div>
            <?php endif; ?>
            
            <a href="public_resume.php?id=<?php echo $user['id']; ?>" 
               class="view-portfolio-btn" 
               onclick="event.stopPropagation();">
              View Portfolio
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
