<?php
session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Ensure only agents can access
if (!isset($_SESSION["login-agen"])) {
    header("Location: login.php");
    exit();
}

// Get transaction ID from URL
if (!isset($_GET['id'])) {
    header("Location: transaksi.php");
    exit();
}

$transactionId = intval($_GET['id']);
$agentId = intval($_SESSION["agen"]);

// Get transaction details
$query = mysqli_query($connect, 
    "SELECT t.*, c.total_item, c.berat, c.jenis, c.item_type, c.tgl_mulai, c.tgl_selesai, c.alamat,
            a.nama_laundry, a.telp as telp_agen, a.alamat as alamat_agen, a.kota as kota_agen,
            p.nama as nama_pelanggan, p.telp as telp_pelanggan
     FROM transaksi t
     JOIN cucian c ON t.id_cucian = c.id_cucian
     JOIN agen a ON t.id_agen = a.id_agen
     JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
     WHERE t.kode_transaksi = $transactionId AND t.id_agen = $agentId");

if (mysqli_num_rows($query) === 0) {
    header("Location: transaksi.php");
    exit();
}

$transaction = mysqli_fetch_assoc($query);

// Parse items and calculate prices
$items = [];
if (!empty($transaction['item_type'])) {
    $itemTypes = explode(', ', $transaction['item_type']);
    foreach ($itemTypes as $item) {
        if (preg_match('/([^(]+)\((\d+)\)/', $item, $matches)) {
            $itemName = trim($matches[1]);
            $quantity = (int)$matches[2];
            $pricePerItem = getHargaPaket(strtolower($itemName), $transaction['id_agen'], $connect);
            $items[] = [
                'name' => $itemName,
                'quantity' => $quantity,
                'price' => $pricePerItem,
                'total' => $pricePerItem * $quantity
            ];
        }
    }
}

// Calculate totals
$subtotalItems = array_sum(array_column($items, 'total'));
$weightPrice = getHargaPaket($transaction['jenis'], $transaction['id_agen'], $connect) * $transaction['berat'];
$totalPrice = $subtotalItems + $weightPrice;

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $transactionId ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .invoice-header {
            background: #f8f9fa;
            padding: 2rem;
            border-bottom: 2px solid #dee2e6;
        }
        .invoice-body {
            padding: 2rem;
        }
        .invoice-footer {
            background: #f8f9fa;
            padding: 2rem;
            border-top: 2px solid #dee2e6;
        }
        .company-info {
            margin-bottom: 2rem;
        }
        .customer-info {
            margin-bottom: 2rem;
        }
        .table th {
            background: #f8f9fa;
        }
        .total-section {
            border-top: 2px solid #dee2e6;
            padding-top: 1rem;
        }
        .invoice-title {
            color: #2c3e50;
            font-weight: bold;
        }
        @page {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="invoice-header">
            <div class="row">
                <div class="col-12 col-md-6">
                    <h1 class="invoice-title mb-4">INVOICE</h1>
                    <h5>No. Invoice: #<?= htmlspecialchars($transactionId) ?></h5>
                    <p class="text-muted mb-0">
                        Tanggal: <?= date('d/m/Y', strtotime($transaction['tgl_mulai'])) ?>
                    </p>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <div class="company-info">
                        <h3><?= htmlspecialchars($transaction['nama_laundry']) ?></h3>
                        <p class="mb-0"><?= htmlspecialchars($transaction['alamat_agen']) ?></p>
                        <p class="mb-0"><?= htmlspecialchars($transaction['kota_agen']) ?></p>
                        <p class="mb-0">Tel: <?= htmlspecialchars($transaction['telp_agen']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="row mb-4">
                <div class="col-12 col-md-6">
                    <h5 class="text-primary">Informasi Pelanggan</h5>
                    <div class="customer-info">
                        <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($transaction['nama_pelanggan']) ?></p>
                        <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($transaction['telp_pelanggan']) ?></p>
                        <p class="mb-1"><strong>Alamat:</strong> <?= htmlspecialchars($transaction['alamat']) ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <h5 class="text-primary">Detail Pesanan</h5>
                    <p class="mb-1"><strong>Jenis Layanan:</strong> <?= htmlspecialchars(ucfirst($transaction['jenis'])) ?></p>
                    <p class="mb-1"><strong>Tanggal Selesai:</strong> <?= date('d/m/Y', strtotime($transaction['tgl_selesai'])) ?></p>
                    <p class="mb-1"><strong>Status:</strong> Lunas</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format($item['total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal Items</strong></td>
                            <td class="text-end">Rp <?= number_format($subtotalItems, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">
                                <strong>Biaya <?= htmlspecialchars(ucfirst($transaction['jenis'])) ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?= $transaction['berat'] ?> kg x 
                                    Rp <?= number_format(getHargaPaket($transaction['jenis'], $transaction['id_agen'], $connect), 0, ',', '.') ?>
                                </small>
                            </td>
                            <td class="text-end">Rp <?= number_format($weightPrice, 0, ',', '.') ?></td>
                        </tr>
                        <tr class="total-section">
                            <td colspan="3" class="text-end"><strong>Total Pembayaran</strong></td>
                            <td class="text-end"><strong>Rp <?= number_format($totalPrice, 0, ',', '.') ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="invoice-footer">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">Terima kasih telah menggunakan jasa laundry kami!</p>
                    <small class="text-muted">Invoice ini sah dan diproses oleh komputer</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$content = ob_get_clean();

require_once 'vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

try {
    $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array(15, 15, 15, 15));
    $html2pdf->writeHTML($content);
    $fileName = sprintf('Invoice-%s-%s.pdf', $transactionId, date('Ymd'));
    $html2pdf->output($fileName, 'D');
} catch (Html2PdfException $e) {
    echo $e->getMessage();
    exit;
}
?>