<?php
$page_title = "Rekap Absensi";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// Set default date range to the current month
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

// Override with user input if provided
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}

// Query to get attendance data within the date range
$stmt = $conn->prepare(
    "SELECT tanggal_absensi, waktu_masuk, status_masuk, waktu_keluar, status_keluar 
     FROM absensi 
     WHERE user_id = ? AND tanggal_absensi BETWEEN ? AND ? 
     ORDER BY tanggal_absensi DESC"
);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800 mb-4 sm:mb-0">Rekapitulasi Absensi</h1>
            <form method="GET" action="rekap_absensi.php" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="form-input rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full sm:w-auto input-field">
                <span class="text-center sm:py-2">to</span>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="form-input rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-full sm:w-auto input-field">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Tanggal</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Masuk</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Status Masuk</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Pulang</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Status Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-slate-700"><?php echo date("d F Y", strtotime($row['tanggal_absensi'])); ?></td>
                            <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_masuk'] ? date("H:i:s", strtotime($row['waktu_masuk'])) : '-'; ?></td>
                            <td class="py-3 px-4 text-slate-600">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_masuk'] === 'Tepat Waktu' ? 'bg-green-100 text-green-800' : ($row['status_masuk'] === 'Terlambat' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo $row['status_masuk'] ?? '-'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_keluar'] ? date("H:i:s", strtotime($row['waktu_keluar'])) : '-'; ?></td>
                            <td class="py-3 px-4 text-slate-600">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_keluar'] === 'Selesai' ? 'bg-blue-100 text-blue-800' : ($row['status_keluar'] === 'Pulang Cepat' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo $row['status_keluar'] ?? '-'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">Tidak ada data absensi untuk periode yang dipilih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php $stmt->close(); ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
