<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../layouts/header.php';

// Ambil pengaturan interval saat ini untuk ditampilkan
$setting_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'qr_interval_minutes' LIMIT 1");
$current_interval = ($setting_result && $setting_result->num_rows > 0) ? $setting_result->fetch_assoc()['setting_value'] : 5;

$today = date("Y-m-d");
// Gunakan prepared statement untuk keamanan dan konsistensi
$stmt_absensi = $conn->prepare("SELECT u.nama_lengkap, a.waktu_masuk, a.status_masuk, a.waktu_keluar, a.status_keluar FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.tanggal_absensi = ? ORDER BY a.waktu_masuk DESC");
$stmt_absensi->bind_param("s", $today);
$stmt_absensi->execute();
$result_absensi = $stmt_absensi->get_result();
?>

<div class="max-w-7xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold mb-4 text-slate-800">QR Code Absensi</h2>
            <div id="qr-code-container" class="flex flex-col justify-center items-center h-auto bg-gray-200/50 rounded-lg p-4">
                <span class="text-gray-500">Memuat QR Code...</span>
            </div>

            <!-- Penampilan Token untuk Input Manual -->
            <div id="manual-code-display" class="text-center mt-4 hidden">
                <h3 class="text-sm font-semibold text-slate-600">Atau Masukkan Kode:</h3>
                <p id="token-display" class="text-4xl font-bold text-slate-800 tracking-widest py-2"></p>
            </div>
            
            <button id="download-btn" class="hidden mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                Download QR Code
            </button>
            <p id="interval-text" class="text-sm text-gray-500 mt-4 text-center" data-interval-minutes="<?php echo $current_interval; ?>">QR Code diperbarui setiap <?php echo $current_interval; ?> menit.</p>

            <form action="proses_interval.php" method="POST" class="mt-4 border-t border-gray-200 pt-4">
                <label for="interval-input" class="block text-sm font-medium text-gray-700 mb-1">Ubah Interval (menit)</label>
                <div class="flex items-center space-x-2">
                    <input type="number" name="interval" id="interval-input" min="1" value="<?php echo $current_interval; ?>" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full py-2 px-3 text-sm input-field" required>
                    <button type="submit" class="bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded-md text-sm transition-colors">Simpan</button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-xl font-bold mb-4 text-slate-800">Daftar Hadir Hari Ini (<?php echo date("d M Y"); ?>)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Nama Lengkap</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Masuk</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Status Masuk</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Jam Pulang</th>
                            <th class="py-3 px-4 text-left text-sm font-semibold text-slate-500 uppercase">Status Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if ($result_absensi->num_rows > 0): ?>
                            <?php while ($row = $result_absensi->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-slate-700"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_masuk']; ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_masuk'] == 'Tepat Waktu' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $row['status_masuk']; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-mono"><?php echo $row['waktu_keluar'] ?? '-'; ?></td>
                                    <td class="py-3 px-4">
                                        <?php if ($row['status_keluar']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $row['status_keluar'] == 'Selesai' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $row['status_keluar']; ?>
                                        </span>
                                        <?php else: echo '-'; endif; ?>
                                    </td>
                                 </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 px-4 text-center text-gray-500">Belum ada yang absen hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="qr-modal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50 transition-opacity duration-300" onclick="this.classList.add('hidden')">
    <div class="bg-white p-6 rounded-xl shadow-2xl">
        <img id="modal-qr-image" src="" alt="QR Code Diperbesar" class="w-80 h-80">
        <p class="text-center text-gray-500 mt-4">Klik di luar gambar untuk menutup</p>
    </div>
</div>

<script>
    let currentQrDataUri = ''; // Pindahkan ke scope global agar bisa diakses fungsi lain
    let currentQrUrl = ''; // Pindahkan ini juga jika Anda masih menggunakannya di modal

    function fetchQrCode() {
        const qrContainer = document.getElementById('qr-code-container');
        const tokenDisplay = document.getElementById('token-display');
        const manualCodeDisplay = document.getElementById('manual-code-display');
        const downloadBtn = document.getElementById('download-btn');

        fetch('generate_qr.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.token && data.dataUri) {
                    currentQrDataUri = data.dataUri; 
                    qrContainer.innerHTML = `<img src="${data.dataUri}" alt="QR Code Absensi" class="w-64 h-64 cursor-pointer" title="Klik untuk perbesar">`;
                    
                    tokenDisplay.textContent = data.token.match(/.{1,3}/g).join('-'); // Format token: 123-456
                    manualCodeDisplay.classList.remove('hidden');

                    downloadBtn.classList.remove('hidden');
                } else {
                    qrContainer.innerHTML = '<span class="text-red-500">Gagal memuat QR Code.</span>';
                }
            })
            .catch(error => {
                console.error('Gagal memuat QR Code:', error);
                qrContainer.innerHTML = `<span class="text-red-500">Error! Cek Console (F12).</span>`;
            });
    }

    // Panggil event listener di luar fungsi agar tidak dibuat berulang kali
    document.getElementById('qr-code-container').addEventListener('click', () => {
        if (currentQrDataUri) {
            // Kita perbesar dengan scale yang lebih tinggi di library, jadi modal tidak perlu URL berbeda
            document.getElementById('modal-qr-image').src = currentQrDataUri;
            document.getElementById('qr-modal').classList.remove('hidden');
        }
    });

    document.getElementById('download-btn').addEventListener('click', () => {
        if (currentQrDataUri) {
            const link = document.createElement('a');
            link.href = currentQrDataUri;
            link.download = 'qrcode_absensi.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });

    // Jalankan fetch pertama kali
    fetchQrCode();
    // Atur interval
    const intervalText = document.getElementById('interval-text');
    const intervalMinutes = intervalText.dataset.intervalMinutes || 5; // Fallback ke 5 menit
    const intervalMilliseconds = parseInt(intervalMinutes) * 60 * 1000;
    if (intervalMilliseconds > 0) setInterval(fetchQrCode, intervalMilliseconds);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>