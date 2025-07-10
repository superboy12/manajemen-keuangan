<header class="bg-white/80 backdrop-blur-sm sticky top-0 z-10 border-b border-stone-200">
    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="index.php" class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.75A.75.75 0 013 4.5h.75m0 0h.75A.75.75 0 015.25 6v.75m0 0v.75A.75.75 0 014.5 8.25h-.75m0 0H3.75m0 0A.75.75 0 013 9v.75m0 0v.75a.75.75 0 01-.75.75h-.75m0 0H2.25m0 0a.75.75 0 01-.75-.75V10.5m0 0a.75.75 0 01.75-.75h.75m0 0V9A.75.75 0 013.75 8.25M5.25 9v.75m0 0V10.5a.75.75 0 01-.75.75h-.75M16.5 18.75h-2.25a.75.75 0 01-.75-.75V9.75a.75.75 0 01.75-.75h2.25a.75.75 0 01.75.75v8.25a.75.75 0 01-.75.75zM18 18.75a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.008c0 .414-.336.75-.75.75h-.75a.75.75 0 01-.75-.75v-.008zM21 18.75a.75.75 0 01.75-.75h.75a.75.75 0 01.75.75v.008c0 .414-.336.75-.75.75h-.75a.75.75 0 01-.75-.75v-.008z" /></svg>
                    <span class="font-bold text-lg text-stone-800">FinTrack</span>
                </a>
                <nav class="hidden md:flex gap-4">
                    <a href="index.php" class="text-sm font-semibold <?= ($currentPage == 'dashboard') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' ?>">Dashboard</a>
                    <a href="tabungan.php" class="text-sm font-semibold <?= ($currentPage == 'tabungan') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' ?>">Tabungan</a>
                    <a href="utang.php" class="text-sm font-semibold <?= ($currentPage == 'utang') ? 'text-amber-600' : 'text-stone-500 hover:text-stone-900' ?>">Utang</a>
                </nav>
            </div>
            <a href="index.php#tambah-transaksi" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
                + Transaksi
            </a>
        </div>
    </div>
</header>