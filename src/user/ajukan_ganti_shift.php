<?php
$page_title = "Ajukan Ganti Shift";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// Ambil shift user saat ini
$stmt_current_shift = $conn->prepare("SELECT s.id, s.nama_shift FROM users u JOIN shifts s ON u.shift_id = s.id WHERE u.id = ?");
$stmt_current_shift->bind_param("i", $user_id);
$stmt_current_shift->execute();
$current_shift_result = $stmt_current_shift->get_result();
$current_shift = $current_shift_result->fetch_assoc();
$stmt_current_shift->close();

if (!$current_shift) {
    echo "<div class='max-w-4xl mx-auto py-8 sm:px-6 lg:px-8'><p class='text-red-500'>Anda tidak memiliki shift utama. Hubungi admin.</p></div>";
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

// Ambil semua shift yang tersedia untuk pilihan
$all_shifts_result = $conn->query("SELECT id, nama_shift FROM shifts");

// Ambil tanggal dari URL jika ada (dari klik kalender)
$tanggal_dari_kalender = isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : '';

?>

<div class="max-w-2xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border">
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Formulir Pengajuan Ganti Shift</h1>

        <form action="proses_ajukan_ganti_shift.php" method="POST">
            <input type="hidden" name="shift_id_lama" value="<?php echo $current_shift['id']; ?>">

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Shift Anda Saat Ini</label>
                    <p class="mt-1 text-lg font-semibold text-slate-600"><?php echo htmlspecialchars($current_shift['nama_shift']); ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?php echo $tanggal_dari_kalender; ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?php echo $tanggal_dari_kalender; ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label for="shift_id_baru" class="block text-sm font-medium text-slate-700">Ganti Ke Shift</label>
                    <select name="shift_id_baru" id="shift_id_baru" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="" disabled selected>-- Pilih Shift Baru --</option>
                        <?php while ($shift = $all_shifts_result->fetch_assoc()): ?>
                            <?php if ($shift['id'] !== $current_shift['id']): ?>
                                <option value="<?php echo $shift['id']; ?>"><?php echo htmlspecialchars($shift['nama_shift']); ?></option>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label for="alasan" class="block text-sm font-medium text-slate-700">Alasan</label>
                    <textarea name="alasan" id="alasan" rows="4" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
