<?php
// Nonaktifkan error display untuk mencegah output HTML error
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Pastikan user telah login sebagai Pelanggan
cekPelanggan();

function getAgentData($connect, $agentId) {
    $stmt = mysqli_prepare($connect, "SELECT * FROM agen WHERE id_agen = ?");
    mysqli_stmt_bind_param($stmt, "i", $agentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

function getCustomerData($connect, $customerId) {
    $stmt = mysqli_prepare($connect, "SELECT * FROM pelanggan WHERE id_pelanggan = ?");
    mysqli_stmt_bind_param($stmt, "i", $customerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $data;
}

function calculateAgentRating($connect, $agentId) {
    $stmt = mysqli_prepare($connect, "SELECT rating FROM transaksi WHERE id_agen = ? AND rating > 0");
    mysqli_stmt_bind_param($stmt, "i", $agentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $totalStars = 0;
    $count = 0;

    while ($rating = mysqli_fetch_assoc($result)) {
        $totalStars += $rating["rating"];
        $count++;
    }
    mysqli_stmt_close($stmt);

    return $count > 0 ? ceil($totalStars / $count) : 0;
}

// Endpoint untuk mengambil data harga secara murni JSON
if (isset($_GET['action']) && $_GET['action'] == 'getPrices') {
    $agentId = filter_input(INPUT_GET, 'idAgen', FILTER_SANITIZE_NUMBER_INT);
    if (!$agentId) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }
    $prices = getAgentPrices($connect, $agentId);
    header('Content-Type: application/json');
    echo json_encode($prices);
    exit;
}

function getAgentPrices($connect, $agentId) {
    $priceTypes = ['cuci', 'setrika', 'komplit', 'baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
    $prices = [];
    foreach ($priceTypes as $jenis) {
        $query = "SELECT harga FROM harga WHERE id_agen = $agentId AND jenis = '$jenis'";
        $result = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($result);
        $prices[$jenis] = $row ? $row['harga'] : 1000;
    }
    return $prices;
}

$agen = getAgentData($connect, filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
$idPelanggan = $_SESSION["pelanggan"];
$pelanggan = getCustomerData($connect, $idPelanggan);
$rating = calculateAgentRating($connect, filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <title>Pemesanan Laundry</title>
    <style>
        /* ... (CSS Styles) ... */
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <script>
        let itemPrices = {};

        function fetchPrices() {
            const idAgen = <?= json_encode(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT)) ?>;
            fetch(`ajax/agen.php?action=getPrices&idAgen=${idAgen}`)
                .then(response => response.json())
                .then(data => {
                    itemPrices = data;
                    calculatePrice();
                })
                .catch(error => {
                    console.error('Error fetching prices:', error);
                    M.toast({html: 'Gagal memuat harga', classes: 'red'});
                });
        }
        // Fungsi JavaScript tambahan (misalnya calculatePrice) dapat ditambahkan di sini
    </script>
    <?php
    // Jika ada pemrosesan order, letakkan kode di sini (jika diperlukan)
    ?>
    <?php include 'footer.php'; ?>
</body>
</html>
