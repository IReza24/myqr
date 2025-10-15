<?php
require '../../config.php';

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$stmt = $conn->prepare("SELECT id, password, role, nama_lengkap FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $hashed_password, $role, $nama_lengkap);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['toast'] = ['message' => 'Login berhasil! Selamat datang, ' . htmlspecialchars($nama_lengkap) . '.', 'type' => 'success'];
        header('Location: ../../index.php');
        exit();
    }
}

$_SESSION['toast'] = ['message' => 'Username atau password salah.', 'type' => 'error'];
header('Location: login.php');
$stmt->close();
$conn->close();
?>