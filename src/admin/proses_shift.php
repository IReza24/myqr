<?php
require '../../config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Logika untuk Tambah dan Edit (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $nama = $_POST['nama_shift'];
    $masuk = $_POST['jam_masuk'];
    $batas = $_POST['batas_jam_masuk'];
    $pulang = $_POST['jam_pulang'];
    $id = $_POST['id'] ?? null;

    // --- VALIDASI WAKTU ---
    if (strtotime($batas) <= strtotime($masuk)) {
        $_SESSION['toast'] = ['message' => 'Validasi Gagal: Batas Jam Masuk harus setelah Jam Masuk.', 'type' => 'error'];
        $redirect_url = $_POST['action'] === 'edit' ? "edit_shift.php?id=$id" : "tambah_shift.php";
        header("Location: $redirect_url");
        exit;
    }

    if (strtotime($pulang) <= strtotime($batas)) {
        $_SESSION['toast'] = ['message' => 'Validasi Gagal: Jam Pulang harus setelah Batas Jam Masuk.', 'type' => 'error'];
        $redirect_url = $_POST['action'] === 'edit' ? "edit_shift.php?id=$id" : "tambah_shift.php";
        header("Location: $redirect_url");
        exit;
    }
    // --- AKHIR VALIDASI ---

    if ($_POST['action'] === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO shifts (nama_shift, jam_masuk, batas_jam_masuk, jam_pulang) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $masuk, $batas, $pulang);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['message' => 'Shift baru berhasil ditambahkan.', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['message' => 'Gagal menambahkan shift.', 'type' => 'error'];
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE shifts SET nama_shift = ?, jam_masuk = ?, batas_jam_masuk = ?, jam_pulang = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama, $masuk, $batas, $pulang, $id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['message' => 'Data shift berhasil diperbarui.', 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['message' => 'Gagal memperbarui data shift.', 'type' => 'error'];
        }
        $stmt->close();
    }
}

// Logika untuk Hapus (method GET)
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // PENTING: Cek apakah shift ini masih digunakan oleh user
    $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE shift_id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $is_used = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
    $stmt_check->close();

    if ($is_used) {
        $_SESSION['toast'] = ['message' => 'Gagal! Shift ini masih digunakan oleh satu atau lebih user.', 'type' => 'error'];
    } else {
        $stmt_delete = $conn->prepare("DELETE FROM shifts WHERE id = ?");
        $stmt_delete->bind_param("i", $id);
        $_SESSION['toast'] = $stmt_delete->execute()
            ? ['message' => 'Shift berhasil dihapus.', 'type' => 'success']
            : ['message' => 'Gagal menghapus shift.', 'type' => 'error'];
        $stmt_delete->close();
    }
}
header('Location: shifts.php');
exit;
?>