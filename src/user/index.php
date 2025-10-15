<?php
// Force no-caching to ensure fresh data is always displayed
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$page_title = "User Dashboard";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];
$today = date("Y-m-d");
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');

// -- QUERY & LOGIKA GABUNGAN UNTUK SEMUA DATA DASHBOARD --

// 1. Status absensi hari ini
$stmt_status = $conn->prepare("SELECT waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? AND tanggal_absensi = ?");
$stmt_status->bind_param("is", $user_id, $today);
$stmt_status->execute();
$status_hari_ini = $stmt_status->get_result()->fetch_assoc();
$stmt_status->close();

// 2. Statistik bulan ini
$stmt_stats = $conn->prepare(
    "SELECT 
        COUNT(*) as total_hadir, 
        SUM(CASE WHEN status_masuk = 'Terlambat' THEN 1 ELSE 0 END) as total_terlambat 
     FROM absensi 
     WHERE user_id = ? AND tanggal_absensi BETWEEN ? AND ?"
);
$stmt_stats->bind_param("iss", $user_id, $current_month_start, $current_month_end);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// --- LOGIKA BARU: HITUNG HARI TIDAK HADIR ---
$hari_kerja_berjalan = 0;
$start_date = new DateTime($current_month_start);
$end_date = new DateTime($today);
// Loop dari awal bulan sampai hari ini
$interval = new DateInterval('P1D');
$period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

foreach ($period as $date) {
    // Hanya hitung hari Senin sampai Jumat
    if ($date->format('N') < 6) {
        $hari_kerja_berjalan++;
    }
}

$total_tidak_hadir = max(0, $hari_kerja_berjalan - $stats['total_hadir']);

// 3. Cek absensi tidak lengkap dari hari sebelumnya
$stmt_incomplete = $conn->prepare("SELECT COUNT(*) as total_tidak_lengkap FROM absensi WHERE user_id = ? AND waktu_keluar IS NULL AND tanggal_absensi < ?");
$stmt_incomplete->bind_param("is", $user_id, $today);
$stmt_incomplete->execute();
$incomplete_check = $stmt_incomplete->get_result()->fetch_assoc();
$absen_tidak_lengkap = $incomplete_check['total_tidak_lengkap'] > 0;
$stmt_incomplete->close();

// 4. Riwayat absensi terakhir
$stmt_history = $conn->prepare("SELECT tanggal_absensi, waktu_masuk, waktu_keluar FROM absensi WHERE user_id = ? ORDER BY tanggal_absensi DESC LIMIT 5");
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$history = $stmt_history->get_result();

// 5. Logika Hitung Streak Tepat Waktu
$streak_count = 0;
$stmt_streak = $conn->prepare("SELECT tanggal_absensi FROM absensi WHERE user_id = ? AND status_masuk = 'Tepat Waktu' ORDER BY tanggal_absensi DESC");
$stmt_streak->bind_param("i", $user_id);
$stmt_streak->execute();
$streak_results = $stmt_streak->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_streak->close();

if (count($streak_results) > 0) {
    $latest_date = new DateTime($streak_results[0]['tanggal_absensi']);
    // Hanya hitung streak jika absensi terakhir adalah hari ini atau kemarin
    if ($latest_date->format('Y-m-d') === $today || $latest_date->diff(new DateTime($today))->days == 1) {
        $streak_count = 1;
        for ($i = 0; $i < count($streak_results) - 1; $i++) {
            $date1 = new DateTime($streak_results[$i]['tanggal_absensi']);
            $date2 = new DateTime($streak_results[$i+1]['tanggal_absensi']);
            if ($date1->diff($date2)->days == 1) {
                $streak_count++;
            } else {
                break; // Streak terputus
            }
        }
    }
}

// -- LOGIKA UNTUK PESAN SELAMAT DATANG --
$pesan_status = "Untuk mencatat kehadiran hari ini, silakan lakukan scan QR Code.";
if ($status_hari_ini && !$status_hari_ini['waktu_keluar']) {
    $pesan_status = "Anda sudah absen masuk. Jangan lupa scan lagi untuk absen pulang.";
} elseif ($status_hari_ini && $status_hari_ini['waktu_keluar']) {
    $pesan_status = "Anda sudah menyelesaikan absensi hari ini. Terima kasih.";
}
?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">

    <!-- Notifikasi Peringatan -->
    <?php if ($absen_tidak_lengkap): ?>
    <div class="mb-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg shadow-md" role="alert">
        <p class="font-bold">Peringatan</p>
        <p>Terdapat data absensi dari hari sebelumnya yang belum lengkap (lupa absen pulang). Harap hubungi admin untuk perbaikan data.</p>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h1>
                        <p class="text-slate-600 mt-1"><?php echo $pesan_status; ?></p>
                    </div>
                    <?php if (!$status_hari_ini || !$status_hari_ini['waktu_keluar']): ?>
                    <a href="scan.php" class="mt-4 sm:mt-0 sm:ml-4 flex-shrink-0 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-lg text-base transition-transform duration-300 hover:scale-105">Scan QR Code</a>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800 mb-4">Statistik Bulan Ini (<?php echo date('F Y'); ?>)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 text-center">
                        <div class="bg-green-100 p-3 rounded-full inline-block">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">Total Kehadiran</p>
                            <p class="text-2xl font-bold text-slate-800"><?php echo $stats['total_hadir'] ?? 0; ?> hari</p>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 text-center">
                        <div class="bg-orange-100 p-3 rounded-full inline-block">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">Tidak Hadir</p>
                            <p class="text-2xl font-bold text-slate-800"><?php echo $total_tidak_hadir; ?> hari</p>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 text-center">
                        <div class="bg-yellow-100 p-3 rounded-full inline-block">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">Keterlambatan</p>
                            <p class="text-2xl font-bold text-slate-800"><?php echo $stats['total_terlambat'] ?? 0; ?> hari</p>
                        </div>
                    </div>
                    <?php if ($streak_count > 1): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 text-center">
                        <div class="bg-purple-100 p-3 rounded-full inline-block">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.657 7.343A8 8 0 0118 18c-1-1-1-1-1-1zM6.343 18.657L4.93 17.243m13.128-1.414l1.414 1.414M4 10c.5 2 2 3 3 3s3-1 3-3c0-2-1-4-3-4S4 8 4 10z"></path></svg>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500">Streak Tepat Waktu</p>
                            <p class="text-2xl font-bold text-slate-800"><?php echo $streak_count; ?> hari</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 h-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-800">Riwayat Terakhir</h2>
                    <a href="rekap_absensi.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="space-y-3">
                    <?php if ($history->num_rows > 0):
                        while($row = $history->fetch_assoc()): ?>
                        <div class="p-3 rounded-lg <?php echo ($row['tanggal_absensi'] == $today) ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50'; ?>">
                            <p class="font-semibold text-slate-700 text-sm"><?php echo date("d F Y", strtotime($row['tanggal_absensi'])); ?></p>
                            <div class="flex justify-between items-center mt-1 text-xs text-slate-500 font-mono">
                                <span>Masuk: <?php echo $row['waktu_masuk'] ? date("H:i:s", strtotime($row['waktu_masuk'])) : '--:--:--'; ?></span>
                                <span>Pulang: <?php echo $row['waktu_keluar'] ? date("H:i:s", strtotime($row['waktu_keluar'])) : '--:--:--'; ?></span>
                            </div>
                        </div>
                        <?php endwhile;
                    else:
                        echo "<p class='text-center text-gray-500 py-10'>Belum ada riwayat.</p>";
                    endif;
                    $history->close(); ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
