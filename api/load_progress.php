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

$user_id = $user['id'];
$user_key = $_COOKIE['fit40_user_key'] ?? null;

try {
    // Son 7 gün ilerlemesi
    $stmt = $conn->prepare("SELECT date, level FROM progress WHERE user_id = ? AND date >= CURDATE() - INTERVAL 7 DAY ORDER BY date");
    if (!$stmt) {
        throw new Exception('Sorgu hazırlanamadı: ' . $conn->error);
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
    
    // To-Do Listesi (user_key kullanarak)
    $todos = [];
    if ($user_key) {
        $stmt2 = $conn->prepare("SELECT id, text, completed FROM todos WHERE user_key = ?");
        if ($stmt2) {
            $stmt2->bind_param("s", $user_key);
            $stmt2->execute();
            
            $stmt2->bind_result($id, $text, $completed);
            while ($stmt2->fetch()) {
                $todos[] = [
                    'id' => $id,
                    'text' => $text,
                    'completed' => (bool)$completed
                ];
            }
            $stmt2->close();
        }
    }
    
    // Son kullanılan seviye
    $last_level = 'beginner';
    if (!empty($progress)) {
        $last_level = $progress[count($progress) - 1]['level'];
    }
    
    echo json_encode([
        'completedDays' => array_column($progress, 'date'),
        'level' => $last_level,
        'todos' => $todos
    ]);
    
} catch (Exception $e) {
    error_log('Load Progress Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>