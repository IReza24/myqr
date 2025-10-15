<?php
$page_title = "Profil Admin";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_SESSION['user_id'];

// Query to get admin details
$stmt = $conn->prepare("SELECT nama_lengkap, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    echo "<div class='max-w-4xl mx-auto py-8 sm:px-6 lg:px-8'><p class='text-red-500'>Gagal memuat data pengguna.</p></div>";
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}
?>

<div class="max-w-2xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        
        <!-- Profile Header -->
        <div class="flex flex-col items-center text-center p-6 sm:p-8 bg-gray-50 border-b border-gray-200">
            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                <svg class="w-16 h-16 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></h1>
            <p class="text-slate-500 mt-1">@<?php echo htmlspecialchars($user_data['username']); ?></p>
        </div>

        <!-- Details Section -->
        <div class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-700 mb-4">Informasi Akun</h2>
            <dl class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <dt class="text-sm font-medium text-slate-500">Role</dt>
                    <dd class="mt-1 sm:mt-0 text-base font-semibold text-slate-800 text-left sm:text-right">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Admin
                        </span>
                    </dd>
                </div>
            </dl>

            <!-- Tombol Aksi -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <a href="ganti_password.php" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Ganti Password
                </a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
