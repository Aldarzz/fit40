<?php
require_once '../includes/auth.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (is_fit40_user_logged_in()) {
    header("Location: ../index.php");
    exit;
}

// Hata mesajını al
$error_message = '';
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Giriş Yap - Fit40+</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #2c7bb6, #1d5a8c);
      min-height: 100vh;
      display: flex;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      max-width: 450px;
      width: 100%;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      overflow: hidden;
    }
    .login-header {
      background: #2c7bb6;
      color: white;
      padding: 25px;
      text-align: center;
    }
    .login-header h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .login-header p {
      opacity: 0.9;
      margin: 0;
    }
    .login-body {
      padding: 30px;
    }
    .google-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border: 2px solid #ddd;
      border-radius: 8px;
      padding: 12px 20px;
      color: #555;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      width: 100%;
    }
    .google-btn:hover {
      border-color: #4285f4;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      color: #4285f4;
      text-decoration: none;
    }
    .google-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
    }
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
    .features {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }
    .feature-item {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      color: #666;
      font-size: 14px;
    }
    .feature-icon {
      width: 16px;
      height: 16px;
      margin-right: 8px;
      color: #2c7bb6;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="login-container mx-auto">
      <div class="login-header">
        <h1>Fit40+</h1>
        <p>40+ yaş için aletsiz ev egzersizleri</p>
      </div>
      <div class="login-body">
        <h2 class="h4 mb-4 text-center">Giriş Yap</h2>
        
        <?php if ($error_message): ?>
        <div class="error-message">
          <strong>Hata:</strong> <?= $error_message ?>
        </div>
        <?php endif; ?>
        
        <p class="text-center text-muted mb-4">
          Google hesabınızla güvenli bir şekilde giriş yapın
        </p>
        
        <a href="<?= get_fit40_google_login_url() ?>" class="google-btn mb-3">
          <svg class="google-icon" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C2.56 13.72 0 19.9 0 24c0 4.18 1.66 8.03 4.56 10.92l7.97-6.18z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.13 1.44-4.75 2.46-7.73 2.46-5.84 0-10.67-3.97-12.53-9.26l-7.98 6.19C6.51 41.62 14.62 48 24 48z"/>
            <path fill="none" d="M0 0h48v48H0z"/>
          </svg>
          Google ile Giriş Yap
        </a>
        
        <div class="features">
          <div class="feature-item">
            <span class="feature-icon">✓</span>
            <span>Kişiselleştirilmiş egzersiz planları</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">✓</span>
            <span>İlerleme takibi ve raporlar</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">✓</span>
            <span>Başarı rozetleri ve motivasyon</span>
          </div>
        </div>
        
        <div class="text-center mt-4 text-muted">
          <small>Devam ederek <a href="#" class="text-decoration-none">Kullanım Koşulları</a> ve <a href="#" class="text-decoration-none">Gizlilik Politikası</a>'nı kabul etmiş sayılırsınız.</small>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>