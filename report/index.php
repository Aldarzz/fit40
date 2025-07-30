<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kullanıcı anahtarı
$user_key = $_COOKIE['fit40_user_key'] ?? 'testuser';
include '../config/db.php';

// Son 7 gün ilerlemesi
$stmt = $conn->prepare("SELECT date, level FROM progress WHERE user_key = ? AND date >= CURDATE() - INTERVAL 7 DAY ORDER BY date");
if (!$stmt) {
    die("Sorgu hazırlanamadı: " . $conn->error);
}
$stmt->bind_param("s", $user_key);

// fetch_assoc() yerine bind_result ve fetch kullan
$stmt->execute();
$stmt->bind_result($date, $level);
$progress = [];
while ($stmt->fetch()) {
    $progress[] = ['date' => $date, 'level' => $level];
}

// Tamamlanan gün sayısı
$completedCount = count($progress);
$streak = 0;
if ($completedCount > 0) {
    // Son günlerde kesinti olmadan devam eden gün sayısı
    $today = new DateTime();
    for ($i = 0; $i < 7; $i++) {
        $date = $today->format('Y-m-d');
        if (in_array($date, array_column($progress, 'date'))) {
            $streak++;
        } else {
            break;
        }
        $today->modify('-1 day');
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Haftalık Raporunuz - Fit40+</title>
  <link rel="stylesheet" href="../style.css" />
  <style>
    .report-container {
      max-width: 800px;
      margin: 20px auto;
    }
    
    .report-header {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      text-align: center;
      box-shadow: var(--card-shadow);
    }
    
    [data-theme="dark"] .stat-card {
      background: #2d2d44;
    }
    
    .stat-value {
      font-size: 2.5rem;
      font-weight: bold;
      color: var(--primary);
      margin: 10px 0;
    }
    
    .chart-container {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      box-shadow: var(--card-shadow);
      margin-bottom: 30px;
    }
    
    [data-theme="dark"] .chart-container {
      background: #2d2d44;
    }
    
    .chart {
      display: flex;
      align-items: flex-end;
      height: 250px;
      gap: 15px;
      padding: 20px 0;
    }
    
    .bar {
      flex: 1;
      min-width: 30px;
      background: var(--primary);
      border-radius: 8px 8px 0 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      font-weight: bold;
      position: relative;
    }
    
    .bar-label {
      position: absolute;
      bottom: -25px;
      width: 100%;
      text-align: center;
      font-size: 0.8rem;
      color: var(--dark);
    }
    
    .report-actions {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 20px;
    }
    
    .action-btn {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      text-align: center;
    }
    
    .action-btn i {
      font-size: 2rem;
    }
  </style>
</head>
<body>
  <div class="report-container">
    <div class="report-header">
      <h1>Haftalık Sağlık Raporu</h1>
      <p>Son 7 gün boyunca yaptığınız egzersizlerin özeti</p>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Tamamlanan Gün</h3>
        <div class="stat-value"><?= $completedCount ?></div>
        <p>Toplamda <?= $completedCount ?> gün egzersiz yaptınız</p>
      </div>
      
      <div class="stat-card">
        <h3>Süreklilik</h3>
        <div class="stat-value"><?= $streak ?></div>
        <p>Son <?= $streak ?> gündür egzersiz yapıyorsunuz</p>
      </div>
    </div>
    
    <div class="chart-container">
      <h2>Egzersiz Grafiği</h2>
      <div class="chart">
        <?php 
        $days = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
        $today = new DateTime();
        $today->modify('-6 days'); // 7 gün geriye git
        
        for ($i = 0; $i < 7; $i++) {
          $date = $today->format('Y-m-d');
          $completed = false;
          foreach ($progress as $p) {
            if ($p['date'] == $date) {
              $completed = true;
              break;
            }
          }
          
          $height = $completed ? 20 + ($i + 1) * 30 : 0;
          
          echo '<div class="bar" style="height: ' . $height . 'px;">';
          echo '<span class="bar-label">' . $days[$i] . '</span>';
          echo '</div>';
          
          $today->modify('+1 day');
        }
        ?>
      </div>
    </div>
    
    <div class="chart-container">
      <h2>Detaylı İlerleme</h2>
      <table>
        <thead>
          <tr>
            <th>Tarih</th>
            <th>Seviye</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($progress as $p): ?>
          <tr>
            <td><?= $p['date'] ?></td>
            <td><?= ucfirst($p['level']) ?></td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (empty($progress)): ?>
          <tr>
            <td colspan="2" style="text-align: center; padding: 20px;">
              Henüz egzersiz yapmadınız. Bugün başlamak için uygulamaya geri dönün!
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <div class="report-actions">
      <?php 
      // TCPDF'in varlığını kontrol et
      $tcpdfExists = file_exists('../tcpdf/tcpdf.php');
      ?>
      
      <?php if ($tcpdfExists): ?>
      <a href="generate_pdf.php" class="btn primary" target="_blank">
        <div class="action-btn">
          <i>📄</i>
          <span>PDF İndir</span>
        </div>
      </a>
      <?php else: ?>
      <div class="action-btn" style="color: #d7191c;">
        <i>⚠️</i>
        <span>TCPDF kurulu değil</span>
      </div>
      <?php endif; ?>
      
      <a href="../index.php" class="btn secondary">
        <div class="action-btn">
          <i>🏋️</i>
          <span>Egzersize Dön</span>
        </div>
      </a>
    </div>
    
    <?php if (!$tcpdfExists): ?>
    <div style="background: #fff8e1; padding: 15px; border-radius: 8px; margin-top: 20px;">
      <h3>⚠️ TCPDF Kurulum Uyarısı</h3>
      <p>Raporu PDF olarak indirebilmek için TCPDF kütüphanesini kurmanız gerekiyor:</p>
      <ol>
        <li><a href="https://tcpdf.org" target="_blank">TCPDF.org</a> adresinden kütüphaneyi indirin</li>
        <li>İndirdiğiniz <code>tcpdf</code> klasörünü sunucunuza yükleyin: <code>/fit40/tcpdf/</code></li>
        <li>Sayfayı yeniden yükleyin</li>
      </ol>
      <p><strong>Not:</strong> TCPDF kurulumu için hosting desteğinize başvurabilirsiniz.</p>
    </div>
    <?php endif; ?>
  </div>
</body>
</html>