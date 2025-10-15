<?php
require '../../config.php';

$nama_lengkap = trim($_POST['nama_lengkap']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$konfirmasi_password = $_POST['konfirmasi_password'];

if (empty($nama_lengkap) || empty($username) || empty($password)) {
    $_SESSION['toast'] = ['message' => 'Semua kolom wajib diisi.', 'type' => 'error'];
    header('Location: register.php');
    exit();
}

if ($password !== $konfirmasi_password) {
    $_SESSION['toast'] = ['message' => 'Konfirmasi password tidak cocok.', 'type' => 'error'];
    header('Location: register.php');
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['toast'] = ['message' => 'Password minimal harus 8 karakter.', 'type' => 'error'];
    header('Location: register.php');
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['toast'] = ['message' => 'Username sudah digunakan.', 'type' => 'error'];
    header('Location: register.php');
    $stmt->close();
    exit();
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'user';

$stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama_lengkap, $username, $hashed_password, $role);

if ($stmt->execute()) {
    $_SESSION['toast'] = ['message' => 'Registrasi berhasil! Silakan login.', 'type' => 'success'];
    header('Location: login.php');
} else {
    $_SESSION['toast'] = ['message' => 'Gagal mendaftar. Coba lagi.', 'type' => 'error'];
    header('Location: register.php');
}

$stmt->close();
$conn->close();
?>