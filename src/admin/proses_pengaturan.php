<?php
require '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_masuk = $_POST['jam_masuk'];
    $batas_jam_masuk = $_POST['batas_jam_masuk'];
    $jam_pulang = $_POST['jam_pulang'];

    $stmt = $conn->prepare("UPDATE pengaturan SET jam_masuk = ?, batas_jam_masuk = ?, jam_pulang = ? WHERE id = 1");
    $stmt->bind_param("sss", $jam_masuk, $batas_jam_masuk, $jam_pulang);

    if ($stmt->execute()) {
        $_SESSION['toast'] = ['message' => 'Pengaturan jadwal berhasil diperbarui.', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['message' => 'Gagal memperbarui pengaturan.', 'type' => 'error'];
    }
    $stmt->close();
}
header('Location: pengaturan.php');
exit;
?>