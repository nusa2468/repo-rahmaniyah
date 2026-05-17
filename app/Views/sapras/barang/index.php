<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// =========================================================================
// KALKULASI STATISTIK UNTUK KPI & GRAFIK (Dari Data Halaman Saat Ini)
// =========================================================================
$jmlBaik = 0; $jmlRingan = 0; $jmlBerat = 0; $jmlAfkir = 0;
$valTotal = 0; $itemTersedia = 0; $itemDipinjam = 0; $itemDiperbaiki = 0; $itemHilang = 0;

if (!empty($barang)) {
    foreach($barang as $b) {
        $valTotal += (float)$b['harga_perolehan'];
        
        // Kalkulasi Kondisi
        if($b['kondisi'] == 'Baik') $jmlBaik++;
        elseif($b['kondisi'] == 'Rusak Ringan') $jmlRingan++;
        elseif($b['kondisi'] == 'Rusak Berat') $jmlBerat++;
        elseif($b['kondisi'] == 'Afkir/Dihapus') $jmlAfkir++;
        
        // Kalkulasi Ketersediaan
        if($b['status_ketersediaan'] == 'Tersedia') $itemTersedia++;
        elseif($b['status_ketersediaan'] == 'Dipinjam') $itemDipinjam++;
        elseif($b['status_ketersediaan'] == 'Diperbaiki') $itemDiperbaiki++;
        elseif($b['status_ketersediaan'] == 'Hilang') $itemHilang++;
    }
}
$totalItem = count($barang);
?>

<div x-data="assetManager()" class="px-4 sm:px-6 py-6 space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-auto">
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 md:mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/sapras/dashboard') ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">SAPRAS</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">KATALOG ASET</li>
                </ol>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                    <?= esc($title) ?>
                </h1>
                
                <!-- BADGE UNIT AKTIF -->
                <?php if($isGlobal && $filterJenjang === 'GLOBAL'): ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                        Unit <?= esc($filterJenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Katalog digital inventaris sekolah lengkap dengan fitur filter dan pelacakan kondisi.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row w-full md:w-auto items-center gap-3 mt-4 md:mt-0">
            <!-- TOMBOL CETAK LAPORAN REKAPITULASI (A4) -->
            <button type="button" onclick="cetakLaporan()" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-[11px] font-black uppercase tracking-widest rounded-xl shadow-sm transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap">
                <i class="fas fa-print text-sm md:text-xs text-indigo-500"></i> <span>Cetak Rekap (A4)</span>
            </button>

            <a href="<?= base_url('app/sapras/barang/new') ?>" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-plus text-sm md:text-xs"></i> <span>Registrasi Aset Baru</span>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- KPI CARDS & CHART SECTION (RESPONSIF) -->
    <!-- ========================================== -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 relative z-10">
        
        <!-- KOLOM KIRI: KPI CARDS -->
        <div class="xl:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Card 1: Valuasi -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-5 md:p-6 text-white shadow-lg relative overflow-hidden group">
                <i class="fas fa-money-bill-wave absolute -right-4 -bottom-4 text-7xl opacity-20 group-hover:scale-110 transition-transform"></i>
                <div class="relative z-10">
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-widest opacity-80 mb-1 border-b border-white/20 pb-1 inline-block">Valuasi (Data Tampil)</p>
                    <h3 class="text-2xl md:text-3xl font-black italic tracking-tighter mt-1">Rp <?= number_format($valTotal, 0, ',', '.') ?></h3>
                    <p class="text-xs font-bold mt-2 opacity-90"><i class="fas fa-boxes mr-1"></i> Dari <?= $totalItem ?> Item Terfilter</p>
                </div>
            </div>

            <!-- Card 2: Tersedia -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 md:p-6 shadow-sm relative overflow-hidden group">
                <i class="fas fa-check-circle absolute -right-4 -bottom-4 text-7xl text-blue-500 opacity-5 group-hover:scale-110 transition-transform"></i>
                <div class="relative z-10">
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Siap Digunakan</p>
                    <h3 class="text-2xl md:text-3xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= $itemTersedia ?> <span class="text-sm">Item</span></h3>
                    <p class="text-xs font-bold mt-2 text-blue-500"><i class="fas fa-info-circle mr-1"></i> Status: Tersedia</p>
                </div>
            </div>

            <!-- Card 3: Sedang Dipinjam -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 md:p-6 shadow-sm relative overflow-hidden group">
                <i class="fas fa-handshake absolute -right-4 -bottom-4 text-7xl text-amber-500 opacity-5 group-hover:scale-110 transition-transform"></i>
                <div class="relative z-10">
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Sedang Keluar</p>
                    <h3 class="text-2xl md:text-3xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= $itemDipinjam ?> <span class="text-sm">Item</span></h3>
                    <p class="text-xs font-bold mt-2 text-amber-500"><i class="fas fa-people-arrows mr-1"></i> Status: Dipinjam</p>
                </div>
            </div>

            <!-- Card 4: Sedang Diservis -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 md:p-6 shadow-sm relative overflow-hidden group">
                <i class="fas fa-tools absolute -right-4 -bottom-4 text-7xl text-purple-500 opacity-5 group-hover:scale-110 transition-transform"></i>
                <div class="relative z-10">
                    <p class="text-[10px] md:text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Proses Maintenance</p>
                    <h3 class="text-2xl md:text-3xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= $itemDiperbaiki ?> <span class="text-sm">Item</span></h3>
                    <p class="text-xs font-bold mt-2 text-purple-500"><i class="fas fa-wrench mr-1"></i> Status: Diperbaiki</p>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: CHART KONDISI -->
        <div class="xl:col-span-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-black text-slate-700 dark:text-white uppercase tracking-widest italic flex items-center gap-2 mb-4">
                    <i class="fas fa-chart-pie text-indigo-500"></i> Distribusi Kondisi Fisik
                </h3>
            </div>
            
            <div class="relative h-[160px] md:h-[140px] w-full flex items-center justify-center">
                <?php if($totalItem > 0): ?>
                    <canvas id="katalogChart"></canvas>
                <?php else: ?>
                    <div class="text-center text-slate-400 text-xs italic"><i class="fas fa-chart-pie text-3xl opacity-20 mb-2 block"></i>Tidak ada data untuk grafik</div>
                <?php endif; ?>
            </div>

            <?php if($totalItem > 0): ?>
            <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-2 gap-3 md:gap-2 mt-4 text-[10px] md:text-[9px] font-bold uppercase tracking-wider">
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 md:w-2 md:h-2 rounded-full bg-emerald-500 shrink-0"></span> Baik: <?= $jmlBaik ?></div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 md:w-2 md:h-2 rounded-full bg-amber-500 shrink-0"></span> Ringan: <?= $jmlRingan ?></div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 md:w-2 md:h-2 rounded-full bg-rose-500 shrink-0"></span> Berat: <?= $jmlBerat ?></div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 md:w-2 md:h-2 rounded-full bg-slate-400 shrink-0"></span> Afkir: <?= $jmlAfkir ?></div>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- FILTER BAR (ADVANCED SEARCH - RESPONSIF SMART GRID) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm relative z-20">
        <form action="" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            
            <?php if ($isGlobal): ?>
            <!-- Filter Unit -->
            <div class="relative lg:col-span-1">
                <select name="jenjang" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200 uppercase">
                    <option value="GLOBAL" <?= ($filterJenjang === 'GLOBAL') ? 'selected' : '' ?>>SEMUA UNIT</option>
                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= ($filterJenjang == $kode) ? 'selected' : '' ?>>UNIT <?= strtoupper($kode) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>
            <?php endif; ?>

            <!-- Filter Kategori -->
            <div class="relative <?= $isGlobal ? 'lg:col-span-1' : 'lg:col-span-1' ?>">
                <select name="kategori" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($filterKategori == $k['id']) ? 'selected' : '' ?>><?= esc($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <!-- Filter Lokasi -->
            <div class="relative <?= $isGlobal ? 'lg:col-span-1' : 'lg:col-span-2' ?>">
                <select name="lokasi" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200">
                    <option value="">Semua Lokasi</option>
                    <?php foreach ($lokasiList as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ($filterLokasi == $l['id']) ? 'selected' : '' ?>><?= esc($l['nama_lokasi']) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <!-- Filter Kondisi -->
            <div class="relative <?= $isGlobal ? 'lg:col-span-1' : 'lg:col-span-1' ?>">
                <select name="kondisi" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" <?= ($filterKondisi == 'Baik') ? 'selected' : '' ?>>Kondisi: Baik</option>
                    <option value="Rusak Ringan" <?= ($filterKondisi == 'Rusak Ringan') ? 'selected' : '' ?>>Kondisi: Rusak Ringan</option>
                    <option value="Rusak Berat" <?= ($filterKondisi == 'Rusak Berat') ? 'selected' : '' ?>>Kondisi: Rusak Berat</option>
                    <option value="Afkir/Dihapus" <?= ($filterKondisi == 'Afkir/Dihapus') ? 'selected' : '' ?>>Kondisi: Afkir / Dihapus</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <!-- Search Bar -->
            <div class="relative lg:col-span-1 sm:col-span-2">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari Kode Aset / Nama..." 
                       class="w-full pl-10 pr-4 py-3 md:py-2.5 text-xs font-bold bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-indigo-500 outline-none transition-all text-slate-700 dark:text-slate-200">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <!-- Button Filter -->
            <button type="submit" class="w-full lg:col-span-1 sm:col-span-2 px-5 py-3 md:py-2.5 bg-slate-800 dark:bg-slate-700 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 dark:hover:bg-slate-600 transition-all shadow-sm">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- ALERT HANDLERS -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- MAIN TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col relative z-0">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-10 text-center">No</th>
                        <th class="px-6 py-4">Informasi Aset</th>
                        <th class="px-6 py-4">Kategori & Lokasi</th>
                        <th class="px-6 py-4 text-center">Status Kepemilikan</th>
                        <th class="px-6 py-4 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-center">Ketersediaan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php 
                    $page = isset($_GET['page_aset']) ? (int)$_GET['page_aset'] : 1;
                    $startNo = ($page - 1) * 15 + 1; // Limit 15 per controller
                    if(empty($barang)): ?>
                         <tr>
                             <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50/30 dark:bg-gray-800/20">
                                 <i class="fas fa-boxes text-3xl mb-3 opacity-50 block"></i>
                                 Belum ada data barang / aset.
                             </td>
                         </tr>
                    <?php else:
                        foreach ($barang as $row): 
                            
                            // Styling Kondisi
                            $kondisiClass = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                            if ($row['kondisi'] == 'Rusak Ringan') $kondisiClass = 'text-amber-600 bg-amber-50 border-amber-200';
                            if ($row['kondisi'] == 'Rusak Berat') $kondisiClass = 'text-rose-600 bg-rose-50 border-rose-200';
                            if ($row['kondisi'] == 'Afkir/Dihapus') $kondisiClass = 'text-slate-500 bg-slate-100 border-slate-200';

                            // Styling Ketersediaan
                            $tersediaClass = 'text-blue-600 bg-blue-50 border-blue-200';
                            if ($row['status_ketersediaan'] == 'Dipinjam') $tersediaClass = 'text-amber-600 bg-amber-50 border-amber-200';
                            if ($row['status_ketersediaan'] == 'Diperbaiki') $tersediaClass = 'text-purple-600 bg-purple-50 border-purple-200';
                            if ($row['status_ketersediaan'] == 'Hilang') $tersediaClass = 'text-red-600 bg-red-50 border-red-200';
                    ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                            
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 dark:text-white mb-0.5 truncate max-w-xs" title="<?= esc($row['nama_aset']) ?>">
                                    <?= esc($row['nama_aset']) ?>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded tracking-widest border border-indigo-100 dark:border-indigo-800">
                                        <i class="fas fa-barcode mr-1"></i><?= esc($row['kode_aset']) ?>
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase"><?= esc($row['kode_jenjang']) ?></span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-0.5">
                                    <i class="fas fa-tag text-slate-400 w-4"></i> <?= esc($row['nama_kategori'] ?? 'Tanpa Kategori') ?>
                                </div>
                                <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                    <i class="fas fa-map-marker-alt text-slate-400 w-4"></i> <?= esc($row['nama_lokasi'] ?? 'Gudang / Tidak Diketahui') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 border-b border-dashed border-slate-400">
                                    <?= esc($row['status_kepemilikan']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border <?= $kondisiClass ?> dark:bg-opacity-10 dark:border-opacity-30">
                                    <?= esc($row['kondisi']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border <?= $tersediaClass ?> dark:bg-opacity-10 dark:border-opacity-30">
                                    <?= esc($row['status_ketersediaan']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <?php $canEdit = $isGlobal || strtoupper($row['kode_jenjang']) === $sessionJenjang; ?>
                                
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <!-- TOMBOL AKSI: QUICK VIEW (Membuka Modal AlpineJS) -->
                                    <button @click="openDetail(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-blue-500 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg shadow-sm transition-all" 
                                            title="Detail Aset (Quick View)">
                                        <i class="fas fa-eye text-sm md:text-xs"></i>
                                    </button>

                                    <!-- TOMBOL AKSI: EDIT / HAPUS -->
                                    <?php if($canEdit): ?>
                                        <a href="<?= base_url('app/sapras/barang/edit/' . $row['id']) ?>" 
                                           class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-amber-500 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg shadow-sm transition-all" 
                                            title="Edit Aset">
                                            <i class="fas fa-pen text-sm md:text-xs"></i>
                                        </a>
                                        <button @click="confirmDelete(<?= $row['id'] ?>, '<?= esc(addslashes($row['nama_aset'])) ?>')" 
                                                class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-rose-500 hover:border-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg shadow-sm transition-all" 
                                                title="Hapus Aset">
                                            <i class="fas fa-trash-alt text-sm md:text-xs"></i>
                                        </button>
                                    <?php else: ?>
                                        <i class="fas fa-lock text-slate-300 dark:text-slate-600 w-10 md:w-8 text-center" title="Terkunci (Milik Unit Lain)"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 flex justify-center">
            <?= isset($pager) ? $pager->links('aset', 'tailwind_pagination') : '' ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: QUICK VIEW DETAIL ASET (RESPONSIF) -->
    <!-- ========================================== -->
    <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end md:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="detailModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl md:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-5 md:p-6 flex justify-between items-start relative overflow-hidden">
                    <i class="fas fa-box-open absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12"></i>
                    <div class="relative z-10 text-white w-full pr-10">
                        <div class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1" x-text="activeAsset.nama_kategori"></div>
                        <h3 class="text-xl md:text-2xl font-black tracking-tight leading-tight mb-2" x-text="activeAsset.nama_aset"></h3>
                        <div class="inline-flex items-center gap-2 font-mono text-xs bg-black/30 px-2.5 py-1 rounded-md border border-white/20">
                            <i class="fas fa-barcode text-indigo-300"></i>
                            <span x-text="activeAsset.kode_aset"></span>
                        </div>
                    </div>
                    <button @click="detailModalOpen = false" class="absolute top-4 right-4 z-10 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 w-8 h-8 rounded-full transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-5 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        
                        <!-- Kolom Info Spesifikasi -->
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Lokasi Aset</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-map-marker-alt text-indigo-500 w-4"></i> <span x-text="activeAsset.nama_lokasi || 'Tidak ada spesifik lokasi'"></span>
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Merek & Spesifikasi</h4>
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300 whitespace-pre-line mt-1" x-text="activeAsset.merk_spesifikasi || '-'"></p>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Penanggung Jawab</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-user-tie text-indigo-500 w-4"></i> <span x-text="activeAsset.nama_penanggung_jawab || 'Belum Ditugaskan'"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Kolom Info Finansial & Kondisi -->
                        <div class="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Kepemilikan</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="activeAsset.status_kepemilikan"></p>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kondisi Saat Ini</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] md:text-xs font-black uppercase tracking-widest bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white" x-text="activeAsset.kondisi"></span>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ketersediaan</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] md:text-xs font-black uppercase tracking-widest bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400" x-text="activeAsset.status_ketersediaan"></span>
                            </div>
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Perolehan</h4>
                                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400 italic">Rp <span x-text="formatRupiah(activeAsset.harga_perolehan)"></span></p>
                                <p class="text-[10px] text-slate-500 mt-1">Sumber: <span x-text="activeAsset.sumber_dana || '-'"></span> (<span x-text="activeAsset.tanggal_perolehan || '-'"></span>)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-slate-50 dark:bg-slate-900 px-5 md:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-100 dark:border-slate-800">
                    <!-- LINK MENUJU HALAMAN CETAK STIKER LABEL BARCODE -->
                    <a :href="'<?= base_url('app/sapras/barang/print-label/') ?>/' + activeAsset.id" target="_blank" class="w-full sm:w-auto justify-center text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest hover:underline flex items-center gap-2 py-2 sm:py-0">
                        <i class="fas fa-barcode"></i> Cetak Label Stiker
                    </a>
                    
                    <button type="button" @click="detailModalOpen = false" class="w-full sm:w-auto px-6 py-3 sm:py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: KONFIRMASI HAPUS (RESPONSIF) -->
    <!-- ========================================== -->
    <div x-show="deleteModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="deleteModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-8 border-rose-600">
                <div class="bg-white dark:bg-slate-800 px-6 pt-6 pb-4 sm:p-8">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-trash-alt text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-4 text-center sm:text-left">
                            <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Aset Ini?</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                    Anda akan menghapus data aset: <strong x-text="activeAsset.nama_aset" class="text-slate-800 dark:text-slate-200 underline"></strong>.<br>
                                    Aset akan masuk ke mode Soft Delete (Arsip).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-3">
                    <a :href="'<?= base_url('app/sapras/barang/delete/') ?>/' + activeAsset.id" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 sm:py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
                        Ya, Hapus Aset
                    </a>
                    <button type="button" @click="deleteModalOpen = false" class="w-full inline-flex justify-center rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-4 py-3 sm:py-2 bg-white dark:bg-slate-800 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:w-auto transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT ALPINE JS MANAGER -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('assetManager', () => ({
            detailModalOpen: false,
            deleteModalOpen: false,
            activeAsset: {},

            openDetail(assetData) {
                this.activeAsset = assetData;
                this.detailModalOpen = true;
            },
            
            confirmDelete(id, nama) {
                this.activeAsset = { id: id, nama_aset: nama };
                this.deleteModalOpen = true;
            },

            formatRupiah(angka) {
                if (!angka) return '0';
                let number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            }
        }))
    });

    // SCRIPT CHART JS
    document.addEventListener("DOMContentLoaded", function() {
        const ctxKatalog = document.getElementById('katalogChart');
        if (ctxKatalog) {
            new Chart(ctxKatalog.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Afkir'],
                    datasets: [{
                        data: [
                            <?= $jmlBaik ?>, 
                            <?= $jmlRingan ?>, 
                            <?= $jmlBerat ?>, 
                            <?= $jmlAfkir ?>
                        ],
                        backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { 
                        legend: { display: false } 
                    }
                }
            });
        }
    });

    // SCRIPT UNTUK CETAK LAPORAN MEMBAWA DATA FILTER YANG SEDANG AKTIF
    function cetakLaporan() {
        const form = document.querySelector('form'); // Ambil form filter bar
        if (form) {
            const url = new URL('<?= base_url('app/sapras/barang/print-report') ?>');
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (value && key !== 'search') { 
                    url.searchParams.append(key, value);
                }
            }
            window.open(url.toString(), '_blank');
        } else {
            window.open('<?= base_url('app/sapras/barang/print-report') ?>', '_blank');
        }
    }
</script>

<?= $this->endSection() ?>