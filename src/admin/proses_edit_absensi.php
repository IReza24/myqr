<?php
require '../../config.php';

// Hanya admin yang bisa mengakses halaman ini
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['toast'] = ['message' => 'Anda tidak memiliki hak akses.', 'type' => 'error'];
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['absensi_id'], $_POST['waktu_keluar'])) {
        
        $absensi_id = filter_input(INPUT_POST, 'absensi_id', FILTER_SANITIZE_NUMBER_INT);
        $waktu_keluar_baru = $_POST['waktu_keluar']; // Format H:i:s dari input time

        if (empty($absensi_id) || empty($waktu_keluar_baru)) {
            $_SESSION['toast'] = ['message' => 'Data yang dikirim tidak lengkap.', 'type' => 'error'];
            header("Location: rekap.php");
            exit;
        }

        $conn->begin_transaction();

        try {
            // 1. Ambil data absensi yang ada untuk mendapatkan user_id dan tanggal
            $stmt_absensi = $conn->prepare("SELECT user_id, tanggal_absensi, waktu_masuk FROM absensi WHERE id = ?");
            $stmt_absensi->bind_param("i", $absensi_id);
            $stmt_absensi->execute();
            $absensi = $stmt_absensi->get_result()->fetch_assoc();
            $stmt_absensi->close();

            if (!$absensi) {
                throw new Exception("Data absensi tidak ditemukan.");
            }

            // Pastikan waktu pulang tidak lebih awal dari waktu masuk
            if (strtotime($waktu_keluar_baru) <= strtotime($absensi['waktu_masuk'])) {
                throw new Exception("Waktu pulang tidak boleh lebih awal atau sama dengan waktu masuk.");
            }

            // 2. Ambil jadwal shift user
            $stmt_jadwal = $conn->prepare("SELECT s.* FROM users u JOIN shifts s ON u.shift_id = s.id WHERE u.id = ?");
            $stmt_jadwal->bind_param("i", $absensi['user_id']);
            $stmt_jadwal->execute();
            $jadwal = $stmt_jadwal->get_result()->fetch_assoc();
            $stmt_jadwal->close();

            if (!$jadwal) {
                throw new Exception("Jadwal shift untuk pengguna ini tidak ditemukan.");
            }

            // 3. Tentukan status pulang berdasarkan jam pulang di shift
            $jam_pulang_shift = $jadwal['jam_pulang'];
            $status_keluar = 'Selesai'; // Default status

            // --- LOGIKA BARU: Gunakan timestamp untuk perbandingan waktu yang andal ---
            $ts_waktu_keluar_baru = strtotime($waktu_keluar_baru);
            $ts_jam_pulang_shift = strtotime($jam_pulang_shift);

            // Tentukan batas waktu dalam format timestamp
            $ts_batas_pulang_cepat = $ts_jam_pulang_shift - 3600; // Kurangi 1 jam (3600 detik)
            $ts_batas_lembur = $ts_jam_pulang_shift + 7200;       // Tambah 2 jam (7200 detik)

            if ($ts_waktu_keluar_baru < $ts_batas_pulang_cepat) {
                $status_keluar = 'Pulang Cepat';
            } elseif ($ts_waktu_keluar_baru > $ts_batas_lembur) {
                $status_keluar = 'Lembur';
            } else {
                $status_keluar = 'Selesai';
            }

            // 4. Update data absensi
            $stmt_update = $conn->prepare("UPDATE absensi SET waktu_keluar = ?, status_keluar = ? WHERE id = ?");
            $stmt_update->bind_param("ssi", $waktu_keluar_baru, $status_keluar, $absensi_id);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Gagal memperbarui data absensi: " . $stmt_update->error);
            }
            $stmt_update->close();

            // Jika semua berhasil, commit transaksi
            $conn->commit();
            $_SESSION['toast'] = ['message' => 'Data berhasil diperbarui. Status: ' . $status_keluar, 'type' => 'success'];

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['toast'] = ['message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'type' => 'error'];
        }

    } else {
        $_SESSION['toast'] = ['message' => 'Data tidak lengkap.', 'type' => 'error'];
    }
} else {
    $_SESSION['toast'] = ['message' => 'Metode request tidak valid.', 'type' => 'error'];
}

header("Location: rekap.php");
exit;
?>