<?php
include '../config/db.php';
$user_key = $_COOKIE['fit40_user_key'] ?? null;
if (!$user_key) exit;

$badges = json_decode(file_get_contents('../achievements/badges.json'), true);
$earned = [];

foreach ($badges as $badge) {
    $id = $badge['id'];
    $condition = $badge['condition_text']; // DÜZELTİLDİ

    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM user_achievements WHERE user_key = ? AND badge_id = ?");
    $stmt->bind_param("ss", $user_key, $id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()['c'] > 0) continue;

    $earnedNow = false;

    if ($condition === 'first_workout') {
        $stmt2 = $conn->prepare("SELECT COUNT(*) as c FROM progress WHERE user_key = ?");
        $stmt2->bind_param("s", $user_key);
        $stmt2->execute();
        if ($stmt2->get_result()->fetch_assoc()['c'] >= 1) $earnedNow = true;
    }

    if (str_starts_with($condition, 'streak:')) {
        $days_needed = (int)explode(':', $condition)[1];
        $stmt2 = $conn->prepare("SELECT date FROM progress WHERE user_key = ? ORDER BY date DESC LIMIT ?");
        $stmt2->bind_param("si", $user_key, $days_needed);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $dates = [];
        while ($row = $result->fetch_assoc()) $dates[] = $row['date'];

        $expected = [];
        for ($i = 0; $i < $days_needed; $i++) {
            $expected[] = date('Y-m-d', strtotime("-$i days"));
        }
        if (array_diff($expected, $dates) === []) $earnedNow = true;
    }

    if ($earnedNow) {
        $stmt3 = $conn->prepare("INSERT INTO user_achievements (user_key, badge_id) VALUES (?, ?)");
        $stmt3->bind_param("ss", $user_key, $id);
        $stmt3->execute();
        $earned[] = $badge;
    }
}

echo json_encode($earned);
?>