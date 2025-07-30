<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';

// Hata durumunda yönlendirme fonksiyonu
function redirect_with_error($error_message) {
    error_log('Auth Error: ' . $error_message);
    header("Location: login.php?error=" . urlencode($error_message));
    exit;
}

// Başarılı durumda yönlendirme
function redirect_success() {
    header("Location: ../index.php");
    exit;
}

// CSRF koruması için state parametresi kontrolü (opsiyonel)
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $error_description = $_GET['error_description'] ?? 'Bilinmeyen hata';
    redirect_with_error("Google Auth Error: $error - $error_description");
}

if (!isset($_GET['code'])) {
    redirect_with_error('Authorization code not received');
}

$code = $_GET['code'];

try {
    // Erişim tokenını al
    $token_data = get_fit40_google_access_token($code);
    
    if (!$token_data || !isset($token_data['access_token'])) {
        $error_msg = 'Failed to get access token';
        if (isset($token_data['error'])) {
            $error_msg .= ': ' . $token_data['error'];
            if (isset($token_data['error_description'])) {
                $error_msg .= ' - ' . $token_data['error_description'];
            }
        }
        redirect_with_error($error_msg);
    }
    
    // Kullanıcı bilgilerini al
    $user_info = get_fit40_google_user_info($token_data['access_token']);
    
    if (!$user_info || !isset($user_info['sub'])) {
        redirect_with_error('Failed to get user information from Google');
    }
    
    // Kullanıcı bilgilerini işle
    $google_id = $user_info['sub'];
    $email = $user_info['email'] ?? '';
    $name = $user_info['name'] ?? 'Unknown User';
    $photo_url = $user_info['picture'] ?? '';
    
    // E-posta kontrolü
    if (empty($email)) {
        redirect_with_error('Email address is required');
    }
    
    // Kullanıcıyı veritabanına kaydet
    if (!save_or_update_fit40_user($google_id, $email, $name, $photo_url)) {
        redirect_with_error('Failed to save user to database');
    }
    
    // Oturum aç
    $user_data = [
        'id' => $google_id,
        'google_id' => $google_id,
        'email' => $email,
        'name' => $name,
        'photo_url' => $photo_url
    ];
    
    set_fit40_user_session($user_data);
    
    // Kullanıcı anahtarı oluştur (fit40_user_key cookie için)
    $user_key = bin2hex(random_bytes(16));
    setcookie('fit40_user_key', $user_key, time() + 31536000, '/'); // 1 yıl
    
    // Ana sayfaya yönlendir
    redirect_success();
    
} catch (Exception $e) {
    error_log('Callback Exception: ' . $e->getMessage());
    redirect_with_error('Authentication failed: ' . $e->getMessage());
}
?>