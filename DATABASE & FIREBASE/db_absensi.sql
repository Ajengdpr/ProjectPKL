-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Okt 2025 pada 07.54
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `status` enum('Hadir','Izin','Cuti','Sakit','Terlambat','Tugas Luar','alpha') NOT NULL,
  `alasan` text DEFAULT NULL,
  `berkas` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_09_08_022938_create_sessions_table', 1),
(2, '2025_09_09_021925_add_point_to_users_table', 2),
(3, '2025_09_15_100145_add_foto_to_users_table', 3),
(4, '2025_09_15_120747_add_timestamps_to_absensi_table', 4),
(5, '2025_09_25_104906_add_role_to_users_table', 5),
(6, '2025_09_25_114604_add_role_to_users_table', 6),
(7, '2025_09_29_085258_add_role_to_users_table', 7),
(8, '2025_09_29_094120_create_settings_table', 8),
(9, '2025_09_30_095312_create_notifications_table', 9),
(10, '2025_09_29_095304_create_notifications_table', 999),
(11, '2025_10_04_204159_modify_status_in_absensi_table', 1000),
(12, '2025_10_04_230552_add_remember_token_to_users_table', 1001),
(13, '2025_10_05_092838_add_device_id_to_absensi_table', 1002);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('33f5fcbe-af7c-4e30-b4c8-5b5a481db285', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 3, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Akhmad Fauzi Sakit\",\"time\":\"2025-10-04 22:33\",\"att_id\":2}', NULL, '2025-10-04 14:33:32', '2025-10-04 14:33:32'),
('4578059b-5296-49a9-9bd6-d37fc1a6ebd1', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 3, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Akhmad Fauzi Sakit\",\"time\":\"2025-10-03 09:54\",\"att_id\":19}', NULL, '2025-10-03 01:54:38', '2025-10-03 01:54:38'),
('4689ee3f-3cc1-46d1-936e-0914ace9a9fe', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 2, '{\"type\":\"absence\",\"title\":\"Absensi: Izin\",\"body\":\"Rahmah Izin (Acara keluarga)\",\"time\":\"2025-09-30 10:02\",\"att_id\":17}', '2025-09-30 02:02:52', '2025-09-30 02:02:42', '2025-09-30 02:02:52'),
('71c2a20f-217b-45bf-95a6-83167015f318', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 2, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Abdul Raji Sakit\",\"time\":\"2025-10-04 22:18\",\"att_id\":1}', NULL, '2025-10-04 14:18:14', '2025-10-04 14:18:14'),
('7f23071a-fecb-4971-adf7-e39e1169ba23', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 2, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Akhmad Fauzi Sakit\",\"time\":\"2025-10-03 09:54\",\"att_id\":19}', NULL, '2025-10-03 01:54:39', '2025-10-03 01:54:39'),
('825dec8c-80fb-4ff4-8276-acf3aad13b7e', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 2, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Akhmad Fauzi Sakit\",\"time\":\"2025-10-04 22:33\",\"att_id\":2}', NULL, '2025-10-04 14:33:32', '2025-10-04 14:33:32'),
('9384d327-147e-4f0b-ad32-b51632e08f2d', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 3, '{\"type\":\"absence\",\"title\":\"Absensi: Sakit\",\"body\":\"Abdul Raji Sakit\",\"time\":\"2025-10-04 22:18\",\"att_id\":1}', NULL, '2025-10-04 14:18:14', '2025-10-04 14:18:14'),
('bb89ef1c-1b81-4af2-8ec5-651f5af7881e', 'App\\Notifications\\AbsenceReported', 'App\\Models\\User', 3, '{\"type\":\"absence\",\"title\":\"Absensi: Izin\",\"body\":\"Rahmah Izin (Acara keluarga)\",\"time\":\"2025-09-30 10:02\",\"att_id\":17}', NULL, '2025-09-30 02:02:42', '2025-09-30 02:02:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6p4oqByMaxNOx2muLU2RsPUKFD9IX2um5eRrSsjz', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQmxUVmZpcWFRWmdQUk55bHlxd1BmQ29DdGFoMUh6V2ZKaUNySFVIcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fX0=', 1759627425),
('TZ7oER2qnCFSmg1H7FyoWexvyRWDhKl1CZcuvzhq', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiczhHMG05dkJMRHdhUlIzWm1hRGZnQWhHeTRqblZlU2FWcjNkUlhHdyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hY2NvdW50Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njt9', 1759643650);

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'poin', '{\"hadir\":\"5\",\"terlambat\":\"-3\",\"izin\":\"0\",\"sakit\":\"0\",\"cuti\":\"0\",\"tugas_luar\":\"5\",\"alpha\":\"-3\"}', NULL, NULL),
(2, 'lokasi', '{\"lat\":\"-3.489179\",\"lng\":\"114.828158\",\"radius\":\"250\"}', NULL, NULL),
(3, 'jam', '{\"batas_hadir\":\"15:00:00\"}', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `foto` varchar(255) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `bidang` varchar(30) DEFAULT NULL,
  `point` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `foto`, `jabatan`, `bidang`, `point`, `active`, `remember_token`) VALUES
(1, 'Admin', 'Admin', '$2y$12$gZWXCuM1yqY1ixV3oJX5GuoAIFdssCOfUXGxIEOu/DLh4Vd9EpJnq', 'admin', 'profile/6INzjcMQAIkGJKyOn6y2vwj4XmSiL89rNzjgU2Sn.jpg', 'ADMIN', 'SEKRETARIAT', 0, 1, NULL),
(2, 'fathimatuzzahra', 'fathimatuzzahra', '$2y$12$qX3O30VrUX2FuOID9Fq96OHThpAIuiVGdYtE6YHiD41VCZy5tp79W', 'user', NULL, 'plt. kepala dinas', 'SEKRETARIAT', 0, 1, NULL),
(3, 'noor eka hasni', 'noorekahasni', '$2y$12$V6mfDQ4X9r5vAKVItd1zyOtV8i84VZg5gaTLE0RQAw4iYrUCGpPgy', 'user', NULL, 'kepala sub bagian umum dan kepegawaian', 'SEKRETARIAT', 0, 1, NULL),
(4, 'yuli istiarini', 'yuliistiarini', '$2y$12$/szjABpJvyvHjGtL.vRa0.G2Wm5sDO1YlKKX/bRwWsnA.sUkPH4uO', 'user', NULL, 'kepala sub bagian perencanaan dan pelaporan', 'SEKRETARIAT', 0, 1, NULL),
(5, 'm. rachman hidayat', 'mrachmanhidayat', '$2y$12$TxKkHjSNA4QHIy3rObnp8.53WJa7LOBBqEzV55b.aRu1w/tLi3JLi', 'user', NULL, 'kepala sub bagian keuangan dan asset', 'SEKRETARIAT', 0, 1, NULL),
(6, 'rahmah', 'rahmah', '$2y$12$kNJU5l4VtFOsxp0g7mcoUeMYW2IdsmJHC4N0/Y62wvW71IBHAE6K.', 'user', NULL, 'penelaah teknis kebijakan', 'SEKRETARIAT', 0, 1, 'Z1rSc5ft5KxSvUwEJLBsRkhUnip3oKhT2kaTsqA9ESN1ETgTztgcNHCduKPj'),
(7, 'rina wardani', 'rinawardani', '$2y$12$T32EU/R0rWUbfClXLR3ITOL1486flUVQLeM5axijJTxAPxKiTZrY2', 'user', NULL, 'penelaah teknis kebijakan', 'SEKRETARIAT', 0, 1, NULL),
(8, 'linda lidiana', 'lindalidiana', '$2y$12$2Lfp3JG9Ph5lGW.FmfLQkeHTtXDAfDgU3xS/BBEldHUFPZEeWfxsW', 'user', NULL, 'penelaah teknis kebijakan', 'SEKRETARIAT', 0, 1, NULL),
(9, 'abdul raji', 'abdulraji', '$2y$12$HjsjJSSmYpUYwnUXaNvn1e5wRVPYnvv9cJZh1nb1y.ALLkZ.P7E0O', 'user', NULL, 'pengadministrasi perkantoran', 'SEKRETARIAT', 0, 1, NULL),
(10, 'muhammad anas', 'muhammadanas', '$2y$12$KEQ8FP7x8472y82bYyaIhe5vqdrS1Yj5CzLGTnOSwJS6Hq86mcP8C', 'user', NULL, 'pengadministrasi perkantoran', 'SEKRETARIAT', 0, 1, NULL),
(11, 'sinar octaviani', 'sinaroctaviani', '$2y$12$rb6TeM8cv9aGdlmtGtk7Qev8i1PWKb2Itj06BAI0180f7ZceAPTxa', 'user', NULL, 'penelaah teknis kebijakan', 'SEKRETARIAT', 0, 1, NULL),
(12, 'hafsah melly farilah', 'hafsahmellyfarilah', '$2y$12$yzr0uclVplQikEDX4bVu7.BAm5CeWp.UkUDXAhH76kKchHW.p.BX6', 'user', NULL, 'pengolah data dan informasi', 'SEKRETARIAT', 0, 1, NULL),
(13, 'ali sulaimansyah', 'alisulaimansyah', '$2y$12$HFJlfiRNj5KkRWI..CzTTutciSU0NLi.gvEzyOGYsBD2bNPxN.Wp2', 'user', NULL, 'pengelola umum operasional', 'SEKRETARIAT', 0, 1, NULL),
(14, 'adhitya ari dwi cahyo', 'adhityaaridwicahyo', '$2y$12$3W63/M.V/zLy1kSSwsnVP.SqbJk2UyYe5gQwhRqTyFI8aMPd0rXXS', 'user', NULL, 'arsiparis terampil', 'SEKRETARIAT', 0, 1, NULL),
(15, 'eka norlena dewi', 'ekanorlenadewi', '$2y$12$90hU/e63g4d9EQsTru4nLeMcIPululgBf7yBAdmXADoD46zW6Euxe', 'user', NULL, 'pranata komputer ahli pertama', 'SEKRETARIAT', 0, 1, NULL),
(16, 'mira febriani', 'mirafebriani', '$2y$12$zp0swlgAiaNXEuLLzBd0wu3CqhGN9Coj1nT1.OMYYEcpQkbc7LVia', 'user', NULL, 'penata layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(17, 'muhammad rizwan', 'muhammadrizwan', '$2y$12$tgjqXXJlLlkDWFroZcEuHu7dmSKp0mRJxZakpjgn3w9oJvMafG6HO', 'user', NULL, 'pengelola layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(18, 'mitha sari rachmayanti', 'mithasarirachmayanti', '$2y$12$raoTj63IQvwv2shc7NV2EOMj88O6YHyiD.6GZOB2BOfbBcDxuHQ5C', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(19, 'haekal aqla almaraghi', 'haekalaqlaalmaraghi', '$2y$12$jRbYQ84.KR5mRK9GEF7KGeAjpMd9jIVABD4Y0vdfz8sHfpEV.Hc6C', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(20, 'muhammad nabil nurdea', 'muhammadnabilnurdea', '$2y$12$cozlbgPpXxV6f.fhSo2Pr.LP7YALwhd/nnLd5iuDvalW3JlpVz.iS', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(21, 'muhammad wisnu karyadi', 'muhammadwisnukaryadi', '$2y$12$gu/KSlhI.iyM7FLwc7OIXeFbCxmrkjjiYUpVhuMtya/90jbrpzKRG', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(22, 'muhammad ulyani', 'muhammadulyani', '$2y$12$33OtJZbVTO3o9Kd.hp8k3O5qcWtQrST14aTwAggC85qwpXzFRI6TG', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(23, 'akhmad fauzi', 'akhmadfauzi', '$2y$12$DKYtXCbZmIQImLbyAV1BLu0vBWrW9m0Z0juTVUqD2z.pMWPcdILmC', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(24, 'muhammad sapuani', 'muhammadsapuani', '$2y$12$bWK4z0bMZBi59GcLyiunietTifnEFaCfz70L6igqU6zk5FJs4dMp6', 'user', NULL, 'petugas layanan operasional phl', 'SEKRETARIAT', 0, 1, NULL),
(25, 'sandy wijaya', 'sandywijaya', '$2y$12$YrR9.IN/aI5NOkA8eNabMeO4uLw4ilkxYsNEfKRZUF6/tYarUoqte', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(26, 'rolian noor', 'roliannoor', '$2y$12$S61gDcqTQg/KHKzm7ACSPe9JyOJH/2MBE9sbSxpmmHfHABwGFK/pq', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(27, 'rudiyanoor', 'rudiyanoor', '$2y$12$Ly1W1PMtsaY3AbLFIPLiXeaXi3l5KJlFWxURzq9aJ2YTIj9OIVQJC', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(28, 'abdul latif', 'abdullatif', '$2y$12$jKJh5PbpF3Mi/dI9Z9QUGOD2wWzjsUr9dEU86bTEb8JtDLV/QzBgq', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(29, 'iqbal priyangga', 'iqbalpriyangga', '$2y$12$tOW6X37bHn2M1lKUJVDFoOwvfvS8WvKkPXgphFu9oqyaE.kYMnvb6', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(30, 'mahyuda', 'mahyuda', '$2y$12$ntwGnc7as6DQIToggIf1/Orw.Kb.0JNvKYiCzoi0.sZik8c5qy0JO', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(31, 'mashur', 'mashur', '$2y$12$PnbsmJVtr0ROBsUhpmnxoOxrtDTtzZHzSHgc.othvz0u6wV6dxVbW', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(32, 'khairun naim', 'khairunnaim', '$2y$12$UAj.ZrBiEvW9qJbapbtOYOJHLkJ5ybgEbAcCEL8xhAjWbSRY5dskG', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(33, 'paisal riza', 'paisalriza', '$2y$12$V9mVSmTLfl9KfjLBycqSiONw7S/sfesZMQ0OM2jmpMkiEqyzFQeZS', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(34, 'ichwan syarif', 'ichwansyarif', '$2y$12$gTCwgukxSnMXuiPny7OJMutJ29XPSZACjeX2EHzDKku7mWamDSwdm', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(35, 'susi rosana', 'susirosana', '$2y$12$z8X4aK/RB9zN5OVLLYBGluXyswVlDoehjfkwToLkH9EApYeIhY0De', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(36, 'much nur cahyadi', 'muchnurcahyadi', '$2y$12$g6annifnp6aXZBOFV/4Fwufp2sxwYuCrO8WJtckPn88NtzFuBzWoS', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(37, 'mahrida', 'mahrida', '$2y$12$zvwyKXClTR4z1iEe/ZTlzu1yaTfBFRqWJYr.Pv5hl94377DVENTXy', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(38, 'sugiono', 'sugiono', '$2y$12$bLGu1L7T/W2GhVZjUpLGZOaTsPrWYOgIxBKrZuBSfrNDlYQDVtkCS', 'user', NULL, 'petugas layanan operasional', 'SEKRETARIAT', 0, 1, NULL),
(39, 'emmy ariani', 'emmyariani', '$2y$12$6v7muL9l4shpUedS7eBHrOIkDlLdeVDE4G0JK/.DJBVqH3SP7Z3Vm', 'user', NULL, 'kepala bidang pengendalian pencemaran dan kerusakan lh', 'PPKLH', 0, 1, NULL),
(40, 'hartopo', 'hartopo', '$2y$12$StYj.DLKfQAvhRR0uCL4peSJAQNJFjLgeDeytEvapK2e1jnBXcRQW', 'user', NULL, 'kepala seksi pemulihan pencemaran dan kerusakan lh', 'PPKLH', 0, 1, NULL),
(41, 'yuliarini', 'yuliarini', '$2y$12$23LjEsATNgEThJaZ2G9td.E2S34VNiITcHEzpVNKwEgTPXK4k8MfO', 'user', NULL, 'kepala seksi pencegahan pencemaran dan kerusakan lh', 'PPKLH', 0, 1, NULL),
(42, 'lalu erwin suprayanto', 'lalurewinsuprayanto', '$2y$12$CoYysOFtKLlVAGcjy5Q.LujayVi.VXGwTh2.CT2J6xjvbct6ONByS', 'user', NULL, 'kepala seksi pengelolan sampah limbah dan b3', 'PPKLH', 0, 1, NULL),
(43, 'rahmaniansyah', 'rahmaniansyah', '$2y$12$O/cATFsNv4KXF1lyAAO.mOFhevS1ZzGqb2R48dpTueKRB6IOLRWJ.', 'user', NULL, 'pengadministrasi perkantoran', 'PPKLH', 0, 1, NULL),
(44, 'adi rizkian noor', 'adirizkiannoor', '$2y$12$hR99pU./EOlRPiTzwAd7Ru/.b2Q57wvumP3LXvp4yIIN/.HfRZGBK', 'user', NULL, 'pengendali dampak lingkungan ahli pertama', 'PPKLH', 0, 1, NULL),
(45, 'muhammad rizky noor pratama', 'muhammadrizkynoorpratama', '$2y$12$df3oMP0lvZYShg8lxkPJNO5kjIAk8NZtsCjvDYZLp0WuTvLP.Kf76', 'user', NULL, 'pengendali dampak lingkungan ahli pertama', 'PPKLH', 0, 1, NULL),
(46, 'maimunah', 'maimunah', '$2y$12$DLsdmyhBgdtI4bSVnFWRQeERWmhh.oIhYOa64nuKb5hgZIoI/UXhG', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(47, 'octaviana dewi syahputri', 'octavianadewisyahputri', '$2y$12$T/o3pbSIK/55tHeJ.K8Q8u/Ljj3JpFbEnsf8uvsoibRNFLHgTPykS', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(48, 'lilis maryani', 'lilismaryani', '$2y$12$r6Uifmj1.WdIwS5wstMQ8OZpD1uxOyblTSg.X1NgE.7.M5eD48XWS', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(49, 'shonu dwi prayogo', 'shonudwiprayogo', '$2y$12$vqKVs2RfFiHdK9WQyPvg3uuXElN4UTY3D9g4CD9VEjjooLSfa8CQu', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(50, 'fitria handayani', 'fitriahandayani', '$2y$12$Oo84EErlPCjlE8Y49KnULOqDN1b87W0ZwxJ2i4HfSrwaDTHIkSfwW', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(51, 'handriansyah s.ak', 'handriansyahsak', '$2y$12$SM5Aj4YL03gTX71L15qiaufsAI0n8kP/TEpklucLbHjCb.kJ6m/zS', 'user', NULL, 'petugas layanan operasional', 'PPKLH', 0, 1, NULL),
(52, 'akhmad suhadi', 'akhmadsuhadi', '$2y$12$0dUFQ.RL846f5Rh7OjkoIOXKwbZ.fJeqiw0B9gJb4x7/pfGTsUnBG', 'user', NULL, 'tenaga ahli', 'PPKLH', 0, 1, NULL),
(53, 'muhammad indera wijaya', 'muhammadinderawijaya', '$2y$12$ThW/zuAqHug4tCLsGcH4Gu/Y6ba/XnkF4k3ECaGvKPbK4VD7/9LSe', 'user', NULL, 'tenaga ahli', 'PPKLH', 0, 1, NULL),
(54, 'hajie hariyanie', 'hajiehariyanie', '$2y$12$Tu8R6Q.OqwYFPWU1PidlUOYT4wn5BPadinWIZt4ZlKjGPLr9tmKdG', 'user', NULL, 'kepala seksi kemitraan', 'KPPI', 0, 1, NULL),
(55, 'zainullah', 'zainullah', '$2y$12$.WbkVunRWx.ssLGysSwYMOGgm1MFWDhuTycJLSVZVSEmBhiC5PXRq', 'user', NULL, 'kepala seksi konservasi dan keanekaragaman hayati', 'KPPI', 0, 1, NULL),
(56, 'yudhi syarif', 'yudhisyarif', '$2y$12$5yO9xXhXOreIe2NVhl9.6O2LtDjLHCRWa3.r936xWjkhPYIqk75V.', 'user', NULL, 'kepala seksi pengendalian perubahan iklim', 'KPPI', 0, 1, NULL),
(57, 'muhammad zamroni', 'muhammadzamroni', '$2y$12$i28ox8bDZtgYGBea0ZdlaeJd//EtOoXStnrrIWPr1tz9rneOLArNu', 'user', NULL, 'penelaah teknis kebijakan', 'KPPI', 0, 1, NULL),
(58, 'fathul umar aditya', 'fathulumaraditya', '$2y$12$tI6ndunCEEyS2umI2ts00uYAma2IIwda6pKyqlOqeaxST9G2Exmqa', 'user', NULL, 'penyuluh lingkungan hidup ahli pertama', 'KPPI', 0, 1, NULL),
(59, 'marissa puji rosmawati', 'marissapujirosmawati', '$2y$12$ElTDftc0FLp/XaAXk3B.f.rEaonsniiver.bS6qkGsFBZQGhdjCK6', 'user', NULL, 'pengadministrasi perkantoran', 'KPPI', 0, 1, NULL),
(60, 'fazri noor hidayat', 'fazrinoorhidayat', '$2y$12$FQxrS4lf0gRF7CgLwjt9wO.6F6djw.aABBpUxBBSYJ.H4YHLPRuya', 'user', NULL, 'pengendali dampak lingkungan ahli muda', 'KPPI', 0, 1, NULL),
(61, 'muhammad faisal', 'muhammadfaisal', '$2y$12$JkKqr10srQXq6RrRcI.n4.o1AXB5av57VELRih0XoUcF1t00aDzwK', 'user', NULL, 'petugas layanan operasional', 'KPPI', 0, 1, NULL),
(62, 'muhammad dhiyaul auliya', 'muhammaddhiyaulauliya', '$2y$12$77NMw8WdIenJ0CHB5/nxh.ghyDgxPPt3tKn4BQm4OrCFJNB/hPjSC', 'user', NULL, 'petugas layanan operasional', 'KPPI', 0, 1, NULL),
(63, 'helda febriani', 'heldafebriani', '$2y$12$w838lW4uPKQpGAK15H8Khetf91ZjAccbfS1VHTiYIFCzr/ISv9XUm', 'user', NULL, 'petugas layanan operasional', 'KPPI', 0, 1, NULL),
(64, 'norbaiti', 'norbaiti', '$2y$12$t.55aSUAhVpFA3FAKJb/FuwhozdBbFgaQ1J70hE/WNNDKdox..qxW', 'user', NULL, 'tenaga ahli', 'KPPI', 0, 1, NULL),
(65, 'kholifah fiana rini', 'kholifahfianarini', '$2y$12$djCGRR5UQio6RgWjEGy2n./DVKOp5HQg20aQqq/UPq.ysQfSbXgeS', 'user', NULL, 'petugas layanan operasional', 'KPPI', 0, 1, NULL),
(66, 'aprianor teguh saputra', 'aprianorteguhsaputra', '$2y$12$25iTVATp4eMlf0yNtqwg7ulA9GtLgQstO7dsHRyfNU7eH/OkbohpS', 'user', NULL, 'tenaga ahli', 'KPPI', 0, 1, NULL),
(67, 'hardini wijayanti', 'hardiniwijayanti', '$2y$12$ZODajrhzBQ77M.wwWiEpzeqT0VxVY/ii.eGAn.yGJdFnFUwL/M2Fu', 'user', NULL, 'kepala bidang penaatan hukum lingkungan', 'PHL', 0, 1, NULL),
(68, 'muhammad darma tri saputra', 'muhammaddarmatrisaputra', '$2y$12$mH/gnSsbiNoAJyZWx.JBiuTCUV.9Ed4/eGWWCA1pg4AnKGRrOk7n2', 'user', NULL, 'kepala seksi pengaduan kasus lh dan penegakan hukum', 'PHL', 0, 1, NULL),
(69, 'achmad yanuar', 'achmadyanuar', '$2y$12$uRKmO.rAjd9TpJ/KUTRtK.PD64h4f6gyF2ixMHTVHh82UiyrgZG42', 'user', NULL, 'kepala seksi pembinaan dan pengawasan lh', 'PHL', 0, 1, NULL),
(70, 'nina tresnawati', 'ninatresnawati', '$2y$12$qwJ1Rpfz2XbPqEEuozJF4Of4AFbb7W9xQcFuOe3ZISctttQAghud2', 'user', NULL, 'penelaah teknis kebijakan', 'PHL', 0, 1, NULL),
(71, 'junindra jaya', 'junindrajaya', '$2y$12$9hV2qk05cJS/UluUfIFQQucbzzg0Eh5uT3dMK41fzDAZq9pvg/Bzu', 'user', NULL, 'pengolah data', 'PHL', 0, 1, NULL),
(72, 'sofian rifani', 'sofianrifani', '$2y$12$PIGCdBNVKVgUUoqFOl70F.MCtKBKmAn9GDd1S07RJKBxEM.v5cCPq', 'user', NULL, 'penelaah teknis kebijakan', 'PHL', 0, 1, NULL),
(73, 'shela rizkita dewi', 'shelarizkitadewi', '$2y$12$Si6R2J7I1IIoOxpIk6nECObHT.fPoo/EPwcgUbt9z/fSsIJRkBLOW', 'user', NULL, 'pengendali dampak lingkungan ahli pertama', 'PHL', 0, 1, NULL),
(74, 'alya qatrunnada', 'alyaqatrunnada', '$2y$12$v1Cf1wMMtqccB2lBumnNW./smO1X/j0lt3aH0rrlv1oZBJSMSXiXO', 'user', NULL, 'petugas layanan operasional', 'PHL', 0, 1, NULL),
(75, 'fariz ramadhan', 'farizramadhan', '$2y$12$VYDgBESj7pZ6T9ektm96A.k3GEAyI0t2GJ569lrN2IS4IbWbkq1lq', 'user', NULL, 'petugas layanan operasional', 'PHL', 0, 1, NULL),
(76, 'muhammad nizham haitami', 'muhammadnizhamhaitami', '$2y$12$ttPoXLhl5Yo0iXClGWGvz.XhQDuKrzcJRFGu79eUaPos3fe31D.eq', 'user', NULL, 'petugas layanan operasional', 'PHL', 0, 1, NULL),
(77, 'nadhya maherwanda', 'nadhyamaherwanda', '$2y$12$bdhjdecBwYXe4Z4C.rYLG.rM.VDxcYED1PCmzENrCcl1ASRB7Pvhi', 'user', NULL, 'petugas layanan operasional', 'PHL', 0, 1, NULL),
(78, 'halimatus sadiah', 'halimatussadiah', '$2y$12$TJ6AKw6zJ5cQ/mgZYDGCQOU9A5VT06iD4GwKWZumuJIWrYZT2IV4m', 'user', NULL, 'petugas layanan operasional', 'TALING', 0, 1, NULL),
(79, 'rezeki nilam sari', 'rezekinilamsari', '$2y$12$jrYSXgqITo2t4IiWYnc1HenURM7wBFiHHukjYZTRU3KcUJD7lHXd.', 'user', NULL, 'petugas layanan operasional', 'TALING', 0, 1, NULL),
(80, 'adhi maulana', 'adhimaulana', '$2y$12$v.KcJuAX5fdILlhjG.tNv.MmY/B5DF/dYXqLv4tnjlV9hAhW79XoW', 'user', NULL, 'kepala bidang tata lingkungan', 'TALING', 0, 1, NULL),
(81, 'arif wardani', 'arifwardani', '$2y$12$01Az0EhFt1ShHWF9FJZFD.OUHVcb36bZdm4DCf4IpRDyFXmpz5cjy', 'user', NULL, 'kepala seksi perencanaan lh', 'TALING', 0, 1, NULL),
(82, 'm.saleh', 'msaleh', '$2y$12$sl4RLNMrYT0PnjeJ5/FB.OkMZIDTIPnKLrjVNOu4AhVLOkR8WH8Fq', 'user', NULL, 'kepala seksi pengelolaan dampak lh', 'TALING', 0, 1, NULL),
(83, 'yenny eranova', 'yennyeranova', '$2y$12$WX2sglANR/vYULhyJokhRO1yuSFMFKtFP5.8CJZBDZw1Drz52bIai', 'user', NULL, 'kepala seksi pengelolaan resiko kebijakan strategis', 'TALING', 0, 1, NULL),
(84, 'aliah', 'aliah', '$2y$12$ICURYAkp5Uh.rG29x4sJbOCq0JtwDTgbMKqBX72g02BzVjHk4uOme', 'user', NULL, 'penelaah teknis kebijakan', 'TALING', 0, 1, NULL),
(85, 'risa atika dewi', 'risatikadewi', '$2y$12$qephKEksT5iMs8h15u5PtO7wWBPV7sUAwc/pQF4LYRZpBZuRvu6rO', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(86, 'fachri rahmadani pratama', 'fachrirahmadanipratama', '$2y$12$AGSpKvHy3t7KOi5VVFXvTeE8vAXQdGfdhK1NCJOP7edLwX9m/Pz/u', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(87, 'nurul paujiah', 'nurulpaujiah', '$2y$12$lJQTN7KgNttqK1U2sANoG.eVUHGAMUZR1VayXQjq0nnj6iyIXK1A6', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(88, 'tri lutfi nawawi', 'trilutfinawawi', '$2y$12$A5WQ7yPnxXM1IwyUz.LiHO/FoDdPwz0DS06ijUjvfr/ipa4oGVBuW', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(89, 'zaizafun zahra', 'zaizafunzahra', '$2y$12$ROB2KWRY6NBitgeBhIXaTOwZWPYufnKx0AiqQOgFtpcY.2CGGhozG', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(90, 'adilla', 'adilla', '$2y$12$HcGgI0aQmvKglVEw2SoeHOlJaJAjgr/1I//xwiXlpCroVwFyDRCuq', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(91, 'azrul azwar', 'azrulazwar', '$2y$12$MMpMhlTKDRQnoVIyrjb1w.Me8rzebYmGcgKv20s0AIFxThxMwd4n2', 'user', NULL, 'tenaga ahli', 'TALING', 0, 1, NULL),
(92, 'asliansyah', 'asliansyah', '$2y$12$EaHJQyvNEKRpt8VHavrNIupO9Y8u.kbwb4SO4yRGzwqmqEU3YZufC', 'user', NULL, 'pengadministrasi perkantoran', 'TALING', 0, 1, NULL),
(93, 'm. akmal hakim', 'makmalhakim', '$2y$12$JyKUOLk93DcEwdcU7Eh9Ju/fdkXMNHmrB/cINjaYYhELvIV7uUAwO', 'user', NULL, 'pengendali dampak lingkungan ahli pertama', 'TALING', 0, 1, NULL),
(94, 'ahmad sairoji', 'ahmadsairoji', '$2y$12$QxvHtaKiBEsf1rJ8kgzJuODNcWVhx/DP5Ot2jT9mXmGLlG0M51Dyu', 'user', NULL, 'penata layanan operasional', 'TALING', 0, 1, NULL);

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
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `users_active_index` (`active`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
