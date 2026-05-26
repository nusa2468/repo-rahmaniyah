<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">
    <!-- Tombol Cetak / Header UI (Disembunyikan saat print) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-list-ol text-amber-500"></i> Neraca Saldo
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/akuntansi') ?>" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md transition-all border-b-4 border-indigo-800 active:scale-95">
                <i class="fas fa-print"></i> Cetak Dokumen
            </button>
        </div>
    </div>

    <!-- Dokumen Kertas Laporan -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-8 md:p-12 print-container relative z-0">
        
        <!-- INJEKSI KOP SURAT DINAMIS -->
        <?= $this->include('akuntansi/laporan/_kop_surat') ?>

        <div class="text-center mb-8">
            <h2 class="text-lg md:text-xl font-black text-slate-900 dark:text-white uppercase tracking-widest underline decoration-2 underline-offset-4">LAPORAN NERACA SALDO (TRIAL BALANCE)</h2>
            <p class="text-xs font-bold text-slate-600 dark:text-slate-400 mt-2 uppercase tracking-widest">
                Periode: <?= date('d M Y', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?>
            </p>
            <p class="text-[10px] font-black text-indigo-500 mt-1 uppercase tracking-widest bg-indigo-50 dark:bg-indigo-900/20 inline-block px-3 py-1 rounded-md border border-indigo-100 dark:border-indigo-800">
                Level Konsolidasi: <?= $filterJenjang === 'GLOBAL' ? 'YAYASAN TERPADU' : 'UNIT ' . $filterJenjang ?>
            </p>
        </div>

        <!-- ========================================================================= -->
        <!-- SMART BALANCE INDICATOR -->
        <!-- ========================================================================= -->
        <?php 
            $isAkhirBalanced = ($grandTotal['akhir_d'] == $grandTotal['akhir_k']);
            $selisihAkhir = abs($grandTotal['akhir_d'] - $grandTotal['akhir_k']);
        ?>

        <div class="mb-8 no-print">
            <?php if (!$isAkhirBalanced && ($grandTotal['akhir_d'] > 0 || $grandTotal['akhir_k'] > 0)): ?>
                <div class="bg-rose-50 dark:bg-rose-900/20 border-2 border-rose-200 dark:border-rose-800 p-5 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm animate-pulse">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center text-xl shrink-0"><i class="fas fa-balance-scale-right"></i></div>
                        <div>
                            <h4 class="text-sm font-black text-rose-700 dark:text-rose-400 uppercase tracking-widest mb-1">Tidak Seimbang (Unbalanced)</h4>
                            <p class="text-xs text-rose-600 dark:text-rose-300 font-medium">
                                Terdapat selisih <strong>Rp <?= number_format($selisihAkhir, 0, ',', '.') ?></strong> antara total Debit dan Kredit.
                            </p>
                        </div>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="text-[9px] text-rose-500 dark:text-rose-400 mb-2 italic">*Saran: Buat Jurnal Penyesuaian ke akun Aset Neto (Modal) untuk pembulatan/rekonsiliasi bank.</p>
                        <a href="<?= base_url('app/akuntansi/jurnal/new') ?>" class="inline-flex items-center px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-sm transition-colors border-b-4 border-rose-800">
                            <i class="fas fa-edit mr-2"></i> Jurnal Penyesuaian
                        </a>
                    </div>
                </div>
            <?php elseif ($isAkhirBalanced && $grandTotal['akhir_d'] > 0): ?>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-200 dark:border-emerald-800 p-4 rounded-2xl flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center text-lg shrink-0"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <h4 class="text-sm font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">Neraca Saldo Seimbang (Balanced)</h4>
                        <p class="text-xs text-emerald-600 dark:text-emerald-500 font-medium">Buku besar telah direkonsiliasi dengan sempurna tanpa selisih.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto print-bg-white">
            <table class="w-full text-left text-sm whitespace-nowrap border-collapse print-table">
                <thead class="bg-slate-800 text-white border-b-2 border-slate-900 print-header-table">
                    <tr>
                        <th rowspan="2" class="p-3 border border-slate-700 text-center uppercase text-[10px] tracking-widest">Kode Akun</th>
                        <th rowspan="2" class="p-3 border border-slate-700 uppercase text-[10px] tracking-widest">Nama Rekening / Akun</th>
                        <th colspan="2" class="p-2 border border-slate-700 text-center uppercase text-[10px] tracking-widest bg-slate-700">Saldo Awal</th>
                        <th colspan="2" class="p-2 border border-slate-700 text-center uppercase text-[10px] tracking-widest bg-slate-600">Mutasi Berjalan</th>
                        <th colspan="2" class="p-2 border border-slate-700 text-center uppercase text-[10px] tracking-widest bg-slate-900">Saldo Akhir</th>
                    </tr>
                    <tr>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Debit</th>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Kredit</th>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Debit</th>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Kredit</th>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Debit</th>
                        <th class="p-2 border border-slate-700 text-right uppercase text-[9px]">Kredit</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                    <?php foreach ($laporan as $row): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 print-gray-row">
                            <td class="p-2 border-x border-slate-100 dark:border-slate-800 text-center font-bold"><?= $row['kode_akun'] ?></td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800"><?= $row['nama_akun'] ?></td>
                            
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right"><?= $row['awal_d'] > 0 ? number_format($row['awal_d'], 0, ',', '.') : '-' ?></td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right"><?= $row['awal_k'] > 0 ? number_format($row['awal_k'], 0, ',', '.') : '-' ?></td>
                            
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right"><?= $row['mut_d'] > 0 ? number_format($row['mut_d'], 0, ',', '.') : '-' ?></td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right"><?= $row['mut_k'] > 0 ? number_format($row['mut_k'], 0, ',', '.') : '-' ?></td>
                            
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right font-black <?= $row['akhir_d'] > 0 ? 'text-indigo-600 dark:text-indigo-400' : '' ?>"><?= $row['akhir_d'] > 0 ? number_format($row['akhir_d'], 0, ',', '.') : '-' ?></td>
                            <td class="p-2 border-r border-slate-100 dark:border-slate-800 text-right font-black <?= $row['akhir_k'] > 0 ? 'text-rose-600 dark:text-rose-400' : '' ?>"><?= $row['akhir_k'] > 0 ? number_format($row['akhir_k'], 0, ',', '.') : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Grand Total -->
                    <tr class="bg-slate-100 dark:bg-slate-800 font-black border-y-4 border-double border-slate-800 dark:border-slate-500 uppercase tracking-widest text-[10px] print-grand-total <?= !$isAkhirBalanced ? 'text-rose-600 dark:text-rose-400' : '' ?>">
                        <td colspan="2" class="p-3 text-right border-r border-slate-200 dark:border-slate-700">TOTAL KESELURUHAN</td>
                        <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700"><?= number_format($grandTotal['awal_d'], 0, ',', '.') ?></td>
                        <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700"><?= number_format($grandTotal['awal_k'], 0, ',', '.') ?></td>
                        <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700"><?= number_format($grandTotal['mut_d'], 0, ',', '.') ?></td>
                        <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700"><?= number_format($grandTotal['mut_k'], 0, ',', '.') ?></td>
                        <td class="p-3 text-right border-r border-slate-200 dark:border-slate-700 <?= $isAkhirBalanced ? 'text-indigo-600 dark:text-indigo-400' : '' ?>"><?= number_format($grandTotal['akhir_d'], 0, ',', '.') ?></td>
                        <td class="p-3 text-right <?= $isAkhirBalanced ? 'text-rose-600 dark:text-rose-400' : '' ?>"><?= number_format($grandTotal['akhir_k'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .print-container { box-shadow: none !important; border: none !important; padding: 0 !important; }
        .print-bg-white { background-color: white !important; }
        .print-table { border-collapse: collapse !important; color: black !important; width: 100% !important; }
        .print-table th, .print-table td { border: 1px solid #000 !important; color: black !important; padding: 4px !important; font-size: 10pt !important; }
        .print-header-table { background-color: #f1f5f9 !important; color: black !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-gray-row { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .print-grand-total { border-top: 3px double black !important; border-bottom: 3px double black !important; background-color: #e2e8f0 !important; -webkit-print-color-adjust: exact; }
        body { background: white !important; color: black !important; }
        @page { size: A4 landscape; margin: 15mm; }
    }
</style>
<?= $this->endSection() ?>