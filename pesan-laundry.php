<?php
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

function createOrder($connect, $orderData) {
    $stmt = mysqli_prepare($connect, 
        "INSERT INTO cucian (id_agen, id_pelanggan, tgl_mulai, jenis, item_type, total_item, alamat, catatan, status_cucian) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Penjemputan')"
    );
    
    mysqli_stmt_bind_param($stmt, "iisssiss",
        $orderData['id_agen'],
        $orderData['id_pelanggan'],
        $orderData['tgl_mulai'],
        $orderData['jenis'],
        $orderData['item_type'],
        $orderData['total_item'],
        $orderData['alamat'],
        $orderData['catatan']
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Ambil parameter dari URL
$idAgen = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$jenis = filter_input(INPUT_GET, 'jenis', FILTER_SANITIZE_STRING);
$idPelanggan = $_SESSION["pelanggan"];

// Ambil data agen dan pelanggan
$agen = getAgentData($connect, $idAgen);
$pelanggan = getCustomerData($connect, $idPelanggan);
$rating = calculateAgentRating($connect, $idAgen);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'headtags.html'; ?>
    <title>Pemesanan Laundry</title>
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
        }
        .agent-info-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .agent-details h3 {
            margin: 0;
        }
        .agent-details ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .item-selection {
            margin: 20px 0;
        }
        .item-selection label {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .item-quantity {
            width: 60px !important;
            margin-left: 10px !important;
        }
        .price-preview {
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Bagian Informasi Laundry -->
    <div class="container">
        <div class="row">
            <div class="agent-info-container col s12">
                <div>
                    <img src="img/agen/<?= htmlspecialchars($agen['foto']) ?>" class="profile-img" alt="Laundry Logo">
                </div>
                <div class="agent-details">
                    <h3><?= htmlspecialchars($agen["nama_laundry"]) ?></h3>
                    <ul>
                        <li>
                            <fieldset class="bintang">
                                <span class="starImg star-<?= $rating ?>"></span>
                            </fieldset>
                        </li>
                        <li>Alamat: <?= htmlspecialchars($agen["alamat"] . ", " . $agen["kota"]) ?></li>
                        <li>No. HP: <?= htmlspecialchars($agen["telp"]) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Pemesanan -->
    <div class="row">
        <div class="col s10 offset-s1">
            <form action="" method="post" id="orderForm">
                <div class="col s5">
                    <h3 class="header light center">Data Diri</h3>
                    <div class="input-field">
                        <input id="nama" type="text" disabled value="<?= htmlspecialchars($pelanggan['nama']) ?>">
                        <label for="nama">Nama Penerima</label>
                    </div>
                    <div class="input-field">
                        <input id="telp" type="text" disabled value="<?= htmlspecialchars($pelanggan['telp']) ?>">
                        <label for="telp">No Telp</label>
                    </div>
                    <div class="input-field">
                        <textarea class="materialize-textarea" name="alamat" id="alamat" required><?= htmlspecialchars($pelanggan['alamat'] . ", " . $pelanggan['kota']) ?></textarea>
                        <label for="alamat">Alamat</label>
                    </div>
                </div>

                <!-- Informasi Paket Laundry -->
                <div class="col s5 offset-s1">
                    <h3 class="header light center">Info Paket Laundry</h3>
                    
                    <!-- Pilihan Item -->
                    <div class="input-field">
                        <h5>Pilih Jenis Pakaian:</h5>
                        <?php
                        $items = ['baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'];
                        foreach ($items as $item):
                        ?>
                            <div class="item-selection">
                                <label>
                                    <input type="checkbox" class="item-checkbox" 
                                           name="items[<?= $item ?>]" value="<?= $item ?>"
                                           onchange="toggleQuantity('<?= $item ?>')">
                                    <span><?= ucfirst($item) ?></span>
                                    <input type="number" name="quantities[<?= $item ?>]" 
                                           id="quantity-<?= $item ?>" class="item-quantity"
                                           min="0" value="0" disabled 
                                           onchange="calculatePrice()">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pilihan Jenis Paket -->
                    <div class="input-field">
                        <h5>Jenis Paket:</h5>
                        <div class="row">
                            <?php
                            $serviceTypes = [
                                'cuci' => 'Cuci',
                                'setrika' => 'Setrika', 
                                'komplit' => 'Komplit'
                            ];
                            foreach ($serviceTypes as $type => $label):
                                $checked = ($jenis === $type) ? 'checked' : '';
                            ?>
                                <div class="col s4">
                                    <div class="card-panel hoverable">
                                        <label>
                                            <input type="radio" name="jenis" value="<?= $type ?>" 
                                                   <?= $checked ?> onchange="calculatePrice()" required>
                                            <span class="black-text"><?= $label ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="input-field">
                        <textarea class="materialize-textarea" name="catatan" id="catatan"></textarea>
                        <label for="catatan">Catatan</label>
                    </div>

                    <!-- Preview Harga -->
                    <div class="price-preview">
                        <h4>Perkiraan Harga: <span id="pricePreview">Rp 0</span></h4>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="input-field center">
                        <button class="btn-large blue darken-2" type="submit" name="pesan">
                            Buat Pesanan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    let itemPrices = {};

    function fetchPrices() {
        // Pastikan idAgen terformat dengan benar
        const idAgen = <?= json_encode($idAgen) ?>;
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

    function toggleQuantity(itemType) {
        const checkbox = document.querySelector(`input[name="items[${itemType}]"]`);
        const quantityInput = document.getElementById(`quantity-${itemType}`);
        quantityInput.disabled = !checkbox.checked;
        if (!checkbox.checked) {
            quantityInput.value = 0;
        }
        calculatePrice();
    }

    function calculatePrice() {
        let totalPrice = 0;
        // Menghitung harga berdasarkan kuantitas dan data harga yang diambil
        ['baju', 'celana', 'jaket', 'karpet', 'pakaian_khusus'].forEach(item => {
            const qty = parseInt(document.getElementById(`quantity-${item}`).value) || 0;
            if (qty > 0 && itemPrices[item]) {
                totalPrice += qty * itemPrices[item];
            }
        });
        document.getElementById('pricePreview').innerText =
            `Rp ${totalPrice.toLocaleString('id-ID')}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchPrices();
        document.querySelectorAll('.item-quantity').forEach(input => {
            input.addEventListener('change', calculatePrice);
        });
    });
    </script>

    <?php
    // Proses Order
    if (isset($_POST["pesan"])) {
        $orderData = [
            'id_agen' => $idAgen,
            'id_pelanggan' => $idPelanggan,
            'tgl_mulai' => date("Y-m-d H:i:s"),
            'jenis' => filter_input(INPUT_POST, 'jenis', FILTER_SANITIZE_STRING),
            'alamat' => filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING),
            'catatan' => filter_input(INPUT_POST, 'catatan', FILTER_SANITIZE_STRING),
        ];

        $items = $_POST['items'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $totalItems = 0;
        $itemDetails = [];

        foreach ($items as $item => $val) {
            $qty = (int)($quantities[$item] ?? 0);
            if ($qty > 0) {
                $totalItems += $qty;
                $itemDetails[] = ucfirst($item) . " ($qty)";
            }
        }

        if ($totalItems <= 0) {
            echo "<script>
                Swal.fire({
                    title: 'Error',
                    text: 'Tidak ada item yang dipilih',
                    icon: 'error'
                });
            </script>";
        } else {
            $orderData['item_type'] = implode(', ', $itemDetails);
            $orderData['total_item'] = $totalItems;

            if (createOrder($connect, $orderData)) {
                echo "<script>
                    Swal.fire({
                        title: 'Pesanan Berhasil Dibuat',
                        text: 'Silahkan periksa status cucian',
                        icon: 'success'
                    }).then(() => {
                        window.location = 'status.php';
                    });
                </script>";
            } else {
                echo "<script>
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal membuat pesanan',
                        icon: 'error'
                    });
                </script>";
            }
        }
    }
    ?>

    <?php include 'footer.php'; ?>
</body>
</html>
