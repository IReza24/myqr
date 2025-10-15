-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 06 Okt 2025 pada 05.41
-- Versi server: 11.8.3-MariaDB-log
-- Versi PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u822465743_absen`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_absensi` date NOT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `waktu_keluar` time DEFAULT NULL,
  `status_masuk` enum('Tepat Waktu','Terlambat') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_keluar` enum('Selesai','Pulang Cepat','Lembur') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal_absensi`, `waktu_masuk`, `waktu_keluar`, `status_masuk`, `status_keluar`) VALUES
(8, 5, '2025-10-05', '19:13:23', '19:14:39', 'Tepat Waktu', 'Pulang Cepat'),
(9, 6, '2025-10-05', '22:49:33', '22:50:08', 'Terlambat', 'Selesai'),
(10, 8, '2025-10-06', '08:36:21', NULL, 'Terlambat', NULL),
(11, 7, '2025-10-06', '11:12:23', NULL, 'Tepat Waktu', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `qr_tokens`
--

CREATE TABLE `qr_tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `berlaku_sampai` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `qr_tokens`
--

INSERT INTO `qr_tokens` (`id`, `token`, `berlaku_sampai`) VALUES
(310, '919654', '2025-10-06 11:24:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('qr_interval_minutes', '5');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `nama_shift` varchar(50) NOT NULL,
  `jam_masuk` time NOT NULL,
  `batas_jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shifts`
--

INSERT INTO `shifts` (`id`, `nama_shift`, `jam_masuk`, `batas_jam_masuk`, `jam_pulang`) VALUES
(1, 'Shift Pagi', '08:00:00', '08:15:00', '16:00:00'),
(2, 'Shift Siang', '17:00:00', '19:15:00', '22:00:00'),
(3, 'Shift Malam', '22:00:00', '22:15:00', '06:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `shift_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `shift_id`) VALUES
(1, 'admin', '$2y$10$Y7G/0LaP3yq2C8rAbAnY5.Q.dI9dIuU2TbClHjV2A/JvI.38JkI.K', 'Admin Utama', 'admin', NULL),
(2, 'user1', '$2y$10$Y7G/0LaP3yq2C8rAbAnY5.Q.dI9dIuU2TbClHjV2A/JvI.38JkI.K', 'Budi Sanjaya', 'user', NULL),
(3, 'admin1', '$2y$10$p0KebKEOrux./Bgvftbjv.mbjSJ.fz5/WRY8h2SN.RrOvbKcqzxUS', 'brokdi', 'admin', NULL),
(4, 'brokda', '$2y$10$/WrocmaPJPttSK3p.8hyRevWINFITDX0nQH7mlLm6blQ.H/kt2yEu', 'brokda', 'user', 2),
(5, 'alex', '$2y$10$gzwadAoy9YzPQ7SRtPwOQ.63Dzg7urOTqj3OtMtVGEd5XnpKy9ebC', 'JOKO ALEX', 'user', 2),
(6, 'memei', '$2y$10$p..XSpzjt9oBRHasRt85QOL0SjU7AltDnvAxknSXfNKcxWuZpdAbC', 'abang memei', 'user', 3),
(7, 'Ijat', '$2y$10$0PRgIdF2kP.BYz9tOhBlpe1CfRsa5Pcf0S4pEdKg0iR.DEtwkQkfe', 'ijat', 'user', 3),
(8, 'Reza', '$2y$10$uveQgwGRM1bS.PC34Eg/iuik1pwpXaPEBMwfKowqnJFBPUG/osqvi', 'Reza Pratama', 'user', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `qr_tokens`
--
ALTER TABLE `qr_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indeks untuk tabel `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `shift_id` (`shift_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `qr_tokens`
--
ALTER TABLE `qr_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=311;

--
-- AUTO_INCREMENT untuk tabel `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
