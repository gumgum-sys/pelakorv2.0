<?php
session_start();
include 'connect-db.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idAgen = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if (!$idAgen) {
    header("Location: index.php");
    exit;
}

// Get agent data
$query = mysqli_query($connect, "SELECT * FROM agen WHERE id_agen = " . intval($idAgen));
$agen = mysqli_fetch_assoc($query);

if (!$agen) {
    header("Location: index.php");
    exit;
}

// Calculate average rating
$temp = $agen["id_agen"];
$queryStar = mysqli_query($connect,"SELECT * FROM transaksi WHERE id_agen = '$temp'");
$totalStar = 0;
$i = 0;
while ($star = mysqli_fetch_assoc($queryStar)){
    // Only count if rating exists
    if ($star["rating"] != 0){
        $totalStar += $star["rating"];
        $i++;
    }
}
$fixStar = ($i > 0) ? ceil($totalStar / $i) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title><?= htmlspecialchars($agen["nama_laundry"]) ?></title>
    <style>
        .profile-img {
            border-radius: 50%;
            width: 70%;
            aspect-ratio: 1/1;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <br><br>
    <div class="row">
        <div class="col s2 offset-s4">
            <img src="img/agen/<?= htmlspecialchars($agen['foto']) ?>" class="profile-img" />
            <a id="download-button" class="btn red darken-3" href="pesan-laundry.php?id=<?= $idAgen ?>">PESAN LAUNDRY</a>
        </div>
        <div class="col s6">
            <h3><?= htmlspecialchars($agen["nama_laundry"]) ?></h3>
            <ul>
                <li>
                    <fieldset class="bintang">
                        <span class="starImg star-<?= $fixStar ?>"></span>
                    </fieldset>
                </li>
                <li>Alamat : <?= htmlspecialchars($agen["alamat"] . ", " . $agen["kota"]) ?></li>
                <li>No. HP : <?= htmlspecialchars($agen["telp"]) ?></li>
            </ul>
        </div>
    </div>

    <!-- Price data section remains unchanged -->
    
    <div class="container">
        <h4 class="header light center">Ulasan Pengguna</h4>
        <div class="row">
            <?php
            $reviewQuery = mysqli_query($connect, "SELECT t.*, p.nama, p.foto 
                                          FROM transaksi t 
                                          JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                                          WHERE t.id_agen = '$idAgen' AND t.rating > 0 AND t.komentar != ''
                                          ORDER BY t.kode_transaksi DESC");
        
            if(mysqli_num_rows($reviewQuery) > 0) {
                while ($row = mysqli_fetch_assoc($reviewQuery)) {
            ?>
                <div class="col s12 m4 l4">
                    <div class="card-panel grey lighten-5">
                        <div class="row" style="margin-bottom: 0;">
                            <div class="col s3">
                                <img src="img/<?= $row['foto'] ? 'pelanggan/' . htmlspecialchars($row['foto']) : 'default-user.png' ?>" 
                                     class="circle responsive-img" 
                                     alt="<?= $row['foto'] ? 'foto pengguna' : 'default foto' ?>"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div class="col s9">
                                <strong class="black-text"><?= htmlspecialchars($row["nama"]) ?></strong>
                                <?php if($row["rating"] > 0): ?>
                                    <div class="rating-container">
                                        <fieldset class="bintang">
                                            <span class="starImg star-<?= ceil($row['rating']) ?>"></span>
                                        </fieldset>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($row["komentar"])): ?>
                                    <p class="grey-text text-darken-1 comment-text">
                                        <?= htmlspecialchars($row["komentar"]) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['agen']) && $_SESSION['agen'] == $idAgen): ?>
                                    <form action="delete-review.php" method="POST" 
                                          style="margin-top: 5px;" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                                        <input type="hidden" name="kode_transaksi" 
                                               value="<?= $row['kode_transaksi'] ?>">
                                        <input type="hidden" name="csrf_token" 
                                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="btn-small red darken-2 waves-effect waves-light">
                                            Hapus
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo '<div class="col s12 center-align">Belum ada ulasan</div>';
            }
            ?>
        </div>
    </div>

    <style>
        .card-panel {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            padding: 15px !important;
            margin: 0.5rem 0.5rem !important;
        }
        
        .rating-container {
            margin: 5px 0;
            min-height: 20px;
        }
        
        .comment-text {
            font-size: 0.9rem;
            margin: 5px 0;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-height: 1.4;
        }
        
        @media only screen and (max-width: 600px) {
            .card-panel {
                margin: 0.3rem 0 !important;
                padding: 10px !important;
            }
            
            .comment-text {
                font-size: 0.85rem;
                -webkit-line-clamp: 2;
            }
        }
        
        @media only screen and (min-width: 993px) {
            .container {
                width: 90%;
                max-width: 1200px;
            }
        }
        
        @media only screen and (min-width: 601px) and (max-width: 992px) {
            .container {
                width: 95%;
            }
        }
    </style>

    <?php include "footer.php"; ?>
</body>
</html>
