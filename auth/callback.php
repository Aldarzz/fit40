<?php
require_once '../includes/auth.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Erişim tokenını al
    $token_data = get_fit40_google_access_token($code);
    
    if (isset($token_data['access_token'])) {
        // Kullanıcı bilgilerini al
        $user_info = get_fit40_google_user_info($token_data['access_token']);
        
        if (isset($user_info['sub'])) {
            // Kullanıcıyı veritabanına kaydet
            $google_id = $user_info['sub'];
            $email = $user_info['email'];
            $name = $user_info['name'];
            $photo_url = $user_info['picture'] ?? '';
            
            if (save_or_update_fit40_user($google_id, $email, $name, $photo_url)) {
                // Oturum aç
                $user_data = [
                    'id' => $google_id,
                    'google_id' => $google_id,
                    'email' => $email,
                    'name' => $name,
                    'photo_url' => $photo_url
                ];
                
                set_fit40_user_session($user_data);
                
                // Ana sayfaya yönlendir
                header("Location: ../index.php");
                exit;
            }
        }
    }
}

// Hata durumunda giriş sayfasına yönlendir
header("Location: login.php?error=auth_failed");
exit;
?>