<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/db.php';

// Kullanıcı girişi kontrolü
$user = get_fit40_user();
if (!$user) {
    header("Location: ../auth/login.php");
    exit;
}

// TCPDF kontrolü
if (!file_exists('../tcpdf/tcpdf.php')) {
    die('TCPDF kütüphanesi bulunamadı. Lütfen tcpdf klasörünü doğru yere yükleyin.');
}

require_once('../tcpdf/tcpdf.php');

$user_id = $user['id'];
$user_key = $_COOKIE['fit40_user_key'] ?? 'test';

try {
    // Son 7 gün ilerlemesi
    $stmt = $conn->prepare("SELECT date, level FROM progress WHERE user_id = ? ORDER BY date DESC");
    if (!$stmt) {
        throw new Exception("Sorgu hazırlanamadı: " . $conn->error);
    }
    
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    
    // Eski PHP sürümü için uyumlu kod
    $progress = [];
    $stmt->bind_result($date, $level);
    while ($stmt->fetch()) {
        $progress[] = ['date' => $date, 'level' => $level];
    }
    $stmt->close();

    // PDF oluştur
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Fit40+');
    $pdf->SetTitle('Fit40+ Haftalık Rapor');
    $pdf->SetSubject('Egzersiz İlerleme Raporu');
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    // Logo (eğer varsa)
    if (file_exists('../favicon.ico')) {
        $pdf->Image('../favicon.ico', 15, 10, 20, 20, 'ICO', '', '', true, 150, '', false, false, 0, false, false, false);
    }
    
    $pdf->SetFont('dejavusans', 'B', 20);
    $pdf->SetXY(35, 10);
    $pdf->Cell(0, 20, 'Fit40+ Haftalık Rapor', 0, 1, 'L');

    // Kullanıcı bilgisi
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 10, 'Kullanıcı: ' . $user['name'], 0, 1, 'L');
    $pdf->Cell(0, 10, 'Oluşturulma Tarihi: ' . date('d.m.Y'), 0, 1, 'R');
    $pdf->Ln(10);

    // Genel Bilgiler
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->SetFillColor(44, 123, 182);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Genel Bilgiler', 0, 1, 'L', true);
    $pdf->Ln(5);

    // İstatistikler
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->SetFillColor(240, 248, 255);
    $pdf->SetTextColor(0, 0, 0);

    // Toplam tamamlanan gün
    $totalDays = count($progress);

    // Süreklilik
    $streak = 0;
    if ($totalDays > 0) {
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

    $pdf->Cell(80, 8, 'Toplam Tamamlanan Gün:', 1, 0, 'L', true);
    $pdf->Cell(30, 8, $totalDays, 1, 1, 'C');

    $pdf->Cell(80, 8, 'Süreklilik (Gün):', 1, 0, 'L', true);
    $pdf->Cell(30, 8, $streak, 1, 1, 'C');

    $pdf->Ln(10);

    // Detaylı İlerleme
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->SetFillColor(44, 123, 182);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Detaylı İlerleme', 0, 1, 'L', true);
    $pdf->Ln(5);

    // Tablo başlık
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetFillColor(230, 247, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(45, 8, 'Tarih', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Seviye', 1, 1, 'C', true);

    // Tablo içeriği
    $pdf->SetFont('dejavusans', '', 12);

    if (count($progress) > 0) {
        foreach ($progress as $row) {
            $pdf->Cell(45, 8, $row['date'], 1, 0, 'C');
            $pdf->Cell(45, 8, ucfirst($row['level']), 1, 1, 'C');
        }
    } else {
        $pdf->Cell(90, 8, 'Henüz egzersiz yapılmamış', 1, 1, 'C');
    }

    // Dipnot
    $pdf->Ln(15);
    $pdf->SetFont('dejavusans', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->MultiCell(0, 5, 'Bu rapor Fit40+ uygulaması tarafından otomatik olarak oluşturulmuştur. Daha fazla bilgi için uygulamamızı ziyaret edin: https://www.lineandframe.com/fit40/', 0, 'L');

    // Çıktı al
    $pdf->Output('fit40-rapor-' . date('Ymd') . '.pdf', 'I');
    
} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    die('PDF oluşturulurken hata oluştu: ' . $e->getMessage());
}
?>