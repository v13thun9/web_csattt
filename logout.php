<?php
session_start();
setcookie('theme_prefs', '', time() - 3600, '/');
setcookie('theme_prefs_new', '', time() - 3600, '/');
session_destroy();
header("Location: login.php");
exit();
?> 