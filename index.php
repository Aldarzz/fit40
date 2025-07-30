<?php

session_start();
require_once 'includes/auth.php';

// Kullanıcı giriş yapmış mı kontrol et
$user = get_fit40_user();
if (!$user) {
    header("Location: auth/login.php");
    exit;
}

// Kullanıcı anahtarı oluştur
$user_key = $_COOKIE['fit40_user_key'] ?? bin2hex(random_bytes(16));
setcookie('fit40_user_key', $user_key, time() + 31536000, '/');

// Bugün hangi gün?
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
$today = strtolower(date('l'));
$todayIndex = array_search($today, $days);
$nextDay = $days[($todayIndex + 1) % 7];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Fit40+ | 40+ Yaş Egzersizleri</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
  <link rel="manifest" href="manifest.json" />
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="theme-color" content="#2c7bb6" />
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">
        <span class="fw-bold">Fit</span>40+
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#">Anasayfa</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="report/index.php">Raporlar</a>
          </li>
        </ul>
        <div class="d-flex align-items-center">
          <div class="user-info me-3">
            <img src="<?= $user['photo_url'] ?>" alt="Profil" class="rounded-circle" width="32" height="32">
            <span class="text-white"><?= $user['name'] ?></span>
          </div>
          <a href="auth/logout.php" class="btn btn-light btn-sm">Çıkış Yap</a>
        </div>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="row">
      <div class="col-lg-8">
        <!-- Seviye Seçici -->
        <section class="level-selector card mb-4">
          <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Antrenman Seviyesi</h2>
          </div>
          <div class="card-body">
            <div class="level-options">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="level-option">
                    <input type="radio" name="level" value="beginner" checked>
                    <div class="level-card card h-100">
                      <div class="card-body text-center">
                        <div class="level-icon fs-1 mb-2">🌱</div>
                        <h3 class="h6">Başlangıç</h3>
                        <p class="text-muted small">Temel hareketler ve denge</p>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="col-md-4">
                  <label class="level-option">
                    <input type="radio" name="level" value="intermediate">
                    <div class="level-card card h-100">
                      <div class="card-body text-center">
                        <div class="level-icon fs-1 mb-2">💪</div>
                        <h3 class="h6">Orta Seviye</h3>
                        <p class="text-muted small">Güç ve kardiyo</p>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="col-md-4">
                  <label class="level-option">
                    <input type="radio" name="level" value="advanced">
                    <div class="level-card card h-100">
                      <div class="card-body text-center">
                        <div class="level-icon fs-1 mb-2">🔥</div>
                        <h3 class="h6">İleri Seviye</h3>
                        <p class="text-muted small">Yüksek yoğunluklu antrenman</p>
                      </div>
                    </div>
                  </label>
                </div>
              </div>
            </div>
            <button id="load-plan" class="btn btn-primary w-100 mt-3">Planı Yükle</button>
          </div>
        </section>

        <!-- Gün Seçimi -->
        <section class="day-selector card mb-4" style="display:none;">
          <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Bugünün Egzersizi</h2>
          </div>
          <div class="card-body">
            <div class="day-info mb-3">
              <div class="today bg-light p-3 rounded">
                <span class="day-name h5"><?= ucfirst($today) ?></span>
                <span class="date d-block"><?= date('d.m.Y') ?></span>
              </div>
              <div class="next-day mt-2">
                <span class="text-muted">Yarın: <?= ucfirst($nextDay) ?></span>
              </div>
            </div>
            <button id="start-workout" class="btn btn-success w-100">Egzersize Başla</button>
          </div>
        </section>

        <!-- Egzersiz Alanı -->
        <section id="workout-area" class="card mb-4" style="display:none;">
          <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h2 id="workout-title" class="h5 mb-0">Isınma</h2>
            <div class="badges d-flex">
              <span class="badge bg-warning text-dark ms-1" title="İlk Adım">🏆</span>
              <span class="badge bg-danger text-white ms-1" title="7 Gün Sürekli">🔥</span>
            </div>
          </div>
          
          <div class="card-body">
            <div class="timer-area text-center mb-4">
              <div id="timer" class="display-4 fw-bold text-danger">00:00</div>
              <div id="exercise-info" class="text-muted">Yerinde yürüyüş yapın</div>
            </div>
            
            <img id="exercise-gif" src="" alt="Egzersiz" class="exercise-gif img-fluid rounded mb-4">
            
            <!-- Kontrol Butonları -->
            <div class="controls d-flex justify-content-between gap-2">
              <button id="prev-exercise" class="btn btn-outline-secondary w-25" disabled><i>◀</i> Önceki</button>
              <button id="pause-resume" class="btn btn-secondary w-50">⏸ Duraklat</button>
              <button id="next-exercise" class="btn btn-outline-secondary w-25">Sonraki <i>▶</i></button>
            </div>
          </div>
        </section>

        <!-- Kazanılan Rozetler -->
        <section class="achievements-section card">
          <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Kazandığınız Rozetler</h2>
          </div>
          <div class="card-body">
            <div class="achievement-list" id="achievements-list"></div>
          </div>
        </section>
      </div>
      
      <div class="col-lg-4">
        <!-- Mola Önerileri -->
        <section class="break-advice card mb-4" style="display:none;">
          <div class="card-header bg-info text-white">
            <h2 class="h5 mb-0">Mola Önerileri</h2>
          </div>
          <div class="card-body">
            <div id="break-advice-content" class="text-muted" style="min-height: 60px;"></div>
            
            <div class="break-timer mt-3 text-center">
              <div id="break-timer" class="display-6 fw-bold text-danger">05:00</div>
            </div>
            
            <div class="break-controls d-flex gap-2 mt-3">
              <button id="extend-break" class="btn btn-outline-info w-50">Süreyi Uzat (+5DK)</button>
              <button id="skip-break" class="btn btn-info w-50">Molayı Bitir</button>
            </div>
          </div>
        </section>
        
        <!-- Profil Bilgisi -->
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Profil Bilgisi</h2>
          </div>
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <img src="<?= $user['photo_url'] ?>" alt="Profil" class="rounded-circle me-3" width="64" height="64">
              <div>
                <h3 class="h6 mb-1"><?= $user['name'] ?></h3>
                <p class="text-muted mb-0"><?= $user['email'] ?></p>
              </div>
            </div>
            
            <div class="progress mb-3" style="height: 10px;">
              <div class="progress-bar bg-success" role="progressbar" style="width: 65%"></div>
            </div>
            
            <div class="d-flex justify-content-between text-muted small">
              <span>7/10 gün tamamlandı</span>
              <span>%65</span>
            </div>
          </div>
        </div>
        
        <!-- Hızlı Erişim -->
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h2 class="h5 mb-0">Hızlı Erişim</h2>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="admin/dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-gear me-2"></i> Admin Paneli
              </a>
              <a href="report/index.php" class="btn btn-outline-success">
                <i class="bi bi-graph-up me-2"></i> Raporlar
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-light py-4 mt-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
          <p class="mb-0 text-muted">© 2023 Fit40+. Tüm hakları saklıdır.</p>
        </div>
        <div class="col-md-6">
          <div class="d-flex justify-content-center justify-content-md-end gap-3">
            <a href="#" class="text-muted"><i class="bi bi-moon"></i> Koyu Mod</a>
            <a href="admin/dashboard.php" class="text-muted"><i class="bi bi-gear"></i> Admin</a>
            <a href="report/index.php" class="text-muted"><i class="bi bi-graph-up"></i> Raporlar</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scriptler -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script src="js/achievements.js"></script>
  <script src="js/dark-mode.js"></script>

  <!-- PWA -->
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('service-worker.js')
        .then(reg => console.log('SW kayıtlı:', reg.scope))
        .catch(err => console.log('SW hatası:', err));
    }
  </script>
</body>
</html>