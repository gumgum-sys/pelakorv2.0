<?php
session_start();
require_once 'connect-db.php';
require_once 'functions/functions.php';

// Authentication & Authorization
function checkAuth() {
    if (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"])) {
        return ["type" => "Admin", "id" => $_SESSION["admin"]];
    } elseif (isset($_SESSION["login-agen"]) && isset($_SESSION["agen"])) {
        return ["type" => "Agen", "id" => $_SESSION["agen"]];
    } elseif (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) {
        return ["type" => "Pelanggan", "id" => $_SESSION["pelanggan"]];
    }
    header("Location: login.php");
    exit();
}

$auth = checkAuth();
$login = $auth["type"];
$userId = $auth["id"];

// Fetch orders: Admin melihat semua, Pelanggan hanya miliknya, Agen sesuai agen
function fetchOrders($connect, $login, $userId) {
    if ($login === "Admin") {
        $query = "SELECT * FROM cucian WHERE status_cucian != 'Selesai'";
    } elseif ($login === "Pelanggan") {
        $query = "SELECT * FROM cucian WHERE id_pelanggan = $userId AND status_cucian != 'Selesai'";
    } elseif ($login === "Agen") {
        $query = "SELECT * FROM cucian WHERE id_agen = $userId AND status_cucian != 'Selesai'";
    }
    $result = mysqli_query($connect, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCustomerName($connect, $customerId) {
    $query = mysqli_query($connect, "SELECT nama FROM pelanggan WHERE id_pelanggan = $customerId");
    $customer = mysqli_fetch_assoc($query);
    return $customer['nama'] ?? 'Unknown';
}

function getStatusIcon($status) {
    return match($status) {
        'Penjemputan'       => 'directions_car',
        'Sedang di Cuci'    => 'local_laundry_service',
        'Sedang Di Jemur'   => 'wb_sunny',
        'Sedang di Setrika' => 'iron',
        'Pengantaran'       => 'local_shipping',
        'Selesai'           => 'check_circle',
        default             => 'info'
    };
}

// Form Handlers (hanya untuk Agen)
function handleWeightUpdate($connect) {
    if (!isset($_POST["simpanBerat"])) return;
    if (!isset($_SESSION["login-agen"])) return;
    
    $id = $_POST["id_cucian"];
    $berat = $_POST["berat"];
    
    $stmt = mysqli_prepare($connect, "UPDATE cucian SET berat = ? WHERE id_cucian = ?");
    mysqli_stmt_bind_param($stmt, "di", $berat, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header("Location: status.php");
    exit();
}

function handleStatusUpdate($connect) {
    if (!isset($_POST["simpanStatus"])) return;
    if (!isset($_SESSION["login-agen"])) return;
    
    $id = $_POST["id_cucian"];
    $status = $_POST["status_cucian"];
    $inputBerat = isset($_POST['berat']) ? $_POST['berat'] : null;
    
    // Update status di tabel cucian
    $stmt = mysqli_prepare($connect, "UPDATE cucian SET status_cucian = ? WHERE id_cucian = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if($status === "Selesai"){
       $orderQuery = mysqli_query($connect, "SELECT * FROM cucian WHERE id_cucian = $id");
       $order = mysqli_fetch_assoc($orderQuery);
       if($order) {
          // Update berat jika kosong dan ada input berat
          if(empty($order['berat']) && !empty($inputBerat)) {
              $updateBerat = mysqli_prepare($connect, "UPDATE cucian SET berat = ? WHERE id_cucian = ?");
              mysqli_stmt_bind_param($updateBerat, "di", $inputBerat, $id);
              mysqli_stmt_execute($updateBerat);
              mysqli_stmt_close($updateBerat);
              $order['berat'] = $inputBerat;
          }
          $tgl_selesai = $order['tgl_selesai'];
          if ($tgl_selesai == '0000-00-00') {
              $tgl_selesai = date("Y-m-d");
              mysqli_query($connect, "UPDATE cucian SET tgl_selesai = '$tgl_selesai' WHERE id_cucian = $id");
          }
          
          // Hitung total bayar (harga paket + total per item)
          $total_bayar = calculateTotalHarga($order, $connect);
          $payment_status = 'Paid';
          $rating = NULL;
          $komentar = '';
          
          // Insert ke tabel transaksi
          $stmt2 = mysqli_prepare($connect, 
              "INSERT INTO transaksi (id_cucian, id_agen, id_pelanggan, tgl_mulai, tgl_selesai, total_bayar, payment_status, rating, komentar)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
          );
          mysqli_stmt_bind_param($stmt2, "iiissdiss",
              $order['id_cucian'],
              $order['id_agen'],
              $order['id_pelanggan'],
              $order['tgl_mulai'],
              $tgl_selesai,
              $total_bayar,
              $payment_status,
              $rating,
              $komentar
          );
          mysqli_stmt_execute($stmt2);
          mysqli_stmt_close($stmt2);
       }
       header("Location: transaksi.php");
    } else {
       header("Location: status.php");
    }
    exit();
}

// Jalankan form handlers
handleWeightUpdate($connect);
handleStatusUpdate($connect);

// Ambil data cucian
$orders = fetchOrders($connect, $login, $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include "headtags.html"; ?>
  <title>Status Cucian - <?= htmlspecialchars($login) ?></title>
  <style>
    .status-badge {
        padding: 8px 15px;
        border-radius: 20px;
        color: white;
        font-size: 0.9em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .status-badge:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .penjemputan { background: linear-gradient(45deg, #2196F3, #64B5F6); }
    .sedang-di-cuci { background: linear-gradient(45deg, #FFC107, #FFD54F); }
    .sedang-di-jemur { background: linear-gradient(45deg, #4CAF50, #81C784); }
    .sedang-di-setrika { background: linear-gradient(45deg, #9C27B0, #BA68C8); }
    .pengantaran { background: linear-gradient(45deg, #FF5722, #FF8A65); }
    .selesai { background: linear-gradient(45deg, #607D8B, #90A4AE); }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>
  
  <div id="body">
    <h3 class="header col s10 light center">Status Cucian</h3>
    <br>
    <div class="col s10 offset-s1">
      <table class="responsive-table centered">
        <thead>
          <tr>
            <th>ID Cucian</th>
            <?php if($login !== "Pelanggan"): ?>
              <th>Pelanggan</th>
            <?php endif; ?>
            <th>Total Item</th>
            <th>Berat (kg)</th>
            <th>Jenis</th>
            <th>Harga Paket</th>
            <th>Total Harga Per Item</th>
            <th>Total Harga</th>
            <th>Tanggal Dibuat</th>
            <th>Status</th>
            <?php if($login === "Agen"): ?>
              <th>Aksi</th>
              <th>Detail</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($orders as $order): ?>
          <tr>
            <td><?= htmlspecialchars($order['id_cucian']) ?></td>
            <?php if($login !== "Pelanggan"): ?>
              <td><?= htmlspecialchars(getCustomerName($connect, $order['id_pelanggan'])) ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($order['total_item']) ?></td>
            <td>
              <?php 
              // Hanya Agen dapat menginput berat jika belum ada; Admin & Pelanggan hanya melihat nilai
              if($login === "Agen" && is_null($order['berat'])): ?>
                <form action="" method="post">
                  <input type="hidden" name="id_cucian" value="<?= htmlspecialchars($order['id_cucian']) ?>">
                  <input type="number" name="berat" size="3" step="0.1" required>
                  <button class="btn blue darken-2" type="submit" name="simpanBerat">
                    <i class="material-icons">send</i>
                  </button>
                </form>
              <?php else: ?>
                <?= htmlspecialchars($order['berat'] ?? '-') ?>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($order['jenis']) ?></td>
            <td>
              <?php
                $paketPrice = getHargaPaket($order['jenis'], $order['id_agen'], $connect);
                echo "Rp " . number_format($paketPrice, 0, ',', '.');
              ?>
            </td>
            <td>
              <?php
                $itemTotal = getTotalPerItem($order['item_type'], $order['id_agen'], $connect);
                echo "Rp " . number_format($itemTotal, 0, ',', '.');
              ?>
            </td>
            <td>
              <?php 
              $totalHarga = calculateTotalHarga($order, $connect);
              echo is_null($totalHarga) ? '-' : "Rp " . number_format($totalHarga, 0, ',', '.');
              ?>
            </td>
            <td><?= htmlspecialchars($order['tgl_mulai']) ?></td>
            <td>
              <span class="status-badge <?= str_replace(' ', '-', strtolower($order['status_cucian'])) ?>">
                <i class="material-icons"><?= getStatusIcon($order['status_cucian']) ?></i>
                <?= htmlspecialchars($order['status_cucian']) ?>
              </span>
            </td>
            <?php if($login === "Agen"): ?>
              <td>
                <form action="" method="post">
                  <input type="hidden" name="id_cucian" value="<?= htmlspecialchars($order['id_cucian']) ?>">
                  <input type="hidden" name="berat" value="<?= htmlspecialchars($order['berat']) ?>">
                  <select class="browser-default" name="status_cucian" required>
                    <option value="" disabled selected>Status :</option>
                    <option value="Penjemputan">Penjemputan</option>
                    <option value="Sedang di Cuci">Sedang di Cuci</option>
                    <option value="Sedang Di Jemur">Sedang Di Jemur</option>
                    <option value="Sedang di Setrika">Sedang di Setrika</option>
                    <option value="Pengantaran">Pengantaran</option>
                    <option value="Selesai">Selesai</option>
                  </select>
                  <button class="btn blue darken-2" type="submit" name="simpanStatus">
                    <i class="material-icons">send</i>
                  </button>
                </form>
              </td>
              <td>
                <button class="btn modal-trigger" data-target="modal-<?= htmlspecialchars($order['id_cucian']) ?>">
                  Detail
                </button>
              </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <?php if($login === "Agen"): ?>
  <!-- Modals untuk detail order (hanya Agen) -->
  <?php foreach($orders as $order): ?>
    <div id="modal-<?= htmlspecialchars($order['id_cucian']) ?>" class="modal">
      <div class="modal-content">
        <h4>Detail Order #<?= htmlspecialchars($order['id_cucian']) ?></h4>
        <table class="striped">
          <thead>
            <tr>
              <th>Item</th>
              <th>Quantity</th>
              <th>Harga per Item</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $items = explode(', ', $order['item_type']);
            foreach($items as $item) {
                if(trim($item) == "") continue;
                if(preg_match('/([^(]+)\((\d+)\)/', $item, $matches)) {
                    $itemName = trim($matches[1]);
                    $quantity = (int)$matches[2];
                    $price = getPerItemPrice(strtolower($itemName), $order['id_agen'], $connect);
                    $total = $price * $quantity;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($itemName) ?></td>
                        <td><?= $quantity ?></td>
                        <td>Rp <?= number_format($price, 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <a href="#!" class="modal-close btn">Close</a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php include "footer.php"; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var modals = document.querySelectorAll('.modal');
      M.Modal.init(modals);
    });
  </script>
</body>
</html>
