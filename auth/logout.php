<?php
session_start();
session_unset();
session_destroy();

// Cookie'leri temizle
setcookie("user_key", "", time() - 3600, "/");
setcookie("admin_logged_in", "", time() - 3600, "/");

// Ana sayfaya yönlendir
header("Location: ../index.php");
exit;
?>