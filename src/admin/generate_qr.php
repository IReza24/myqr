<?php
require '../../vendor/autoload.php';
require '../../config.php';

// Import class yang dibutuhkan untuk Versi 3
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// 1. Logika untuk mendapatkan/membuat token (ini sudah benar, tidak diubah)
$setting_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'qr_interval_minutes' LIMIT 1");
$interval_minutes = ($setting_result && $setting_result->num_rows > 0) ? $setting_result->fetch_assoc()['setting_value'] : 5;
$conn->query("DELETE FROM qr_tokens WHERE berlaku_sampai < NOW()");
$result = $conn->query("SELECT token FROM qr_tokens WHERE berlaku_sampai > NOW() LIMIT 1");
$token = '';
if ($result && $result->num_rows > 0) {
    $token = $result->fetch_assoc()['token'];
} else {
    $token = (string)random_int(100000, 999999);
    $expiry_time = date('Y-m-d H:i:s', strtotime("+$interval_minutes minutes"));
    $stmt = $conn->prepare("INSERT INTO qr_tokens (token, berlaku_sampai) VALUES (?, ?)");
    $stmt->bind_param("ss", $token, $expiry_time);
    $stmt->execute();
    $stmt->close();
}
$conn->close();

// 2. Konfigurasi QR Code dengan konstanta Versi 3
$options = new QROptions([
    'version'       => -1,
    'outputType'    => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'      => QRCode::ECC_L,
    'scale'         => 20,
    'imageBase64'   => true,
    'quietzoneSize'  => 4,
]);

// 3. Buat QR code dengan CARA PEMANGGILAN VERSI 3 YANG BENAR
$qrcode = new QRCode($options);
// Panggil render() dengan DATA TOKEN sebagai argumen pertama
$dataUri = $qrcode->render($token);

// 4. Kirim hasil dalam format JSON tanpa escaping slash
header('Content-Type: application/json');
// Tambahkan JSON_UNESCAPED_SLASHES untuk memperbaiki masalah gambar putih
echo json_encode([
    'token'   => $token,
    'dataUri' => $dataUri
], JSON_UNESCAPED_SLASHES);