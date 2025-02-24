<?php
session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Pastikan hanya Admin atau Agen yang dapat mengakses
if (!isset($_SESSION["login-admin"]) && !isset($_SESSION["login-agen"])) {
    header("Location: login.php");
    exit();
}

$login = isset($_SESSION["login-admin"]) ? "Admin" : "Agen";

// Build filter conditions
$conditions = "t.payment_status = 'Paid'";
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = mysqli_real_escape_string($connect, $_GET['start_date']);
    $conditions .= " AND c.tgl_mulai >= '$start_date'";
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = mysqli_real_escape_string($connect, $_GET['end_date']);
    $conditions .= " AND c.tgl_mulai <= '$end_date'";
}
if ($login == "Admin" && isset($_GET['agent_id']) && !empty($_GET['agent_id'])) {
    $agent_id = intval($_GET['agent_id']);
    $conditions .= " AND t.id_agen = $agent_id";
}
if ($login == "Agen") {
    $idAgen = intval($_SESSION["agen"]);
    $conditions .= " AND t.id_agen = $idAgen";
}

$queryStr = "
    SELECT t.*, c.total_item, c.berat, c.jenis, c.item_type, c.tgl_mulai, c.tgl_selesai, c.id_pelanggan 
    FROM transaksi t 
    JOIN cucian c ON t.id_cucian = c.id_cucian 
    WHERE $conditions
    ORDER BY c.tgl_mulai DESC
";
$query = mysqli_query($connect, $queryStr);

// Mulai tampungan output
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        /* Font dasar */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* Container utama */
        .report-container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }

        /* Header styling */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .report-header h1 {
            font-size: 16pt;
            margin: 0;
            padding: 0;
        }
        .report-header .sub-header {
            font-size: 9pt;
            color: #555;
        }

        /* Info Periode */
        .period-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 9pt;
        }

        /* Tabel utama */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table thead tr {
            background-color: #f2f2f2;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 5px 8px;
            text-align: left;
        }
        th {
            font-weight: bold;
        }

        /* Tabel nested item detail */
        .nested-table {
            width: 100%;
            border-collapse: collapse;
        }
        .nested-table thead tr {
            background-color: #e8e8e8;
        }
        .nested-table th, .nested-table td {
            border: 1px solid #ccc;
            padding: 4px;
            font-size: 8pt;
        }
        .nested-table th {
            text-align: center;
        }

        /* Footer / tanda tangan (opsional) */
        .report-footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9pt;
        }

        /* Contoh styling tanda tangan (jika ingin) */
        .signature {
            margin-top: 40px;
            text-align: center;
        }
        .signature .signee {
            display: inline-block;
            width: 200px;
            margin: 0 20px;
        }
        .signature .signee .name {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- HEADER -->
        <div class="report-header">
            <h1>LAPORAN TRANSAKSI</h1>
            <div class="sub-header">Laundry Management</div>
        </div>

        <!-- PERIODE (jika ada) -->
        <?php if (isset($start_date) && isset($end_date)): ?>
            <div class="period-info">
                Periode: <strong><?= htmlspecialchars($start_date) ?></strong> s/d 
                <strong><?= htmlspecialchars($end_date) ?></strong>
            </div>
        <?php endif; ?>

        <!-- TABLE DATA -->
        <table>
            <thead>
                <tr>
                    <th>Kode Transaksi</th>
                    <?php if ($login != "Pelanggan"): ?>
                        <th>Agen</th>
                    <?php endif; ?>
                    <?php if ($login != "Agen"): ?>
                        <th>Pelanggan</th>
                    <?php endif; ?>
                    <th>Total Item</th>
                    <th>Berat</th>
                    <th>Jenis</th>
                    <th>Harga Paket</th>
                    <th>Total Per Item</th>
                    <th>Total Bayar</th>
                    <th>Tanggal Pesan</th>
                    <th>Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($transaksi = mysqli_fetch_assoc($query)): 
                // Dapatkan nama agen (jika diperlukan)
                $agentName = '';
                if ($login != "Pelanggan") {
                    $agenQuery = mysqli_query($connect, "SELECT nama_laundry FROM agen WHERE id_agen = " . intval($transaksi["id_agen"]));
                    $agenRow = mysqli_fetch_assoc($agenQuery);
                    $agentName = $agenRow["nama_laundry"] ?? '';
                }
                // Dapatkan nama pelanggan (jika diperlukan)
                $pelName = '';
                if ($login != "Agen") {
                    $pelQuery = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = " . intval($transaksi["id_pelanggan"]));
                    $pelRow = mysqli_fetch_assoc($pelQuery);
                    $pelName = $pelRow["nama"] ?? '';
                }
                // Hitung nilai summary
                $hargaPaket   = getHargaPaket($transaksi["jenis"], $transaksi["id_agen"], $connect);
                $totalPerItem = getTotalPerItem($transaksi["item_type"] ?? '', $transaksi["id_agen"], $connect);
                $totalBayar   = calculateTotalHarga($transaksi, $connect);
            ?>
                <tr>
                    <td><?= htmlspecialchars($transaksi["kode_transaksi"]) ?></td>
                    <?php if ($login != "Pelanggan"): ?>
                        <td><?= htmlspecialchars($agentName) ?></td>
                    <?php endif; ?>
                    <?php if ($login != "Agen"): ?>
                        <td><?= htmlspecialchars($pelName) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($transaksi["total_item"]) ?></td>
                    <td><?= htmlspecialchars($transaksi["berat"]) ?></td>
                    <td><?= htmlspecialchars($transaksi["jenis"]) ?></td>
                    <td>Rp <?= number_format($hargaPaket, 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($totalPerItem, 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($totalBayar, 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($transaksi["tgl_mulai"]) ?></td>
                    <td><?= htmlspecialchars($transaksi["tgl_selesai"]) ?></td>
                </tr>
                <?php 
                    // Jika ada detail item, tampilkan tabel detail di bawah baris transaksi
                    if (!empty($transaksi['item_type'])) {
                        $items = [];
                        $itemTypes = explode(', ', $transaksi['item_type']);
                        foreach ($itemTypes as $item) {
                            if (preg_match('/([^(]+)\((\d+)\)/', $item, $matches)) {
                                $itemName = trim($matches[1]);
                                $quantity = (int)$matches[2];
                                $pricePerItem = getHargaPaket(strtolower($itemName), $transaksi['id_agen'], $connect);
                                $items[] = [
                                    'name' => $itemName,
                                    'quantity' => $quantity,
                                    'price' => $pricePerItem,
                                    'total' => $pricePerItem * $quantity
                                ];
                            }
                        }
                        if (!empty($items)) {
                ?>
                <tr>
                    <!-- Gunakan colspan = 11 karena header memiliki 11 kolom total -->
                    <td colspan="11">
                        <table class="nested-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $itm): ?>
                                <tr>
                                    <td><?= htmlspecialchars($itm['name']) ?></td>
                                    <td style="text-align:center;"><?= $itm['quantity'] ?></td>
                                    <td style="text-align:right;">Rp <?= number_format($itm['price'], 0, ',', '.') ?></td>
                                    <td style="text-align:right;">Rp <?= number_format($itm['total'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php 
                        }
                    }
                ?>
            <?php endwhile; ?>
            </tbody>
        </table>

        <!-- FOOTER (opsional) -->
        <div class="report-footer">
            Dicetak oleh: <strong><?= $login ?></strong><br>
            Tanggal Cetak: <strong><?= date('Y-m-d H:i:s') ?></strong>
        </div>

        <!-- Contoh baris tanda tangan (opsional) 
        <div class="signature">
            <div class="signee">
                Mengetahui,<br><br><br><br>
                <div class="name">______________________</div>
            </div>
            <div class="signee">
                Disetujui,<br><br><br><br>
                <div class="name">______________________</div>
            </div>
        </div>
        -->
    </div>
</body>
</html>
<?php
$content = ob_get_clean();

// Load library HTML2PDF
require_once 'vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;

try {
    // Landscape, A4, margin 10
    $html2pdf = new Html2Pdf('L', 'A4', 'en', true, 'UTF-8', array(10,10,10,10));
    $html2pdf->writeHTML($content);
    // 'D' untuk forced download
    $html2pdf->output('laporan.pdf', 'D');
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}
?>
