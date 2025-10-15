<?php
$page_title = "Pengaturan Jadwal";
require_once __DIR__ . '/../layouts/header.php';

// Ambil data pengaturan saat ini
$result = $conn->query("SELECT * FROM pengaturan WHERE id = 1");
$pengaturan = $result->fetch_assoc();
?>

<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Pengaturan Jadwal Kerja</h1>
        
        <form action="proses_pengaturan.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="jam_masuk" class="block text-slate-600 text-sm font-medium mb-2">Jam Masuk Standar</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" value="<?php echo $pengaturan['jam_masuk']; ?>" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full p-2 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="batas_jam_masuk" class="block text-slate-600 text-sm font-medium mb-2">Batas Toleransi Masuk</label>
                    <input type="time" id="batas_jam_masuk" name="batas_jam_masuk" value="<?php echo $pengaturan['batas_jam_masuk']; ?>" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full p-2 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="jam_pulang" class="block text-slate-600 text-sm font-medium mb-2">Jam Pulang Standar</label>
                    <input type="time" id="jam_pulang" name="jam_pulang" value="<?php echo $pengaturan['jam_pulang']; ?>" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full p-2 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-8 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>