<?php
include '../config/db.php';
header('Content-Type: application/json');

$user_key = $_COOKIE['fit40_user_key'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

if (!$user_key || !isset($data['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik veri']);
    exit;
}

$sql = "INSERT INTO todos (user_key, text, completed) VALUES (?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_key, $data['text']);
$stmt->execute();

echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
?>