<?php
session_start();

// Oturum kontrolü
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = null;

// Giriş kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['user'] ?? '';
    $password = $_POST['pass'] ?? '';
    
    // Kullanıcı adı ve şifre kontrolü
    if ($username === 'admin' && $password === 'fit40plus2025') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Geçersiz kullanıcı adı veya şifre.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fit40+ Admin Girişi</title>
  <link rel="stylesheet" href="../style.css" />
  <style>
    .admin-login {
      max-width: 400px;
      margin: 100px auto;
    }
    
    .error {
      color: #d7191c;
      background: #ffebee;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="admin-login card">
    <h2>Fit40+ Admin Girişi</h2>
    
    <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
      <div style="margin-bottom: 20px;">
        <input type="text" name="user" placeholder="Kullanıcı Adı" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
      </div>
      <div style="margin-bottom: 20px;">
        <input type="password" name="pass" placeholder="Şifre" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
      </div>
      <button type="submit" class="btn primary" style="width: 100%;">Giriş Yap</button>
    </form>
  </div>
</body>
</html>