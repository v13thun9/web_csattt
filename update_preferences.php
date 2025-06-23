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
$stmt = $db->prepare('SELECT is_legacy_user FROM users WHERE id = :id');
$stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = in_array($_POST['theme'] ?? '', ['light', 'dark']) ? $_POST['theme'] : 'light';
    $fontSize = in_array($_POST['fontSize'] ?? '', ['small', 'medium', 'large']) ? $_POST['fontSize'] : 'medium';
    
    if ($user['is_legacy_user']) {
        $preferences = new UserPreferences($theme, $fontSize);
        setcookie('theme_prefs', base64_encode(serialize($preferences)), time() + (86400 * 30), '/');
    } else {
        $preferences = ['theme' => $theme, 'fontSize' => $fontSize];
        setcookie('theme_prefs_new', json_encode($preferences), time() + (86400 * 30), '/');
    }
    
    $_SESSION['preferences'] = $preferences;
    $_SESSION['prefs_message'] = 'Saved preferences successfully!';
}

header("Location: profile.php");
exit();
?>