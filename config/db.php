
<?php

// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$username = 'u7544710_fit40user';        // Hostingindeki kullanıcı adın
$password = 'X1pVO_tRh4qi1M}&';            // Şifre
$database = 'u7544710_fit40plus';   // Yukarıda oluşturduğun veritabanı

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// UTF-8 desteği
$conn->set_charset("utf8mb4");
?>