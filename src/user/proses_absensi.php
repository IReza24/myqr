<?php
// Mulai output buffering untuk menangkap semua output liar (warning/notice)
ob_start();

require '../../config.php';

// Inisialisasi array balasan
$response = ['success' => false, 'message' => 'Terjadi kesalahan tidak diketahui.'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    $response['message'] = 'Akses ditolak.';
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';
    $user_id = $_SESSION['user_id'];
    $today = date("Y-m-d");
    $currentTime = date("H:i:s");

    // 1. Validasi token
    $stmt_token = $conn->prepare("SELECT id FROM qr_tokens WHERE token = ? AND berlaku_sampai > NOW()");
    $stmt_token->bind_param("s", $token);
    $stmt_token->execute();
    $token_result = $stmt_token->get_result();
    $stmt_token->close();

    if ($token_result->num_rows === 0) {
        $response['message'] = 'QR Code tidak valid atau sudah kedaluwarsa.';
    } else {
        // 2. Ambil jadwal kerja, prioritaskan shift sementara yang disetujui
        $sql_jadwal = "
            SELECT s.* FROM shifts s
            WHERE s.id = (
                -- Cek dulu apakah ada shift pengganti yang disetujui untuk hari ini
                SELECT pgs.shift_id_baru
                FROM pengajuan_ganti_shift pgs
                WHERE pgs.user_id = ? 
                  AND pgs.status = 'disetujui' 
                  AND ? BETWEEN pgs.tanggal_mulai AND pgs.tanggal_selesai
                LIMIT 1
            ) OR s.id = (
                -- Jika tidak ada, ambil shift default user
                SELECT u.shift_id FROM users u WHERE u.id = ?
            )
            ORDER BY FIELD(s.id, (SELECT pgs.shift_id_baru FROM pengajuan_ganti_shift pgs WHERE pgs.user_id = ? AND pgs.status = 'disetujui' AND ? BETWEEN pgs.tanggal_mulai AND pgs.tanggal_selesai LIMIT 1)) DESC
            LIMIT 1";
        $stmt_jadwal = $conn->prepare($sql_jadwal);
        $stmt_jadwal->bind_param("isisi", $user_id, $today, $user_id, $user_id, $today);
        $stmt_jadwal->execute();
        $jadwal_result = $stmt_jadwal->get_result();

        if ($jadwal_result->num_rows === 0) {
            $response['message'] = 'Anda belum memiliki jadwal shift. Hubungi admin.';
        } else {
            $jadwal = $jadwal_result->fetch_assoc();
            $jam_masuk = $jadwal['jam_masuk'];
            $batas_jam_masuk = $jadwal['batas_jam_masuk'];
            $jam_pulang = $jadwal['jam_pulang'];

            // 3. Cek data absensi hari ini
            $stmt_absensi = $conn->prepare("SELECT id, waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
            $stmt_absensi->bind_param("is", $user_id, $today);
            $stmt_absensi->execute();
            $data_absensi_hari_ini = $stmt_absensi->get_result()->fetch_assoc();
            $stmt_absensi->close();

            $absenBerhasil = false;
            $conn->begin_transaction(); // Mulai transaksi

            if (!$data_absensi_hari_ini) {
                // KASUS 1: ABSEN MASUK

                // Validasi waktu absen masuk
                if ($currentTime < $jam_masuk) {
                    $response['message'] = 'Waktu absen masuk belum dimulai. Anda dapat absen mulai dari jam ' . $jam_masuk . '.';
                    $conn->rollback(); // Batalkan transaksi karena tidak ada perubahan
                } else {
                    $status_masuk = ($currentTime > $batas_jam_masuk) ? 'Terlambat' : 'Tepat Waktu';
                    $stmt_insert = $conn->prepare("INSERT INTO absensi (user_id, tanggal_absensi, waktu_masuk, status_masuk) VALUES (?, ?, ?, ?)");
                    $stmt_insert->bind_param("isss", $user_id, $today, $currentTime, $status_masuk);
                    if ($absenBerhasil = $stmt_insert->execute()) {
                        $_SESSION['toast'] = ['message' => 'Absen MASUK berhasil dicatat pukul ' . $currentTime, 'type' => 'success'];
                        $response = ['success' => true];
                    } else {
                        $response['message'] = 'Gagal menyimpan data absen masuk.';
                    }
                    $stmt_insert->close();
                }
            } elseif ($data_absensi_hari_ini['waktu_masuk'] && !$data_absensi_hari_ini['waktu_keluar']) {
                // KASUS 2: ABSEN PULANG
                $batas_pulang_cepat = date('H:i:s', strtotime($jam_pulang . ' -1 hour'));
                $batas_lembur = date('H:i:s', strtotime($jam_pulang . ' +2 hours'));

                if ($currentTime < $batas_pulang_cepat) {
                    $status_keluar = 'Pulang Cepat';
                } elseif ($currentTime > $batas_lembur) {
                    $status_keluar = 'Lembur';
                } else {
                    $status_keluar = 'Selesai';
                }

                $stmt_update = $conn->prepare("UPDATE absensi SET waktu_keluar = ?, status_keluar = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $currentTime, $status_keluar, $data_absensi_hari_ini['id']);
                if ($absenBerhasil = $stmt_update->execute()) {
                    $_SESSION['toast'] = ['message' => 'Absen PULANG berhasil dicatat pukul ' . $currentTime, 'type' => 'success'];
                    $response = ['success' => true];
                } else {
                    $response['message'] = 'Gagal menyimpan data absen pulang.';
                }
                $stmt_update->close();
            } else {
                // KASUS 3: SUDAH LENGKAP
                $response['message'] = 'Anda sudah menyelesaikan absensi hari ini.';
            }

            // Jika absensi (masuk atau pulang) berhasil, hapus token
            if ($absenBerhasil) {
                $stmt_delete = $conn->prepare("DELETE FROM qr_tokens WHERE token = ?");
                $stmt_delete->bind_param("s", $token);
                // Jika penghapusan token juga berhasil, commit transaksi
                if ($stmt_delete->execute()) {
                    $conn->commit();
                } else {
                    $conn->rollback(); // Batalkan jika hapus token gagal
                    $response = ['success' => false, 'message' => 'Gagal memvalidasi token setelah absen.'];
                }
                $stmt_delete->close();
            } else {
                // Jika absen gagal, batalkan semua perubahan
                $conn->rollback();
            }
        }
    }
}

// Hapus semua output liar (warning/notice) yang mungkin sudah tertangkap
ob_clean();

// Kirim balasan JSON yang bersih
header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
exit();
?>