<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    $currentTipe = $tipe_pegawai ?? 'guru';
    $namaBulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li><a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="hover:text-indigo-600 transition-colors">PAYROLL</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">REKAP BULANAN</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Payroll <span class="text-indigo-600">Summary</span>
            </h1>
        </div>
        
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm active:scale-95">
                <i class="fas fa-print mr-2"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl p-8 mb-8 no-print">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jenis Pegawai</label>
                <select name="tipe" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black uppercase focus:border-indigo-500 outline-none">
                    <option value="guru" <?= $currentTipe == 'guru' ? 'selected' : '' ?>>GURU / PENDIDIK</option>
                    <option value="staff" <?= $currentTipe == 'staff' ? 'selected' : '' ?>>STAFF / TENDIK</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unit Kerja</label>
                <select name="unit" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black uppercase focus:border-indigo-500 outline-none" <?= !$is_global ? 'disabled' : '' ?>>
                    <option value="GLOBAL">SEMUA UNIT</option>
                    <?php foreach($jenjang_list as $j): ?>
                        <option value="<?= $j['kode_jenjang'] ?>" <?= $current_unit == $j['kode_jenjang'] ? 'selected' : '' ?>>UNIT <?= $j['kode_jenjang'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Bulan</label>
                <select name="bulan" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black focus:border-indigo-500 outline-none">
                    <?php foreach($namaBulan as $m => $n): ?>
                        <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= strtoupper($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun</label>
                <select name="tahun" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black focus:border-indigo-500 outline-none">
                    <?php for($i=date('Y'); $i>=2023; $i--): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white font-black py-4 px-6 rounded-2xl shadow-lg hover:bg-indigo-700 transition-all border-b-4 border-indigo-800 text-[10px] uppercase tracking-widest">
                Hitung Gaji
            </button>
        </form>
    </div>

    <!-- GRAND TOTAL CARD -->
    <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl mb-8 relative overflow-hidden group">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Total Pengeluaran Gaji</p>
                <h2 class="text-4xl font-black italic tracking-tight">Rp <?= number_format($grand_total, 0, ',', '.') ?></h2>
                <p class="text-[10px] font-bold text-emerald-400 mt-2 uppercase tracking-widest">Periode: <?= $namaBulan[$bulan] ?> <?= $tahun ?></p>
            </div>
            <div class="text-right mt-4 md:mt-0">
                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Jumlah Penerima</p>
                <p class="text-2xl font-black"><?= count($payroll) ?> <span class="text-xs font-normal">Pegawai</span></p>
            </div>
        </div>
        <i class="fas fa-wallet absolute -right-6 -bottom-6 text-white/5 text-9xl group-hover:scale-110 transition-transform duration-500"></i>
    </div>

    <!-- TABEL REKAP -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden print-container">
        
        <!-- Print Header -->
        <div class="hidden print-header p-8 text-center border-b-2 border-black mb-4">
            <h2 class="text-2xl font-black uppercase">Laporan Rekapitulasi Gaji</h2>
            <p class="text-sm font-bold mt-1">Periode: <?= $namaBulan[$bulan] ?> <?= $tahun ?> | Unit: <?= esc($current_unit) ?></p>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse print-table">
                <thead>
                    <tr class="bg-slate-50 dark:bg-white/5 border-b-2 border-slate-100 dark:border-white/10 print:bg-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-12 text-center print:text-black">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest print:text-black">Pegawai</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center print:text-black">Hadir</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-emerald-600 tracking-widest text-right print:text-black">Pendapatan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-rose-500 tracking-widest text-right print:text-black">Potongan</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-indigo-600 tracking-widest text-right bg-slate-50/50 print:text-black print:bg-transparent">THP (Bersih)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($payroll)): ?>
                        <tr><td colspan="6" class="px-8 py-24 text-center opacity-30 italic font-black uppercase">Data Payroll Kosong</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($payroll as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group print:no-hover">
                                <td class="px-8 py-4 text-center font-black text-slate-300 print:text-black print:border-b"><?= $no++ ?></td>
                                <td class="px-6 py-4 print:border-b">
                                    <p class="font-black text-slate-800 dark:text-slate-100 uppercase italic leading-none group-hover:text-indigo-600 transition-colors print:text-black"><?= esc($row['pegawai']['nama_lengkap']) ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 print:text-black">NIP: <?= esc($row['pegawai']['nip'] ?? '-') ?></p>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-slate-600 bg-slate-50/50 rounded-lg print:bg-transparent print:text-black print:border-b">
                                    <?= $row['kehadiran'] ?> Hari
                                </td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600 print:text-black print:border-b">
                                    Rp <?= number_format($row['pendapatan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-rose-500 print:text-black print:border-b">
                                    Rp <?= number_format($row['potongan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-8 py-4 text-right font-black text-indigo-700 bg-indigo-50/30 print:text-black print:bg-transparent print:border-b text-sm italic">
                                    Rp <?= number_format($row['thp'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Print Footer Signature -->
        <div class="hidden print-footer p-10 mt-8">
            <div class="grid grid-cols-3 gap-10 text-center text-xs font-bold">
                <div><p>Disiapkan Oleh,<br>Admin Keuangan</p><div class="h-20"></div><p class="underline">( .......................... )</p></div>
                <div></div>
                <div><p>Mengetahui,<br>Kepala Sekolah / Ketua Yayasan</p><div class="h-20"></div><p class="underline">( .......................... )</p></div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, nav, header, aside, form, button { display: none !important; }
        .max-w-7xl { max-width: 100% !important; padding: 0 !important; }
        body { background: white !important; color: black !important; }
        .print-header, .print-footer { display: block !important; }
        .print-container { box-shadow: none !important; border: none !important; }
        table.print-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        table.print-table th, table.print-table td { border: 1px solid #000 !important; padding: 6px; color: black !important; }
    }
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?= $this->endSection() ?>