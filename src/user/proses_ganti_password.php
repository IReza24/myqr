<?php
require '../../config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Sertakan logika utama dari file terpusat
require_once __DIR__ . '/../core/password_logic.php';
?>
