-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 08 Mar 2026 pada 13.53
-- Versi server: 11.8.5-MariaDB-4 from Debian
-- Versi PHP: 8.4.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `php_native_master`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `route` varchar(150) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order_number` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `menus`
--

INSERT INTO `menus` (`id`, `name`, `route`, `icon`, `parent_id`, `order_number`, `created_at`) VALUES
(1, 'Dashboard', 'dashboard', 'fas fa-home', NULL, 1, '2026-02-15 22:06:59'),
(2, 'Users', 'users.index', 'fas fa-users', 67, 2, '2026-02-15 22:06:59'),
(3, 'Roles', 'roles.index', 'fas fa-address-card', 67, 3, '2026-02-15 22:06:59'),
(4, 'Permissions', 'permissions.index', 'fas fa-unlock', 67, 7, '2026-02-15 22:06:59'),
(5, 'Menus', 'menus.index', 'fas fa-grip', 67, 8, '2026-02-15 22:06:59'),
(66, 'Master', 'master', 'fas fa-screwdriver-wrench', NULL, 8, '2026-03-05 10:00:50'),
(67, 'Sistem', 'sistem', 'fas fa-gears', NULL, 2, '2026-03-05 10:54:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_permissions`
--

CREATE TABLE `menu_permissions` (
  `menu_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `menu_permissions`
--

INSERT INTO `menu_permissions` (`menu_id`, `permission_id`) VALUES
(1, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(4, 15),
(4, 16),
(5, 17),
(5, 18),
(5, 19),
(5, 20),
(66, 213),
(67, 214);

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'View Dashboard', 'dashboard.view', '2026-02-15 22:06:56'),
(2, 'View User', 'users.view', '2026-03-05 10:42:54'),
(3, 'Create User', 'users.create', '2026-03-05 10:42:54'),
(4, 'Edit User', 'users.edit', '2026-03-05 10:42:54'),
(5, 'Delete User', 'users.delete', '2026-03-05 10:42:54'),
(6, 'View Roles', 'roles.view', '2026-02-15 22:06:57'),
(7, 'Create Roles', 'roles.create', '2026-02-15 22:06:57'),
(8, 'Edit Roles', 'roles.edit', '2026-02-15 22:06:57'),
(9, 'Delete Roles', 'roles.delete', '2026-02-15 22:06:58'),
(15, 'Create Permissions', 'permissions.create', '2026-02-15 22:06:58'),
(16, 'Delete Permissions', 'permissions.delete', '2026-02-15 22:06:58'),
(17, 'VIew Menus', 'menus.view', '2026-02-15 22:06:58'),
(18, 'Create Menus', 'menus.create', '2026-02-15 22:06:58'),
(19, 'Edit Menus', 'menus.edit', '2026-02-15 22:06:58'),
(20, 'Delete Menus', 'menus.delete', '2026-02-15 22:06:58'),
(213, 'MASTER VIEW', 'master.view', '2026-03-05 10:00:50'),
(214, 'SISTEM VIEW', 'sistem.view', '2026-03-05 10:54:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'SUPER ADMIN', 'super-admin', '2026-02-15 22:06:56'),
(2, 'GUDANG', 'gudang', '2026-02-15 22:06:56'),
(3, 'PENGADAAN', 'pengadaan', '2026-02-15 22:06:56'),
(4, 'RUANG', 'ruang', '2026-02-18 07:34:29'),
(5, 'MANAJER', 'manajer', '2026-02-18 17:45:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data untuk tabel `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(2, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 213),
(1, 214);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `singkatan` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `refidunit` bigint(20) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `api_token` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `profile_photo` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `role_id`, `password`, `singkatan`, `is_active`, `refidunit`, `email_verified_at`, `api_token`, `phone`, `address`, `date_of_birth`, `gender`, `profile_photo`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'superadmin@test.com', 'Super Admin', 1, '$2y$12$WWxB/6NRgdKdAnSBqYYGzuBhHDlflf4PgSF14ALkHZoSf7o3YYWui', NULL, 1, NULL, NULL, NULL, '08135729317', '', '2026-03-08', 'male', '1772977835_20231012_123541 (2).jpg', NULL, NULL, '2026-03-08 20:31:30', '2026-03-08 20:50:35');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeks untuk tabel `menu_permissions`
--
ALTER TABLE `menu_permissions`
  ADD PRIMARY KEY (`menu_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_api_token` (`api_token`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `menu_permissions`
--
ALTER TABLE `menu_permissions`
  ADD CONSTRAINT `menu_permissions_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
