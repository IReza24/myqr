<?php
$page_title = "Edit Absensi";
require_once __DIR__ . '/../layouts/header.php';

// Pastikan ID absensi ada
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID Absensi tidak valid.";
    header("Location: rekap.php");
    exit;
}

$absensi_id = $_GET['id'];

// Ambil data absensi dari database
$stmt = $conn->prepare("SELECT a.*, u.nama_lengkap FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt->bind_param("i", $absensi_id);
$stmt->execute();
$result = $stmt->get_result();
$absensi = $result->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan
if (!$absensi) {
    $_SESSION['error_message'] = "Data absensi tidak ditemukan.";
    header("Location: rekap.php");
    exit;
}

// Jika sudah ada waktu keluar, redirect saja
if (!is_null($absensi['waktu_keluar'])) {
    $_SESSION['info_message'] = "Data absensi ini sudah lengkap.";
    header("Location: rekap.php");
    exit;
}
?>

<div class="max-w-2xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold mb-6 text-center">Edit Waktu Pulang</h1>

        <form action="proses_edit_absensi.php" method="POST">
            <input type="hidden" name="absensi_id" value="<?php echo $absensi['id']; ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nama Karyawan</label>
                <input type="text" value="<?php echo htmlspecialchars($absensi['nama_lengkap']); ?>" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Tanggal Absensi</label>
                <input type="text" value="<?php echo date("d F Y", strtotime($absensi['tanggal_absensi'])); ?>" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Waktu Masuk</label>
                <input type="text" value="<?php echo $absensi['waktu_masuk']; ?>" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm" readonly>
            </div>

            <div class="mb-6">
                <label for="waktu_keluar" class="block text-sm font-medium text-gray-700">Waktu Pulang (Baru)</label>
                <input type="time" name="waktu_keluar" id="waktu_keluar" step="1" required class="mt-1 block w-full border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                <p class="mt-2 text-xs text-gray-500">Masukkan waktu pulang yang benar untuk karyawan ini.</p>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="rekap.php" class="text-sm text-gray-600 hover:underline">Batal</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
