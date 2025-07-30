<?php
include '../config/db.php';
$user_key = $_COOKIE['fit40_user_key'] ?? null;
if (!$user_key) { echo json_encode([]); exit; }

$sql = "SELECT ab.id, ab.title, ab.description, ab.icon, ua.earned_at 
        FROM user_achievements ua
        JOIN achievements_badges ab ON ua.badge_id = ab.id
        WHERE ua.user_key = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_key);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>