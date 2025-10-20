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

      /* === MODE GELAP === */
      body.dark {
        background-color: #0f172a;
        color: #f1f5f9;
      }
      
      body.dark .bg-white {
        background-color: #1e293b !important;
      }
      
      body.dark .bg-gray-50 {
        background-color: #334155 !important;
      }
      
      body.dark .bg-gray-100 {
        background-color: #0f172a !important;
      }
      
      body.dark .text-slate-800 {
        color: #f1f5f9 !important;
      }
      
      body.dark .text-slate-600 {
        color: #cbd5e1 !important;
      }
      
      body.dark .text-slate-500 {
        color: #94a3b8 !important;
      }
      
      body.dark .border-gray-200 {
        border-color: #475569 !important;
      }
      
      body.dark .border-gray-300 {
        border-color: #475569 !important;
      }
      
      body.dark .hover\:bg-gray-200:hover {
        background-color: #334155 !important;
      }
      
      body.dark .hover\:bg-blue-50:hover {
        background-color: #1e3a5f !important;
      }
      
      body.dark .bg-blue-100 {
        background-color: #1e3a5f !important;
        color: #93c5fd !important;
      }
      
      body.dark .text-blue-800 {
        color: #93c5fd !important;
      }
      
      body.dark .bg-purple-100 {
        background-color: #4c1d95 !important;
        color: #d8b4fe !important;
      }
      
      body.dark .text-purple-800 {
        color: #d8b4fe !important;
      }
      
      body.dark .bg-gray-200 {
        background-color: #475569 !important;
      }
      
      body.dark .text-gray-600 {
        color: #cbd5e1 !important;
      }
      
      body.dark .shadow-lg {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.7), 0 4px 6px -2px rgba(0, 0, 0, 0.5) !important;
      }
      
      body.dark .shadow-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.7), 0 2px 4px -1px rgba(0, 0, 0, 0.5) !important;
      }
      
      body.dark .input-field {
        background-color: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
      }
      
      body.dark .select-field {
        background-color: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
      }
      
      body.dark .textarea-field {
        background-color: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
      }
      
      body.dark .text-yellow-700 {
        color: #fbbf24 !important;
      }
      
      body.dark .bg-yellow-50 {
        background-color: #451a03 !important;
      }
      
      body.dark .border-yellow-400 {
        border-color: #f59e0b !important;
      }
      
      body.dark .text-yellow-400 {
        color: #fbbf24 !important;
      }
      
      body.dark .table-fixed th {
        background-color: #334155 !important;
        color: #f1f5f9 !important;
        border-color: #475569 !important;
      }
      
      body.dark .table-fixed td {
        border-color: #475569 !important;
      }
      
      /* Tambahan untuk elemen lainnya */
      body.dark .text-slate-700 {
        color: #e2e8f0 !important;
      }
      
      body.dark .text-slate-400 {
        color: #94a3b8 !important;
      }
      
      body.dark .text-slate-300 {
        color: #cbd5e1 !important;
      }
      
      body.dark .bg-blue-600 {
        background-color: #2563eb !important;
      }
      
      body.dark .text-white {
        color: #f1f5f9 !important;
      }
      
      body.dark .hover\:text-slate-800:hover {
        color: #f1f5f9 !important;
      }
      
      body.dark .hover\:text-slate-700:hover {
        color: #e2e8f0 !important;
      }
      
      body.dark .text-red-600 {
        color: #f87171 !important;
      }
      
      body.dark .hover\:bg-red-50:hover {
        background-color: #7f1d1d !important;
      }
      
      body.dark .text-green-500 {
        color: #34d399 !important;
      }
      
      body.dark .text-yellow-500 {
        color: #fbbf24 !important;
      }
      
      body.dark .text-red-500 {
        color: #f87171 !important;
      }
      
      body.dark .bg-blue-50 {
        background-color: #1e3a5f !important;
      }
      
      body.dark .bg-red-50 {
        background-color: #7f1d1d !important;
      }
      
      body.dark .bg-green-50 {
        background-color: #064e3b !important;
      }
      
      body.dark .text-green-600 {
        color: #34d399 !important;
      }
      
      body.dark .bg-gradient-to-r {
        background-image: linear-gradient(to right, #3b82f6, #8b5cf6) !important;
      }
      
      /* Tambahan untuk statistik dan badge */
      body.dark .bg-gray-50 {
        background-color: #334155 !important;
      }
      
      body.dark .bg-orange-50 {
        background-color: #7c2d12 !important;
      }
      
      body.dark .text-orange-600 {
        color: #fb923c !important;
      }
      
      body.dark .text-orange-800 {
        color: #fed7aa !important;
      }
      
      body.dark .bg-red-50 {
        background-color: #7f1d1d !important;
      }
      
      body.dark .text-red-600 {
        color: #f87171 !important;
      }
      
      body.dark .text-red-800 {
        color: #fecaca !important;
      }
      
      body.dark .bg-yellow-50 {
        background-color: #713f12 !important;
      }
      
      body.dark .text-yellow-600 {
        color: #fbbf24 !important;
      }
      
      body.dark .text-yellow-800 {
        color: #fef3c7 !important;
      }
      
      body.dark .bg-purple-50 {
        background-color: #581c87 !important;
      }
      
      body.dark .text-purple-600 {
        color: #c084fc !important;
      }
      
      body.dark .text-purple-800 {
        color: #e9d5ff !important;
      }
      
      body.dark .text-gray-900 {
        color: #f1f5f9 !important;
      }
      
      body.dark .text-gray-500 {
        color: #94a3b8 !important;
      }
      
      body.dark .border-orange-200 {
        border-color: #9a3412 !important;
      }
      
      body.dark .border-red-200 {
        border-color: #991b1b !important;
      }
      
      body.dark .border-yellow-200 {
        border-color: #a16207 !important;
      }
      
      body.dark .border-purple-200 {
        border-color: #6b21a8 !important;
      }
      
      /* Badge colors */
      body.dark .bg-green-100 {
        background-color: #064e3b !important;
      }
      
      body.dark .text-green-800 {
        color: #6ee7b7 !important;
      }
      
      body.dark .bg-blue-100 {
        background-color: #1e3a5f !important;
      }
      
      body.dark .text-blue-800 {
        color: #93c5fd !important;
      }
      
      body.dark .bg-indigo-100 {
        background-color: #312e81 !important;
      }
      
      body.dark .text-indigo-800 {
        color: #a5b4fc !important;
      }
      
      body.dark .text-indigo-600 {
        color: #818cf8 !important;
      }
      
      body.dark .hover\:text-indigo-900:hover {
        color: #c7d2fe !important;
      }
      
      body.dark .text-gray-400 {
        color: #94a3b8 !important;
      }
      
      body.dark .border-gray-300 {
        border-color: #475569 !important;
      }
      
      body.dark .focus\:ring-indigo-200:focus {
        --tw-ring-color: #6366f1 !important;
      }
      
      body.dark .focus\:border-indigo-300:focus {
        border-color: #818cf8 !important;
      }
      
      body.dark .focus\:ring-blue-500:focus {
        --tw-ring-color: #3b82f6 !important;
      }
      
      body.dark .focus\:border-blue-500:focus {
        border-color: #3b82f6 !important;
      }
      
      body.dark .hover\:text-gray-700:hover {
        color: #cbd5e1 !important;
      }
      
      body.dark .hover\:border-gray-300:hover {
        border-color: #64748b !important;
      }
      
      body.dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: #475569 !important;
      }
      
      body.dark .divide-y > :not([hidden]) ~ :not([hidden]) {
        border-color: #475569 !important;
      }
      
      body.dark .uppercase {
        text-transform: uppercase;
      }
      
      body.dark .tracking-wider {
        letter-spacing: 0.05em;
      }
      
      /* Tambahan untuk semua tabel dan elemen interaktif */
      body.dark .bg-white {
        background-color: #1e293b !important;
      }
      
      body.dark .bg-gray-50 {
        background-color: #334155 !important;
      }
      
      body.dark .text-gray-500 {
        color: #94a3b8 !important;
      }
      
      body.dark .text-gray-900 {
        color: #f1f5f9 !important;
      }
      
      body.dark .text-gray-700 {
        color: #e2e8f0 !important;
      }
      
      body.dark .text-gray-600 {
        color: #cbd5e1 !important;
      }
      
      body.dark .text-gray-400 {
        color: #94a3b8 !important;
      }
      
      body.dark .border-gray-200 {
        border-color: #475569 !important;
      }
      
      body.dark .border-gray-300 {
        border-color: #475569 !important;
      }
      
      body.dark .hover\:bg-gray-50:hover {
        background-color: #334155 !important;
      }
      
      body.dark .hover\:text-blue-900:hover {
        color: #dbeafe !important;
      }
      
      body.dark .hover\:text-indigo-900:hover {
        color: #c7d2fe !important;
      }
      
      body.dark .hover\:text-green-900:hover {
        color: #bbf7d0 !important;
      }
      
      body.dark .hover\:text-red-900:hover {
        color: #fecaca !important;
      }
      
      body.dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: #475569 !important;
      }
      
      body.dark .divide-y > :not([hidden]) ~ :not([hidden]) {
        border-color: #475569 !important;
      }
      
      /* Badge status colors yang lebih jelas */
      body.dark .bg-green-100 {
        background-color: #064e3b !important;
      }
      
      body.dark .text-green-800 {
        color: #6ee7b7 !important;
      }
      
      body.dark .bg-red-100 {
        background-color: #7f1d1d !important;
      }
      
      body.dark .text-red-800 {
        color: #fca5a5 !important;
      }
      
      body.dark .bg-yellow-100 {
        background-color: #713f12 !important;
      }
      
      body.dark .text-yellow-800 {
        color: #fde047 !important;
      }
      
      body.dark .bg-blue-100 {
        background-color: #1e3a8a !important;
      }
      
      body.dark .text-blue-800 {
        color: #93c5fd !important;
      }
      
      body.dark .bg-purple-100 {
        background-color: #581c87 !important;
      }
      
      body.dark .text-purple-800 {
        color: #d8b4fe !important;
      }
      
      body.dark .bg-indigo-100 {
        background-color: #312e81 !important;
      }
      
      body.dark .text-indigo-800 {
        color: #a5b4fc !important;
      }
      
      body.dark .bg-gray-100 {
        background-color: #374151 !important;
      }
      
      body.dark .text-gray-800 {
        color: #f3f4f6 !important;
      }
      
      /* Link colors */
      body.dark .text-blue-600 {
        color: #60a5fa !important;
      }
      
      body.dark .text-indigo-600 {
        color: #818cf8 !important;
      }
      
      body.dark .text-green-600 {
        color: #34d399 !important;
      }
      
      body.dark .text-red-600 {
        color: #f87171 !important;
      }
      
      /* Focus states */
      body.dark .focus\:ring-blue-500:focus {
        --tw-ring-color: #3b82f6 !important;
      }
      
      body.dark .focus\:border-blue-500:focus {
        border-color: #3b82f6 !important;
      }
      
      body.dark .focus\:ring-indigo-500:focus {
        --tw-ring-color: #6366f1 !important;
      }
      
      body.dark .focus\:border-indigo-500:focus {
        border-color: #6366f1 !important;
      }
      
      body.dark .focus\:outline-none:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
      }
      
      /* Button hover states */
      body.dark .hover\:bg-blue-700:hover {
        background-color: #1d4ed8 !important;
      }
      
      body.dark .bg-blue-600 {
        background-color: #2563eb !important;
      }
      
      /* Table specific styling */
      body.dark .table-fixed {
        border-color: #475569 !important;
      }
      
      body.dark .min-w-full {
        border-color: #475569 !important;
      }
      
      body.dark .whitespace-nowrap {
        color: #f1f5f9 !important;
      }
      
      body.dark .font-mono {
        color: #e2e8f0 !important;
      }
      
      body.dark .font-medium {
        color: #f1f5f9 !important;
      }
      
      body.dark .font-semibold {
        color: #f1f5f9 !important;
      }
      
      body.dark .text-xs {
        color: #cbd5e1 !important;
      }
      
      body.dark .text-sm {
        color: #e2e8f0 !important;
      }
      
      body.dark .text-center {
        color: #f1f5f9 !important;
      }
      
      /* Form elements */
      body.dark .border {
        border-color: #475569 !important;
      }
      
      body.dark .rounded-lg {
        border-color: #475569 !important;
      }
      
      body.dark .rounded-md {
        border-color: #475569 !important;
      }
      
      body.dark .rounded-full {
        border-color: #475569 !important;
      }
      
      /* ========================== */

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
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
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
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
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
            <button id="theme-toggle" title="Toggle Mode Gelap" class="nav-link flex items-center w-full px-4 py-2.5 rounded-lg text-slate-600 hover:bg-gray-200">
                <div class="icon-wrapper w-10 h-10 flex items-center justify-center flex-shrink-0">
                    <!-- Sun icon for light mode -->
                    <svg id="theme-icon-light" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <!-- Moon icon for dark mode -->
                    <svg id="theme-icon-dark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </div>
                <span class="pl-3" data-sidebar-state="expanded">Mode Gelap</span>
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