<?php
session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Authentication check
cekPelanggan();

// Database helper functions
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

// MODIFIKASI: Fungsi createOrder disesuaikan untuk menyertakan preview_price
function createOrder($connect, $orderData) {
    // Hitung preview_price menggunakan fungsi calculateTotalHarga
    $totalHarga = calculateTotalHarga($orderData, $connect);

    $stmt = mysqli_prepare($connect,
        "INSERT INTO cucian (id_agen, id_pelanggan, tgl_mulai, jenis, item_type, total_item, preview_price, alamat, catatan, status_cucian) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Penjemputan')");

    mysqli_stmt_bind_param($stmt, "iisssisss",
        $orderData['id_agen'],
        $orderData['id_pelanggan'],
        $orderData['tgl_mulai'],
        $orderData['jenis'],
        $orderData['item_type'],
        $orderData['total_item'],
        $totalHarga, // preview_price
        $orderData['alamat'],
        $orderData['catatan']
    );

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Get request parameters
$idAgen = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$jenis = filter_input(INPUT_GET, 'jenis', FILTER_SANITIZE_STRING);
$idPelanggan = $_SESSION["pelanggan"];

// Handle AJAX request for prices
if (isset($_GET['action']) && $_GET['action'] == 'getPrices' && isset($_GET['idAgen'])) {
    $agentId = filter_input(INPUT_GET, 'idAgen', FILTER_SANITIZE_NUMBER_INT);
    $prices = getAgentPrices($connect, $agentId);
    header('Content-Type: application/json');
    echo json_encode($prices);
    exit;
}

// Fetch data
$agen = getAgentData($connect, $idAgen);
$pelanggan = getCustomerData($connect, $idPelanggan);
$rating = calculateAgentRating($connect, $idAgen);

// Function to get agent prices
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
            const idAgen = <?= $idAgen ?>;
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

        // ... (JavaScript Functions) ...
    </script>

    <?php
    // Order Processing
    if (isset($_POST["pesan"])) {
        // ... (Order Processing Code) ...
    }
    ?>

    <?php include 'footer.php'; ?>
</body>
</html>