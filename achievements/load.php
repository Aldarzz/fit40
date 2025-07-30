<?php
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

$user_key = $_COOKIE['fit40_user_key'] ?? null;
if (!$user_key) { 
    echo json_encode([]); 
    exit; 
}

try {
    $sql = "SELECT ab.id, ab.title, ab.description, ab.icon, ua.earned_at 
            FROM user_achievements ua
            JOIN achievements_badges ab ON ua.badge_id = ab.id
            WHERE ua.user_key = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Sorgu hazırlanamadı: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $user_key);
    $stmt->execute();
    
    // Eski PHP sürümü için uyumlu kod
    $achievements = [];
    $stmt->bind_result($id, $title, $description, $icon, $earned_at);
    while ($stmt->fetch()) {
        $achievements[] = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'earned_at' => $earned_at
        ];
    }
    $stmt->close();
    
    echo json_encode($achievements);
    
} catch (Exception $e) {
    error_log('Load Achievements Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>