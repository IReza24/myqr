<?php
require '../../config.php';

// Ambil semua shift untuk dropdown
$sql_shifts = "SELECT id, nama_shift FROM shifts ORDER BY nama_shift";
$result_shifts = $conn->query($sql_shifts);
$shifts = [];
while ($row = $result_shifts->fetch_assoc()) {
    $shifts[] = $row;
}

// Ambil query pencarian
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Ambil semua user dengan filter pencarian
$sql_users = "SELECT u.id, u.nama_lengkap, u.username, u.shift_id 
        FROM users u 
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

// Generate HTML untuk baris tabel
if ($result_users->num_rows > 0) {
    while ($user = $result_users->fetch_assoc()) {
        echo '<tr>';
        echo '<form action="proses_user.php" method="POST">';
        echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
        echo '<td class="py-2 px-4 align-middle whitespace-nowrap">' . htmlspecialchars($user['nama_lengkap']) . '</td>';
        echo '<td class="py-2 px-4 align-middle whitespace-nowrap">' . htmlspecialchars($user['username']) . '</td>';
        echo '<td class="py-2 px-4 align-middle whitespace-nowrap">';
        echo '<select name="shift_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">';
        echo '<option value="">- Tanpa Shift -</option>';
        foreach ($shifts as $shift) {
            $selected = ($user['shift_id'] == $shift['id']) ? 'selected' : '';
            echo '<option value="' . $shift['id'] . '" ' . $selected . '>' . htmlspecialchars($shift['nama_shift']) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '<td class="py-2 px-4 align-middle whitespace-nowrap">';
        echo '<button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg">Simpan</button>';
        echo '</td>';
        echo '</form>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-center py-4">Tidak ada user yang cocok dengan kriteria pencarian.</td></tr>';
}

$conn->close();
?>