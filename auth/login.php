<?php
require_once '../includes/auth.php';
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
    }
    .login-header p {
      opacity: 0.9;
    }
    .login-body {
      padding: 30px;
    }
    .google-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 12px;
      color: #555;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
    }
    .google-btn:hover {
      border-color: #aaa;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .google-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
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
        
        <p class="text-center text-muted mb-4">
          Hesabınızla güvenli bir şekilde giriş yapın
        </p>
        
        <a href="<?= get_fit40_google_login_url() ?>" class="google-btn w-100 mb-3">
          <svg class="google-icon" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C2.56 13.72 0 19.9 0 24c0 4.18 1.66 8.03 4.56 10.92l7.97-6.18z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.13 1.44-4.75 2.46-7.73 2.46-5.84 0-10.67-3.97-12.53-9.26l-7.98 6.19C6.51 41.62 14.62 48 24 48z"/>
            <path fill="none" d="M0 0h48v48H0z"/>
          </svg>
          Google ile Giriş Yap
        </a>
        
        <div class="text-center mt-4 text-muted">
          <small>Devam ederek <a href="#">Kullanım Koşulları</a> ve <a href="#">Gizlilik Politikası</a>'nı kabul etmiş sayılırsınız.</small>
        </div>
      </div>
    </div>
  </div>
</body>
</html>