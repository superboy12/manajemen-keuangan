<?php
require_once 'config/koneksi.php';

// Data Target Tabungan
$query_tabungan = "SELECT * FROM tabungan ORDER BY tanggal_dibuat DESC";
$result_tabungan = mysqli_query($koneksi, $query_tabungan);

// Persiapan data untuk Chart.js
$chart_labels = [];
$chart_data_terkumpul = [];
$chart_data_target = [];

// Loop untuk mengambil data dari hasil query
mysqli_data_seek($result_tabungan, 0); // Pastikan pointer kembali ke awal
while ($row = mysqli_fetch_assoc($result_tabungan)) {
    $chart_labels[] = $row['nama_tujuan'];
    $chart_data_terkumpul[] = $row['jumlah_terkumpul'];
    $chart_data_target[] = $row['target_jumlah'];
}

$chart_labels_json = json_encode($chart_labels);
$chart_data_terkumpul_json = json_encode($chart_data_terkumpul);
$chart_data_target_json = json_encode($chart_data_target);


// Set halaman saat ini untuk menandai link header yang aktif
$currentPage = 'tabungan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Tabungan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>@import url('https://rsms.me/inter/inter.css'); html { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-stone-100 text-stone-800">

    <?php require_once 'partials/header.php'; ?>

    <main class="container mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
        <h1 class="text-3xl font-bold text-stone-900 mb-8">Progres Tabungan Saya</h1>

        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm mb-8">
            <h3 class="text-xl font-bold text-stone-900 mb-6">Visualisasi Target</h3>
             <div class="h-80">
                <canvas id="tabunganChart"></canvas>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php mysqli_data_seek($result_tabungan, 0); // Reset pointer lagi untuk loop kedua
            while ($tabungan = mysqli_fetch_assoc($result_tabungan)): 
                $persentase = ($tabungan['target_jumlah'] > 0) ? ($tabungan['jumlah_terkumpul'] / $tabungan['target_jumlah']) * 100 : 0;
            ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="font-semibold text-stone-700"><?= htmlspecialchars($tabungan['nama_tujuan']) ?></span>
                        <span class="text-sm font-medium text-amber-600"><?= number_format($persentase, 1) ?>%</span>
                    </div>
                    <div class="w-full bg-stone-200 rounded-full h-2.5">
                        <div class="bg-amber-500 h-2.5 rounded-full" style="width: <?= $persentase ?>%"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-stone-500">Terkumpul: Rp <?= number_format($tabungan['jumlah_terkumpul'], 0, ',', '.') ?></span>
                        <span class="text-xs text-stone-500">Target: Rp <?= number_format($tabungan['target_jumlah'], 0, ',', '.') ?></span>
                    </div>
                </div>
                <form action="aksi.php?action=tambah_dana_tabungan" method="POST" class="mt-3 flex gap-2">
                    <input type="hidden" name="id_tabungan" value="<?= $tabungan['id'] ?>">
                    <input type="hidden" name="nama_tujuan" value="<?= htmlspecialchars($tabungan['nama_tujuan']) ?>">
                    <input type="number" name="jumlah_ditabung" placeholder="Jumlah" class="block w-full px-3 py-1.5 text-sm bg-stone-100 border-transparent rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                    <button type="submit" class="bg-stone-800 text-white font-semibold text-sm px-4 py-1.5 rounded-lg hover:bg-stone-900" title="Tambah Dana">+</button>
                </form>
            </div>
            <?php endwhile; ?>

            <div id="tambah-goal" class="bg-amber-100/50 border-2 border-dashed border-amber-400 p-6 rounded-2xl flex flex-col items-center justify-center text-center">
                 <h3 class="text-lg font-bold text-amber-800 mb-4">Punya Tujuan Baru?</h3>
                 <form action="aksi.php?action=tambah_goal_tabungan" method="POST" class="w-full space-y-3">
                    <input type="text" name="nama_tujuan" placeholder="Nama Tujuan" class="block w-full px-3 py-2 text-sm bg-white border-stone-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                    <input type="number" name="target_jumlah" placeholder="Target Jumlah" class="block w-full px-3 py-2 text-sm bg-white border-stone-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500" required>
                    <button type="submit" class="w-full bg-amber-500 text-white font-semibold py-2 rounded-lg hover:bg-amber-600">Buat Target</button>
                </form>
            </div>
        </div>
    </main>
    <script>
        const ctx = document.getElementById('tabunganChart').getContext('2d');
        const savingsLabels = <?= $chart_labels_json; ?>;
        const savingsCollected = <?= $chart_data_terkumpul_json; ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: savingsLabels,
                datasets: [{
                    label: 'Dana Terkumpul',
                    data: savingsCollected,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    borderColor: 'rgba(245, 158, 11, 1)',
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
                                let value = context.parsed.y || 0;
                                return ' Terkumpul: ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>