-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Feb 2025 pada 07.56
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundryku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `agen`
--

CREATE TABLE `agen` (
  `id_agen` int(11) NOT NULL,
  `nama_laundry` varchar(30) DEFAULT NULL,
  `nama_pemilik` varchar(30) DEFAULT NULL,
  `telp` varchar(13) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `kota` varchar(20) DEFAULT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `plat_driver` varchar(12) DEFAULT NULL,
  `foto` text NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `agen`
--

INSERT INTO `agen` (`id_agen`, `nama_laundry`, `nama_pemilik`, `telp`, `email`, `kota`, `alamat`, `plat_driver`, `foto`, `password`) VALUES
(16, 'Pelakor Bersih', 'Ahmad', '081234567890', 'bersih@gmail.com', 'Jakarta', 'Jl. Kebersihan No. 1, Jakarta', 'B1234CD', '67b883f47548b.jpg', '$2y$10$6eJezSxANaQMbZQzxmhKFuKcul.QxFcyeROVpYTqaMdBWags2w2w.'),
(17, 'Pelakor Cepat', 'Budi', '081234567891', 'cepat@gmail.com', 'Bandung', 'Jl. Kecepatan No. 2, Bandung', 'B1234CE', '67b8837071f57.jpg', '$2y$10$wJ1XrUtyIi5hdfxLH0OlYeba0qX0kJXA5eD8H62EQ2JHp1fQzSxUm'),
(18, 'Pelakor Kuat', 'Citra', '081234567892', 'kuat@gmail.com', 'Surabaya', 'Jl. Kekuatan No. 3, Surabaya', 'B1234KU', '67b883507f2d6.jpg', '$2y$10$6JVY/WIjyuq4tgEFbvLTMOsxvQ2Ag3Go01NJ0H0R.Wbbb9hUI/19e'),
(19, 'Pelakor Terpercaya', 'Dewi', '081234567893', 'terpercaya@gmail.com', 'Medan', 'Jl. Kepercayaan No. 4, Medan', 'B1234TR', '67b8833220a8b.jpg', '$2y$10$Zx6CnEShkTeglAtuLurO3eQkPlNSBsBYoAhXch59jhfZtlVh6hCRm'),
(20, 'Pelakor Ramah', 'Eko', '081234567894', 'ramah@gmail.com', 'Makassar', 'Jl. Keramahan No. 5, Makassar', 'B1234RA', '67b8831432911.jpg', '$2y$10$WrM4PZVf6lx7VtMgZJTgAeuSxWOZJIPnziE4swChnK1bJBX61nRqy'),
(21, 'Pelakor Jago', 'Fajar', '081234567895', 'jago@gmail.com', 'Semarang', 'Jl. Kejagoan No. 6, Semarang', 'B1234JA', '67b882e086d4f.jpg', '$2y$10$fQFO0B9sYBJVYWRKecOyUuXFtR9Ug9B9q9M2L4R.KMES97eEhVBmi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cucian`
--

CREATE TABLE `cucian` (
  `id_cucian` int(11) NOT NULL,
  `id_agen` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `jenis` varchar(15) DEFAULT NULL,
  `item_type` varchar(30) DEFAULT NULL,
  `total_item` int(11) DEFAULT NULL,
  `preview_price` decimal(10,2) DEFAULT NULL,
  `berat` double DEFAULT NULL,
  `alamat` varchar(100) NOT NULL,
  `catatan` text NOT NULL,
  `status_cucian` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cucian`
--

INSERT INTO `cucian` (`id_cucian`, `id_agen`, `id_pelanggan`, `tgl_mulai`, `tgl_selesai`, `jenis`, `item_type`, `total_item`, `preview_price`, `berat`, `alamat`, `catatan`, `status_cucian`) VALUES
(46, 16, 17, '2025-02-24', '2025-02-24', 'cuci', 'Baju (1)', 1, NULL, 100, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Selesai'),
(47, 16, 17, '2025-02-24', '2025-02-24', 'komplit', 'Baju (2), Celana (2), Jaket (2', 6, NULL, 100, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Selesai'),
(48, 16, 17, '2025-02-24', '2025-02-24', 'cuci', 'Baju (1)', 1, NULL, 100, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Selesai'),
(49, 20, 17, '2025-02-24', '0000-00-00', 'cuci', 'Baju (1)', 1, NULL, NULL, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Penjemputan'),
(50, 16, 17, '2025-02-24', '2025-02-24', 'komplit', 'Baju (8)', 8, NULL, 100, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Selesai'),
(51, 16, 17, '2025-02-24', '0000-00-00', 'komplit', 'Jaket (21)', 21, NULL, NULL, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Penjemputan'),
(52, 16, 17, '2025-02-24', '0000-00-00', 'komplit', 'Jaket (21)', 21, NULL, 100, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Penjemputan'),
(53, 16, 17, '2025-02-24', '2025-02-24', 'komplit', 'Baju (1), Celana (1)', 2, NULL, 100000, 'Jl. Sudirman No. 2, Bandung, Bandung', '', 'Selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `harga`
--

CREATE TABLE `harga` (
  `id_harga` int(11) NOT NULL,
  `jenis` varchar(30) NOT NULL,
  `id_agen` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `harga_baju` int(11) DEFAULT NULL,
  `harga_celana` int(11) DEFAULT NULL,
  `harga_jaket` int(11) DEFAULT NULL,
  `harga_karpet` int(11) DEFAULT NULL,
  `harga_pakaian_khusus` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `harga`
--

INSERT INTO `harga` (`id_harga`, `jenis`, `id_agen`, `harga`, `harga_baju`, `harga_celana`, `harga_jaket`, `harga_karpet`, `harga_pakaian_khusus`) VALUES
(1, 'cuci', 16, 100000, NULL, NULL, NULL, NULL, NULL),
(2, 'setrika', 16, 100000, NULL, NULL, NULL, NULL, NULL),
(3, 'komplit', 16, 100000, NULL, NULL, NULL, NULL, NULL),
(4, 'baju', 16, 1500, NULL, NULL, NULL, NULL, NULL),
(5, 'celana', 16, 1500, NULL, NULL, NULL, NULL, NULL),
(6, 'jaket', 16, 1500, NULL, NULL, NULL, NULL, NULL),
(7, 'karpet', 16, 1500, NULL, NULL, NULL, NULL, NULL),
(8, 'pakaian_khusus', 16, 1500, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(30) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `telp` varchar(13) DEFAULT NULL,
  `kota` varchar(20) DEFAULT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `foto` text NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `email`, `telp`, `kota`, `alamat`, `foto`, `password`) VALUES
(16, 'takamura', 'takamura@gmail.com', '081111111111', 'Jakarta', 'Jl. Merdeka No. 1, Jakarta', '67b875f5aeb28.jpg', '$2y$10$1.GMkosPlY8Lq5vk9BYEcuOVgLJNSZv7O9EbGKyjyFr8W.T4a1sLy'),
(17, 'sakamoto', 'sakamoto@gmail.com', '081111111112', 'Bandung', 'Jl. Sudirman No. 2, Bandung', '67b8759ddf42b.jpg', '$2y$10$kYifgo9RiePliU1/.IHHhOZH1/WGyGeRneiSzEZDLjCUpb2Cp7kpG'),
(18, 'oldboy', 'oldboy@gmail.com', '081111111113', 'Surabaya', 'Jl. Pemuda No. 3, Surabaya', '67b87559ebc66.jpg', '$2y$10$PvXQ40kRQUVSOg2hbWt.XOw5Ea9EiARb.yB43ERBREbF/EvAE/7Da'),
(19, 'nagumo', 'nagumo@gmail.com', '081111111114', 'Medan', 'Jl. Gatot Subroto No. 4, Medan', '67b8750166694.jpg', '$2y$10$DCQLWGirRNwcAA1DsJB2neuOSp2Kj9cjZqU8aBSC83n3emhhbmk/S'),
(20, 'yiyi', 'yiyi@gmail.com', '081111111115', 'Makassar', 'Jl. Ahmad Yani No. 5, Makassar', '67b8749653b6f.jpg', '$2y$10$Q7rNyZOkn39GkQ5uHwTv8OQp1Ytz.XFiPY/FwTQiHsY64x/fKpXG6'),
(21, 'nakoshi susumu', 'nakoshi@gmail.com', '081111111116', 'Semarang', 'Jl. Veteran No. 6, Semarang', '67b8744fc456f.jpg', '$2y$10$wMG6juWtnrK1lo2egCv5bukqTLLt2g9WFTa90lAUVUv32OWiZ66q.'),
(22, 'johanliebert', 'johanliebert@gmail.com', '081111111117', 'Yogyakarta', 'Jl. Malioboro No. 7, Yogyakarta', '67b873f0ca1d1.jpg', '$2y$10$5MbSh97oYMrQSpdZDSNpOOLF4ZwcbQNjgvKKxgV8klUFLqy.BJW0S'),
(23, 'bradpitt', 'bradpitt@gmail.com', '081111111118', 'Bogor', 'Jl. Pahlawan No. 8, Bogor', '67b873a0a6061.jpg', '$2y$10$CtoXsijMcsGTE55Tw7.V1.YJxP7rz7fzv65ANbKfenbRgvhRhzaAG'),
(24, 'reiayanami', 'reiayanami@gmail.com', '081111111119', 'Depok', 'Jl. Sudirman No. 9, Depok', '67b87313e72da.jpg', '$2y$10$nFR1DMw04Jj2fg/O.va7fuJboAxzluuOOB8wTjVqyfm731jZeE956');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `kode_transaksi` int(11) NOT NULL,
  `id_cucian` int(11) NOT NULL,
  `id_agen` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `total_bayar` int(11) DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `rating` int(11) DEFAULT NULL,
  `komentar` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`kode_transaksi`, `id_cucian`, `id_agen`, `id_pelanggan`, `tgl_mulai`, `tgl_selesai`, `total_bayar`, `payment_status`, `rating`, `komentar`) VALUES
(46, 46, 16, 17, '2025-02-24', '2025-02-24', 0, '', NULL, ''),
(47, 47, 16, 17, '2025-02-24', '2025-02-24', 10006000, '', NULL, ''),
(48, 47, 16, 17, '2025-02-24', '2025-02-24', 10006000, '', NULL, ''),
(49, 48, 16, 17, '2025-02-24', '2025-02-24', 10001500, '', NULL, ''),
(50, 53, 16, 17, '2025-02-24', '2025-02-24', 2147483647, '', NULL, ''),
(51, 50, 16, 17, '2025-02-24', '2025-02-24', 10012000, '', NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `agen`
--
ALTER TABLE `agen`
  ADD PRIMARY KEY (`id_agen`);

--
-- Indeks untuk tabel `cucian`
--
ALTER TABLE `cucian`
  ADD PRIMARY KEY (`id_cucian`);

--
-- Indeks untuk tabel `harga`
--
ALTER TABLE `harga`
  ADD PRIMARY KEY (`id_harga`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`kode_transaksi`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `agen`
--
ALTER TABLE `agen`
  MODIFY `id_agen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `cucian`
--
ALTER TABLE `cucian`
  MODIFY `id_cucian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `harga`
--
ALTER TABLE `harga`
  MODIFY `id_harga` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `kode_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
