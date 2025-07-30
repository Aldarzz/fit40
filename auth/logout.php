<?php
require_once '../includes/auth.php';

// Kullanıcı çıkışı yap
fit40_logout();

// Ana sayfaya yönlendir
header("Location: ../index.php");
exit;
?>