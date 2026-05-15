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
    $listRekap = is_array($rekap) ? $rekap : [];
    
    // Default data sekolah jika kosong
    $namaSekolah = $sekolah['nama_sekolah'] ?? 'YAYASAN PENDIDIKAN';
    $alamatSekolah = $sekolah['alamat'] ?? 'Alamat belum dikonfigurasi';
    $kontakSekolah = ($sekolah['telepon'] ?? '-') . ' | ' . ($sekolah['email'] ?? '-');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER (HIDDEN ON PRINT) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 no-print">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">REKAPITULASI PRESENSI</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Attendance <span class="text-indigo-600 font-medium">Summary</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm active:scale-95">
                <i class="fas fa-print mr-2"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- TAB NAVIGASI (HIDDEN ON PRINT) -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit mb-8 border border-slate-200 dark:border-white/5 shadow-inner no-print">
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600">
            Monitoring Harian
        </a>
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            Rekap Bulanan
        </a>
    </div>

    <!-- FILTER SECTION (HIDDEN ON PRINT) -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl p-8 mb-8 no-print">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 items-end">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kategori SDM</label>
                <select name="tipe" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-black uppercase focus:border-indigo-500 outline-none">
                    <option value="guru" <?= $currentTipe == 'guru' ? 'selected' : '' ?>>GURU / PENDIDIK</option>
                    <option value="staff" <?= $currentTipe == 'staff' ? 'selected' : '' ?>>STAFF / TENDIK</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unit Kerja</label>
                <select name="unit" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-black uppercase focus:border-indigo-500 outline-none" <?= !$is_global ? 'disabled' : '' ?>>
                    <option value="GLOBAL">SEMUA UNIT</option>
                    <?php foreach($jenjang_list as $j): ?>
                        <option value="<?= $j['kode_jenjang'] ?>" <?= $current_unit == $j['kode_jenjang'] ? 'selected' : '' ?>>UNIT <?= $j['kode_jenjang'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Periode Bulan</label>
                <select name="bulan" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-black focus:border-indigo-500 outline-none">
                    <?php foreach($namaBulan as $m => $n): ?>
                        <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= strtoupper($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun</label>
                <select name="tahun" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-black focus:border-indigo-500 outline-none">
                    <?php for($i=date('Y'); $i>=2023; $i--): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-800 text-[10px] uppercase tracking-widest">
                Tampilkan Rekap
            </button>
        </form>
    </div>

    <!-- MAIN TABLE CONTAINER -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden print-container">
        
        <!-- HEADER KOP SURAT (Hanya Muncul Saat Print) -->
        <div class="hidden print-header mb-6">
            <div class="flex items-center border-b-2 border-black pb-4 mb-4">
                <!-- Logo Placeholder (Bisa diganti image asli) -->
                <div class="w-20 h-20 mr-4 flex items-center justify-center border-2 border-black rounded-lg bg-gray-100">
                    <span class="text-xs font-bold text-gray-500">LOGO</span>
                </div>
                <div class="text-left flex-1">
                    <h2 class="text-2xl font-black uppercase tracking-tight leading-none"><?= esc($namaSekolah) ?></h2>
                    <p class="text-sm font-bold uppercase tracking-widest mt-1 text-gray-600">Laporan Rekapitulasi Kehadiran Pegawai</p>
                    <p class="text-xs mt-1 text-gray-500"><?= esc($alamatSekolah) ?></p>
                    <p class="text-xs text-gray-500 italic"><?= esc($kontakSekolah) ?></p>
                </div>
            </div>
            
            <div class="flex justify-between items-end mb-2 text-xs font-bold uppercase border-b border-gray-300 pb-2">
                <div>
                    <span class="block text-gray-500 text-[10px]">Periode Laporan:</span>
                    <span><?= $namaBulan[$bulan] ?? $bulan ?> <?= $tahun ?></span>
                </div>
                <div class="text-right">
                    <span class="block text-gray-500 text-[10px]">Unit Kerja / Scope:</span>
                    <span><?= esc($current_unit) ?></span>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 text-white px-8 py-5 flex items-center justify-between italic no-print">
            <h3 class="text-xs font-black uppercase tracking-widest leading-none">
                Laporan Presensi: <?= $namaBulan[$bulan] ?? $bulan ?> <?= $tahun ?>
            </h3>
            <span class="text-[9px] font-bold opacity-50 uppercase tracking-widest">Unit Otoritas: <?= esc($current_unit) ?></span>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse print-table">
                <thead>
                    <tr class="bg-slate-50 dark:bg-white/5 border-b-2 border-slate-100 dark:border-white/10 print:bg-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-16 text-center print:text-black">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest print:text-black">Nama Pegawai</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-emerald-600 tracking-widest text-center print:text-black">Hadir</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-amber-500 tracking-widest text-center print:text-black">Late</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-indigo-500 tracking-widest text-center print:text-black">Sakit</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-orange-500 tracking-widest text-center print:text-black">Izin</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-rose-500 tracking-widest text-center print:text-black">Alpa</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-sky-500 tracking-widest text-center print:text-black">Cuti</th>
                        <th class="px-4 py-5 text-[10px] font-black uppercase text-indigo-700 tracking-widest text-center print:text-black">Dinas</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-900 dark:text-white tracking-widest text-center bg-slate-50/50 dark:bg-white/5 no-print-bg print:text-black print:bg-transparent">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($listRekap)): ?>
                        <tr>
                            <td colspan="10" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center opacity-30 no-print">
                                    <i class="fas fa-calendar-times text-6xl mb-4"></i>
                                    <p class="text-sm font-black uppercase tracking-widest">Laporan Masih Kosong</p>
                                    <p class="text-[10px] font-bold mt-1 uppercase italic">Belum ada pegawai yang aktif atau tercatat pada periode ini.</p>
                                </div>
                                <div class="hidden print:block text-center italic font-bold">Data Presensi Kosong</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($listRekap as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group print:no-hover">
                                <td class="px-8 py-4 text-center font-black text-slate-300 print:text-black print:border-b print:border-gray-300"><?= $no++ ?></td>
                                <td class="px-6 py-4 print:border-b print:border-gray-300">
                                    <p class="font-black text-slate-800 dark:text-slate-100 uppercase italic leading-none group-hover:text-indigo-600 transition-colors print:text-black"><?= esc($row->nama_lengkap) ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 print:text-gray-600">NIP: <?= esc($row->nip ?? '-') ?> • Unit: <?= esc($row->kode_jenjang) ?></p>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-emerald-600 bg-emerald-50/20 print:bg-transparent print:text-black print:border-b print:border-gray-300"><?= $row->jml_hadir ?></td>
                                <td class="px-4 py-4 text-center font-black text-amber-500 print:text-black print:border-b print:border-gray-300"><?= $row->jml_terlambat ?></td>
                                <td class="px-4 py-4 text-center font-black text-indigo-500 print:text-black print:border-b print:border-gray-300"><?= $row->jml_sakit ?></td>
                                <td class="px-4 py-4 text-center font-black text-orange-500 print:text-black print:border-b print:border-gray-300"><?= $row->jml_izin ?></td>
                                <td class="px-4 py-4 text-center font-black text-rose-500 <?= $row->jml_alpa > 0 ? 'bg-rose-50' : '' ?> print:bg-transparent print:text-black print:border-b print:border-gray-300"><?= $row->jml_alpa ?></td>
                                <td class="px-4 py-4 text-center font-black text-sky-500 print:text-black print:border-b print:border-gray-300"><?= $row->jml_cuti ?></td>
                                <td class="px-4 py-4 text-center font-black text-indigo-700 print:text-black print:border-b print:border-gray-300"><?= $row->jml_dinas ?></td>
                                <td class="px-8 py-4 text-center font-black text-slate-900 dark:text-white bg-slate-50/50 dark:bg-white/5 italic text-sm no-print-bg print:text-black print:bg-transparent print:border-b print:border-gray-300">
                                    <?= $row->total_log ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Signature Section (Only Print) -->
        <div class="hidden print-footer p-10 mt-4">
            <div class="grid grid-cols-3 gap-10 text-center text-xs font-bold">
                <div>
                    <p>Mengetahui,<br>Kepala Sekolah / Pimpinan</p>
                    <div class="h-20"></div>
                    <p class="underline">( <?= esc($sekolah['kepala_sekolah'] ?? '.....................................') ?> )</p>
                    <p class="text-[9px] font-normal mt-1">NIP. <?= esc($sekolah['nip_kepala_sekolah'] ?? '-') ?></p>
                </div>
                <div></div>
                <div>
                    <p>Jakarta, <?= date('d F Y') ?><br>Petugas Administrasi</p>
                    <div class="h-20"></div>
                    <p class="underline">( ..................................... )</p>
                </div>
            </div>
            <div class="mt-8 text-right text-[8px] text-gray-400 italic">
                Dicetak otomatis oleh Sistem Informasi Manajemen Sekolah pada <?= date('d-m-Y H:i:s') ?>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        /* Sembunyikan navigasi dan elemen dekoratif */
        .no-print, nav, header, aside, form { display: none !important; }
        
        /* Reset container */
        .max-w-7xl, body, html { 
            width: 100% !important; margin: 0 !important; padding: 10px !important; background: white !important; 
        }

        /* Tampilkan komponen khusus print */
        .print-header { display: block !important; }
        .print-footer { display: block !important; }
        
        /* Reset tabel */
        .print-container { 
            box-shadow: none !important; 
            border: none !important; 
            margin-top: 0 !important;
            border-radius: 0 !important;
        }

        table.print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            font-family: Arial, sans-serif;
        }
        
        table.print-table th, table.print-table td {
            border: 1px solid #000 !important;
            padding: 5px;
            color: #000 !important;
        }

        table.print-table thead {
            background-color: #f3f3f3 !important;
            -webkit-print-color-adjust: exact; 
        }
        
        /* Hilangkan background warna warni saat print untuk hemat tinta */
        .bg-emerald-50\/20, .bg-rose-50, .no-print-bg {
            background-color: transparent !important;
        }
    }

    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?= $this->endSection() ?>