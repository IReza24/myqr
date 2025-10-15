<?php
$page_title = "Jadwal Saya";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// 1. Ambil shift pengguna
$stmt_shift = $conn->prepare(
    "SELECT s.nama_shift, s.jam_masuk, s.jam_pulang 
     FROM users u 
     JOIN shifts s ON u.shift_id = s.id 
     WHERE u.id = ?"
);
$stmt_shift->bind_param("i", $user_id);
$stmt_shift->execute();
$shift_result = $stmt_shift->get_result();
$shift_info = $shift_result->fetch_assoc();
$stmt_shift->close();

// 2. Ambil semua pengajuan ganti shift yang disetujui dan relevan dengan bulan ini
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$start_of_month_sql = "$year-$month-01";
$end_of_month_sql = date('Y-m-t', strtotime($start_of_month_sql));

$stmt_temp_shifts = $conn->prepare(
    "SELECT pgs.tanggal_mulai, pgs.tanggal_selesai, s.nama_shift 
     FROM pengajuan_ganti_shift pgs
     JOIN shifts s ON pgs.shift_id_baru = s.id
     WHERE pgs.user_id = ? 
       AND pgs.status = 'disetujui'
       AND pgs.tanggal_mulai <= ? AND pgs.tanggal_selesai >= ?"
);
$stmt_temp_shifts->bind_param("iss", $user_id, $end_of_month_sql, $start_of_month_sql);
$stmt_temp_shifts->execute();
$temp_shifts_result = $stmt_temp_shifts->get_result();

// Proses menjadi array yang mudah dicari berdasarkan tanggal
$temporary_shifts = [];
while ($row = $temp_shifts_result->fetch_assoc()) {
    $period = new DatePeriod(new DateTime($row['tanggal_mulai']), new DateInterval('P1D'), (new DateTime($row['tanggal_selesai']))->modify('+1 day'));
    foreach ($period as $date) {
        $temporary_shifts[$date->format('Y-m-d')] = $row['nama_shift'];
    }
}
$stmt_temp_shifts->close();

// 3. Ambil riwayat absensi untuk bulan ini untuk menandai kalender
$stmt_absensi = $conn->prepare(
    "SELECT tanggal_absensi, status_masuk 
     FROM absensi 
     WHERE user_id = ? AND tanggal_absensi BETWEEN ? AND ?"
);
$stmt_absensi->bind_param("iss", $user_id, $start_of_month_sql, $end_of_month_sql);
$stmt_absensi->execute();
$absensi_result = $stmt_absensi->get_result();

$attendance_history = [];
while ($row = $absensi_result->fetch_assoc()) {
    $attendance_history[$row['tanggal_absensi']] = $row['status_masuk'];
}
$stmt_absensi->close();


$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day_of_month);
$day_of_week_of_first_day = date('w', $first_day_of_month); // 0 for Sunday, 6 for Saturday
$month_name = date('F Y', $first_day_of_month);

$days_of_week = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Navigasi bulan
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) {
    $next_month = 1;
    $next_year++;
}
?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <!-- Header Kalender dan Navigasi -->
    <div class="flex justify-between items-center mb-6 px-2">
        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="p-2 rounded-full hover:bg-gray-100">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 text-center"><?php echo $month_name; ?></h1>
        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="p-2 rounded-full hover:bg-gray-100">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kalender -->
        <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full table-fixed border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <?php foreach ($days_of_week as $day): ?>
                                <th class="py-3 px-2 text-center text-sm font-semibold text-slate-500 uppercase border border-gray-200"><?php echo $day; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_day = 1;
                        echo "<tr>";
                        // Sel kosong sebelum hari pertama bulan ini
                        for ($i = 0; $i < $day_of_week_of_first_day; $i++) {
                            echo "<td class='h-28 border border-gray-200 bg-gray-50'></td>";
                        }

                        // Loop melalui semua hari dalam bulan
                        while ($current_day <= $days_in_month) {
                            $day_of_week = date('w', mktime(0, 0, 0, $month, $current_day, $year));
                            
                            // Jika hari pertama, mulai baris baru
                            if ($day_of_week == 0 && $current_day != 1) {
                                echo "</tr><tr>";
                            }

                            $current_date_str = date('Y-m-d', mktime(0, 0, 0, $month, $current_day, $year));
                            $today_str = date('Y-m-d');
                            $is_today = ($current_date_str == $today_str);
                            $is_past = ($current_date_str < $today_str);

                            $cell_class = 'h-28 border border-gray-200 p-2 align-top relative';
                            if ($is_past) {
                                $cell_class .= ' bg-gray-50 cursor-not-allowed';
                            } else {
                                $cell_class .= ' hover:bg-blue-50 cursor-pointer transition-colors';
                            }

                            $day_number_class = $is_today ? 'bg-blue-600 text-white rounded-full w-7 h-7 flex items-center justify-center font-bold' : 'font-medium text-slate-600';

                            // --- PERUBAHAN UTAMA: Membuat sel bisa diklik ---
                            if (!$is_past) {
                                $link_url = "ajukan_ganti_shift.php?tanggal=" . $current_date_str;
                                echo "<td class='$cell_class' onclick=\"window.location.href='$link_url'\" title='Ajukan ganti shift untuk tanggal ini'>";
                            } else {
                                echo "<td class='$cell_class'>";
                            }
                            echo "<div class='$day_number_class'>$current_day</div>";
                            
                            // --- LOGIKA BARU YANG DIPERBAIKI ---
                            
                            // Tampilkan jadwal untuk hari ini dan masa depan
                            if (!$is_past) {
                                if (isset($temporary_shifts[$current_date_str])) {
                                    echo "<div class='mt-1 text-xs p-1 rounded bg-purple-100 text-purple-800 text-center truncate' title='Shift Pengganti'>";
                                    echo htmlspecialchars($temporary_shifts[$current_date_str]);
                                    echo "</div>";
                                } elseif ($shift_info && $day_of_week != 0 && $day_of_week != 6) {
                                    echo "<div class='mt-1 text-xs p-1 rounded bg-blue-100 text-blue-800 text-center truncate'>";
                                    echo htmlspecialchars($shift_info['nama_shift']);
                                    echo "</div>";
                                } else { // Hari libur atau tidak ada shift
                                    echo "<div class='mt-1 text-xs p-1 rounded bg-gray-200 text-gray-600 text-center'>Libur</div>";
                                }
                            }

                            // Tampilkan ikon status untuk hari yang sudah lewat
                            if ($is_past) {
                                if ($day_of_week != 0 && $day_of_week != 6) { // Bukan akhir pekan
                                    if (isset($attendance_history[$current_date_str])) {
                                        if ($attendance_history[$current_date_str] == 'Tepat Waktu') {
                                            echo "<div class='absolute bottom-2 right-2 text-green-500' title='Hadir Tepat Waktu'><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path></svg></div>";
                                        } else { // Terlambat
                                            echo "<div class='absolute bottom-2 right-2 text-yellow-500' title='Hadir Terlambat'><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z' clip-rule='evenodd'></path></svg></div>";
                                        }
                                    } else { // Tidak ada data absensi
                                        echo "<div class='absolute bottom-2 right-2 text-red-500' title='Tidak Hadir'><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path></svg></div>";
                                    }
                                }
                            }

                            echo "</td>";

                            // Jika hari terakhir dalam seminggu, tutup baris
                            if ($day_of_week == 6) {
                                echo "</tr>";
                            }
                            $current_day++;
                        }

                        // Sel kosong setelah hari terakhir bulan ini
                        if ($day_of_week != 6) {
                            while ($day_of_week < 6) {
                                echo "<td class='h-28 border border-gray-200 bg-gray-50'></td>";
                                $day_of_week++;
                            }
                        }
                        echo "</tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Keterangan -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-8">
                <h3 class="text-lg font-semibold text-slate-700 mb-4">Keterangan</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full bg-blue-100 border border-blue-300 mr-3 flex-shrink-0"></span>
                        <span class="text-slate-600">Jadwal Shift Normal</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full bg-purple-100 border border-purple-300 mr-3 flex-shrink-0"></span>
                        <span class="text-slate-600">Jadwal Shift Pengganti</span>
                    </div>
                    <div class="flex items-center">
                        <div class="text-green-500 mr-2 flex-shrink-0"><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path></svg></div>
                        <span class="text-slate-600">Hadir Tepat Waktu</span>
                    </div>
                    <div class="flex items-center">
                        <div class="text-yellow-500 mr-2 flex-shrink-0"><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z' clip-rule='evenodd'></path></svg></div>
                        <span class="text-slate-600">Hadir Terlambat</span>
                    </div>
                    <div class="flex items-center">
                        <div class="text-red-500 mr-2 flex-shrink-0"><svg class='w-5 h-5' fill='currentColor' viewBox='0 0 20 20'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path></svg></div>
                        <span class="text-slate-600">Tidak Hadir</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
