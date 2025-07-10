<?php
require_once 'config/koneksi.php';

// Mengambil nilai 'action' dari URL, jika tidak ada maka null
$action = $_GET['action'] ?? null;

// Menentukan jenis aksi untuk mengarahkan pengguna ke halaman yang benar nanti
$is_savings_action = in_array($action, ['tambah_goal_tabungan', 'tambah_dana_tabungan']);
$is_debt_action = in_array($action, ['tambah_utang', 'hapus_utang']);

// Hanya proses jika ada nilai 'action'
if ($action) {
    // Menggunakan switch untuk memilih blok kode berdasarkan nilai 'action'
    switch ($action) {
        
        // --- AKSI UNTUK TRANSAKSI ---
        case 'tambah_transaksi':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO transaksi (deskripsi, jumlah, jenis, id_kategori) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sdsi", $_POST['deskripsi'], $_POST['jumlah'], $_POST['jenis'], $_POST['id_kategori']);
                mysqli_stmt_execute($stmt);
            }
            break;

        case 'update_transaksi':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET deskripsi = ?, jumlah = ?, jenis = ?, id_kategori = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sdsii", $_POST['deskripsi'], $_POST['jumlah'], $_POST['jenis'], $_POST['id_kategori'], $_POST['id']);
                mysqli_stmt_execute($stmt);
            }
            break;

        case 'hapus_transaksi':
            if (isset($_GET['id'])) {
                $stmt = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $_GET['id']);
                mysqli_stmt_execute($stmt);
            }
            break;

        // --- AKSI UNTUK TABUNGAN ---
        case 'tambah_goal_tabungan':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO tabungan (nama_tujuan, target_jumlah) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "sd", $_POST['nama_tujuan'], $_POST['target_jumlah']);
                mysqli_stmt_execute($stmt);
            }
            break;

        case 'tambah_dana_tabungan':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // 1. Update jumlah terkumpul di tabel tabungan
                $stmt_update = mysqli_prepare($koneksi, "UPDATE tabungan SET jumlah_terkumpul = jumlah_terkumpul + ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt_update, "di", $_POST['jumlah_ditabung'], $_POST['id_tabungan']);
                mysqli_stmt_execute($stmt_update);

                // 2. Catat sebagai transaksi pengeluaran (Asumsi ID kategori 'Tabungan' adalah 8)
                $id_kategori_tabungan = 8; 
                $deskripsi = "Menabung untuk " . $_POST['nama_tujuan'];
                
                $stmt_insert = mysqli_prepare($koneksi, "INSERT INTO transaksi (deskripsi, jumlah, jenis, id_kategori) VALUES (?, ?, 'pengeluaran', ?)");
                mysqli_stmt_bind_param($stmt_insert, "sdi", $deskripsi, $_POST['jumlah_ditabung'], $id_kategori_tabungan);
                mysqli_stmt_execute($stmt_insert);
            }
            break;

        // --- AKSI UNTUK UTANG ---
        case 'tambah_utang':
             if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Saat utang baru dibuat, sisa utang diisi sama dengan total utang
                $stmt = mysqli_prepare($koneksi, "INSERT INTO utang (nama_utang, total_utang, sisa_utang, cicilan_minimum, tanggal_mulai) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sddds", $_POST['nama_utang'], $_POST['total_utang'], $_POST['total_utang'], $_POST['cicilan_minimum'], $_POST['tanggal_mulai']);
                mysqli_stmt_execute($stmt);
            }
            break;
            
        case 'hapus_utang':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = mysqli_prepare($koneksi, "DELETE FROM utang WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
            }
            break;
    }
}

// Redirect ke halaman yang sesuai setelah aksi selesai
if ($is_savings_action) {
    header('Location: tabungan.php');
} elseif ($is_debt_action) {
    header('Location: utang.php');
} else {
    header('Location: index.php');
}
exit();
?>