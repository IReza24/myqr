<?php
$page_type = 'auth';
$page_title = "Login";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm border border-gray-200">
        <h1 class="text-3xl font-bold mb-6 text-center">
            <span class="bg-gradient-to-r from-blue-500 to-purple-500 text-transparent bg-clip-text">Absensi QR</span>
        </h1>
        
        <form action="proses_login.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-slate-600 text-sm font-medium mb-2">Username</label>
                <input type="text" id="username" name="username" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full py-2 px-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-slate-600 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" class="bg-gray-50 border border-gray-300 text-slate-900 rounded-md w-full py-2 px-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="flex flex-col items-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-blue-500 transition-colors duration-300">
                    Login
                </button>
                <a href="register.php" class="mt-4 text-sm text-blue-500 hover:underline">
                    Belum punya akun? Daftar di sini
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>