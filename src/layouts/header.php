<?php
require_once __DIR__ . '/../../config.php';

// Fungsi untuk menampilkan halaman 404 dan menghentikan eksekusi
function show_404() {
    // Pastikan path ke file 404 benar
    $path_404 = __DIR__ . '/../pages/404.php';
    if (file_exists($path_404)) {
        include $path_404;
    } else {
        // Fallback jika file 404 tidak ditemukan
        die('404 Not Found');
    }
    exit;
}

// Logika untuk otentikasi dan otorisasi terpusat
if (!isset($_SESSION['user_id'])) {
    if (!isset($page_type) || $page_type !== 'auth') {
        header('Location: ' . BASE_URL . 'src/auth/login.php');
        exit;
    }
} else {
    $current_page_path = $_SERVER['SCRIPT_NAME'];
    $user_role = $_SESSION['role'];

    // Jika admin mencoba akses halaman user
    if ($user_role === 'admin' && strpos($current_page_path, '/src/user/') !== false) {
        show_404();
    }

    // Jika user biasa mencoba akses halaman admin
    if ($user_role === 'user' && strpos($current_page_path, '/src/admin/') !== false) {
        show_404();
    }
    
    // Jika sudah login dan mencoba akses halaman auth, arahkan ke dashboard
    if (isset($page_type) && $page_type === 'auth') {
        $dashboard_url = ($user_role === 'admin') ? BASE_URL . 'src/admin/index.php' : BASE_URL . 'src/user/index.php';
        header('Location: ' . $dashboard_url);
        exit;
    }
}

function is_active($page_name) {
    $current_page = basename($_SERVER['SCRIPT_NAME']);
    $page_names = is_array($page_name) ? $page_name : [$page_name];
    return in_array($current_page, $page_names);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistem Absensi QR'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style> 
      body { font-family: 'Inter', sans-serif; } 
      .toastify.on { opacity: 1 !important; }
      
      #sidebar { transition: width 0.3s ease-in-out, transform 0.3s ease-in-out; }
      .content-wrapper { transition: margin-left 0.3s ease-in-out; }

      /* === INI PERBAIKANNYA: ATURAN HANYA UNTUK DESKTOP === */
      @media (min-width: 768px) {
        #sidebar.is-collapsed {
          width: 5rem; /* 80px */
        }
        /* Sembunyikan semua elemen teks saat diciutkan */
        #sidebar.is-collapsed [data-sidebar-state="expanded"] {
          display: none;
        }
        #sidebar.is-collapsed .nav-link {
          justify-content: center;
          padding-left: 0;
          padding-right: 0;
          width: 3rem;  /* 48px */
          height: 3rem; /* 48px */
          margin-left: auto;
          margin-right: auto;
        }
        /* Pusatkan semua ikon/pembungkus ikon saat diciutkan */
        #sidebar.is-collapsed .nav-link svg,
        #sidebar.is-collapsed .nav-link .avatar-icon,
        #sidebar.is-collapsed .nav-link .icon-wrapper {
            margin-right: 0;
        }
      }
      /* ======================================================= */
    </style>
</head>
<body class="bg-gray-100 text-slate-800">

<div>
    <?php if (!isset($page_type) || $page_type !== 'auth'): ?>
    <aside id="sidebar" class="w-64 bg-white shadow-lg flex flex-col border-r border-gray-200 fixed inset-y-0 left-0 z-40 transform -translate-x-full md:translate-x-0">
        <div class="h-16 flex items-center justify-between border-b border-gray-200 flex-shrink-0 px-4">
            <h1 class="text-2xl font-bold w-full text-center" data-sidebar-state="expanded">
                <span class="bg-gradient-to-r from-blue-500 to-purple-500 text-transparent bg-clip-text">Absensi<span class="text-blue-500">QR</span></span>
            </h1>
            <button id="sidebar-close-mobile" class="md:hidden text-slate-500 hover:text-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <nav class="flex-1 px-4 py-6 space-y-2">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="index.php" title="Dashboard" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('index.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Dashboard</span>
                </a>
                <a href="profil.php" title="Profil" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active(['profil.php', 'ganti_password.php']) ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Profil</span>
                </a>
                <a href="rekap.php" title="Rekap Absensi" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('rekap.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Rekap</span>
                </a>
                <a href="shifts.php" title="Manajemen Shift" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active(['shifts.php', 'tambah_shift.php']) ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Shift</span>
                </a>
                <a href="users.php" title="Manajemen User" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active(['users.php', 'edit_user.php']) ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 21v-2a4 4 0 00-4-4H9.828a4 4 0 01-3.172-1.257l-1.257-1.257A4 4 0 013.828 9H3l2.828-2.828a4 4 0 011.172-3.172l1.257-1.257A4 4 0 019 3h6a4 4 0 014 4v12z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">User</span>
                </a>
                <a href="review_pengajuan.php" title="Pengajuan Shift" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('review_pengajuan.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l4-4m0 0l4 4m-4-4v12m0 0l4 4m-4-4H3m10 0h8M3 12h8m-8 4h8m8-8l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Pengajuan Shift</span>
                </a>
            <?php else: ?>
                <a href="index.php" title="Dashboard" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('index.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Dashboard</span>
                </a>
                <a href="profil.php" title="Profil Saya" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('profil.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Profil Saya</span>
                </a>
                <a href="jadwal.php" title="Jadwal Saya" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active('jadwal.php') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Jadwal Saya</span>
                </a>
                <a href="riwayat_pengajuan.php" title="Ganti Shift" class="nav-link flex items-center px-4 py-2.5 rounded-lg <?php echo is_active(['riwayat_pengajuan.php', 'ajukan_ganti_shift.php']) ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-gray-200'; ?>">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h.01M12 7h.01M16 7h.01M9 11h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-medium pl-3" data-sidebar-state="expanded">Ganti Shift</span>
                </a>
            <?php endif; ?>
        </nav>


        <div class="mt-auto p-4 border-t border-gray-200 space-y-2">
             <a href="profil.php" title="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" class="nav-link flex items-center px-4 py-2.5 rounded-lg text-slate-600 hover:bg-gray-200">
                <div class="avatar-icon w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="pl-3" data-sidebar-state="expanded">
                    <p class="text-sm font-semibold text-slate-800 truncate"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></p>
                    <p class="text-xs text-slate-500"><?php echo ucfirst($_SESSION['role']); ?></p>
                </div>
            </a>
            <a href="../auth/logout.php" title="Logout" class="nav-link flex items-center px-4 py-2.5 rounded-lg text-red-600 hover:bg-red-50">
                <div class="icon-wrapper w-10 h-10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </div>
                <span class="font-medium pl-3" data-sidebar-state="expanded">Logout</span>
            </a>
            <button id="sidebar-toggle-desktop" title="Lipat Sidebar" class="nav-link hidden md:flex items-center w-full px-4 py-2.5 rounded-lg text-slate-600 hover:bg-gray-200">
                <div class="icon-wrapper w-10 h-10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </div>
                <span class="pl-3" data-sidebar-state="expanded">Lipat Sidebar</span>
            </button>
        </div>
    </aside>
    
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 md:hidden hidden"></div>
    
    <div class="content-wrapper md:ml-64">
        <?php if (!isset($page_type) || $page_type !== 'auth'): ?>
            <header class="h-16 flex items-center justify-between bg-white border-b border-gray-200 px-6 md:hidden sticky top-0 z-20">
                <button id="sidebar-toggle-mobile" class="text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <div class="text-xl font-bold text-slate-800"><?php echo $page_title ?? 'Menu'; ?></div>
                <div></div>
            </header>
        <?php endif; ?>
        <main>
            <?php endif; ?>