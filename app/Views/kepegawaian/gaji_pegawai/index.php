<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    $request = \Config\Services::request();
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobalUser = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
    $currentTipe = $tipe_pegawai ?? 'guru';
    
    // Helper untuk Nama Bulan
    $listBulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];

    // Ambil filter bulan/tahun dari URL atau default saat ini
    $filterBulan = $request->getGet('bulan') ?? date('m');
    $filterTahun = $request->getGet('tahun') ?? date('Y');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">PAYROLL SETTINGS</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Manajemen <span class="text-indigo-600">Gaji Pegawai</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- TOMBOL GENERATE -->
            <button onclick="toggleGenerateModal()" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-xl active:scale-95 border-b-4 border-emerald-800">
                <i class="fas fa-cogs mr-2"></i> Generate Gaji
            </button>

            <!-- TOMBOL REKAP -->
            <a href="<?= base_url('app/kepegawaian/gaji-pegawai/rekap') ?>" class="inline-flex items-center px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all shadow-xl active:scale-95 border-b-4 border-slate-950">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Rekap Gaji
            </a>
        </div>
    </div>

    <!-- NAVIGASI TAB -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-th-large mr-2 opacity-50"></i> Dashboard
        </a>
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-clock mr-2 opacity-50"></i> Presensi
        </a>
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-invoice mr-2 opacity-50"></i> Rekap Absen
        </a>
        <a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-coins mr-2"></i> Payroll
        </a>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-indigo-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-indigo-900">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Estimasi Pengeluaran</p>
            <h3 class="text-2xl font-black mt-2 italic">Rp <?= number_format(($stats->est_pendapatan - $stats->est_potongan), 0, ',', '.') ?></h3>
            <p class="text-[9px] font-bold mt-2 opacity-60 uppercase tracking-wide">Total THP Bulan Ini</p>
            <i class="fas fa-wallet absolute -right-4 -bottom-4 text-white/10 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>
        <div class="bg-emerald-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-emerald-800">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Total Pendapatan</p>
            <h3 class="text-2xl font-black mt-2 italic">Rp <?= number_format($stats->est_pendapatan, 0, ',', '.') ?></h3>
            <i class="fas fa-arrow-up absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
        <div class="bg-rose-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-rose-800">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Total Potongan</p>
            <h3 class="text-2xl font-black mt-2 italic">Rp <?= number_format($stats->est_potongan, 0, ',', '.') ?></h3>
            <i class="fas fa-arrow-down absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
        <div class="bg-slate-800 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-slate-950">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Penerima Gaji</p>
            <h3 class="text-2xl font-black mt-2 italic"><?= number_format($stats->total_pegawai) ?> Pegawai</h3>
            <i class="fas fa-users absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
    </div>

    <!-- MAIN DATA CONTROL -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
        
        <!-- TOOLBAR -->
        <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="flex p-1 bg-slate-200 dark:bg-slate-900 rounded-xl shadow-inner border border-slate-300 dark:border-slate-700">
                    <a href="?tipe=guru&unit=<?= esc($current_unit) ?>&bulan=<?= $filterBulan ?>&tahun=<?= $filterTahun ?>" 
                       class="px-6 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?= $currentTipe === 'guru' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        TENAGA PENDIDIK
                    </a>
                    <a href="?tipe=staff&unit=<?= esc($current_unit) ?>&bulan=<?= $filterBulan ?>&tahun=<?= $filterTahun ?>" 
                       class="px-6 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?= $currentTipe === 'staff' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        KARYAWAN
                    </a>
                </div>
            </div>

            <form action="" method="get" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tipe" value="<?= esc($currentTipe) ?>">
                
                <!-- Unit Filter -->
                <div class="relative">
                    <select name="unit" onchange="this.form.submit()" 
                            class="pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase focus:border-indigo-500 outline-none appearance-none cursor-pointer <?= !$isGlobalUser ? 'opacity-50 cursor-not-allowed bg-slate-100' : 'hover:border-indigo-300' ?>"
                            <?= !$isGlobalUser ? 'disabled' : '' ?>>
                        <option value="GLOBAL">SELURUH UNIT</option>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>" <?= $current_unit == $j['kode_jenjang'] ? 'selected' : '' ?>>UNIT <?= $j['kode_jenjang'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[8px]"></i>
                </div>

                <!-- Bulan & Tahun Filter -->
                <div class="relative">
                    <select name="bulan" onchange="this.form.submit()" class="pl-4 pr-8 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase focus:border-indigo-500 outline-none cursor-pointer">
                        <?php foreach($listBulan as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $filterBulan == $k ? 'selected' : '' ?>><?= strtoupper($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="relative">
                    <select name="tahun" onchange="this.form.submit()" class="pl-4 pr-8 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase focus:border-indigo-500 outline-none cursor-pointer">
                        <?php for($i=date('Y'); $i>=2023; $i--): ?>
                            <option value="<?= $i ?>" <?= $filterTahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative">
                    <input type="text" name="search" value="<?= esc($request->getGet('search')) ?>" placeholder="Cari Pegawai..." 
                           class="pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest focus:border-indigo-500 outline-none w-40 shadow-sm">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Identitas Pegawai</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Pendapatan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Potongan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">THP (Estimasi)</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($list_pegawai)): ?>
                        <tr><td colspan="6" class="px-8 py-24 text-center opacity-30 italic">Data Pegawai Kosong</td></tr>
                    <?php else: ?>
                        <?php $no = 1 + (20 * ($pager->getCurrentPage() - 1)); foreach ($list_pegawai as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5 text-center text-xs font-black text-slate-300 group-hover:text-indigo-600"><?= $no++ ?>.</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-black text-slate-800 dark:text-slate-100 uppercase italic leading-none group-hover:text-indigo-600 transition-colors">
                                            <?= esc($row['nama_lengkap']) ?>
                                        </span>
                                        <div class="mt-2 flex items-center gap-2">
                                            <?php
                                                // LOGIKA NIP PINTAR (NIP -> NIPY -> NIK)
                                                $nomorInduk = '-';
                                                $labelInduk = 'ID';
                                                
                                                if (!empty($row['nip'])) { 
                                                    $nomorInduk = $row['nip']; $labelInduk = 'NIP'; 
                                                } elseif (!empty($row['nipy'])) { 
                                                    $nomorInduk = $row['nipy']; $labelInduk = 'NIPY'; 
                                                } elseif (!empty($row['nik'])) { 
                                                    $nomorInduk = $row['nik']; $labelInduk = 'NIK'; 
                                                }
                                            ?>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?= $labelInduk ?>: <?= esc($nomorInduk) ?></span>
                                            <span class="text-slate-300">•</span>
                                            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[8px] font-black rounded uppercase">Unit <?= esc($row['kode_jenjang']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-emerald-600">
                                    Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-rose-500">
                                    Rp <?= number_format($row['total_potongan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100 font-black shadow-sm">
                                        Rp <?= number_format($row['thp_estimasi'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Kelola (Setting) -->
                                        <a href="<?= base_url('app/kepegawaian/gaji-pegawai/kelola/' . $row['id']) ?>" 
                                           class="w-8 h-8 flex items-center justify-center bg-white border-2 border-slate-200 rounded-lg text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm" 
                                           title="Atur Komponen Gaji">
                                            <i class="fas fa-cog text-[10px]"></i>
                                        </a>
                                        <!-- Tombol Riwayat Slip -->
                                        <a href="<?= base_url('app/kepegawaian/gaji-pegawai/riwayat/' . $row['id']) ?>" 
                                           class="w-8 h-8 flex items-center justify-center bg-white border-2 border-slate-200 rounded-lg text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm" 
                                           title="Riwayat & Cetak Slip">
                                            <i class="fas fa-file-invoice text-[10px]"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($pager->getPageCount() > 1): ?>
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Halaman <?= $pager->getCurrentPage() ?> dari <?= $pager->getPageCount() ?></span>
                <div class="custom-pagination"><?= $pager->links('default', 'tailwind_pagination') ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL GENERATE GAJI -->
<div id="generateModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="toggleGenerateModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg w-full border-t-8 border-emerald-600">
            <form action="<?= base_url('app/kepegawaian/gaji-pegawai/generate') ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-10">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                            <i class="fas fa-magic"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tighter italic leading-none">Generate Payroll</h3>
                        <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Proses Gaji Bulanan Otomatis</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Tipe -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipe Pegawai</label>
                            <select name="tipe" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 transition-all outline-none">
                                <option value="guru" <?= $currentTipe == 'guru' ? 'selected' : '' ?>>GURU / PENDIDIK</option>
                                <option value="staff" <?= $currentTipe == 'staff' ? 'selected' : '' ?>>STAFF / TENDIK</option>
                            </select>
                        </div>
                        
                        <!-- Unit -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unit Kerja</label>
                            <select name="unit" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 transition-all outline-none" <?= !$isGlobalUser ? 'readonly' : '' ?>>
                                <option value="GLOBAL">SEMUA UNIT</option>
                                <?php foreach($jenjang_list as $j): ?>
                                    <option value="<?= $j['kode_jenjang'] ?>" <?= $sessionUnit == $j['kode_jenjang'] ? 'selected' : '' ?>>UNIT <?= $j['kode_jenjang'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Periode -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Bulan</label>
                                <select name="bulan" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 transition-all outline-none">
                                    <?php foreach($listBulan as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $filterBulan == $k ? 'selected' : '' ?>><?= strtoupper($v) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun</label>
                                <select name="tahun" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 transition-all outline-none">
                                    <?php for($i=date('Y'); $i>=2023; $i--): ?>
                                        <option value="<?= $i ?>" <?= $filterTahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" onclick="toggleGenerateModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-emerald-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-emerald-700 transition-all active:scale-95 border-b-4 border-emerald-900">PROSES SEKARANG</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleGenerateModal() {
        const m = document.getElementById('generateModal');
        m.classList.toggle('hidden');
        document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    
    .custom-pagination nav ul { display: flex; gap: 0.35rem; }
    .custom-pagination nav ul li a, .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.2rem; height: 2.2rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white;
        border-radius: 0.75rem; transition: all 0.2s; color: #64748b;
    }
    .custom-pagination nav ul li.active span {
        background: #4f46e5; color: white; border-color: #4f46e5;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }
</style>

<?= $this->endSection() ?>