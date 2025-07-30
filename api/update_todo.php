<?php
include '../config/db.php';
header('Content-Type: application/json');

$user_key = $_COOKIE['fit40_user_key'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

if (!$user_key || !isset($data['id']) || !isset($data['completed'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Eksik veri']);
    exit;
}

$sql = "UPDATE todos SET completed = ? WHERE id = ? AND user_key = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $data['completed'], $data['id'], $user_key);
$stmt->execute();

echo json_encode(['success' => true]);
?>