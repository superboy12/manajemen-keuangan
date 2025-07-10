<?php
require_once 'config/koneksi.php';

$avg_income_query = "SELECT AVG(total) as rata_pemasukan FROM (SELECT SUM(jumlah) as total FROM transaksi WHERE jenis = 'pemasukan' AND tanggal >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(tanggal), MONTH(tanggal)) as bulanan";
$rata_pemasukan = mysqli_fetch_assoc(mysqli_query($koneksi, $avg_income_query))['rata_pemasukan'] ?? 0;

$avg_expense_query = "SELECT AVG(total) as rata_pengeluaran FROM (SELECT SUM(jumlah) as total FROM transaksi t JOIN kategori k ON t.id_kategori = k.id WHERE t.jenis = 'pengeluaran' AND k.nama_kategori NOT IN ('Tabungan', 'Utang') AND t.tanggal >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(t.tanggal), MONTH(t.tanggal)) as bulanan";
$rata_pengeluaran_rutin = mysqli_fetch_assoc(mysqli_query($koneksi, $avg_expense_query))['rata_pengeluaran'] ?? 0;

$utang_query = "SELECT * FROM utang WHERE sisa_utang > 0";
$result_utang = mysqli_query($koneksi, $utang_query);

$utang_list = [];
$total_sisa_utang = 0;
$total_cicilan_minimum = 0;
while ($row = mysqli_fetch_assoc($result_utang)) {
    $utang_list[] = $row;
    $total_sisa_utang += $row['sisa_utang'];
    $total_cicilan_minimum += $row['cicilan_minimum'];
}

$persen_alokasi = isset($_POST['alokasi']) ? (float)$_POST['alokasi'] : 15;
$dana_alokasi_utang = ($persen_alokasi / 100) * $rata_pemasukan;

if ($dana_alokasi_utang < $total_cicilan_minimum) {
    $dana_alokasi_utang = $total_cicilan_minimum;
}

$sisa_gaji_bersih = $rata_pemasukan - $rata_pengeluaran_rutin - $dana_alokasi_utang;
if ($sisa_gaji_bersih < 0) {
    $sisa_gaji_bersih = 0;
}

$perkiraan_lunas = "N/A";
if ($dana_alokasi_utang > 0 && $total_sisa_utang > 0) {
    $estimasi_bulan = ceil($total_sisa_utang / $dana_alokasi_utang);
    $tanggal_lunas = new DateTime();
    $tanggal_lunas->add(new DateInterval("P{$estimasi_bulan}M"));
    $perkiraan_lunas = $tanggal_lunas->format('F Y');
}

$currentPage = 'utang';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Utang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://rsms.me/inter/inter.css'); html { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-stone-100 text-stone-800">

    <?php require_once 'partials/header.php'; ?>

    <main class="container mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm mb-8">
            <h3 class="text-2xl font-bold text-stone-900 mb-2">Perencanaan Pelunasan Utang</h3>
            <p class="text-sm text-stone-500 mb-6">Gunakan slider untuk melihat bagaimana alokasi pemasukan Anda mempercepat pelunasan utang.</p>
            
            <form method="POST" action="utang.php" class="bg-amber-50 p-6 rounded-lg">
                <label for="alokasi" class="block text-center font-semibold text-stone-700">Saya akan alokasikan <span id="alokasi-value" class="text-amber-600 font-bold text-lg"><?= $persen_alokasi ?>%</span> dari pemasukan untuk utang.</label>
                <input type="range" min="5" max="80" value="<?= $persen_alokasi ?>" name="alokasi" id="alokasi" class="w-full h-2 bg-stone-200 rounded-lg appearance-none cursor-pointer mt-4" oninput="document.getElementById('alokasi-value').textContent = this.value + '%'">
                <button type="submit" class="w-full mt-6 bg-amber-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-amber-600 transition-colors">
                    Lihat Proyeksi
                </button>
            </form>
        </div>
        
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
             <div class="bg-white p-5 rounded-2xl shadow-sm">
                <h4 class="text-sm font-medium text-stone-500">Total Sisa Utang</h4>
                <p class="text-2xl font-bold text-red-600">Rp <?= number_format($total_sisa_utang, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm">
                <h4 class="text-sm font-medium text-stone-500">Cicilan Min./Bulan</h4>
                <p class="text-2xl font-bold text-stone-700">Rp <?= number_format($total_cicilan_minimum, 0, ',', '.') ?></p>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm">
                <h4 class="text-sm font-medium text-stone-500">Dana Alokasi/Bulan</h4>
                <p class="text-2xl font-bold text-amber-600">Rp <?= number_format($dana_alokasi_utang, 0, ',', '.') ?></p>
            </div>
            <div class="bg-lime-100 p-5 rounded-2xl shadow-sm">
                <h4 class="text-sm font-medium text-lime-700">Estimasi Sisa Gaji Bersih</h4>
                <p class="text-2xl font-bold text-lime-800">Rp <?= number_format($sisa_gaji_bersih, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="bg-sky-100 text-sky-900 p-5 rounded-2xl shadow-sm mb-8 text-center">
            <h4 class="font-semibold">Dengan alokasi <span class="font-bold"><?= $persen_alokasi ?>%</span>, perkiraan semua utang Anda akan lunas pada:</h4>
            <p class="text-3xl font-bold mt-2"><?= $perkiraan_lunas ?></p>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <h3 class="text-xl font-bold text-stone-900">Daftar Utang Aktif</h3>
                <?php foreach ($utang_list as $utang): ?>
            <div class="bg-white p-4 rounded-xl shadow-sm">
    <div class="flex items-center justify-between group">
        <div>
            <p class="font-bold text-stone-800"><?= htmlspecialchars($utang['nama_utang']) ?></p>
            <p class="text-sm text-stone-500">Sisa: <span class="font-medium text-red-600">Rp <?= number_format($utang['sisa_utang'], 0, ',', '.') ?></span></p>
        </div>
        <div class="flex items-center gap-2">
            <details class="relative">
                <summary class="cursor-pointer list-none">
                    <div class="bg-amber-500 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors">Bayar</div>
                </summary>
                <form action="aksi.php?action=bayar_utang" method="POST" class="absolute right-0 top-full mt-2 bg-white p-4 rounded-xl shadow-lg w-64 z-10 border">
                    <label for="jumlah_pembayaran" class="text-xs font-medium text-stone-500">Jumlah Pembayaran</label>
                    <input type="hidden" name="id_utang" value="<?= $utang['id'] ?>">
                    <input type="hidden" name="nama_utang" value="<?= htmlspecialchars($utang['nama_utang']) ?>">
                    <input type="number" name="jumlah_pembayaran" class="mt-1 block w-full px-3 py-2 text-sm bg-stone-100 rounded-lg" required>
                    <button type="submit" class="mt-2 w-full bg-stone-800 text-white text-sm font-semibold py-2 rounded-lg hover:bg-stone-900">Konfirmasi</button>
                </form>
            </details>
            <a href="aksi.php?action=hapus_utang&id=<?= $utang['id'] ?>" class="text-stone-400 hover:text-red-500" title="Hapus Utang" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan utang ini?')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </a>
        </div>
    </div>
</div>
                <?php endforeach; 
                if (empty($utang_list)) {
                    echo '<p class="text-center text-stone-500 py-10">Selamat, tidak ada utang yang tercatat!</p>';
                }
                ?>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm h-fit">
                <h3 class="text-lg font-bold text-stone-900 mb-4">Tambah Catatan Utang</h3>
                <form action="aksi.php?action=tambah_utang" method="POST" class="space-y-3">
                    <input type="text" name="nama_utang" placeholder="Nama Utang (e.g., Cicilan Motor)" class="block w-full px-3 py-2 text-sm bg-stone-100 rounded-lg" required>
                    <input type="number" name="total_utang" placeholder="Total Utang" class="block w-full px-3 py-2 text-sm bg-stone-100 rounded-lg" required>
                    <input type="number" name="cicilan_minimum" placeholder="Cicilan Minimum / Bulan" class="block w-full px-3 py-2 text-sm bg-stone-100 rounded-lg" required>
                    <div>
                        <label class="text-xs text-stone-500">Tanggal Mulai Utang</label>
                        <input type="date" name="tanggal_mulai" class="block w-full px-3 py-2 text-sm bg-stone-100 rounded-lg" required>
                    </div>
                    <button type="submit" class="w-full bg-stone-800 text-white font-semibold py-2 rounded-lg hover:bg-stone-900">Tambah Utang</button>
                </form>
            </div>
        </div>

    </main>

</body>
</html>