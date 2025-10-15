<?php
$page_title = "Ganti Password";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-lg mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Ganti Password</h1>
            <a href="profil.php" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Profil</a>
        </div>

        <form action="proses_ganti_password.php" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="password_lama" class="block text-sm font-medium text-slate-700">Password Saat Ini</label>
                    <input type="password" name="password_lama" id="password_lama" required 
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="password_baru" class="block text-sm font-medium text-slate-700">Password Baru</label>
                    <input type="password" name="password_baru" id="password_baru" required minlength="8"
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
                </div>

                <div>
                    <label for="konfirmasi_password" class="block text-sm font-medium text-slate-700">Konfirmasi Password Baru</label>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" required
                           class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
