<?php
session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Pastikan hanya Admin atau Agen yang dapat mengakses
if (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) {
    $login = "Admin";
} elseif (isset($_SESSION["login-agen"]) && isset($_SESSION["agen"])) {
    $login = "Agen";
} else {
    header("Location: login.php");
    exit();
}

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

// Execute query
$queryStr = "SELECT t.*, c.total_item, c.berat, c.jenis, c.item_type, c.tgl_mulai, c.tgl_selesai 
    FROM transaksi t 
    JOIN cucian c ON t.id_cucian = c.id_cucian 
    WHERE $conditions
    ORDER BY c.tgl_mulai DESC";
$query = mysqli_query($connect, $queryStr);

// Jika ini request AJAX, keluarkan baris tabel saja dan exit
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    while ($transaksi = mysqli_fetch_assoc($query)) {
        $agentName = '';
        if ($login != "Pelanggan") {
            $agenQuery = mysqli_query($connect, "SELECT nama_laundry FROM agen WHERE id_agen = " . intval($transaksi["id_agen"]));
            $agenRow = mysqli_fetch_assoc($agenQuery);
            $agentName = $agenRow["nama_laundry"] ?? '';
        }
        $pelName = '';
        if ($login != "Agen") {
            $pelQuery = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = " . intval($transaksi["id_pelanggan"]));
            $pelRow = mysqli_fetch_assoc($pelQuery);
            $pelName = $pelRow["nama"] ?? '';
        }
        echo '<tr>';
        echo '  <td>' . htmlspecialchars($transaksi["kode_transaksi"]) . '</td>';
        if ($login != "Pelanggan") {
            echo '  <td>' . htmlspecialchars($agentName) . '</td>';
        }
        if ($login != "Agen") {
            echo '  <td>' . htmlspecialchars($pelName) . '</td>';
        }
        echo '  <td>' . htmlspecialchars($transaksi["total_item"]) . '</td>';
        echo '  <td>' . htmlspecialchars($transaksi["berat"]) . '</td>';
        echo '  <td>' . htmlspecialchars($transaksi["jenis"]) . '</td>';
        echo '  <td>Rp ' . number_format(getHargaPaket($transaksi["jenis"], $transaksi["id_agen"], $connect), 0, ',', '.') . '</td>';
        echo '  <td>Rp ' . number_format(getTotalPerItem($transaksi["item_type"] ?? '', $transaksi["id_agen"], $connect), 0, ',', '.') . '</td>';
        echo '  <td>Rp ' . number_format(calculateTotalHarga($transaksi, $connect), 0, ',', '.') . '</td>';
        echo '  <td>' . htmlspecialchars($transaksi["tgl_mulai"]) . '</td>';
        echo '  <td>' . htmlspecialchars($transaksi["tgl_selesai"]) . '</td>';
        echo '</tr>';
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "headtags.html"; ?>
    <title>Laporan Transaksi - <?= htmlspecialchars($login) ?></title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container my-4">
        <h3 class="text-center mb-4">Laporan Transaksi</h3>
        <div class="row mb-3">
            <div class="col-md-8">
                <form id="filterForm" method="GET" action="">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>">
                        </div>
                        <?php if ($login == "Admin"): ?>
                        <div class="col-md-4">
                            <label for="agent_id" class="form-label">Filter Agen</label>
                            <select class="form-select" id="agent_id" name="agent_id">
                                <option value="">-- Semua Agen --</option>
                                <?php
                                $agenListQuery = mysqli_query($connect, "SELECT DISTINCT t.id_agen, a.nama_laundry FROM transaksi t JOIN agen a ON t.id_agen = a.id_agen WHERE t.payment_status = 'Paid' ORDER BY a.nama_laundry ASC");
                                while ($row = mysqli_fetch_assoc($agenListQuery)) {
                                    $selected = (isset($_GET['agent_id']) && $_GET['agent_id'] == $row['id_agen']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($row['id_agen']) . '" ' . $selected . '>' . htmlspecialchars($row['nama_laundry']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
                <div class="mt-3">
                    <a href="cetak-laporan.php?<?= http_build_query($_GET) ?>" class="btn btn-success" target="_blank">Cetak Laporan</a>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
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
                <tbody id="reportBody">
                    <?php
                    // Reset pointer hasil query agar dapat digunakan kembali
                    mysqli_data_seek($query, 0);
                    while ($transaksi = mysqli_fetch_assoc($query)) {
                        $agentName = '';
                        if ($login != "Pelanggan") {
                            $agenQuery = mysqli_query($connect, "SELECT nama_laundry FROM agen WHERE id_agen = " . intval($transaksi["id_agen"]));
                            $agenRow = mysqli_fetch_assoc($agenQuery);
                            $agentName = $agenRow["nama_laundry"] ?? '';
                        }
                        $pelName = '';
                        if ($login != "Agen") {
                            $pelQuery = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = " . intval($transaksi["id_pelanggan"]));
                            $pelRow = mysqli_fetch_assoc($pelQuery);
                            $pelName = $pelRow["nama"] ?? '';
                        }
                        echo '<tr>';
                        echo '  <td>' . htmlspecialchars($transaksi["kode_transaksi"]) . '</td>';
                        if ($login != "Pelanggan") {
                            echo '  <td>' . htmlspecialchars($agentName) . '</td>';
                        }
                        if ($login != "Agen") {
                            echo '  <td>' . htmlspecialchars($pelName) . '</td>';
                        }
                        echo '  <td>' . htmlspecialchars($transaksi["total_item"]) . '</td>';
                        echo '  <td>' . htmlspecialchars($transaksi["berat"]) . '</td>';
                        echo '  <td>' . htmlspecialchars($transaksi["jenis"]) . '</td>';
                        echo '  <td>Rp ' . number_format(getHargaPaket($transaksi["jenis"], $transaksi["id_agen"], $connect), 0, ',', '.') . '</td>';
                        echo '  <td>Rp ' . number_format(getTotalPerItem($transaksi["item_type"] ?? '', $transaksi["id_agen"], $connect), 0, ',', '.') . '</td>';
                        echo '  <td>Rp ' . number_format(calculateTotalHarga($transaksi, $connect), 0, ',', '.') . '</td>';
                        echo '  <td>' . htmlspecialchars($transaksi["tgl_mulai"]) . '</td>';
                        echo '  <td>' . htmlspecialchars($transaksi["tgl_selesai"]) . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // Real-time filtering dengan fetch API
        document.getElementById('start_date').addEventListener('change', filterReport);
        document.getElementById('end_date').addEventListener('change', filterReport);
        <?php if ($login == "Admin"): ?>
        document.getElementById('agent_id').addEventListener('change', filterReport);
        <?php endif; ?>

        function filterReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            <?php if ($login == "Admin"): ?>
            const agentId = document.getElementById('agent_id').value;
            <?php else: ?>
            const agentId = '';
            <?php endif; ?>
            const params = new URLSearchParams({
                ajax: 'true',
                start_date: startDate,
                end_date: endDate,
                <?php if ($login == "Admin"): ?>
                agent_id: agentId,
                <?php endif; ?>
            });
            fetch('laporan.php?' + params.toString())
                .then(response => response.text())
                .then(data => {
                    document.getElementById('reportBody').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>