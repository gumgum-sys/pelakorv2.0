<?php
session_start();
include 'connect-db.php';
include 'functions/functions.php';

cekAgen();

$idAgen = $_SESSION["agen"];

// Ambil data harga yang sudah tersimpan untuk tiap jenis
$priceTypes = ['cuci', 'setrika', 'komplit', 'baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
$prices = [];
foreach ($priceTypes as $jenis) {
    $query = "SELECT harga FROM harga WHERE id_agen = $idAgen AND jenis = '$jenis'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    $prices[$jenis] = $row ? $row['harga'] : 1000;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Ubah Data Harga</title>
    <style>
        .price-input {
            font-size: 1.2rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        .price-card {
            padding: 2rem;     
            margin: 2rem auto;
            max-width: 800px;
        }
        .price-card .input-field {
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="card price-card">
            <div class="card-content">
                <h3 class="header light center">Ubah Data Harga</h3>
                <form action="" method="post" id="editPriceForm">
                    <!-- Package Prices -->
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="cuci" name="cuci" class="validate price-input" 
                               value="<?= $prices['cuci'] ?>" required min="1000" step="500">
                        <label for="cuci">Harga Cuci (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">iron</i>
                        <input type="number" id="setrika" name="setrika" class="validate price-input" 
                               value="<?= $prices['setrika'] ?>" required min="1000" step="500">
                        <label for="setrika">Harga Setrika (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="komplit" name="komplit" class="validate price-input" 
                               value="<?= $prices['komplit'] ?>" required min="1000" step="500">
                        <label for="komplit">Harga Cuci + Setrika (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <!-- Per-Item Prices -->
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="baju" name="baju" class="validate price-input" 
                               value="<?= $prices['baju'] ?>" required min="1000" step="500">
                        <label for="baju">Harga Baju (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="celana" name="celana" class="validate price-input" 
                               value="<?= $prices['celana'] ?>" required min="1000" step="500">
                        <label for="celana">Harga Celana (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="jaket" name="jaket" class="validate price-input" 
                               value="<?= $prices['jaket'] ?>" required min="1000" step="500">
                        <label for="jaket">Harga Jaket (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="karpet" name="karpet" class="validate price-input" 
                               value="<?= $prices['karpet'] ?>" required min="1000" step="500">
                        <label for="karpet">Harga Karpet (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <div class="input-field">
                        <i class="material-icons prefix">local_laundry_service</i>
                        <input type="number" id="pakaian_khusus" name="pakaian_khusus" class="validate price-input" 
                               value="<?= $prices['pakaian_khusus'] ?>" required min="1000" step="500">
                        <label for="pakaian_khusus">Harga Pakaian Khusus (Rp)</label>
                        <span class="helper-text" data-error="Harga minimal Rp 1000"></span>
                    </div>
                    <!-- Submit Button -->
                    <div class="center">
                        <button class="btn-large waves-effect waves-light blue darken-2" type="submit" name="simpan">
                            <i class="material-icons left">save</i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // Client-side validation
        document.querySelector('#editPriceForm').addEventListener('submit', function(e) {
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
// Function to update price data using prepared statements
function ubahHarga($data) {
    global $connect, $idAgen;
    
    $priceKeys = ['cuci', 'setrika', 'komplit', 'baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
    $prices = [];
    $minPrice = 1000;
    
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
    
    mysqli_begin_transaction($connect);
    
    try {
        // MODIFIKASI: Gunakan INSERT ... ON DUPLICATE KEY UPDATE agar jika data belum ada maka akan INSERT
        $stmt = mysqli_prepare($connect, 
            "INSERT INTO harga (id_agen, jenis, harga) 
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE harga = ?");
        if (!$stmt) {
            throw new Exception(mysqli_error($connect));
        }
        
        foreach ($prices as $jenis => $harga) {
            mysqli_stmt_bind_param($stmt, "isii", $idAgen, $jenis, $harga, $harga);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Gagal menyimpan harga untuk $jenis");
            }
        }
        
        mysqli_commit($connect);
        return ['status' => true];
    } catch (Exception $e) {
        mysqli_rollback($connect);
        error_log("Error in ubahHarga: " . $e->getMessage());
        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}

if (isset($_POST["simpan"])) {
    $result = ubahHarga($_POST);
    if ($result['status']) {
        echo "
            <script>
                Swal.fire({
                    title: 'Success',
                    text: 'Prices updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location = 'edit-harga.php';
                });
            </script>
        ";
    } else {
        $errorMsg = $result['message'] ?? "Failed to update prices. Please try again.";
        echo "
            <script>
                Swal.fire({
                    title: 'Error',
                    text: '$errorMsg',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        ";
        error_log("Database error: " . mysqli_error($connect));
    }
}
?>
