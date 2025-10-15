<?php
// Force no-caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$page_title = "Rekap Absensi";
require_once __DIR__ . '/../layouts/header.php';

// Ambil data untuk filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$nama_karyawan_filter = isset($_GET['nama_karyawan']) ? trim($_GET['nama_karyawan']) : '';

$nama_bulan = DateTime::createFromFormat('!m', $bulan)->format('F');

// --- LOGIKA BARU: HITUNG HARI KERJA (Senin-Jumat) ---
// Dihitung untuk menentukan potensi "tidak hadir".
$hari_kerja = 0;
$jumlah_hari_bulan_ini = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$end_day = $jumlah_hari_bulan_ini;

// Jika bulan dan tahun yang dipilih adalah bulan dan tahun saat ini,
// maka hitung hari kerja hanya sampai hari ini.
if ($bulan == date('m') && $tahun == date('Y')) {
    $end_day = date('d');
}

for ($i = 1; $i <= $end_day; $i++) {
    $tanggal = "$tahun-$bulan-$i";
    $hari = date('N', strtotime($tanggal)); // 1 (Senin) - 7 (Minggu)
    if ($hari < 6) { // Hanya hitung Senin sampai Jumat
        $hari_kerja++;
    }
}

// Persiapkan parameter untuk query
$params = [];
$param_types = '';

// Base SQL
$base_sql_where = " WHERE MONTH(a.tanggal_absensi) = ? AND YEAR(a.tanggal_absensi) = ?";
$param_types .= "ss";
$params[] = $bulan;
$params[] = $tahun;

// Tambahkan filter nama karyawan jika diisi
if (!empty($nama_karyawan_filter)) {
    $base_sql_where .= " AND u.nama_lengkap LIKE ?";
    $param_types .= "s";
    $search_term = "%" . $nama_karyawan_filter . "%";
    $params[] = $search_term;
}

// --- HITUNG JUMLAH KARYAWAN AKTIF UNTUK PERHITUNGAN --- 
// Query ini menyesuaikan dengan filter nama karyawan
$sql_total_users = "SELECT COUNT(id) as total_users FROM users WHERE role = 'user'";
if (!empty($nama_karyawan_filter)) {
    $sql_total_users .= " AND nama_lengkap LIKE ?";
    $stmt_total_users = $conn->prepare($sql_total_users);
    $stmt_total_users->bind_param("s", $search_term);
} else {
    $stmt_total_users = $conn->prepare($sql_total_users);
}
$stmt_total_users->execute();
$total_users = $stmt_total_users->get_result()->fetch_assoc()['total_users'];
$stmt_total_users->close();


// Query untuk Statistik
$sql_stats = "SELECT
                COUNT(a.id) as total_kehadiran,
                SUM(CASE WHEN a.status_masuk = 'Terlambat' THEN 1 ELSE 0 END) as total_terlambat,
                SUM(CASE WHEN a.status_keluar = 'Pulang Cepat' THEN 1 ELSE 0 END) as total_pulang_cepat,
                SUM(CASE WHEN a.status_keluar = 'Lembur' THEN 1 ELSE 0 END) as total_lembur
              FROM absensi a
              JOIN users u ON a.user_id = u.id
              " . $base_sql_where;

$stmt_stats = $conn->prepare($sql_stats);
if ($stmt_stats && !empty($params)) {
    $stmt_stats->bind_param($param_types, ...$params);
}
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// Hitung total tidak hadir berdasarkan asumsi hari kerja
$total_tidak_hadir = max(0, ($total_users * $hari_kerja) - $result_stats['total_kehadiran']);


// Query untuk Tabel Rekap
$sql_rekap = "SELECT a.id, u.nama_lengkap, a.tanggal_absensi, a.waktu_masuk, a.status_masuk, a.waktu_keluar, a.status_keluar
              FROM absensi a
              JOIN users u ON a.user_id = u.id
              " . $base_sql_where . " ORDER BY u.nama_lengkap, a.tanggal_absensi";

$stmt_rekap = $conn->prepare($sql_rekap);
if ($stmt_rekap && !empty($params)) {
    $stmt_rekap->bind_param($param_types, ...$params);
}
$stmt_rekap->execute();
$result_rekap = $stmt_rekap->get_result();
$stmt_rekap->close();
?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold mb-4">Rekap Absensi - <?php echo "$nama_bulan $tahun"; ?></h1>

        <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4">
            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                <select name="bulan" id="bulan" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php if ($i == $bulan) echo 'selected'; ?>>
                            <?php echo DateTime::createFromFormat('!m', $i)->format('F'); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                <input type="number" name="tahun" id="tahun" value="<?php echo $tahun; ?>" class="mt-1 block w-full border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            </div>
            <div>
                <label for="nama_karyawan" class="block text-sm font-medium text-gray-700">Nama Karyawan</label>
                <input type="text" name="nama_karyawan" id="nama_karyawan" value="<?php echo htmlspecialchars($nama_karyawan_filter); ?>" placeholder="Cari nama..." class="mt-1 block w-full border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            </div>
            <button type="submit" class="w-full justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm">Tampilkan</button>
        </form>

        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg shadow border border-gray-200">
                <h3 class="text-sm font-medium text-gray-500">Total Kehadiran</h3>
                <p class="mt-1 text-3xl font-semibold text-gray-900">
                    <?php echo $result_stats['total_kehadiran'] ?? 0; ?>
                </p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg shadow border border-orange-200">
                <h3 class="text-sm font-medium text-orange-600">Total Tidak Hadir</h3>
                <p class="mt-1 text-3xl font-semibold text-orange-800">
                    <?php echo $total_tidak_hadir; ?>
                </p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg shadow border border-red-200">
                <h3 class="text-sm font-medium text-red-600">Total Terlambat</h3>
                <p class="mt-1 text-3xl font-semibold text-red-800">
                    <?php echo $result_stats['total_terlambat'] ?? 0; ?>
                </p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg shadow border border-yellow-200">
                <h3 class="text-sm font-medium text-yellow-600">Total Pulang Cepat</h3>
                <p class="mt-1 text-3xl font-semibold text-yellow-800">
                    <?php echo $result_stats['total_pulang_cepat'] ?? 0; ?>
                </p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg shadow border border-purple-200">
                <h3 class="text-sm font-medium text-purple-600">Total Lembur</h3>
                <p class="mt-1 text-3xl font-semibold text-purple-800">
                    <?php echo $result_stats['total_lembur'] ?? 0; ?>
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pulang</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result_rekap->num_rows > 0): ?>
                        <?php while ($row = $result_rekap->fetch_assoc()): ?>
                            <tr>
                                <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap"><?php echo date("d-m-Y", strtotime($row['tanggal_absensi'])); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono"><?php echo $row['waktu_masuk']; ?></td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_masuk'] == 'Tepat Waktu' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($row['status_masuk']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono"><?php echo $row['waktu_keluar'] ?? '-'; ?></td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                <?php
                                if ($row['status_keluar']) {
                                    $status = $row['status_keluar'];
                                    $color_class = '';
                                    switch ($status) {
                                        case 'Selesai':
                                            $color_class = 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'Pulang Cepat':
                                            $color_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'Lembur':
                                            $color_class = 'bg-purple-100 text-purple-800';
                                            break;
                                        default:
                                            $color_class = 'bg-gray-100 text-gray-800';
                                            break;
                                    }
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $color_class . '">' . htmlspecialchars($status) . '</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                    <?php if (is_null($row['waktu_keluar'])): ?>
                                        <a href="edit_absensi.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4">Tidak ada data absensi untuk periode dan filter yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
