<?php
require '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    // Gunakan filter untuk mengubah string kosong menjadi NULL
    $shift_id = filter_var($_POST['shift_id'], FILTER_VALIDATE_INT, ['options' => ['default' => NULL]]);

    $stmt = $conn->prepare("UPDATE users SET shift_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $shift_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['message' => 'Shift user berhasil diperbarui.', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['message' => 'Gagal memperbarui shift.', 'type' => 'error'];
    }
    header('Location: users.php');
}
?>