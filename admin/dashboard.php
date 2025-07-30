<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once '../config/db.php';

// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Kullanıcı sayısı
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    if (!$stmt) {
        throw new Exception("Kullanıcı sayısı sorgusu başarısız: " . $conn->error);
    }
    $users_result = $stmt->fetch_assoc();
    $users_count = $users_result['count'];

    // Toplam tamamlanan gün
    $stmt = $conn->query("SELECT COUNT(*) as count FROM progress");
    if (!$stmt) {
        throw new Exception("Tamamlanan gün sorgusu başarısız: " . $conn->error);
    }
    $completed_result = $stmt->fetch_assoc();
    $completed_count = $completed_result['count'];

    // Toplam todo
    $stmt = $conn->query("SELECT COUNT(*) as count FROM todos");
    if (!$stmt) {
        throw new Exception("Todo sorgusu başarısız: " . $conn->error);
    }
    $todos_result = $stmt->fetch_assoc();
    $todos_count = $todos_result['count'];

    // Son 10 kullanıcı
    $stmt = $conn->query("SELECT google_id, email, name, photo_url, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    if (!$stmt) {
        throw new Exception("Son kullanıcılar sorgusu başarısız: " . $conn->error);
    }
    $lastUsers = [];
    while ($row = $stmt->fetch_assoc()) {
        $lastUsers[] = $row;
    }

    // Son 10 ilerleme
    $stmt = $conn->query("SELECT user_id, date, level FROM progress ORDER BY date DESC LIMIT 10");
    if (!$stmt) {
        throw new Exception("Son ilerleme sorgusu başarısız: " . $conn->error);
    }
    $lastProgress = [];
    while ($row = $stmt->fetch_assoc()) {
        $lastProgress[] = $row;
    }

    // Son 10 todo
    $stmt = $conn->query("SELECT user_key, text, completed, created_at FROM todos ORDER BY created_at DESC LIMIT 10");
    if (!$stmt) {
        throw new Exception("Son todo sorgusu başarısız: " . $conn->error);
    }
    $lastTodos = [];
    while ($row = $stmt->fetch_assoc()) {
        $lastTodos[] = $row;
    }
    
} catch (Exception $e) {
    error_log('Admin Dashboard Error: ' . $e->getMessage());
    $users_count = 0;
    $completed_count = 0;
    $todos_count = 0;
    $lastUsers = [];
    $lastProgress = [];
    $lastTodos = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fit40+ Admin Paneli</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../style.css" />
  <style>
    body {
      background-color: #f8f9fa;
    }
    .admin-container {
      max-width: 1400px;
      margin: 20px auto;
    }
    .stat-card {
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      transition: all 0.3s;
      height: 100%;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .stat-value {
      font-size: 2.5rem;
      font-weight: bold;
      color: #2c7bb6;
    }
    .table-container {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }
    .badge-active {
      background-color: #d4edda;
      color: #155724;
    }
    .badge-inactive {
      background-color: #f8d7da;
      color: #721c24;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">Fit40+ Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#">Genel Bakış</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Kullanıcılar</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">İlerleme</a>
          </li>
        </ul>
        <a href="index.php?logout" class="btn btn-light btn-sm">Çıkış Yap</a>
      </div>
    </div>
  </nav>

  <div class="admin-container mt-4">
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title text-muted">Toplam Kullanıcı</h5>
            <div class="stat-value"><?= $users_count ?></div>
            <p class="card-text text-muted">Sistemde kayıtlı toplam kullanıcı sayısı</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title text-muted">Tamamlanan Gün</h5>
            <div class="stat-value"><?= $completed_count ?></div>
            <p class="card-text text-muted">Egzersiz yapılan toplam gün sayısı</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title text-muted">Yapılacaklar</h5>
            <div class="stat-value"><?= $todos_count ?></div>
            <p class="card-text text-muted">Toplam görev sayısı</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="table-container mb-4">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h5 class="card-title mb-0">Son Kullanıcılar</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Profil</th>
                      <th>İsim</th>
                      <th>Email</th>
                      <th>Kayıt Tarihi</th>
                      <th>Durum</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lastUsers as $user): ?>
                    <tr>
                      <td>
                        <img src="<?= htmlspecialchars($user['photo_url']) ?>" alt="Profil" class="user-avatar">
                      </td>
                      <td><?= htmlspecialchars($user['name']) ?></td>
                      <td><?= htmlspecialchars($user['email']) ?></td>
                      <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                      <td><span class="badge bg-success badge-active">Aktif</span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Sistem Durumu</h5>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
              <span>Veritabanı Bağlantısı:</span>
              <span class="badge bg-success">Bağlandı</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>PHP Sürümü:</span>
              <span class="badge bg-primary"><?= phpversion() ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>Sunucu:</span>
              <span class="badge bg-info"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
            </div>
            <div class="d-flex justify-content-between">
              <span>SSL:</span>
              <span class="badge bg-success">Aktif</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="table-container mb-4">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h5 class="card-title mb-0">Son İlerlemeler</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Kullanıcı</th>
                      <th>Tarih</th>
                      <th>Seviye</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lastProgress as $progress): ?>
                    <tr>
                      <td><?= substr($progress['user_id'], 0, 8) ?>...</td>
                      <td><?= $progress['date'] ?></td>
                      <td><span class="badge bg-primary"><?= ucfirst($progress['level']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="table-container mb-4">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h5 class="card-title mb-0">Son Yapılacaklar</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Kullanıcı</th>
                      <th>Görev</th>
                      <th>Tamamlandı</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lastTodos as $todo): ?>
                    <tr>
                      <td><?= substr($todo['user_key'], 0, 8) ?>...</td>
                      <td><?= htmlspecialchars($todo['text']) ?></td>
                      <td>
                        <?php if ($todo['completed']): ?>
                          <span class="badge bg-success">Evet</span>
                        <?php else: ?>
                          <span class="badge bg-danger">Hayır</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (isset($_GET['logout'])): ?>
    <?php 
    session_destroy();
    setcookie("admin_logged_in", "", time() - 3600, "/");
    echo '<script>window.location.href = "index.php";</script>';
    ?>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>