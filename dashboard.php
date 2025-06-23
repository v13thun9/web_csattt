<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new SQLite3('database/main.sqlite');
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - KMA Internal Notes</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <div class="notice">
            <h2>Technical Notice: Legacy Interface</h2>
            <p>The IT department has detected configuration issues with the legacy user account "v13thun9". 
            Please be aware that legacy accounts use a different authentication system and may have limited functionality.</p>
        </div>
        
        <div class="actions">
            <a href="profile.php" class="button">View Profile</a>
            <a href="logout.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html> 