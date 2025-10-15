<?php
require '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interval'])) {
    $interval = (int)$_POST['interval'];

    // Validasi sederhana, pastikan nilainya positif
    if ($interval > 0) {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'qr_interval_minutes'");
        $stmt->bind_param("s", $interval);
        
        if ($stmt->execute()) {
            $_SESSION['toast'] = ['message' => 'Interval QR berhasil diubah menjadi ' . $interval . ' menit.', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['message' => 'Gagal mengubah interval.', 'type' => 'error'];
        }
        $stmt->close();
    } else {
        $_SESSION['toast'] = ['message' => 'Nilai interval harus lebih besar dari 0.', 'type' => 'error'];
    }
}

$conn->close();
header('Location: index.php');
exit;