<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Absensi QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-gray-200 min-h-screen flex items-center justify-center py-12">
    <div class="bg-slate-800 p-8 rounded-xl shadow-2xl w-full max-w-sm border border-slate-700">
        <h1 class="text-3xl font-bold mb-6 text-center">
             <span class="bg-gradient-to-r from-blue-500 to-purple-500 text-transparent bg-clip-text">Buat Akun Baru</span>
        </h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded mb-4 text-sm">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="proses_register.php" method="POST">
            <div class="mb-4">
                <label for="nama_lengkap" class="block text-slate-400 text-sm font-medium mb-2">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" class="bg-slate-900 border border-slate-600 rounded-md w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="username" class="block text-slate-400 text-sm font-medium mb-2">Username</label>
                <input type="text" id="username" name="username" class="bg-slate-900 border border-slate-600 rounded-md w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-slate-400 text-sm font-medium mb-2">Password</label>
                <input type="password" id="password" name="password" class="bg-slate-900 border border-slate-600 rounded-md w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="konfirmasi_password" class="block text-slate-400 text-sm font-medium mb-2">Konfirmasi Password</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="bg-slate-900 border border-slate-600 rounded-md w-full py-2 px-3 text-gray-200 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div class="flex flex-col items-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-blue-500 transition-colors duration-300">
                    Register
                </button>
                <a href="login.php" class="mt-4 text-sm text-blue-400 hover:underline">
                    Sudah punya akun? Login di sini
                </a>
            </div>
        </form>
    </div>
</body>
</html>