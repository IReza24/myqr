<?php
$page_title = "Manajemen User";
require_once __DIR__ . '/../layouts/header.php';

// Ambil semua shift untuk dropdown
$sql_shifts = "SELECT id, nama_shift FROM shifts ORDER BY nama_shift";
$result_shifts = $conn->query($sql_shifts);
$shifts = [];
while ($row = $result_shifts->fetch_assoc()) {
    $shifts[] = $row;
}

// Ambil query pencarian awal (jika ada, untuk non-JS)
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Ambil semua user dengan filter pencarian
$sql_users = "SELECT u.id, u.nama_lengkap, u.username, u.shift_id, s.nama_shift 
        FROM users u 
        LEFT JOIN shifts s ON u.shift_id = s.id
        WHERE u.role = 'user'";

$params = [];
$param_types = '';

if (!empty($search_query)) {
    $sql_users .= " AND (u.nama_lengkap LIKE ? OR u.username LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ss";
}

$sql_users .= " ORDER BY u.nama_lengkap";

$stmt_users = $conn->prepare($sql_users);

if (!empty($param_types)) {
    $bind_params = [&$param_types];
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array([$stmt_users, 'bind_param'], $bind_params);
}

$stmt_users->execute();
$result_users = $stmt_users->get_result();
?>
<div class="max-w-5xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Daftar User</h1>

        <!-- Search Form -->
        <div class="mb-6">
            <form id="search-form" method="GET" action="users.php">
                <div class="relative">
                    <input type="text" id="search-input" name="search" placeholder="Ketik untuk mencari nama atau username..." value="<?php echo htmlspecialchars($search_query); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 input-field">
                    <div class="absolute top-0 left-0 inline-flex items-center justify-center h-full w-10 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atur Shift</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-table-body" class="divide-y divide-gray-200">
                    <?php if ($result_users->num_rows > 0): ?>
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <form action="proses_user.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                
                                <td class="py-2 px-4 align-middle whitespace-nowrap"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td class="py-2 px-4 align-middle whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-2 px-4 align-middle whitespace-nowrap">
                                    <select name="shift_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 select-field">
                                        <option value="">- Tanpa Shift -</option>
                                        <?php foreach ($shifts as $shift): ?>
                                            <option value="<?php echo $shift['id']; ?>" <?php echo ($user['shift_id'] == $shift['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($shift['nama_shift']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="py-2 px-4 align-middle whitespace-nowrap">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg">Simpan</button>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">Tidak ada user yang cocok dengan kriteria pencarian.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const tableBody = document.getElementById('user-table-body');
    const searchForm = document.getElementById('search-form');

    // Mencegah form dikirim saat menekan Enter
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
    });

    let debounceTimer;
    searchInput.addEventListener('input', function () {
        const query = this.value;

        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            // Tampilkan indikator loading
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Mencari...</td></tr>';

            fetch(`ajax_search_users.php?search=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    tableBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">Gagal memuat hasil. Silakan coba lagi.</td></tr>';
                });
        }, 300); // Sedikit delay (300ms) untuk tidak membebani server
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
