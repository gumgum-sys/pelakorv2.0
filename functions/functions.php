<?php
// USERNAME
function validasiUsername($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('Username Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!preg_match("/^[a-zA-Z0-9]*$/",$objek)){
        echo "
            <script>
                Swal.fire('Username Hanya Boleh Huruf dan Angka','','error');
            </script>
        ";
        exit;
    }
}

// NO HP
function validasiTelp($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('No Telp Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!preg_match("/^[0-9]*$/",$objek)){
        echo "
            <script>
                Swal.fire('No Telp Hanya Diperbolehkan Angka','','error');
            </script>
        ";
        exit;
    }
}

// Berat
function validasiBerat($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('Form Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!preg_match("/^[0-9]*$/",$objek)){
        echo "
            <script>
                Swal.fire('Satuan Berat Hanya Diperbolehkan Angka','','error');
            </script>
        ";
        exit;
    }
}

// HARGA
function validasiHarga($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('Form Harga Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!preg_match("/^[0-9]*$/",$objek)){
        echo "
            <script>
                Swal.fire('Masukkan Harga Yang Benar !','','error');
            </script>
        ";
        exit;
    }
}

// EMAIL
function validasiEmail($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('Form Email Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!filter_var($objek, FILTER_VALIDATE_EMAIL)){
        echo "
            <script>
                Swal.fire('Masukkan Format Email Yang Benar','','error');
            </script>
        ";
        exit;
    }
}

// NAMA ORANG
function validasiNama($objek){
    if (empty($objek)){
        echo "
            <script>
                Swal.fire('Form Nama Tidak Boleh Kosong','','error');
            </script>
        ";
        exit;
    } else if (!preg_match("/^[a-zA-Z .]*$/",$objek)){
        echo "
            <script>
                Swal.fire('Nama Hanya Diperbolehkan Huruf dan Spasi','','error');
            </script>
        ";
        exit;
    }
}

// SESSION CHECKS
function cekAdmin(){
    if ( isset($_SESSION["login-admin"]) && isset($_SESSION["admin"]) ){
        $idAdmin = $_SESSION["admin"];
    } else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}

function cekAgen(){
    if (isset($_SESSION["login-agen"]) && isset($_SESSION["agen"]) ){
        $idAgen = $_SESSION["agen"];
    } else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}

function cekPelanggan(){
    if ( isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"]) ){
        $idPelanggan = $_SESSION["pelanggan"];
    } else {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}

function cekLogin(){
    if (
        (isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) ||
        (isset($_SESSION["login-agen"]) && isset($_SESSION["agen"])) ||
        (isset($_SESSION["login-admin"]) && isset($_SESSION["admin"]))
    ) {
        echo "
            <script>
                window.location = 'index.php';
            </script>
        ";
        exit;
    }
}

function cekBelumLogin(){
    if (
        !(isset($_SESSION["login-pelanggan"]) && isset($_SESSION["pelanggan"])) &&
        !(isset($_SESSION["login-agen"]) && isset($_SESSION["agen"])) &&
        !(isset($_SESSION["login-admin"]) && isset($_SESSION["admin"]))
    ) {
        echo "
            <script>
                window.location = 'login.php';
            </script>
        ";
        exit;
    }
}

// ============== BAGIAN HARGA DAN PERHITUNGAN ============= //

// Ambil harga paket cuci / setrika / komplit
function getHargaPaket($jenis, $idAgen, $connect) {
    $q = mysqli_query($connect, "SELECT harga FROM harga WHERE id_agen = $idAgen AND jenis = '$jenis'");
    $row = mysqli_fetch_assoc($q);
    return $row['harga'] ?? 0;
}

// Ambil harga per item (baju, celana, jaket, dll.)
function getPerItemPrice($item, $idAgen, $connect) {
    $q = mysqli_query($connect, "SELECT harga FROM harga WHERE id_agen = $idAgen AND jenis = '$item'");
    $row = mysqli_fetch_assoc($q);
    return $row['harga'] ?? 0;
}

// Menghitung total harga semua item. Contoh item_type: "Baju(2), Celana(3)"
function getTotalPerItem($itemType, $idAgen, $connect) {
    $total = 0;
    if (empty($itemType)) return $total;
    
    $items = explode(', ', $itemType);
    foreach($items as $it) {
        if(trim($it) == "") continue;
        if(preg_match('/([^(]+)\((\d+)\)/', $it, $matches)) {
            $item = strtolower(trim($matches[1]));  // 'baju', 'celana', dsb.
            $qty = (int)$matches[2];
            $price = getPerItemPrice($item, $idAgen, $connect);
            $total += $price * $qty;
        }
    }
    return $total;
}

/**
 * Menghitung total harga cucian:
 *   - Secara default: (harga paket * berat) + total per item.
 *   - Jika berat belum diisi, boleh diatur:
 *       a) pakai default 0 (tidak menambah biaya paket)
 *       b) atau pakai default 1 (harga paket flat)
 *       c) atau hilangkan perkalian berat sama sekali jika tak dibutuhkan
 */
function calculateTotalHarga($order, $connect) {
    // Contoh: jika berat masih null, kita anggap 0 agar tidak error:
    $berat = $order['berat'] ?? 0;
    
    // Apabila Bos ingin harga paket TIDAK tergantung berat, pakai:
    //   $paket = getHargaPaket($order["jenis"], $order["id_agen"], $connect);
    // Apabila Bos tetap ingin dikalikan berat, pakai:
    $paket = getHargaPaket($order["jenis"], $order["id_agen"], $connect) * $berat;
    
    $totalPerItem = getTotalPerItem($order["item_type"] ?? '', $order["id_agen"], $connect);
    return $paket + $totalPerItem;
}
