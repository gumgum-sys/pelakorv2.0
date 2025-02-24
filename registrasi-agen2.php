<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekAgen();

$idAgen = $_SESSION["agen"];

// Ambil data agen
$query = "SELECT * FROM agen WHERE id_agen = '$idAgen'";
$result = mysqli_query($connect, $query);
$agen = mysqli_fetch_assoc($result);

// Cek apakah data harga sudah ada (diharapkan 8 jenis)
$checkPricesQuery = "SELECT COUNT(*) as count FROM harga WHERE id_agen = $idAgen";
$checkPricesResult = mysqli_query($connect, $checkPricesQuery);
$exists = (mysqli_fetch_assoc($checkPricesResult)['count'] >= 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Registrasi Agen Lanjutan</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="row">
        <!-- Syarat dan Ketentuan -->
        <div class="col s4 offset-s1">
            <div class="card">
                <div class="col center" style="margin:20px">
                    <img src="img/banner.png" alt="laundryku" width="100%"/><br><br>
                    <span class="card-title black-text">Syarat dan Ketentuan :</span>
                </div>
                <div class="card-content">
                    <p>1. Memiliki lokasi usaha laundry yang strategis dan teridentifikasi oleh google map</p>
                    <p>2. Agen memiliki nama usaha serta logo perusahaan agar dapat diposting di website laundryKU</p>
                    <p>3. Mampu memberikan layanan Laundry dengan kualitas prima dan harga yang bersaing</p>
                    <p>4. Memiliki driver yang bersedia untuk melakukan penjemputan dan pengantaran terhadap laundry pelanggan</p>
                    <p>5. Harga dari jenis laundry ditentukan berdasarkan berat per kilo (kg) ditambah dengan biaya ongkos kirim</p>
                    <p>6. Bersedia untuk memberikan informasi kepada pelanggan mengenai harga Laundry Kiloan</p>
                    <p>7. Bersedia untuk menerapkan sistem poin kepada pelanggan</p>
                    <p>8. Bersedia memberikan kompensasi untuk setiap kemungkinan terjadinya seperti kehilangan atau kerusakan pakaian</p>
                    <p>9. Agen tidak diperkenankan untuk melakukan kerjasama dengan pihak laundry lain</p>
                    <p>10. Sistem bagi hasil sebesar 5% setiap 7 hari</p>
                    <p>11. Status agen dicabut apabila melanggar kesepakatan</p>
                </div>
                <div class="card-action">
                    <a href="term.php">Baca Selengkapnya</a>
                </div>
            </div>
        </div>
        <!-- Form Input Harga -->
        <div class="col s4 offset-s1">
            <div class="card price-card">
                <div class="card-content">
                    <h3 class="header light center">Data Harga</h3>
                    <form action="" method="post" id="priceForm">
                        <!-- Package Prices -->
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="cuci" name="cuci" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="cuci">Harga Cuci (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">iron</i>
                            <input type="number" id="setrika" name="setrika" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="setrika">Harga Setrika (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="komplit" name="komplit" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="komplit">Harga Cuci + Setrika (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <!-- Per-Item Prices -->
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="baju" name="baju" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="baju">Harga Baju (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="celana" name="celana" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="celana">Harga Celana (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="jaket" name="jaket" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="jaket">Harga Jaket (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="karpet" name="karpet" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="karpet">Harga Karpet (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">local_laundry_service</i>
                            <input type="number" id="pakaian_khusus" name="pakaian_khusus" class="validate price-input" 
                                   value="1000" required min="1000" step="500">
                            <label for="pakaian_khusus">Harga Pakaian Khusus (Rp)</label>
                            <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                        </div>
                        <!-- Submit Button -->
                        <div class="center">
                            <button class="btn-large waves-effect waves-light blue darken-2" type="submit" name="submit">
                                <i class="material-icons left">save</i>Simpan Harga
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // Client-side validation
        document.querySelector('#priceForm').addEventListener('submit', function(e) {
            const minPrice = 1000;
            const inputs = ['cuci', 'setrika', 'komplit', 'baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
            for (const name of inputs) {
                const value = parseInt(document.querySelector(`input[name="${name}"]`).value);
                if (value < minPrice) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: `Harga minimum untuk ${name} adalah Rp. ${minPrice.toLocaleString()}`,
                        icon: 'error'
                    });
                    return;
                }
            }
        });
    </script>
</body>
</html>

<?php
// Function to insert or update price data using prepared statements
function dataHarga($data) {
    global $connect, $idAgen;
    
    $priceKeys = ['cuci', 'setrika', 'komplit', 'baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
    $prices = [];
    $minPrice = 1000;
    
    // Validasi dan sanitasi input
    foreach ($priceKeys as $key) {
        $price = filter_var($data[$key], FILTER_SANITIZE_NUMBER_INT);
        if ($price < $minPrice) {
            return [
                'status' => false,
                'message' => "Harga minimum untuk $key adalah Rp. " . number_format($minPrice)
            ];
        }
        $prices[$key] = $price;
    }
    
    // Cek apakah data harga sudah ada (>= 8 baris)
    $check = mysqli_query($connect, "SELECT COUNT(*) as count FROM harga WHERE id_agen = $idAgen");
    $exists = (mysqli_fetch_assoc($check)['count'] >= count($priceKeys));
    
    mysqli_begin_transaction($connect);
    
    try {
        // Siapkan prepared statement (UPDATE atau INSERT)
        if ($exists) {
            $stmt = mysqli_prepare($connect, "UPDATE harga SET harga = ? WHERE id_agen = ? AND jenis = ?");
        } else {
            $stmt = mysqli_prepare($connect, "INSERT INTO harga (harga, id_agen, jenis) VALUES (?, ?, ?)");
        }
        if (!$stmt) {
            throw new Exception(mysqli_error($connect));
        }
        
        $successCount = 0;
        foreach ($prices as $jenis => $harga) {
            mysqli_stmt_bind_param($stmt, "iis", $harga, $idAgen, $jenis);
            if (mysqli_stmt_execute($stmt)) {
                $successCount++;
            } else {
                throw new Exception("Gagal menyimpan harga untuk $jenis");
            }
        }
        
        mysqli_stmt_close($stmt);
        
        if ($successCount == count($priceKeys)) {
            mysqli_commit($connect);
            return ['status' => true];
        } else {
            throw new Exception("Gagal menyimpan semua harga");
        }
    } catch (Exception $e) {
        mysqli_rollback($connect);
        error_log("Error in dataHarga: " . $e->getMessage());
        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}

if (isset($_POST["submit"])) {
    $result = dataHarga($_POST);
    if ($result['status']) {
        echo "
            <script>
                Swal.fire({
                    title: 'Pendaftaran Berhasil',
                    text: 'Data harga berhasil disimpan',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location = 'index.php';
                });
            </script>
        ";
    } else {
        echo "
            <script>
                Swal.fire({
                    title: 'Gagal',
                    text: '".$result['message']."',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        ";
        error_log("Database error: " . mysqli_error($connect));
    }
}
?>
