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

// Son 7 gün ilerlemesi
$stmt = $conn->prepare("SELECT date, level FROM progress WHERE user_id = ? AND date >= CURDATE() - INTERVAL 7 DAY ORDER BY date");
if (!$stmt) {
    die(json_encode(['error' => 'Sorgu hazırlanamadı: ' . $conn->error]));
}
$stmt->bind_param("s", $user['id']);
$stmt->execute();

// fetch_assoc() yerine bind_result ve fetch kullan
$progress = [];
$stmt->bind_result($date, $level);
while ($stmt->fetch()) {
    $progress[] = ['date' => $date, 'level' => $level];
}

// To-Do Listesi
$stmt2 = $conn->prepare("SELECT id, text, completed FROM todos WHERE user_id = ?");
if (!$stmt2) {
    die(json_encode(['error' => 'Sorgu hazırlanamadı: ' . $conn->error]));
}
$stmt2->bind_param("s", $user['id']);
$stmt2->execute();

$todos = [];
$stmt2->bind_result($id, $text, $completed);
while ($stmt2->fetch()) {
    $todos[] = [
        'id' => $id,
        'text' => $text,
        'completed' => (bool)$completed
    ];
}

echo json_encode([
    'completedDays' => array_column($progress, 'date'),
    'level' => !empty($progress) ? $progress[0]['level'] : 'beginner',
    'todos' => $todos
]);
?>