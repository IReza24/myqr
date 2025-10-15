<?php
$page_title = "Riwayat Pengajuan Ganti Shift";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// Ambil riwayat pengajuan dari database
$stmt = $conn->prepare(
    "SELECT 
        p.tanggal_mulai, 
        p.tanggal_selesai, 
        s_lama.nama_shift as shift_lama, 
        s_baru.nama_shift as shift_baru, 
        p.alasan, 
        p.status, 
        p.created_at
     FROM pengajuan_ganti_shift p
     JOIN shifts s_lama ON p.shift_id_lama = s_lama.id
     JOIN shifts s_baru ON p.shift_id_baru = s_baru.id
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$riwayat = $stmt->get_result();
$stmt->close();

function get_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>';
        case 'disetujui':
            return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>';
        case 'ditolak':
            return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>';
        default:
            return '<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Tidak Diketahui</span>';
    }
}
?>

<div class="max-w-5xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pengajuan Anda</h1>
        <a href="ajukan_ganti_shift.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">+ Ajukan Ganti Shift</a>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tgl. Pengajuan</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Dari Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Ke Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($riwayat->num_rows > 0): ?>
                        <?php while ($row = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500"><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-900"><?php echo date("d M Y", strtotime($row['tanggal_mulai'])); ?> - <?php echo date("d M Y", strtotime($row['tanggal_selesai'])); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['shift_lama']); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($row['shift_baru']); ?></td>
                                <td class="py-4 px-4 text-sm text-gray-500 max-w-xs truncate"><?php echo htmlspecialchars($row['alasan']); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm"><?php echo get_status_badge($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-gray-500">
                                Anda belum pernah mengajukan ganti shift.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
