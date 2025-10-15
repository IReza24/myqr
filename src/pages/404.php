<?php
$page_title = "Halaman Tidak Ditemukan";
// Set header agar browser tahu ini adalah halaman 404
header("HTTP/1.0 404 Not Found");

// Hapus header dan footer standar agar halaman 404 bisa full screen
// require_once __DIR__ . '/../layouts/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex flex-col items-center justify-center text-center p-6">
        
        <div class="mb-8">
            <svg class="mx-auto h-48 w-48 text-blue-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.536 8.464a5 5 0 010 7.072m-7.072 0a5 5 0 010-7.072" />
            </svg>
        </div>

        <h1 class="text-6xl md:text-8xl font-extrabold text-white mb-4 tracking-tighter">
            <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-teal-400">404</span>
        </h1>
        
        <p class="text-2xl md:text-3xl font-light text-gray-300 mb-6">
            Halaman Tidak Ditemukan
        </p>
        
        <p class="max-w-md text-gray-400 mb-10">
            Maaf, kami tidak dapat menemukan halaman yang Anda cari. Mungkin halaman tersebut telah dihapus, diganti namanya, atau tidak pernah ada.
        </p>
        
        <div>
            <a href="/myqr/index.php" 
               class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-blue-700 transition-transform transform hover:scale-105 duration-300 shadow-lg shadow-blue-500/30">
                Kembali ke Dashboard
            </a>
        </div>

    </div>
</body>
</html>
