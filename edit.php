<?php
require_once 'config/koneksi.php';

// Cek apakah ada ID di URL
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = $_GET['id'];

// Ambil data transaksi yang akan diedit
$query_transaksi = "SELECT * FROM transaksi WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query_transaksi);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = mysqli_fetch_assoc($result);

if (!$transaksi) {
    echo "Transaksi tidak ditemukan!";
    exit();
}

// Ambil semua data kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);

// Kode Tampilan (di-copy dari file index.php dan disederhanakan)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      @import url('https://rsms.me/inter/inter.css');
      html { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-stone-100">
    <div class="container mx-auto max-w-xl p-4 sm:p-6 lg:p-8">
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm">
            <h3 class="text-xl font-bold text-stone-800 mb-6">Edit Transaksi</h3>
            <form action="aksi.php?action=update" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?= $transaksi['id'] ?>">

                <div>
                    <label for="deskripsi" class="text-sm font-medium text-stone-600">Deskripsi</label>
                    <input type="text" name="deskripsi" id="deskripsi" value="<?= htmlspecialchars($transaksi['deskripsi']) ?>" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah" class="text-sm font-medium text-stone-600">Jumlah (Rp)</label>
                        <input type="number" name="jumlah" id="jumlah" value="<?= $transaksi['jumlah'] ?>" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                    </div>
                    <div>
                        <label for="jenis" class="text-sm font-medium text-stone-600">Jenis</label>
                        <select name="jenis" id="jenis" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="pemasukan" <?= ($transaksi['jenis'] == 'pemasukan') ? 'selected' : '' ?>>Pemasukan</option>
                            <option value="pengeluaran" <?= ($transaksi['jenis'] == 'pengeluaran') ? 'selected' : '' ?>>Pengeluaran</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="id_kategori" class="text-sm font-medium text-stone-600">Kategori</label>
                    <select name="id_kategori" id="id_kategori" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                        <option value="">Pilih Kategori</option>
                        <?php while($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                            <option value="<?= $kategori['id'] ?>" <?= ($transaksi['id_kategori'] == $kategori['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kategori['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex gap-4 pt-4">
                    <button type="submit" class="w-full bg-amber-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-amber-600 transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="index.php" class="w-full text-center bg-stone-200 text-stone-800 font-semibold py-3 px-4 rounded-lg hover:bg-stone-300 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>