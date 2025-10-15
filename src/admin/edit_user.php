<?php
$page_title = "Atur Shift User";
require_once __DIR__ . '/../layouts/header.php';

$user_id = $_GET['id'];
// Ambil data user
$stmt_user = $conn->prepare("SELECT id, nama_lengkap, shift_id FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// Ambil semua shift
$shifts = $conn->query("SELECT id, nama_shift FROM shifts");
?>
<div class="max-w-xl mx-auto py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg border">
        <h1 class="text-2xl font-bold mb-2">Atur Shift untuk</h1>
        <h2 class="text-xl mb-6 text-slate-600"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>
        <form action="proses_user.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <div class="mb-4">
                <label for="shift_id" class="block text-sm font-medium mb-2">Pilih Shift</label>
                <select name="shift_id" id="shift_id" class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="">-- Tidak Ada Shift --</option>
                    <?php while($shift = $shifts->fetch_assoc()): ?>
                    <option value="<?php echo $shift['id']; ?>" <?php if($user['shift_id'] == $shift['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($shift['nama_shift']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>