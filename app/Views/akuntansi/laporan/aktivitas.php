<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$url = current_url();
$isBagan = strpos($url, 'akuntansi') !== false && !strpos($url, 'jurnal') && !strpos($url, 'buku-besar') && !strpos($url, 'laporan');
$isJurnal = strpos($url, 'jurnal') !== false;
$isBukuBesar = strpos($url, 'buku-besar') !== false;
$isLaporan = strpos($url, 'laporan') !== false;
?>

<div class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-chart-line text-amber-500"></i> Laporan Penghasilan Komprehensif
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan Aktivitas (Surplus/Defisit) Entitas Nonlaba sesuai <strong>Standar ISAK 35</strong>.
            </p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 transition-all shadow-md active:scale-95 border-b-4 border-slate-900 no-print">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>

    <!-- GLOBAL NAVIGATION TABS -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit flex-wrap shadow-inner no-print z-40 relative">
        <a href="<?= base_url('app/akuntansi') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isBagan ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-amber-600 hover:bg-white/50' ?>">
            <i class="fas fa-sitemap mr-2"></i> Bagan Akun
        </a>
        <a href="<?= base_url('app/akuntansi/jurnal') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isJurnal ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-amber-600 hover:bg-white/50' ?>">
            <i class="fas fa-book mr-2"></i> Jurnal Umum
        </a>
        <a href="<?= base_url('app/akuntansi/buku-besar') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isBukuBesar ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-amber-600 hover:bg-white/50' ?>">
            <i class="fas fa-book-open mr-2"></i> Buku Besar
        </a>

        <!-- DROPDOWN KHUSUS LAPORAN -->
        <div x-data="{ openLaporan: false }" class="relative">
            <button @click="openLaporan = !openLaporan" @click.away="openLaporan = false" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 <?= $isLaporan ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-amber-600 hover:bg-white/50' ?>">
                <i class="fas fa-file-invoice"></i> Laporan Keuangan <i class="fas fa-chevron-down text-[8px] transition-transform" :class="openLaporan ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="openLaporan" x-transition.opacity.duration.200ms style="display: none;" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-[100] flex flex-col">
                <div class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Sesuai Standar ISAK 35</div>
                <a href="<?= base_url('app/akuntansi/laporan/posisi-keuangan') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center">
                    <i class="fas fa-balance-scale w-6 text-center text-amber-500 mr-2"></i> Posisi Keuangan (Neraca)
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/aktivitas') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center bg-amber-50/50">
                    <i class="fas fa-chart-line w-6 text-center text-amber-500 mr-2"></i> Penghasilan Komprehensif
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/perubahan-aset-neto') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center">
                    <i class="fas fa-layer-group w-6 text-center text-amber-500 mr-2"></i> Perubahan Aset Neto
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/arus-kas') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center">
                    <i class="fas fa-water w-6 text-center text-amber-500 mr-2"></i> Laporan Arus Kas
                </a>
                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                <a href="<?= base_url('app/akuntansi/laporan/calk') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-600 transition-colors flex items-center">
                    <i class="fas fa-file-contract w-6 text-center text-amber-500 mr-2"></i> CALK (ISAK 35)
                </a>
            </div>
        </div>
    </div>

    <!-- FILTER CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 no-print relative z-10">
        <form action="" method="get" class="flex flex-col md:flex-row gap-6 items-end">
            <!-- Filter Unit -->
            <div class="space-y-2 flex-grow">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Mode Laporan (Filter Entitas)</label>
                <div class="relative">
                    <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 uppercase appearance-none cursor-pointer focus:ring-2 focus:ring-amber-500 outline-none hover:border-amber-300">
                        <option value="MULTI" <?= $filterJenjang === 'MULTI' ? 'selected' : '' ?> class="font-black text-emerald-600">📊 KONSOLIDASI MULTI-KOLOM (Pusat & Semua Cabang)</option>
                        <option disabled>───────────────</option>
                        <option value="GLOBAL" <?= $filterJenjang === 'GLOBAL' ? 'selected' : '' ?>>🏢 KONSOLIDASI TOTAL (1 Kolom)</option>
                        <option disabled>───────────────</option>
                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= $filterJenjang === $kode ? 'selected' : '' ?>>🏫 SPESIFIK UNIT <?= strtoupper($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= $startDate ?>" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= $endDate ?>" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none focus:ring-2 focus:ring-amber-500">
            </div>
            
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg hover:shadow-xl active:scale-95 border-b-4 border-orange-700 transition-transform">Tampilkan</button>
        </form>
    </div>

    <!-- MAIN REPORT DOCUMENT (ISAK 35 COMPLIANT) -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-8 md:p-12 overflow-hidden print-container relative z-0">
        
        <!-- INJEKSI KOP SURAT DINAMIS -->
        <?= $this->include('akuntansi/laporan/_kop_surat') ?>

        <!-- HEADER JUDUL LAPORAN -->
        <div class="text-center mb-8">
            <h2 class="text-lg md:text-xl font-black text-slate-900 dark:text-white uppercase tracking-widest underline decoration-2 underline-offset-4">LAPORAN PENGHASILAN KOMPREHENSIF (AKTIVITAS)</h2>
            <?php 
                $teksEntitas = 'Unit ' . $filterJenjang;
                if ($filterJenjang === 'GLOBAL') $teksEntitas = 'Konsolidasi Yayasan Terpadu (Single Column)';
                if ($filterJenjang === 'MULTI') $teksEntitas = 'Konsolidasi Yayasan Terpadu (Multi-Kolom Unit)';
            ?>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mt-2 uppercase tracking-widest">Entitas: <?= $teksEntitas ?></p>
            <p class="text-[10px] font-black text-indigo-500 mt-1 uppercase tracking-widest bg-indigo-50 dark:bg-indigo-900/20 inline-block px-3 py-1 rounded-md border border-indigo-100 dark:border-indigo-800">
                Periode: <?= date('d M Y', strtotime($startDate)) ?> s.d. <?= date('d M Y', strtotime($endDate)) ?>
            </p>
            <p class="text-[10px] italic text-slate-500 mt-2 block">(Disajikan dalam Rupiah penuh, kecuali dinyatakan lain)</p>
        </div>

        <?php if($isMultiColumn): ?>
            <!-- ========================================================================= -->
            <!-- TAMPILAN: KONSOLIDASI MULTI-KOLOM (ISAK 35 - EXCEL STYLE) -->
            <!-- ========================================================================= -->
            <div class="overflow-x-auto w-full pb-8 print-bg-white">
                <table class="w-full text-left text-sm whitespace-nowrap border-collapse min-w-[800px] print-table">
                    <thead class="bg-slate-800 text-white border-b-4 border-slate-900 print-header-table">
                        <tr>
                            <th class="p-4 font-black uppercase tracking-widest text-xs border-r border-slate-700">Uraian Akun</th>
                            <?php foreach($activeUnits as $uKode => $uName): ?>
                                <th class="p-4 text-right font-black uppercase tracking-widest text-[10px] opacity-90 border-r border-slate-700"><?= $uName ?></th>
                            <?php endforeach; ?>
                            <th class="p-4 text-right font-black uppercase tracking-widest text-[11px] bg-slate-900">Total Konsolidasi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-800 dark:text-slate-300">
                        
                        <!-- 1. PENDAPATAN -->
                        <tr class="bg-slate-100 dark:bg-slate-800/80 border-b border-slate-300 dark:border-slate-700 print-gray-row">
                            <td colspan="100%" class="p-4 font-black text-slate-900 dark:text-white uppercase tracking-widest">PENDAPATAN</td>
                        </tr>
                        <?php foreach($laporan['Pendapatan'] as $akun): ?>
                            <?php $isHeader = $akun['is_parent'] == 1; ?>
                            <tr class="border-b border-slate-100 dark:border-slate-800 transition-colors <?= $isHeader ? 'bg-slate-50/50 dark:bg-slate-800/30' : 'hover:bg-amber-50 dark:hover:bg-slate-800/50' ?>">
                                <td class="p-3 border-r border-slate-100 dark:border-slate-800 <?= $isHeader ? 'font-bold uppercase text-xs pl-4 text-slate-700 dark:text-slate-300' : 'pl-8 font-medium' ?>">
                                    <?= $isHeader ? '<i class="fas fa-folder text-amber-500 mr-2"></i>' : '<i class="fas fa-angle-right text-slate-300 mr-2"></i>' ?>
                                    <?= esc($akun['nama_akun']) ?>
                                </td>
                                <?php foreach($activeUnits as $uKode => $uName): ?>
                                    <td class="p-3 text-right text-xs border-r border-slate-100 dark:border-slate-800">
                                        <?= $isHeader ? '' : number_format($akun['mutasi_per_unit'][$uKode], 0, ',', '.') ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="p-3 text-right font-bold bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white">
                                    <?= $isHeader ? '' : number_format($akun['mutasi'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- TOTAL PENDAPATAN -->
                        <tr class="font-black bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-t-2 border-b-4 border-double border-emerald-300 dark:border-emerald-800 print-strong-row">
                            <td class="p-4 uppercase tracking-widest text-xs border-r border-emerald-200 dark:border-emerald-700">Total Pendapatan</td>
                            <?php foreach($activeUnits as $uKode => $uName): ?>
                                <td class="p-4 text-right text-xs border-r border-emerald-200 dark:border-emerald-700"><?= number_format($totalPerUnit['Pendapatan'][$uKode], 0, ',', '.') ?></td>
                            <?php endforeach; ?>
                            <td class="p-4 text-right bg-emerald-100 dark:bg-emerald-900/40 text-sm print:bg-transparent">
                                <?= number_format($totalPendapatan, 0, ',', '.') ?>
                            </td>
                        </tr>

                        <!-- 2. BEBAN -->
                        <tr class="bg-slate-100 dark:bg-slate-800/80 border-b border-slate-300 dark:border-slate-700 mt-4 print-gray-row">
                            <td colspan="100%" class="p-4 font-black text-slate-900 dark:text-white uppercase tracking-widest">BEBAN</td>
                        </tr>
                        <?php foreach($laporan['Beban'] as $akun): ?>
                            <?php $isHeader = $akun['is_parent'] == 1; ?>
                            <tr class="border-b border-slate-100 dark:border-slate-800 transition-colors <?= $isHeader ? 'bg-slate-50/50 dark:bg-slate-800/30' : 'hover:bg-amber-50 dark:hover:bg-slate-800/50' ?>">
                                <td class="p-3 border-r border-slate-100 dark:border-slate-800 <?= $isHeader ? 'font-bold uppercase text-xs pl-4 text-slate-700 dark:text-slate-300' : 'pl-8 font-medium' ?>">
                                    <?= $isHeader ? '<i class="fas fa-folder text-amber-500 mr-2"></i>' : '<i class="fas fa-angle-right text-slate-300 mr-2"></i>' ?>
                                    <?= esc($akun['nama_akun']) ?>
                                </td>
                                <?php foreach($activeUnits as $uKode => $uName): ?>
                                    <td class="p-3 text-right text-xs border-r border-slate-100 dark:border-slate-800">
                                        <?= $isHeader ? '' : '(' . number_format($akun['mutasi_per_unit'][$uKode], 0, ',', '.') . ')' ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="p-3 text-right font-bold bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-white">
                                    <?= $isHeader ? '' : '(' . number_format($akun['mutasi'], 0, ',', '.') . ')' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- TOTAL BEBAN -->
                        <tr class="font-black bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border-t-2 border-rose-200 dark:border-rose-800 print-strong-row">
                            <td class="p-4 uppercase tracking-widest text-xs border-r border-rose-200 dark:border-rose-700">Total Beban</td>
                            <?php foreach($activeUnits as $uKode => $uName): ?>
                                <td class="p-4 text-right text-xs border-r border-rose-200 dark:border-rose-700">(<?= number_format($totalPerUnit['Beban'][$uKode], 0, ',', '.') ?>)</td>
                            <?php endforeach; ?>
                            <td class="p-4 text-right bg-rose-100 dark:bg-rose-900/40 text-sm print:bg-transparent">
                                (<?= number_format($totalBeban, 0, ',', '.') ?>)
                            </td>
                        </tr>

                        <!-- GRAND TOTAL SURPLUS / DEFISIT (ISAK 35 TERMINOLOGY) -->
                        <tr class="font-black bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border-y-4 border-double border-indigo-300 dark:border-indigo-800 print-grand-total">
                            <td class="p-5 uppercase tracking-widest border-r border-indigo-200 dark:border-indigo-700">SURPLUS (DEFISIT)</td>
                            <?php foreach($activeUnits as $uKode => $uName): 
                                $val = $surplusPerUnit[$uKode];
                                $valTxt = $val < 0 ? '(' . number_format(abs($val), 0, ',', '.') . ')' : number_format($val, 0, ',', '.');
                            ?>
                                <td class="p-5 text-right text-sm border-r border-indigo-200 dark:border-indigo-700 <?= $val < 0 ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' ?>"><?= $valTxt ?></td>
                            <?php endforeach; ?>
                            <td class="p-5 text-right bg-indigo-100 dark:bg-indigo-900/40 text-base print:bg-transparent <?= $surplusDefisit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                                <?= $surplusDefisit < 0 ? '(' . number_format(abs($surplusDefisit), 0, ',', '.') . ')' : number_format($surplusDefisit, 0, ',', '.') ?>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- ========================================================================= -->
            <!-- TAMPILAN: SINGLE COLUMN (ISAK 35 - CLEAN FORMAT) -->
            <!-- ========================================================================= -->
            <div class="print-bg-white">
                <table class="w-full text-left text-sm whitespace-nowrap border-collapse print-table">
                    <tbody class="text-slate-800 dark:text-slate-300">
                        
                        <!-- PENDAPATAN -->
                        <tr class="border-b-2 border-slate-800 dark:border-slate-400 print-strong-row bg-slate-50 dark:bg-slate-800/80">
                            <td colspan="2" class="py-3 px-4 font-black text-slate-900 dark:text-white uppercase tracking-widest text-sm">PENDAPATAN</td>
                        </tr>
                        <?php foreach ($laporan['Pendapatan'] as $akun): ?>
                            <?php $isHeader = $akun['is_parent'] == 1; ?>
                            <tr class="border-b border-slate-100 dark:border-slate-800 transition-colors <?= $isHeader ? 'bg-slate-50/50 dark:bg-slate-800/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' ?>">
                                <td class="py-2.5 <?= $isHeader ? 'font-bold uppercase text-xs pl-6 text-slate-700 dark:text-slate-300' : 'pl-10 font-medium text-slate-600 dark:text-slate-400' ?>">
                                    <?= esc($akun['nama_akun']) ?>
                                </td>
                                <td class="py-2.5 pr-4 text-right <?= $isHeader ? 'font-bold' : 'font-medium' ?>">
                                    <?= $isHeader ? '' : number_format($akun['mutasi'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="font-black bg-slate-50 dark:bg-slate-800/50 border-t border-b-4 border-slate-800 dark:border-slate-500 print-gray-row text-emerald-700 dark:text-emerald-400">
                            <td class="py-3 pl-4 uppercase tracking-widest text-xs">Total Pendapatan</td>
                            <td class="py-3 pr-4 text-right">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></td>
                        </tr>

                        <tr><td colspan="2" class="py-4"></td></tr> <!-- Spacer -->

                        <!-- BEBAN -->
                        <tr class="border-b-2 border-slate-800 dark:border-slate-400 mt-8 print-strong-row bg-slate-50 dark:bg-slate-800/80">
                            <td colspan="2" class="py-3 px-4 font-black text-slate-900 dark:text-white uppercase tracking-widest text-sm">BEBAN</td>
                        </tr>
                        <?php foreach ($laporan['Beban'] as $akun): ?>
                            <?php $isHeader = $akun['is_parent'] == 1; ?>
                            <tr class="border-b border-slate-100 dark:border-slate-800 transition-colors <?= $isHeader ? 'bg-slate-50/50 dark:bg-slate-800/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' ?>">
                                <td class="py-2.5 <?= $isHeader ? 'font-bold uppercase text-xs pl-6 text-slate-700 dark:text-slate-300' : 'pl-10 font-medium text-slate-600 dark:text-slate-400' ?>">
                                    <?= esc($akun['nama_akun']) ?>
                                </td>
                                <td class="py-2.5 pr-4 text-right <?= $isHeader ? 'font-bold' : 'font-medium' ?>">
                                    <?= $isHeader ? '' : '(' . number_format($akun['mutasi'], 0, ',', '.') . ')' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="font-black bg-slate-50 dark:bg-slate-800/50 border-t border-slate-800 dark:border-slate-500 print-gray-row text-rose-700 dark:text-rose-400">
                            <td class="py-3 pl-4 uppercase tracking-widest text-xs">Total Beban</td>
                            <td class="py-3 pr-4 text-right">(Rp <?= number_format($totalBeban, 0, ',', '.') ?>)</td>
                        </tr>

                        <!-- SURPLUS / DEFISIT -->
                        <tr class="font-black text-lg text-slate-900 dark:text-white border-y-4 border-double border-slate-900 dark:border-slate-400 print-grand-total mt-4">
                            <td class="py-5 pl-4 uppercase tracking-widest">SURPLUS (DEFISIT)</td>
                            <td class="py-5 pr-4 text-right <?= $surplusDefisit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' ?>">
                                Rp <?= $surplusDefisit < 0 ? '(' . number_format(abs($surplusDefisit), 0, ',', '.') . ')' : number_format($surplusDefisit, 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- TANDA TANGAN (HANYA MUNCUL SAAT PRINT) -->
        <div class="hidden print-signature p-10 mt-8 text-slate-900">
            <div class="flex justify-between items-end text-sm font-bold">
                <div class="text-center w-64">
                    <p>Mengetahui,</p>
                    <p class="mb-24 mt-1">Ketua Yayasan / Pimpinan</p>
                    <p class="underline">( ........................................ )</p>
                </div>
                <div class="text-center w-64">
                    <p>Depok, <?= date('d F Y') ?></p>
                    <p class="mb-24 mt-1">Kepala Bag. Keuangan</p>
                    <p class="underline">( ........................................ )</p>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    @media print {
        .no-print { display: none !important; }
        .print-container { box-shadow: none !important; border: none !important; margin: 0 !important; overflow: visible !important; padding: 0 !important; }
        .print-bg-white { background-color: white !important; }
        .print-header { color: black !important; padding-top: 0 !important; border-bottom: 3px double black !important; }
        .print-signature { display: block !important; page-break-inside: avoid; }
        
        /* Pewarnaan Tabel saat Cetak (Hitam Putih Standar Audit) */
        .print-table { border-collapse: collapse !important; color: black !important; width: 100% !important; }
        .print-table th, .print-table td { border: 1px solid #000 !important; color: black !important; padding: 4px !important; font-size: 10pt !important; }
        .print-header-table { background-color: #f1f5f9 !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-gray-row { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-strong-row { border-top: 2px solid black !important; border-bottom: 2px solid black !important; }
        .print-grand-total { border-top: 3px double black !important; border-bottom: 3px double black !important; font-size: 1.1em !important; }

        body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 0 !important; }
        
        /* Format Kertas */
        <?php if($isMultiColumn): ?>
            @page { size: A4 landscape; margin: 15mm; }
        <?php else: ?>
            @page { size: A4 portrait; margin: 15mm; }
        <?php endif; ?>
    }
</style>

<?= $this->endSection() ?>