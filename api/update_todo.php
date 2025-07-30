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
$data = json_decode(file_get_contents('php://input'), true);

if (!$user_key || !isset($data['id']) || !isset($data['completed'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik veri']);
    exit;
}

try {
    $sql = "UPDATE todos SET completed = ? WHERE id = ? AND user_key = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Sorgu hazırlanamadı: ' . $conn->error);
    }
    
    $stmt->bind_param("iis", $data['completed'], $data['id'], $user_key);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception('Sorgu çalıştırılamadı: ' . $stmt->error);
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Update Todo Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>