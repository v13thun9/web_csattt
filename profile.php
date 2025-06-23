<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/ThemeAssetManager.php';
require_once 'classes/UserPreferences.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new SQLite3('database/main.sqlite');
$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

$preferences = null;

if ($user['is_legacy_user']) {
    if (isset($_COOKIE['theme_prefs'])) {
        $preferences = unserialize(base64_decode($_COOKIE['theme_prefs']));
    }
} else {
    if (isset($_COOKIE['theme_prefs_new'])) {
        $preferences = json_decode($_COOKIE['theme_prefs_new'], true);
    }
}

if (!$preferences) {
    $preferences = $user['is_legacy_user'] ? new UserPreferences() : ['theme' => 'light', 'fontSize' => 'medium'];
}

$currentTheme = $user['is_legacy_user'] ? $preferences->theme : $preferences['theme'];
$currentFontSize = $user['is_legacy_user'] ? $preferences->fontSize : $preferences['fontSize'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - KMA Internal Notes</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?php echo 'theme-' . htmlspecialchars($currentTheme) . ' font-' . htmlspecialchars($currentFontSize); ?>">
    <?php if (isset($_SESSION['prefs_message'])): ?>
        <div class="notice"><?php echo htmlspecialchars($_SESSION['prefs_message']); unset($_SESSION['prefs_message']); ?></div>
    <?php endif; ?>
    <div class="container">
        <h1>User Profile</h1>
        <div class="profile-info">
            <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>
            <p>Account Type: <?php echo $user['is_legacy_user'] ? 'Legacy' : 'Modern'; ?></p>
        </div>
        
        <div class="preferences">
            <h2>Theme Preferences</h2>
            <form method="POST" action="update_preferences.php">
                <div>
                    <label>Theme:</label>
                    <select name="theme">
                        <option value="light" <?php echo $currentTheme === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $currentTheme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>
                <div>
                    <label>Font Size:</label>
                    <select name="fontSize">
                        <option value="small" <?php echo $currentFontSize === 'small' ? 'selected' : ''; ?>>Small</option>
                        <option value="medium" <?php echo $currentFontSize === 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="large" <?php echo $currentFontSize === 'large' ? 'selected' : ''; ?>>Large</option>
                    </select>
                </div>
                <button type="submit">Save Preferences</button>
            </form>
        </div>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
        <p><a href="logout.php">Logout</a></p>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeSelect = document.querySelector('select[name="theme"]');
        const fontSelect = document.querySelector('select[name="fontSize"]');
        const body = document.body;
        if (themeSelect) {
            themeSelect.addEventListener('change', function() {
                body.classList.remove('theme-light', 'theme-dark');
                body.classList.add('theme-' + this.value);
            });
        }
        if (fontSelect) {
            fontSelect.addEventListener('change', function() {
                body.classList.remove('font-small', 'font-medium', 'font-large');
                body.classList.add('font-' + this.value);
            });
        }
    });
    </script>
</body>
</html> 