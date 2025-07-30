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
    $badges = json_decode(file_get_contents(__DIR__ . '/badges.json'), true);
    $earned = [];

    foreach ($badges as $badge) {
        $id = $badge['id'];
        $condition = $badge['condition_text'];

        // Bu rozet daha önce kazanılmış mı kontrol et
        $stmt = $conn->prepare("SELECT COUNT(*) as c FROM user_achievements WHERE user_key = ? AND badge_id = ?");
        if (!$stmt) {
            continue;
        }
        
        $stmt->bind_param("ss", $user_key, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) continue; // Zaten kazanılmış

        $earnedNow = false;

        if ($condition === 'first_workout') {
            $stmt2 = $conn->prepare("SELECT COUNT(*) as c FROM progress WHERE user_id = ?");
            if ($stmt2) {
                $stmt2->bind_param("s", $user['id']);
                $stmt2->execute();
                $stmt2->bind_result($workout_count);
                $stmt2->fetch();
                $stmt2->close();
                
                if ($workout_count >= 1) $earnedNow = true;
            }
        }

        if (str_starts_with($condition, 'streak:')) {
            $days_needed = (int)explode(':', $condition)[1];
            $stmt2 = $conn->prepare("SELECT date FROM progress WHERE user_id = ? ORDER BY date DESC LIMIT ?");
            if ($stmt2) {
                $stmt2->bind_param("si", $user['id'], $days_needed);
                $stmt2->execute();
                
                $dates = [];
                $stmt2->bind_result($date);
                while ($stmt2->fetch()) {
                    $dates[] = $date;
                }
                $stmt2->close();

                $expected = [];
                for ($i = 0; $i < $days_needed; $i++) {
                    $expected[] = date('Y-m-d', strtotime("-$i days"));
                }
                if (array_diff($expected, $dates) === []) $earnedNow = true;
            }
        }

        if ($earnedNow) {
            $stmt3 = $conn->prepare("INSERT INTO user_achievements (user_key, badge_id) VALUES (?, ?)");
            if ($stmt3) {
                $stmt3->bind_param("ss", $user_key, $id);
                $stmt3->execute();
                $stmt3->close();
                $earned[] = $badge;
            }
        }
    }

    echo json_encode($earned);
    
} catch (Exception $e) {
    error_log('Check Achievements Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>