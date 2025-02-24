<?php
session_start();
include 'connect-db.php';

// Konfigurasi pagination server-side
$jumlahDataPerHalaman = 3;
$query = mysqli_query($connect, "SELECT a.*, COALESCE(AVG(NULLIF(t.rating, 0)), 0) as rating 
                                FROM agen a 
                                LEFT JOIN transaksi t ON a.id_agen = t.id_agen 
                                GROUP BY a.id_agen");
$jumlahData = mysqli_num_rows($query);
$jumlahHalaman = ceil($jumlahData / $jumlahDataPerHalaman);
$halamanAktif = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$awalData = ($jumlahDataPerHalaman * $halamanAktif) - $jumlahDataPerHalaman;

// Query data agen untuk tampilan awal (server-side)
$agen = mysqli_query($connect, "SELECT a.*, COALESCE(AVG(NULLIF(t.rating, 0)), 0) as rating 
                               FROM agen a 
                               LEFT JOIN transaksi t ON a.id_agen = t.id_agen 
                               GROUP BY a.id_agen 
                               ORDER BY a.nama_laundry ASC 
                               LIMIT $awalData, $jumlahDataPerHalaman");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laundryku</title>
    <?php include 'headtags.html'; ?>
    <style>
        .agent-card {
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .agent-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .agent-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .card-content {
            padding: 16px;
        }
        .card-action {
            padding: 16px;
            border-top: 1px solid #eee;
        }
        .rating-stars {
            color: #ffd700;
            font-size: 20px;
        }
        .pagination {
            margin: 2rem 0;
        }
        /* Sembunyikan pesan "no results" secara default */
        #noResults {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <br>
        <h1 class="header center orange-text">
            <img src="img/banner.png" width="70%" alt="Laundryku Banner">
        </h1>
        <div class="row center">
            <h5 class="header col s12 light">"Solusi Laundry Praktis Tanpa Keluar Rumah"</h5>
        </div>

        <!-- Menu Buttons Section (contoh) -->
        <div class="row center">
            <div id="body">
                <?php if (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) : ?>
                    <div class="hero__btn" data-animation="fadeInRight" data-delay="1s">
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="pelanggan.php">Profil Saya</a>
                        <?php 
                        $idPelanggan = $_SESSION['pelanggan'];
                        $cek = mysqli_query($connect, "SELECT * FROM cucian WHERE id_pelanggan = $idPelanggan AND status_cucian != 'Selesai'");
                        $status = mysqli_num_rows($cek) > 0 ? "Status Cucian<i class='material-icons right'>notifications_active</i>" : "Status Cucian";

                        $cek = mysqli_query($connect, "SELECT * FROM transaksi WHERE id_pelanggan = $idPelanggan AND rating = 0 OR komentar = ''");
                        $transaksi = mysqli_num_rows($cek) > 0 ? "Riwayat Transaksi<i class='material-icons right'>notifications_active</i>" : "Riwayat Transaksi";
                        ?>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="status.php"><?= $status ?></a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="transaksi.php"><?= $transaksi ?></a>
                    </div>
                <?php elseif (isset($_SESSION["login-agen"]) && isset($_SESSION["agen"])) : ?>
                    <div class="hero__btn" data-animation="fadeInRight" data-delay="1s">
                        <?php
                        $idAgen = $_SESSION['agen'];
                        $cek = mysqli_query($connect, "SELECT * FROM cucian WHERE id_agen = $idAgen AND status_cucian != 'Selesai'");
                        $status = mysqli_num_rows($cek) > 0 ? "Status Cucian<i class='material-icons right'>notifications_active</i>" : "Status Cucian";
                        ?>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="agen.php">Profil Saya</a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="status.php"><?= $status ?></a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="transaksi.php">Riwayat Transaksi</a>
                    </div>
                <?php elseif (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) : ?>
                    <div class="hero__btn" data-animation="fadeInRight" data-delay="1s">
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="admin.php">Profil Saya</a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="status.php">Status Cucian</a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="transaksi.php">Riwayat Transaksi</a>
                        <br><br>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="list-agen.php">Data Agen</a>
                        <a id="download-button" class="btn-large waves-effect waves-light blue darken-3" href="list-pelanggan.php">Data Pelanggan</a>
                    </div>
                <?php else : ?>
                    <div class="hero__btn" data-animation="fadeInRight" data-delay="1s">
                        <a href="registrasi.php" id="download-button" class="btn-large waves-effect waves-light blue darken-3">Daftar Sekarang</a>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row">
            <div class="input-field col s12 m6 offset-m3">
                <i class="material-icons prefix">search</i>
                <input type="text" id="keyword" placeholder="Cari berdasarkan nama laundry atau kota..." class="validate" autocomplete="off">
                <label for="keyword">Pencarian</label>
            </div>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="row grey-text center">
            <div class="col s12">
                <h5>Tidak ada hasil yang ditemukan</h5>
            </div>
        </div>

        <!-- Agent List Container -->
        <div class="row" id="agentContainer">
            <!-- Data awal server-side -->
            <?php foreach($agen as $dataAgen): ?>
                <div class="col s12 m4">
                    <div class="card agent-card">
                        <div class="card-image">
                            <a href="detail-agen.php?id=<?= $dataAgen['id_agen'] ?>">
                                <img src="img/agen/<?= $dataAgen['foto'] ?>" alt="<?= $dataAgen["nama_laundry"] ?>" class="agent-image">
                            </a>
                        </div>
                        <div class="card-content">
                            <span class="card-title"><?= $dataAgen["nama_laundry"] ?></span>
                            <p>
                                <i class="material-icons tiny">location_on</i> <?= $dataAgen["alamat"] ?>, <?= $dataAgen["kota"] ?>
                            </p>
                            <p>
                                <i class="material-icons tiny">phone</i> <?= $dataAgen["telp"] ?>
                            </p>
                        </div>
                        <div class="card-action">
                            <div class="rating-stars">
                                <?= str_repeat('★', round($dataAgen['rating'])) . str_repeat('☆', 5 - round($dataAgen['rating'])) ?>
                            </div>
                            <a href="detail-agen.php?id=<?= $dataAgen['id_agen'] ?>">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination (server-side, sudah dimodifikasi untuk AJAX) -->
        <div class="row center">
            <ul class="pagination">
                <?php if($halamanAktif > 1) : ?>
                    <li class="waves-effect">
                        <a href="#!" data-page="<?= $halamanAktif - 1 ?>"><i class="material-icons">chevron_left</i></a>
                    </li>
                <?php endif; ?>

                <?php for($i = 1; $i <= $jumlahHalaman; $i++) : ?>
                    <li class="waves-effect <?= $i == $halamanAktif ? 'active blue' : '' ?>">
                        <a href="#!" data-page="<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if($halamanAktif < $jumlahHalaman) : ?>
                    <li class="waves-effect">
                        <a href="#!" data-page="<?= $halamanAktif + 1 ?>"><i class="material-icons">chevron_right</i></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <script src="materialize/js/materialize.min.js"></script>
    <script src="js/script.js"></script>
    <!-- File scriptAjax.js menangani pencarian & pagination AJAX -->
    <script src="js/scriptAjax.js"></script>
</body>
</html>
