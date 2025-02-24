<?php
session_start();
include '../connect-db.php';

header('Content-Type: application/json');

// Konfigurasi pagination
$jumlahDataPerHalaman = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$awalData = ($jumlahDataPerHalaman * $page) - $jumlahDataPerHalaman;

if(isset($_GET['type']) && isset($_GET['keyword'])) {
    $type = $_GET['type'];
    $keyword = mysqli_real_escape_string($connect, $_GET['keyword']);
    
    if($type === 'agen') {
        // Hitung total data untuk pagination
        $countQuery = "SELECT COUNT(*) as total FROM agen";
        if(!empty($keyword)) {
            $countQuery .= " WHERE nama_laundry LIKE '%$keyword%' 
                             OR nama_pemilik LIKE '%$keyword%' 
                             OR kota LIKE '%$keyword%' 
                             OR email LIKE '%$keyword%' 
                             OR alamat LIKE '%$keyword%'";
        }
        $countResult = mysqli_query($connect, $countQuery);
        $totalData = mysqli_fetch_assoc($countResult)['total'];
        
        // Query data agen
        $query = "SELECT * FROM agen";
        if(!empty($keyword)) {
            $query .= " WHERE nama_laundry LIKE '%$keyword%' 
                        OR nama_pemilik LIKE '%$keyword%' 
                        OR kota LIKE '%$keyword%' 
                        OR email LIKE '%$keyword%' 
                        OR alamat LIKE '%$keyword%'";
        }
        $query .= " ORDER BY id_agen DESC 
                    LIMIT $awalData, $jumlahDataPerHalaman";
        
    } else if($type === 'pelanggan') {
        // Hitung total data untuk pagination
        $countQuery = "SELECT COUNT(*) as total FROM pelanggan";
        if(!empty($keyword)) {
            $countQuery .= " WHERE nama LIKE '%$keyword%' 
                             OR kota LIKE '%$keyword%' 
                             OR email LIKE '%$keyword%' 
                             OR alamat LIKE '%$keyword%'";
        }
        $countResult = mysqli_query($connect, $countQuery);
        $totalData = mysqli_fetch_assoc($countResult)['total'];
        
        // Query data pelanggan
        $query = "SELECT * FROM pelanggan";
        if(!empty($keyword)) {
            $query .= " WHERE nama LIKE '%$keyword%' 
                        OR kota LIKE '%$keyword%' 
                        OR email LIKE '%$keyword%' 
                        OR alamat LIKE '%$keyword%'";
        }
        $query .= " ORDER BY id_pelanggan DESC 
                    LIMIT $awalData, $jumlahDataPerHalaman";
    }
    
    // Eksekusi query
    $result = mysqli_query($connect, $query);
    $data = [];
    
    while($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    $totalPages = ceil($totalData / $jumlahDataPerHalaman);
    
    // Kembalikan data dalam format JSON
    echo json_encode([
        'data' => $data,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    exit;
}

// Jika parameter tidak valid
echo json_encode(['error' => 'Invalid parameters']);
?>
