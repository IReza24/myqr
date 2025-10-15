<?php
$page_title = "Tambah Shift Baru";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Form Tambah Shift</h1>
        
        <form action="proses_shift.php" method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="mb-4">
                <label for="nama_shift" class="block text-slate-600 text-sm font-medium mb-2">Nama Shift</label>
                <input type="text" id="nama_shift" name="nama_shift" class="bg-gray-50 border border-gray-300 rounded-md w-full p-2" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="jam_masuk" class="block text-slate-600 text-sm font-medium mb-2">Jam Masuk</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" class="bg-gray-50 border border-gray-300 rounded-md w-full p-2" required>
                </div>
                <div>
                    <label for="batas_jam_masuk" class="block text-slate-600 text-sm font-medium mb-2">Batas Toleransi</label>
                    <input type="time" id="batas_jam_masuk" name="batas_jam_masuk" class="bg-gray-50 border border-gray-300 rounded-md w-full p-2" required>
                </div>
                <div>
                    <label for="jam_pulang" class="block text-slate-600 text-sm font-medium mb-2">Jam Pulang</label>
                    <input type="time" id="jam_pulang" name="jam_pulang" class="bg-gray-50 border border-gray-300 rounded-md w-full p-2" required>
                </div>
            </div>
            
            <!-- Pesan error validasi -->
            <div id="time-validation-error" class="text-red-600 text-sm mt-3 h-5"></div>

            <div class="mt-6 flex justify-end space-x-4">
                <a href="shifts.php" class="bg-gray-200 hover:bg-gray-300 text-slate-800 font-bold py-2 px-6 rounded-lg">Batal</a>
                <button type="submit" class="text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">Simpan Shift</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jamMasukInput = document.getElementById('jam_masuk');
    const batasMasukInput = document.getElementById('batas_jam_masuk');
    const jamPulangInput = document.getElementById('jam_pulang');
    const submitButton = document.querySelector('button[type="submit"]');
    const errorMessageDiv = document.getElementById('time-validation-error');

    function validateTimes() {
        const masuk = jamMasukInput.value;
        const batas = batasMasukInput.value;
        const pulang = jamPulangInput.value;

        let errorMessage = '';
        let isValid = true;

        if (masuk && batas && batas <= masuk) {
            errorMessage = 'Batas Toleransi harus setelah Jam Masuk.';
            isValid = false;
        } else if (batas && pulang && pulang <= batas) {
            errorMessage = 'Jam Pulang harus setelah Batas Toleransi.';
            isValid = false;
        }

        errorMessageDiv.textContent = errorMessage;

        if (isValid) {
            submitButton.disabled = false;
            submitButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            submitButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        }
    }

    jamMasukInput.addEventListener('input', validateTimes);
    batasMasukInput.addEventListener('input', validateTimes);
    jamPulangInput.addEventListener('input', validateTimes);

    // Initial validation check on page load
    validateTimes();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
