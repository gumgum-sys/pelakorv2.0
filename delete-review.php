<?php
session_start();
include 'connect-db.php';

// Only allow if logged in as agent
if (!isset($_SESSION['agen'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: login.php");
        exit;
    }
    
    // Validate and sanitize input
    if (!isset($_POST['kode_transaksi']) || !is_numeric($_POST['kode_transaksi'])) {
        header("Location: login.php");
        exit;
    }
    
    $kode_transaksi = intval($_POST['kode_transaksi']);
    
    // Verify that this transaction belongs to the agent using prepared statement
    $query = "SELECT id_agen FROM transaksi WHERE kode_transaksi = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $kode_transaksi);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transaksi = mysqli_fetch_assoc($result);
    
    if ($transaksi && $transaksi['id_agen'] == $_SESSION['agen']) {
        // Reset the review using prepared statement
        $updateQuery = "UPDATE transaksi SET rating = 0, komentar = '' WHERE kode_transaksi = ?";
        $stmt = mysqli_prepare($connect, $updateQuery);
        mysqli_stmt_bind_param($stmt, "i", $kode_transaksi);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Ulasan berhasil dihapus";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus ulasan";
        }
        
        // Redirect back to agent profile
        header("Location: detail-agen.php?id=" . $_SESSION['agen']);
        exit;
    }
}

// Invalid request
header("Location: login.php");
exit;
?>