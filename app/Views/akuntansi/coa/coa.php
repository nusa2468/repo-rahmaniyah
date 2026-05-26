<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// Inisialisasi variabel keamanan
$userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
$isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT', 'ALL']);

$request = \Config\Services::request();
$activeTabFromUrl = $request->getGet('tab') ?? 'dashboard';

// Karena Akuntansi adalah sentral Yayasan, kita kunci filter ke GLOBAL
$filterJenjang = 'GLOBAL';

$url = current_url();
$isBagan = strpos($url, 'akuntansi') !== false && !strpos($url, 'jurnal') && !strpos($url, 'buku-besar') && !strpos($url, 'laporan');
$isJurnal = strpos($url, 'jurnal') !== false;
$isBukuBesar = strpos($url, 'buku-besar') !== false;
$isLaporan = strpos($url, 'laporan') !== false;

// =========================================================================
// LOGIKA DASHBOARD KPI (KONSOLIDASI YAYASAN)
// =========================================================================
$db = \Config\Database::connect();
$tahunIni = date('Y-01-01');

// 1. Saldo Kas & Bank (Kode Akun 1101) - Konsolidasi
$kasAwal = $db->table('akuntansi_coa')
    ->where('kode_akun', '1101')
    ->selectSum('saldo_awal')->get()->getRow()->saldo_awal ?? 0;
    
$mutasiKas = $db->table('akuntansi_jurnal_detail jd')
    ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
    ->join('akuntansi_coa c', 'c.id = jd.id_coa')
    ->where('c.kode_akun', '1101')
    ->where('j.status', 'Posted')
    ->selectSum('jd.debit', 'd')->selectSum('jd.kredit', 'k')->get()->getRow();
    
$totalKas = $kasAwal + ($mutasiKas->d ?? 0) - ($mutasiKas->k ?? 0);

// 2. Pendapatan (YTD) - Konsolidasi
$mutasiPendapatan = $db->table('akuntansi_jurnal_detail jd')
    ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
    ->join('akuntansi_coa c', 'c.id = jd.id_coa')
    ->join('akuntansi_kategori k', 'k.id = c.id_kategori')
    ->where('k.kode_kategori', '4')
    ->where('j.tanggal >=', $tahunIni)->where('j.status', 'Posted')
    ->selectSum('jd.kredit', 'k')->selectSum('jd.debit', 'd')->get()->getRow();
    
$totalPendapatan = ($mutasiPendapatan->k ?? 0) - ($mutasiPendapatan->d ?? 0);

// 3. Beban (YTD) - Konsolidasi
$mutasiBeban = $db->table('akuntansi_jurnal_detail jd')
    ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
    ->join('akuntansi_coa c', 'c.id = jd.id_coa')
    ->join('akuntansi_kategori k', 'k.id = c.id_kategori')
    ->where('k.kode_kategori', '5')
    ->where('j.tanggal >=', $tahunIni)->where('j.status', 'Posted')
    ->selectSum('jd.debit', 'd')->selectSum('jd.kredit', 'k')->get()->getRow();
    
$totalBeban = ($mutasiBeban->d ?? 0) - ($mutasiBeban->k ?? 0);

$surplus = $totalPendapatan - $totalBeban;
?>

<!-- KONTROL TAB ALPINE JS UTAMA -->
<div x-data="{ activeTab: '<?= esc($activeTabFromUrl) ?>', deleteModalOpen: false, deleteActionUrl: '', deleteItemName: '' }" class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-8">

    <!-- TEASER / WELCOME BANNER -->
    <div class="relative bg-slate-900 rounded-[2.5rem] p-8 md:p-12 shadow-2xl border-2 border-amber-500/20 overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-orange-600/10 rounded-full blur-3xl -ml-10 -mb-10"></div>
        <i class="fas fa-landmark absolute -right-10 -bottom-10 text-9xl text-amber-500/5 transform -rotate-12"></i>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-md text-[10px] font-black uppercase tracking-widest mb-4 shadow-lg shadow-amber-500/20 border border-amber-400/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> ENTERPRISE EDITION
                </div>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-3">
                    Akuntansi <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">Yayasan Terpadu</span>
                </h1>
                <p class="text-sm md:text-base text-slate-400 font-medium max-w-2xl leading-relaxed">
                    Pusat komando tata kelola keuangan (*Holding*). Semua akun terpusat menggunakan <strong>Tunggal COA Yayasan</strong> yang patuh pada <strong>ISAK 35</strong>.
                </p>
                
                <!-- TOMBOL NAVIGASI TAB -->
                <div class="mt-6 flex flex-wrap gap-3 justify-center md:justify-start">
                    <button @click="activeTab = 'dashboard'" :class="activeTab == 'dashboard' ? 'bg-amber-500 text-white border-amber-400 shadow-lg shadow-amber-500/30' : 'bg-white/10 text-slate-300 border-white/20 hover:bg-white/20'" class="px-5 py-2.5 rounded-xl border text-xs font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                        <i class="fas fa-chart-pie"></i> Konsolidasi KPI
                    </button>
                    <button @click="activeTab = 'coa'" :class="activeTab == 'coa' ? 'bg-amber-500 text-white border-amber-400 shadow-lg shadow-amber-500/30' : 'bg-white/10 text-slate-300 border-white/20 hover:bg-white/20'" class="px-5 py-2.5 rounded-xl border text-xs font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                        <i class="fas fa-sitemap"></i> Master COA Yayasan
                    </button>
                </div>
            </div>
            
            <!-- Statistik Singkat Mini -->
            <div class="flex shrink-0 gap-4">
                <div class="bg-black/40 backdrop-blur-md border border-white/10 p-5 rounded-2xl text-center w-32 shadow-xl">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Total Akun</p>
                    <h3 class="text-3xl font-black text-white"><?= number_format($stats['total'] ?? 0) ?></h3>
                </div>
                <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/20 backdrop-blur-md border border-amber-500/30 p-5 rounded-2xl text-center w-32 shadow-xl">
                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-400 mb-2">Jurnal Post</p>
                    <h3 class="text-3xl font-black text-amber-500"><?= number_format($stats['transaksi'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- KOMPONEN NAVBAR AKUNTANSI -->
    <?= $this->include('akuntansi/components/navbar') ?>

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
                <i class="fas fa-exclamation-triangle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- ========================================================================================= -->
    <!-- AREA TAB 1: DASHBOARD KPI KONSOLIDASI YAYASAN -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'dashboard'" x-transition.opacity.duration.300ms>
        <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4 mt-2">
            <i class="fas fa-tachometer-alt text-amber-500 mr-2"></i> Konsolidasi Ringkasan Eksekutif <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded ml-2">YTD <?= date('Y') ?></span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Kas & Setara Kas -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-slate-200 dark:border-slate-800 flex items-start gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kas & Setara Kas</p>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white truncate" title="Rp <?= number_format($totalKas, 0, ',', '.') ?>">
                        Rp <?= number_format($totalKas, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>

            <!-- Pendapatan (YTD) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-slate-200 dark:border-slate-800 flex items-start gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-14 h-14 rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pendapatan (YTD)</p>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white truncate" title="Rp <?= number_format($totalPendapatan, 0, ',', '.') ?>">
                        Rp <?= number_format($totalPendapatan, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>

            <!-- Beban (YTD) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-slate-200 dark:border-slate-800 flex items-start gap-4 hover:-translate-y-1 transition-transform">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Beban (YTD)</p>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white truncate" title="Rp <?= number_format($totalBeban, 0, ',', '.') ?>">
                        Rp <?= number_format($totalBeban, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>

            <!-- Surplus / Defisit -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl border border-slate-200 dark:border-slate-800 flex items-start gap-4 hover:-translate-y-1 transition-transform relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 opacity-5 text-7xl"><i class="fas fa-chart-line"></i></div>
                <div class="w-14 h-14 rounded-2xl <?= $surplus >= 0 ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600' : 'bg-rose-100 dark:bg-rose-900/40 text-rose-600' ?> flex items-center justify-center text-2xl shadow-inner shrink-0">
                    <i class="fas <?= $surplus >= 0 ? 'fa-chart-line' : 'fa-chart-line-down' ?>"></i>
                </div>
                <div class="relative z-10 overflow-hidden">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Surplus (Defisit)</p>
                    <h3 class="text-xl font-black <?= $surplus >= 0 ? 'text-amber-600 dark:text-amber-500' : 'text-rose-600' ?> truncate" title="Rp <?= number_format(abs($surplus), 0, ',', '.') ?>">
                        <?= $surplus < 0 ? '(' : '' ?>Rp <?= number_format(abs($surplus), 0, ',', '.') ?><?= $surplus < 0 ? ')' : '' ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- AREA TAB 2: MAIN COA VIEWER (BAGAN AKUN TUNGGAL YAYASAN) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'coa'" x-cloak x-transition.opacity.duration.300ms>
        
        <!-- ========================================================================= -->
        <!-- SMART OPENING BALANCE CHECKER (Mencegah Unbalance Sejak Awal) -->
        <!-- ========================================================================= -->
        <?php
        $totDebAwal = 0; $totKreAwal = 0;
        if (!empty($grouped_coa)) {
            foreach ($grouped_coa as $kat => $coas) {
                foreach ($coas as $c) {
                    if ($c['is_parent'] == 0) {
                        if (($c['saldo_normal'] ?? 'Debit') == 'Debit') $totDebAwal += $c['saldo_awal'];
                        else $totKreAwal += $c['saldo_awal'];
                    }
                }
            }
        }
        $isSeimbang = ($totDebAwal === $totKreAwal);
        $selisih = abs($totDebAwal - $totKreAwal);
        ?>

        <?php if(!$isSeimbang && ($totDebAwal > 0 || $totKreAwal > 0)): ?>
            <div class="bg-rose-50 dark:bg-rose-900/20 border-2 border-rose-200 dark:border-rose-800 rounded-2xl p-5 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center text-xl shrink-0"><i class="fas fa-balance-scale-right"></i></div>
                    <div>
                        <h4 class="text-sm font-black text-rose-700 dark:text-rose-400 uppercase tracking-widest mb-1">Peringatan: Saldo Awal (Opening Balance) Tidak Seimbang</h4>
                        <p class="text-xs text-rose-600 dark:text-rose-300 font-medium leading-relaxed">
                            Total Debit Awal (Rp <?= number_format($totDebAwal, 0, ',', '.') ?>) berbeda dengan Total Kredit Awal (Rp <?= number_format($totKreAwal, 0, ',', '.') ?>). Terdapat selisih <strong>Rp <?= number_format($selisih, 0, ',', '.') ?></strong>. 
                        </p>
                        <p class="text-[10px] text-rose-500 dark:text-rose-400 mt-1 font-bold italic">*Saran: Sesuaikan/Edit nilai Saldo Awal pada kelompok akun Aset Neto (Modal/Ekuitas) untuk menampung selisih pembulatan ini.</p>
                    </div>
                </div>
            </div>
        <?php elseif($isSeimbang && $totDebAwal > 0): ?>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4 mb-6 flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-emerald-500 dark:text-emerald-400 text-2xl"></i>
                <div>
                    <h4 class="text-sm font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">Setup Saldo Awal Sempurna (Balanced)</h4>
                    <p class="text-xs text-emerald-600 dark:text-emerald-500 font-medium">Buku Besar telah siap digunakan dengan Total Debit & Kredit: Rp <?= number_format($totDebAwal, 0, ',', '.') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            
            <!-- Toolbar COA - Bebas dari Filter Unit -->
            <div class="bg-slate-50 dark:bg-slate-950 p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-sitemap text-amber-500"></i> Master Chart of Accounts
                    </h3>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <!-- BADGE TUNGGAL YAYASAN -->
                    <div class="px-5 py-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 w-full sm:w-auto shadow-sm">
                        <i class="fas fa-landmark"></i> COA Yayasan Terpadu
                    </div>

                    <!-- TOMBOL TAMBAH AKUN -->
                    <a href="<?= base_url('app/akuntansi/coa/new') ?>" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                        <i class="fas fa-plus"></i> Tambah Akun
                    </a>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 gap-8">
                    <?php if (empty($grouped_coa)): ?>
                        <div class="text-center py-16 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                            <i class="fas fa-database text-4xl text-slate-300 dark:text-slate-600 mb-4"></i>
                            <p class="text-slate-500 dark:text-slate-400 font-bold">Bagan akun belum di-generate untuk Yayasan.</p>
                        </div>
                    <?php else: ?>
                        
                        <?php foreach ($grouped_coa as $kategori => $coas): ?>
                            <!-- Kategori Header -->
                            <div class="bg-slate-50 dark:bg-slate-800/30 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                                <div class="px-6 py-4 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                    <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">
                                        <?= esc($kategori) ?>
                                    </h4>
                                    <span class="px-3 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-[10px] font-bold text-slate-500 shadow-sm">
                                        <?= count($coas) ?> Akun
                                    </span>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm whitespace-nowrap">
                                        <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                            <tr>
                                                <th class="px-6 py-3 w-32">Kode Akun</th>
                                                <th class="px-6 py-3">Nama Akun</th>
                                                <th class="px-6 py-3 text-center">Tipe</th>
                                                <th class="px-6 py-3 text-right">Saldo Awal</th>
                                                <th class="px-6 py-3 text-right w-24">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300">
                                            <?php foreach ($coas as $c): ?>
                                                <tr class="hover:bg-amber-50 dark:hover:bg-amber-900/10 transition-colors group <?= $c['is_parent'] ? 'bg-slate-50 dark:bg-slate-800/50' : '' ?>">
                                                    <td class="px-6 py-3">
                                                        <span class="font-mono font-black <?= $c['is_parent'] ? 'text-slate-800 dark:text-white text-sm' : 'text-slate-500 dark:text-slate-400' ?>">
                                                            <?= esc($c['kode_akun']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-3">
                                                        <span class="<?= $c['is_parent'] ? 'font-black text-slate-800 dark:text-white uppercase text-sm' : 'pl-4' ?>">
                                                            <?= $c['is_parent'] ? '<i class="fas fa-folder text-amber-500 mr-2"></i>' : '<i class="fas fa-angle-right text-slate-300 mr-2"></i>' ?>
                                                            <?= esc($c['nama_akun']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-3 text-center">
                                                        <?php if ($c['is_parent']): ?>
                                                            <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9px] font-bold uppercase tracking-wider">Header</span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[9px] font-bold uppercase tracking-wider border border-emerald-200 dark:border-emerald-800">Detail (Postable)</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-3 text-right font-mono font-bold <?= $c['saldo_awal'] > 0 ? 'text-amber-600 dark:text-amber-500' : 'text-slate-400' ?>">
                                                        <?= $c['is_parent'] ? '-' : 'Rp ' . number_format($c['saldo_awal'], 2, ',', '.') ?>
                                                    </td>
                                                    <td class="px-6 py-3 text-right">
                                                        <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                                            <a href="<?= base_url('app/akuntansi/coa/edit/' . $c['id']) ?>" 
                                                               class="w-8 h-8 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-lg shadow-sm transition-all" title="Edit Akun">
                                                                <i class="fas fa-pen text-xs"></i>
                                                            </a>
                                                            <button @click="deleteModalOpen = true; deleteItemName = '<?= esc(addslashes($c['nama_akun'])) ?>'; deleteActionUrl = '<?= base_url('app/akuntansi/coa/delete/' . $c['id']) ?>'" 
                                                                    class="w-8 h-8 inline-flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg shadow-sm transition-all" title="Hapus Akun">
                                                                <i class="fas fa-trash-alt text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS COA -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="deleteModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-8 border-rose-600">
                <div class="bg-white dark:bg-slate-800 px-6 pt-6 pb-4 sm:p-8">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-trash-alt text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-4 text-center sm:text-left">
                            <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Akun COA?</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                    Anda akan menghapus akun: <strong x-text="deleteItemName" class="text-slate-800 dark:text-slate-200"></strong>.<br>
                                    Apakah Anda yakin? (Hanya dapat dihapus jika tidak ada sub-akun & riwayat jurnal).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-3">
                    <a :href="deleteActionUrl" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 sm:py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
                        Ya, Hapus
                    </a>
                    <button type="button" @click="deleteModalOpen = false" class="w-full inline-flex justify-center rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-4 py-3 sm:py-2 bg-white dark:bg-slate-800 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:w-auto transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>