<?php
require '../../config.php';

// Pastikan user sudah login dan adalah user biasa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $shift_id_baru = $_POST['shift_id_baru'];
    $alasan = trim($_POST['alasan']);

    // Ambil shift_id lama dari database untuk keamanan, bukan dari form
    $stmt_current_shift = $conn->prepare("SELECT shift_id FROM users WHERE id = ?");
    $stmt_current_shift->bind_param("i", $user_id);
    $stmt_current_shift->execute();
    $current_shift_data = $stmt_current_shift->get_result()->fetch_assoc();
    $shift_id_lama = $current_shift_data['shift_id'];
    $stmt_current_shift->close();

    // Validasi dasar
    if (empty($tanggal_mulai) || empty($tanggal_selesai) || empty($shift_id_baru) || empty($alasan) || is_null($shift_id_lama)) {
        $_SESSION['toast'] = ['message' => 'Semua field harus diisi.', 'type' => 'error'];
        // Tambahan: jika shift lama null, user tidak bisa mengajukan
        if (is_null($shift_id_lama)) $_SESSION['toast']['message'] = 'Anda belum memiliki shift utama, tidak dapat mengajukan penggantian.';
        header('Location: ajukan_ganti_shift.php');
        exit;
    }

    if ($tanggal_selesai < $tanggal_mulai) {
        $_SESSION['toast'] = ['message' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'type' => 'error'];
        header('Location: ajukan_ganti_shift.php');
        exit;
    }

    // Validasi hari libur (Sabtu dan Minggu)
    $start_day = date('w', strtotime($tanggal_mulai)); // 0 = Minggu, 6 = Sabtu
    $end_day = date('w', strtotime($tanggal_selesai));
    
    // Cek apakah ada hari libur dalam rentang tanggal
    $current_date = new DateTime($tanggal_mulai);
    $end_date = new DateTime($tanggal_selesai);
    $has_weekend = false;
    
    while ($current_date <= $end_date) {
        $day_of_week = $current_date->format('w'); // 0 = Minggu, 6 = Sabtu
        if ($day_of_week == 0 || $day_of_week == 6) {
            $has_weekend = true;
            break;
        }
        $current_date->modify('+1 day');
    }
    
    if ($has_weekend) {
        $_SESSION['toast'] = ['message' => 'Tidak dapat mengajukan ganti shift pada hari libur (Sabtu/Minggu).', 'type' => 'error'];
        header('Location: ajukan_ganti_shift.php');
        exit;
    }

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO pengajuan_ganti_shift (user_id, tanggal_mulai, tanggal_selesai, shift_id_lama, shift_id_baru, alasan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiis", $user_id, $tanggal_mulai, $tanggal_selesai, $shift_id_lama, $shift_id_baru, $alasan);

    if ($stmt->execute()) {
        $_SESSION['toast'] = ['message' => 'Pengajuan ganti shift berhasil dikirim.', 'type' => 'success'];
        header('Location: riwayat_pengajuan.php'); // Arahkan ke riwayat setelah berhasil
    } else {
        $_SESSION['toast'] = ['message' => 'Gagal mengirim pengajuan. Silakan coba lagi.', 'type' => 'error'];
        header('Location: ajukan_ganti_shift.php');
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>