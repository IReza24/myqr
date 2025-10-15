<?php
$page_title = "Manajemen Shift";
require_once __DIR__ . '/../layouts/header.php';

// Query untuk mengambil data shift beserta jumlah karyawan yang menggunakannya
$sql = "SELECT s.*, COUNT(u.id) as jumlah_karyawan
        FROM shifts s
        LEFT JOIN users u ON s.id = u.shift_id
        GROUP BY s.id
        ORDER BY s.jam_masuk";
$result = $conn->query($sql);

?>

<div class="max-w-5xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Daftar Shift</h1>
            <a href="tambah_shift.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                + Tambah Shift Baru
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Masuk</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                        <th class="py-3 px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Karyawan</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="py-3 px-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($row['nama_shift']); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono"><?php echo date("H:i", strtotime($row['jam_masuk'])); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono"><?php echo date("H:i", strtotime($row['batas_jam_masuk'])); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap font-mono"><?php echo date("H:i", strtotime($row['jam_pulang'])); ?></td>
                                <td class="py-3 px-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-sm font-semibold leading-5 rounded-full <?php echo $row['jumlah_karyawan'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $row['jumlah_karyawan']; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_shift.php?id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit Shift">Edit</a>
                                    <?php if ($row['jumlah_karyawan'] > 0): ?>
                                        <span class="text-gray-400 cursor-not-allowed ml-4" title="Shift tidak bisa dihapus karena masih digunakan oleh <?php echo $row['jumlah_karyawan']; ?> karyawan">Hapus</span>
                                    <?php else: ?>
                                        <a href="proses_shift.php?action=hapus&id=<?php echo $row['id']; ?>" class="delete-btn text-red-600 hover:text-red-900 ml-4" title="Hapus Shift">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">Belum ada data shift.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Mencegah link langsung dieksekusi
            const url = this.href;

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data shift yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url; // Lanjutkan ke URL penghapusan
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
