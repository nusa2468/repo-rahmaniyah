<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$url = current_url();
$isBagan = strpos($url, 'akuntansi') !== false && !strpos($url, 'jurnal') && !strpos($url, 'buku-besar') && !strpos($url, 'laporan');
$isJurnal = strpos($url, 'jurnal') !== false;
$isBukuBesar = strpos($url, 'buku-besar') !== false;
$isLaporan = strpos($url, 'laporan') !== false;
?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-water text-emerald-500"></i> Laporan Arus Kas
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Laporan Arus Kas (Cash Flow) pergerakan likuiditas Yayasan.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/akuntansi') ?>" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200 transition-colors border border-slate-200">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 transition-all shadow-md active:scale-95 border-b-4 border-slate-900">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- GLOBAL NAVIGATION TABS -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit flex-wrap shadow-inner no-print z-40 relative max-w-full">
        <a href="<?= base_url('app/akuntansi') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isBagan ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-emerald-600 hover:bg-white/50' ?> whitespace-nowrap">Bagan Akun</a>
        <a href="<?= base_url('app/akuntansi/jurnal') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isJurnal ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-emerald-600 hover:bg-white/50' ?> whitespace-nowrap">Jurnal Umum</a>
        <a href="<?= base_url('app/akuntansi/buku-besar') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isBukuBesar ? 'bg-white text-amber-600 shadow-md' : 'text-slate-500 hover:text-emerald-600 hover:bg-white/50' ?> whitespace-nowrap">Buku Besar</a>
        
        <!-- DROPDOWN KHUSUS LAPORAN -->
        <div x-data="{ openLaporan: false }" class="relative">
            <button @click="openLaporan = !openLaporan" @click.away="openLaporan = false" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 <?= $isLaporan ? 'bg-white text-emerald-600 shadow-md' : 'text-slate-500 hover:text-emerald-600 hover:bg-white/50' ?> whitespace-nowrap">
                <i class="fas fa-file-invoice"></i> Laporan <i class="fas fa-chevron-down text-[8px] transition-transform" :class="openLaporan ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="openLaporan" x-transition.opacity.duration.200ms style="display: none;" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-[100] flex flex-col">
                <div class="px-4 py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest">Sesuai Standar ISAK 35</div>
                <a href="<?= base_url('app/akuntansi/laporan/neraca-saldo') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center">
                    <i class="fas fa-list-ol w-6 text-center text-emerald-500 mr-2"></i> Neraca Saldo (Trial Balance)
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/posisi-keuangan') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center">
                    <i class="fas fa-balance-scale w-6 text-center text-emerald-500 mr-2"></i> Posisi Keuangan (Neraca)
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/aktivitas') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center">
                    <i class="fas fa-chart-line w-6 text-center text-emerald-500 mr-2"></i> Penghasilan Komprehensif
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/perubahan-aset-neto') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center">
                    <i class="fas fa-layer-group w-6 text-center text-emerald-500 mr-2"></i> Perubahan Aset Neto
                </a>
                <a href="<?= base_url('app/akuntansi/laporan/arus-kas') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center bg-emerald-50/50">
                    <i class="fas fa-water w-6 text-center text-emerald-500 mr-2"></i> Laporan Arus Kas
                </a>
                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                <a href="<?= base_url('app/akuntansi/laporan/calk') ?>" class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center">
                    <i class="fas fa-file-contract w-6 text-center text-emerald-500 mr-2"></i> CALK (ISAK 35)
                </a>
            </div>
        </div>
    </div>

    <!-- FILTER CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 no-print">
        <form action="" method="get" class="flex flex-col md:flex-row gap-6 items-end">
            <div class="space-y-2 flex-grow">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Unit Konsolidasi</label>
                <div class="relative">
                    <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 uppercase appearance-none outline-none">
                        <option value="GLOBAL" <?= $filterJenjang === 'GLOBAL' ? 'selected' : '' ?>>🏢 KONSOLIDASI (SEMUA UNIT)</option>
                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= $filterJenjang === $kode ? 'selected' : '' ?>>🏫 UNIT <?= strtoupper($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= $startDate ?>" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none">
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= $endDate ?>" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none">
            </div>
            
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg border-b-4 border-teal-800">Tampilkan</button>
        </form>
    </div>

    <!-- MAIN REPORT DOCUMENT -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden print-container relative z-0">
        
        <!-- INJEKSI KOP SURAT DINAMIS -->
        <div class="p-8 md:p-12 pb-0">
            <?= $this->include('akuntansi/laporan/_kop_surat') ?>
        </div>

        <!-- HEADER JUDUL LAPORAN -->
        <div class="text-center mb-8 mt-2">
            <h2 class="text-lg md:text-xl font-black text-slate-900 dark:text-white uppercase tracking-widest underline decoration-2 underline-offset-4">LAPORAN ARUS KAS (CASH FLOW)</h2>
            <?php 
                $teksEntitas = 'Unit ' . $filterJenjang;
                if ($filterJenjang === 'GLOBAL') $teksEntitas = 'Konsolidasi Yayasan Terpadu';
            ?>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mt-2 uppercase tracking-widest">Entitas: <?= $teksEntitas ?></p>
            <p class="text-[10px] font-black text-emerald-500 mt-1 uppercase tracking-widest bg-emerald-50 dark:bg-emerald-900/20 inline-block px-3 py-1 rounded-md border border-emerald-100 dark:border-emerald-800">
                Periode: <?= date('d M Y', strtotime($startDate)) ?> s.d. <?= date('d M Y', strtotime($endDate)) ?>
            </p>
        </div>

        <div class="p-6 md:p-10 pt-0 max-w-2xl mx-auto print-bg-white">
            <div class="space-y-4 text-slate-800 dark:text-slate-200">
                
                <!-- Aktivitas Operasi (Sederhana) -->
                <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest border-b-2 border-emerald-500 pb-2 mb-4 mt-4">Aktivitas Likuiditas</h3>
                
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm pl-4 font-bold">Penerimaan Kas (Inflow)</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp <?= number_format($kasMasuk, 0, ',', '.') ?></span>
                </div>
                
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm pl-4 font-bold">Pengeluaran Kas (Outflow)</span>
                    <span class="font-bold text-rose-600 dark:text-rose-400">(Rp <?= number_format($kasKeluar, 0, ',', '.') ?>)</span>
                </div>

                <div class="flex justify-between items-center py-4 border-t border-b border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 px-4 mt-2 print-gray-row">
                    <span class="font-black text-slate-800 dark:text-white text-sm uppercase tracking-widest">Kenaikan (Penurunan) Kas Bersih</span>
                    <span class="font-black <?= $kenaikanKas >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' ?>">
                        <?= $kenaikanKas < 0 ? '(' : '' ?>Rp <?= number_format(abs($kenaikanKas), 0, ',', '.') ?><?= $kenaikanKas < 0 ? ')' : '' ?>
                    </span>
                </div>

                <div class="flex justify-between items-center py-3 mt-4">
                    <span class="font-bold text-slate-600 dark:text-slate-400 text-sm">Saldo Kas & Setara Kas Awal Periode</span>
                    <span class="font-bold text-slate-800 dark:text-white">Rp <?= number_format($saldoAwalKas, 0, ',', '.') ?></span>
                </div>

                <div class="flex justify-between items-center py-6 border-y-4 border-double border-slate-800 dark:border-slate-400 mt-2 bg-emerald-50 dark:bg-emerald-900/20 px-4 rounded-xl print-grand-total">
                    <span class="font-black text-emerald-800 dark:text-emerald-400 uppercase tracking-widest">Saldo Kas Akhir Periode</span>
                    <span class="font-black text-2xl text-emerald-700 dark:text-emerald-400 italic">Rp <?= number_format($saldoAkhirKas, 0, ',', '.') ?></span>
                </div>

                <p class="text-[9px] text-slate-400 text-center mt-8 italic no-print">
                    * Catatan: Laporan Arus Kas ini disajikan menggunakan pendekatan agregasi langsung dari mutasi buku besar kelompok Kas & Bank.
                </p>
            </div>

            <!-- TANDA TANGAN (HANYA MUNCUL SAAT PRINT) -->
            <div class="hidden print-signature mt-16 text-slate-900">
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
        .print-gray-row { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-grand-total { border-top: 3px double black !important; border-bottom: 3px double black !important; background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; padding: 0 !important; }
        @page { size: A4 portrait; margin: 15mm; }
    }
</style>

<?= $this->endSection() ?>