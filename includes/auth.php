<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Google API bilgileri
define('GOOGLE_CLIENT_ID', '1012415828704-gco6dacia33hu0f4ssrf0klvu68071kc.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'https://www.lineandframe.com/fit40/auth/callback.php');

// Mevcut kullanıcıyı al
function get_fit40_user() {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

// Kullanıcı giriş yaptı mı?
function is_fit40_user_logged_in() {
    return isset($_SESSION['user']);
}

// Google ile giriş URL'si oluştur
function get_fit40_google_login_url() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

// Google'dan erişim tokenı al
function get_fit40_google_access_token($code) {
    $url = 'https://oauth2.googleapis.com/token';
    
    $params = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        error_log('CURL Error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log('HTTP Error: ' . $http_code . ' Response: ' . $response);
        return false;
    }
    
    return json_decode($response, true);
}

// Google'dan kullanıcı bilgilerini al
function get_fit40_google_user_info($access_token) {
    $url = 'https://www.googleapis.com/oauth2/v3/userinfo';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        error_log('CURL Error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log('HTTP Error: ' . $http_code . ' Response: ' . $response);
        return false;
    }
    
    return json_decode($response, true);
}

// Kullanıcıyı veritabanına kaydet veya güncelle
function save_or_update_fit40_user($google_id, $email, $name, $photo_url) {
    global $conn;
    
    try {
        // Kullanıcıyı kontrol et
        $stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
        if (!$stmt) {
            error_log('Prepare failed: ' . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $google_id);
        $stmt->execute();
        
        // Eski PHP sürümü için uyumlu kod
        $stmt->bind_result($id, $db_google_id, $db_email, $db_name, $db_photo_url, $created_at, $last_login);
        $user_exists = $stmt->fetch();
        $stmt->close();
        
        if ($user_exists) {
            // Kullanıcı var, güncelle
            $stmt = $conn->prepare("UPDATE users SET email = ?, name = ?, photo_url = ?, last_login = NOW() WHERE google_id = ?");
            if (!$stmt) {
                error_log('Prepare failed: ' . $conn->error);
                return false;
            }
            $stmt->bind_param("ssss", $email, $name, $photo_url, $google_id);
        } else {
            // Yeni kullanıcı ekle
            $stmt = $conn->prepare("INSERT INTO users (google_id, email, name, photo_url, created_at, last_login) VALUES (?, ?, ?, ?, NOW(), NOW())");
            if (!$stmt) {
                error_log('Prepare failed: ' . $conn->error);
                return false;
            }
            $stmt->bind_param("ssss", $google_id, $email, $name, $photo_url);
        }
        
        $result = $stmt->execute();
        if (!$result) {
            error_log('Execute failed: ' . $stmt->error);
        }
        $stmt->close();
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        return false;
    }
}

// Kullanıcıyı oturumda sakla
function set_fit40_user_session($user_data) {
    $_SESSION['user'] = $user_data;
}

// Kullanıcı çıkışı
function fit40_logout() {
    session_unset();
    session_destroy();
    
    // Cookie'leri temizle
    if (isset($_COOKIE['fit40_user_key'])) {
        setcookie('fit40_user_key', '', time() - 3600, '/');
    }
}
?>