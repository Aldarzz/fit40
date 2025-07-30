<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');

// Kullanıcı girişi kontrolü
$user = get_fit40_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['date']) || !isset($data['level'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik veri']);
    exit;
}

// İlerlemeyi kaydet
$stmt = $conn->prepare("INSERT IGNORE INTO progress (user_id, date, level) VALUES (?, ?, ?)");
if (!$stmt) {
    die(json_encode(['error' => 'Sorgu hazırlanamadı: ' . $conn->error]));
}
$stmt->bind_param("sss", $user['id'], $data['date'], $data['level']);
$stmt->execute();

echo json_encode(['success' => true]);
?>