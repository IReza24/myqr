<?php
require '../../config.php';

// Pastikan user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_pengajuan = $_GET['id'];
    $status_baru = $_GET['status'];

    // Validasi status
    if (!in_array($status_baru, ['disetujui', 'ditolak'])) {
        $_SESSION['toast'] = ['message' => 'Status tidak valid.', 'type' => 'error'];
        header('Location: review_pengajuan.php');
        exit;
    }

    // Mulai transaksi untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // 1. Update status pengajuan
        $stmt_update_status = $conn->prepare("UPDATE pengajuan_ganti_shift SET status = ? WHERE id = ?");
        $stmt_update_status->bind_param("si", $status_baru, $id_pengajuan);
        $stmt_update_status->execute();
        $stmt_update_status->close();

        // Jika semua berhasil, commit transaksi
        $conn->commit();
        $pesan_sukses = 'Status pengajuan berhasil diperbarui.';
        if ($status_baru === 'disetujui') {
            $pesan_sukses = 'Pengajuan disetujui. Jadwal karyawan akan otomatis berubah pada periode yang ditentukan.';
        }
        $_SESSION['toast'] = ['message' => $pesan_sukses, 'type' => 'success'];
    } catch (Exception $e) {
        $conn->rollback(); // Batalkan semua perubahan jika ada error
        $_SESSION['toast'] = ['message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'type' => 'error'];
    }
} else {
    $_SESSION['toast'] = ['message' => 'Parameter tidak lengkap.', 'type' => 'error'];
}

$conn->close();
header('Location: review_pengajuan.php');
exit;
?>