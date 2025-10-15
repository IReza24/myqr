<?php
require '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // 1. Validasi input
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $_SESSION['toast'] = ['message' => 'Semua field harus diisi.', 'type' => 'error'];
        header('Location: profil.php');
        exit;
    }

    if ($password_baru !== $konfirmasi_password) {
        $_SESSION['toast'] = ['message' => 'Password baru dan konfirmasi tidak cocok.', 'type' => 'error'];
        header('Location: profil.php');
        exit;
    }

    // 2. Verifikasi password lama
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result && password_verify($password_lama, $result['password'])) {
        // 3. Jika password lama benar, hash dan update password baru
        $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt_update->bind_param("si", $hashed_password_baru, $user_id);
        
        $_SESSION['toast'] = $stmt_update->execute()
            ? ['message' => 'Password berhasil diperbarui.', 'type' => 'success']
            : ['message' => 'Gagal memperbarui password.', 'type' => 'error'];
        $stmt_update->close();
    } else {
        $_SESSION['toast'] = ['message' => 'Password lama yang Anda masukkan salah.', 'type' => 'error'];
    }
}

$conn->close();
header('Location: profil.php');
exit;