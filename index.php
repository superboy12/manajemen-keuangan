<?php
require_once 'config/koneksi.php';

// --- Ambil semua data yang dibutuhkan ---

// Data Kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);

// Data Ringkasan (saldo, dll)
$query_pemasukan = "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis = 'pemasukan'";
$total_pemasukan = mysqli_fetch_assoc(mysqli_query($koneksi, $query_pemasukan))['total'] ?? 0;
$query_pengeluaran = "SELECT SUM(jumlah) as total FROM transaksi WHERE jenis = 'pengeluaran'";
$total_pengeluaran = mysqli_fetch_assoc(mysqli_query($koneksi, $query_pengeluaran))['total'] ?? 0;
$saldo = $total_pemasukan - $total_pengeluaran;

// Data Transaksi Terakhir
$query_transaksi = "SELECT t.*, k.nama_kategori 
                    FROM transaksi t 
                    LEFT JOIN kategori k ON t.id_kategori = k.id 
                    ORDER BY t.tanggal DESC LIMIT 5";
$result_transaksi = mysqli_query($koneksi, $query_transaksi);

// Data untuk Grafik Alokasi Pengeluaran
$query_chart = "SELECT k.nama_kategori, SUM(t.jumlah) as total_pengeluaran
                FROM transaksi t
                JOIN kategori k ON t.id_kategori = k.id
                WHERE t.jenis = 'pengeluaran'
                GROUP BY t.id_kategori
                ORDER BY total_pengeluaran DESC
                LIMIT 5";
$result_chart = mysqli_query($koneksi, $query_chart);

$chart_labels = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($result_chart)) {
    $chart_labels[] = $row['nama_kategori'];
    $chart_data[] = $row['total_pengeluaran'];
}
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);


// Fungsi untuk Saran & Peringatan Keuangan
function getFinancialInsight($pemasukan, $pengeluaran) {
    if ($pemasukan <= 0) {
        return ['status' => 'netral', 'pesan' => 'Selamat datang! Mulai catat pemasukan dan pengeluaran Anda.'];
    }
    $rasio = $pengeluaran / $pemasukan;
    if ($rasio > 0.9) {
        return ['status' => 'peringatan', 'pesan' => 'Peringatan! Pengeluaran Anda sudah lebih dari 90% pemasukan. Hati-hati!'];
    } elseif ($rasio > 0.6) {
        return ['status' => 'netral', 'pesan' => 'Pengeluaran Anda wajar, tetap kontrol agar tidak berlebihan.'];
    } else {
        return ['status' => 'baik', 'pesan' => 'Kerja bagus! Pengeluaran Anda terkendali dengan baik di bawah 60% pemasukan.'];
    }
}
$insight = getFinancialInsight($total_pemasukan, $total_pengeluaran);

// Set halaman saat ini untuk menandai link header yang aktif
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Keuangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>@import url('https://rsms.me/inter/inter.css'); html { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-stone-100 text-stone-800">

    <?php require_once 'partials/header.php'; ?>

    <main class="container mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">

        <div class="mb-8 p-4 rounded-lg flex items-center gap-4
            <?php if($insight['status'] == 'baik') echo 'bg-lime-100 text-lime-800'; ?>
            <?php if($insight['status'] == 'netral') echo 'bg-sky-100 text-sky-800'; ?>
            <?php if($insight['status'] == 'peringatan') echo 'bg-red-100 text-red-800'; ?>
        ">
            <span class="text-xl">
                <?php if($insight['status'] == 'baik') echo '👍'; ?>
                <?php if($insight['status'] == 'netral') echo '💡'; ?>
                <?php if($insight['status'] == 'peringatan') echo '⚠️'; ?>
            </span>
            <p class="font-medium text-sm"><?= $insight['pesan'] ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm">
                    <h3 class="text-xl font-bold text-stone-900 mb-6">Riwayat Transaksi Terakhir</h3>
                    <div class="space-y-2">
                        <?php while ($row = mysqli_fetch_assoc($result_transaksi)): ?>
                        <div class="group flex items-center justify-between p-3 rounded-lg hover:bg-stone-50">
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="font-semibold text-stone-800"><?= htmlspecialchars($row['deskripsi']); ?></p>
                                    <p class="text-sm text-stone-500"><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?> · <?= date('d M Y', strtotime($row['tanggal'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <p class="font-bold text-right <?= $row['jenis'] == 'pemasukan' ? 'text-lime-600' : 'text-red-600'; ?>">
                                    <?= ($row['jenis'] == 'pemasukan' ? '+' : '-') ?>Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?>
                                </p>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="text-stone-500 hover:text-amber-500" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" /></svg></a>
                                    <a href="aksi.php?action=hapus_transaksi&id=<?= $row['id'] ?>" class="text-stone-500 hover:text-red-500" title="Hapus" onclick="return confirm('Apakah Anda yakin?')"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div id="tambah-transaksi" class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm">
                    <h3 class="text-xl font-bold text-stone-900 mb-6">Catat Transaksi Baru</h3>
                    <form action="aksi.php?action=tambah_transaksi" method="POST" class="space-y-4">
                        <div>
                            <label for="deskripsi" class="text-sm font-medium text-stone-600">Deskripsi</label>
                            <input type="text" name="deskripsi" id="deskripsi" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jumlah" class="text-sm font-medium text-stone-600">Jumlah (Rp)</label>
                                <input type="number" name="jumlah" id="jumlah" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                            </div>
                            <div>
                                <label for="jenis" class="text-sm font-medium text-stone-600">Jenis</label>
                                <select name="jenis" id="jenis" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="pemasukan">Pemasukan</option>
                                    <option value="pengeluaran">Pengeluaran</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="id_kategori" class="text-sm font-medium text-stone-600">Kategori</label>
                            <select name="id_kategori" id="id_kategori" class="mt-1 block w-full px-4 py-2 bg-stone-100 border border-transparent rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                                <option value="">Pilih Kategori</option>
                                <?php mysqli_data_seek($result_kategori, 0); while($kategori = mysqli_fetch_assoc($result_kategori)): ?>
                                    <option value="<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama_kategori']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-amber-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-amber-600 transition-colors">Simpan</button>
                    </form>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm">
                    <h3 class="text-xl font-bold text-stone-900 mb-6">Alokasi Pengeluaran</h3>
                     <div class="h-80">
                        <canvas id="pengeluaranChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm">
                    <h3 class="text-xl font-bold text-stone-900 mb-6">Ringkasan Saldo</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center"><span class="text-stone-500">Pemasukan</span><span class="font-semibold text-lime-600">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></span></div>
                        <div class="flex justify-between items-center"><span class="text-stone-500">Pengeluaran</span><span class="font-semibold text-red-600">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></span></div>
                        <div class="border-t border-stone-200 my-2"></div>
                        <div class="flex justify-between items-center"><span class="font-bold text-stone-800">Saldo Akhir</span><span class="font-bold text-xl text-amber-500">Rp <?= number_format($saldo, 0, ',', '.'); ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('pengeluaranChart').getContext('2d');
        const labels = <?= $chart_labels_json; ?>;
        const data = <?= $chart_data_json; ?>;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Pengeluaran',
                    data: data,
                    backgroundColor: ['rgba(245, 158, 11, 0.8)','rgba(251, 191, 36, 0.8)','rgba(252, 211, 77, 0.8)','rgba(253, 230, 138, 0.8)','rgba(254, 243, 199, 0.8)'],
                    borderColor: ['rgba(245, 158, 11, 1)','rgba(251, 191, 36, 1)','rgba(252, 211, 77, 1)','rgba(253, 230, 138, 1)','rgba(254, 243, 199, 1)'],
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { callback: function(value) { return 'Rp ' + value / 1000 + 'k'; } }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>