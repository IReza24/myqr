<?php
$page_title = "Review Pengajuan Ganti Shift";
require_once __DIR__ . '/../layouts/header.php';

// Tentukan tab aktif
$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

// Ambil data pengajuan berdasarkan tab yang aktif
$sql = 
    "SELECT 
        p.id, 
        u.nama_lengkap, 
        p.tanggal_mulai, 
        p.tanggal_selesai, 
        s_lama.nama_shift as shift_lama, 
        s_baru.nama_shift as shift_baru, 
        p.alasan, 
        p.created_at
     FROM pengajuan_ganti_shift p
     JOIN users u ON p.user_id = u.id
     JOIN shifts s_lama ON p.shift_id_lama = s_lama.id
     JOIN shifts s_baru ON p.shift_id_baru = s_baru.id
     WHERE p.status = ?
     ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tab_aktif);
$stmt->execute();
$pengajuan = $stmt->get_result();
$stmt->close();

?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-4 text-slate-800">Pengajuan Ganti Shift</h1>

    <!-- Navigasi Tab -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <a href="?tab=pending" class="<?php echo $tab_aktif == 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                Pending
            </a>
            <a href="?tab=disetujui" class="<?php echo $tab_aktif == 'disetujui' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                Disetujui
            </a>
            <a href="?tab=ditolak" class="<?php echo $tab_aktif == 'ditolak' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                Ditolak
            </a>
        </nav>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <div class="overflow-x-auto">
            <?php if ($pengajuan->num_rows == 0): ?>
                <div class="text-center py-10 text-gray-500">
                    Tidak ada data pengajuan dengan status '<?php echo htmlspecialchars($tab_aktif); ?>'.
                </div>
            <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Karyawan</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Dari Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Ke Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Alasan</th>
                        <?php if ($tab_aktif == 'pending'): ?>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $pengajuan->fetch_assoc()): ?>
                            <tr>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                    <div class="text-xs text-gray-500">Diajukan: <?php echo date("d M Y", strtotime($row['created_at'])); ?></div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-900"><?php echo date("d M Y", strtotime($row['tanggal_mulai'])); ?> - <?php echo date("d M Y", strtotime($row['tanggal_selesai'])); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['shift_lama']); ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($row['shift_baru']); ?></td>
                                <td class="py-4 px-4 text-sm text-gray-500 max-w-xs truncate"><?php echo htmlspecialchars($row['alasan']); ?></td>
                                <?php if ($tab_aktif == 'pending'): ?>
                                    <td class="py-4 px-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="proses_review_pengajuan.php?id=<?php echo $row['id']; ?>&status=disetujui" class="text-green-600 hover:text-green-900" onclick="return confirm('Anda yakin ingin menyetujui pengajuan ini?')">Setujui</a>
                                        <a href="proses_review_pengajuan.php?id=<?php echo $row['id']; ?>&status=ditolak" class="text-red-600 hover:text-red-900" onclick="return confirm('Anda yakin ingin menolak pengajuan ini?')">Tolak</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
