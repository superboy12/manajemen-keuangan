<?php
require_once 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $deskripsi = $_POST['deskripsi'];
    $jumlah = $_POST['jumlah'];
    $jenis = $_POST['jenis'];

    if (!empty($deskripsi) && is_numeric($jumlah)) {
        $query = "INSERT INTO transaksi (deskripsi, jumlah, jenis) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sds", $deskripsi, $jumlah, $jenis);
        mysqli_stmt_execute($stmt);
    }
}
header('Location: index.php');
exit();
?>