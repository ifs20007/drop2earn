-- Database: db_drop2earn
-- Smart Waste Management Platform (Drop2Earn)

CREATE DATABASE IF NOT EXISTS `db_drop2earn` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_drop2earn`;

-- 1. Tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `role` ENUM('admin', 'nasabah') NOT NULL DEFAULT 'nasabah',
  `saldo` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel kategori_sampah
CREATE TABLE IF NOT EXISTS `kategori_sampah` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `jenis_induk` ENUM('Plastik', 'Kertas & Kardus', 'Logam', 'Kaca', 'Lainnya') NOT NULL,
  `harga_per_kg` INT(11) NOT NULL,
  `panduan_pilah` TEXT NOT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel setoran_sampah
CREATE TABLE IF NOT EXISTS `setoran_sampah` (
  `id_setoran` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `id_kategori` INT(11) NOT NULL,
  `metode_setor` ENUM('Jemput ke Rumah', 'Antar ke Drop Point') NOT NULL,
  `tanggal_jadwal` DATE NOT NULL,
  `alamat_jemput` TEXT NOT NULL,
  `berat_estimasi` DECIMAL(5,2) NOT NULL,
  `berat_aktual` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `total_pendapatan` INT(11) NOT NULL DEFAULT 0,
  `status` ENUM('Menunggu', 'Dijemput/Diproses', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_setoran`),
  KEY `fk_setoran_user` (`id_user`),
  KEY `fk_setoran_kategori` (`id_kategori`),
  CONSTRAINT `fk_setoran_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_setoran_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_sampah` (`id_kategori`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel penarikan_saldo
CREATE TABLE IF NOT EXISTS `penarikan_saldo` (
  `id_penarikan` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) NOT NULL,
  `jumlah_tarik` INT(11) NOT NULL,
  `metode_pencairan` VARCHAR(50) NOT NULL,
  `nomor_rekening` VARCHAR(50) DEFAULT NULL,
  `status_tarik` ENUM('Pending', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Pending',
  `tanggal_pengajuan` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_penarikan`),
  KEY `fk_penarikan_user` (`id_user`),
  CONSTRAINT `fk_penarikan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data Awal: Users (Admin & Nasabah Contoh)
-- Password Admin: admin123
-- Password Nasabah: password123
INSERT INTO `users` (`id_user`, `nama_lengkap`, `email`, `password`, `no_hp`, `alamat`, `role`, `saldo`, `created_at`) VALUES
(1, 'Admin Pengelola Drop2Earn', 'admin@drop2earn.id', '$2y$10$YNDzeoouaavvW63tL2yrNedKNHUK4FcM9MCVnZ5kMync/nY5TqbXe', '081234567890', 'Kantor Bank Sampah Induk, Jl. Sutomo No. 18, Pematangsiantar', 'admin', 0, NOW()),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$xCLw9eF9wYN1aEtckPeAWe1ca9s/MVselxHI5GiUJxvfwQQKUfK9q', '082198765432', 'Jl. Merdeka No. 45, RT 02 / RW 05, Kel. Siantar Barat', 'nasabah', 45000, NOW()),
(3, 'Siti Rahmawati', 'siti@gmail.com', '$2y$10$xCLw9eF9wYN1aEtckPeAWe1ca9s/MVselxHI5GiUJxvfwQQKUfK9q', '081377889900', 'Jl. Kartini No. 12, Pematangsiantar', 'nasabah', 22500, NOW())
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

-- Data Awal: Master Kategori Sampah
INSERT INTO `kategori_sampah` (`id_kategori`, `nama_kategori`, `jenis_induk`, `harga_per_kg`, `panduan_pilah`, `gambar`) VALUES
(1, 'Botol Plastik PET Bening (Air Mineral)', 'Plastik', 4500, 'Kosongkan sisa cairan, lepas tutup dan label plastik, lalu remas botol hingga pipih untuk menghemat ruang penyimpanan.', 'plastik-pet.svg'),
(2, 'Plastik Gelas & Tutup Botol (PP/HDPE)', 'Plastik', 2500, 'Cuci bersih sisa minuman manis dari bibir gelas, keringkan, dan susun bertumpuk rapi agar tidak mengundang semut.', 'plastik-pp.svg'),
(3, 'Kardus Box Cokelat (Packaging/Karton)', 'Kertas & Kardus', 2200, 'Buka selotip atau lakban cokelat yang menempel, lipat kardus hingga lembaran pipih, dan ikat rapi menggunakan tali.', 'kardus.svg'),
(4, 'Kertas HVS, Buku Tulis & Dokumen Arsip', 'Kertas & Kardus', 2800, 'Lepaskan staples kawat, klip kertas, atau jilid lakban plastik. Pastikan kertas kering dan tidak terkena minyak atau air.', 'kertas-hvs.svg'),
(5, 'Kaleng Minuman Aluminium', 'Logam', 14000, 'Bilas sisa soda atau jus, keringkan, kemudian pipihkan kaleng dengan cara diinjak secara hati-hati.', 'kaleng-alu.svg'),
(6, 'Besi Scrap, Seng & Kaleng Makanan', 'Logam', 4000, 'Bersihkan dari sisa makanan atau kotoran tanah. Pisahkan dari bahan kimia atau benda tajam yang berbahaya.', 'besi-logam.svg'),
(7, 'Botol Kaca Utuh (Sirup, Kecap, Saus)', 'Kaca', 1200, 'Hanya menerima botol utuh tanpa retak atau pecah. Bilas bagian dalam dengan air bersih dan tiriskan.', 'botol-kaca.svg'),
(8, 'Minyak Jelantah Rumah Tangga', 'Lainnya', 6000, 'Saring minyak goreng bekas pakai dari remah makanan gosong, lalu simpan dalam jerigen atau botol plastik bertutup rapat.', 'minyak-jelantah.svg')
ON DUPLICATE KEY UPDATE `nama_kategori` = VALUES(`nama_kategori`);

-- Data Awal: Setoran Sampah
INSERT INTO `setoran_sampah` (`id_setoran`, `id_user`, `id_kategori`, `metode_setor`, `tanggal_jadwal`, `alamat_jemput`, `berat_estimasi`, `berat_aktual`, `total_pendapatan`, `status`, `created_at`) VALUES
(1, 2, 3, 'Jemput ke Rumah', '2026-11-05', 'Jl. Merdeka No. 45, RT 02 / RW 05, Kel. Siantar Barat', 10.00, 10.00, 22000, 'Selesai', '2026-11-05 09:30:00'),
(2, 2, 1, 'Antar ke Drop Point', '2026-11-08', 'Drop Point Utama - Bank Sampah Induk Siantar', 5.00, 5.10, 22950, 'Selesai', '2026-11-08 14:15:00'),
(3, 3, 4, 'Jemput ke Rumah', '2026-11-09', 'Jl. Kartini No. 12, Pematangsiantar', 8.00, 0.00, 0, 'Menunggu', '2026-11-09 10:00:00')
ON DUPLICATE KEY UPDATE `id_setoran` = VALUES(`id_setoran`);

-- Data Awal: Penarikan Saldo
INSERT INTO `penarikan_saldo` (`id_penarikan`, `id_user`, `jumlah_tarik`, `metode_pencairan`, `nomor_rekening`, `status_tarik`, `tanggal_pengajuan`) VALUES
(1, 2, 20000, 'DANA', '082198765432', 'Disetujui', '2026-11-06 16:00:00'),
(2, 3, 10000, 'GoPay', '081377889900', 'Pending', '2026-11-09 11:20:00')
ON DUPLICATE KEY UPDATE `id_penarikan` = VALUES(`id_penarikan`);

