<?php
// Mengatur pelaporan error agar semua jenis error ditampilkan
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Mencoba Koneksi ke Database...</h1>";

// Memuat file konfigurasi database Anda
require 'config.php';

// Variabel $conn sudah dibuat di dalam config.php
// Kita hanya perlu memeriksanya

if (isset($conn) && !$conn->connect_error) {
    // Jika koneksi berhasil
    echo '<p style="color: green; font-size: 18px; font-weight: bold;">';
    echo "Koneksi ke database '" . DB_NAME . "' BERHASIL!";
    echo '</p>';

    // Menutup koneksi
    $conn->close();
    echo "<p>Koneksi ditutup.</p>";

} else {
    // Jika koneksi gagal
    echo '<p style="color: red; font-size: 18px; font-weight: bold;">';
    echo "Koneksi GAGAL!";
    echo '</p>';

    // Tampilkan pesan error yang spesifik
    if (isset($conn)) {
         echo "<p><strong>Error:</strong> " . $conn->connect_error . "</p>";
    } else {
         echo "<p>Variabel koneksi (\$conn) tidak ditemukan di config.php.</p>";
    }
}

?>