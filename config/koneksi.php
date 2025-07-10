<?php
// config/koneksi.php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_keuangan';

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>