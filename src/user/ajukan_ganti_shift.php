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

// Validasi jika tanggal dari kalender adalah hari libur
if (!empty($tanggal_dari_kalender)) {
    $day_of_week = date('w', strtotime($tanggal_dari_kalender)); // 0 = Minggu, 6 = Sabtu
    if ($day_of_week == 0 || $day_of_week == 6) {
        $_SESSION['toast'] = ['message' => 'Tidak dapat mengajukan ganti shift pada hari libur (Sabtu/Minggu).', 'type' => 'error'];
        // Kosongkan tanggal agar pengguna harus memilih tanggal yang valid
        $tanggal_dari_kalender = '';
    }
}

?>

<div class="max-w-2xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border">
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Formulir Pengajuan Ganti Shift</h1>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Perhatian:</strong> Pengajuan ganti shift tidak dapat dilakukan pada hari libur (Sabtu dan Minggu).
                    </p>
                </div>
            </div>
        </div>

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
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?php echo $tanggal_dari_kalender; ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 input-field">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?php echo $tanggal_dari_kalender; ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 input-field">
                    </div>
                </div>

                <div>
                    <label for="shift_id_baru" class="block text-sm font-medium text-slate-700">Ganti Ke Shift</label>
                    <select name="shift_id_baru" id="shift_id_baru" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md select-field">
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
                    <textarea name="alasan" id="alasan" rows="4" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 textarea-field"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
