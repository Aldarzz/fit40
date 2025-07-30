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

if (!$user_key || !isset($data['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik veri']);
    exit;
}

try {
    $sql = "INSERT INTO todos (user_key, text, completed) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Sorgu hazırlanamadı: ' . $conn->error);
    }
    
    $stmt->bind_param("ss", $user_key, $data['text']);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception('Sorgu çalıştırılamadı: ' . $stmt->error);
    }
    
    $insert_id = $stmt->insert_id;
    $stmt->close();
    
    echo json_encode(['success' => true, 'id' => $insert_id]);
    
} catch (Exception $e) {
    error_log('Save Todo Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>