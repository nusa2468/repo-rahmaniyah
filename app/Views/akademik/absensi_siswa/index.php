<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- 1. INITIALIZATION ---
    $request = \Config\Services::request();
    $sessionUnit = session()->get('kode_jenjang');
    
    // Cek Otoritas
    $isGlobal = empty($sessionUnit) || in_array(strtoupper($sessionUnit), ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT', 'ALL']);
    
    // Tentukan Filter Unit
    $urlUnit = $request->getGet('unit');
    $currentFilter = $isGlobal ? strtoupper($urlUnit ?? 'GLOBAL') : strtoupper($sessionUnit);
    
    // --- 2. CALCULATE STATS (ABSENSI) ---
    // Hitung statistik dari data $absensi yang dikirim controller
    $stats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
    $dataAbsensi = $absensi ?? []; // Fail-safe
    
    foreach ($dataAbsensi as $row) {
        $st = strtolower($row['status'] ?? '');
        if (in_array($st, ['hadir', 'h', 'present'])) $stats['hadir']++;
        elseif ($st == 'sakit') $stats['sakit']++;
        elseif ($st == 'izin') $stats['izin']++;
        elseif ($st == 'alpa') $stats['alpa']++;
    }
    
    $totalData = count($dataAbsensi);
    $persenHadir = $totalData > 0 ? round(($stats['hadir'] / $totalData) * 100) : 0;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-900">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600">PRESENSI SISWA</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase italic leading-none">
                Monitoring <span class="text-indigo-600">Presensi</span>
            </h1>
        </div>

        <!-- Filter Scope Unit -->
        <div class="flex flex-col sm:flex-row items-end lg:items-center gap-4 bg-white dark:bg-slate-800 p-3 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm">
            <div class="space-y-1">
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 ml-1">
                    <i class="fas fa-shield-alt"></i> Unit Otoritas
                </label>
                <form action="" method="get" id="filterUnitForm" class="flex items-center gap-2">
                    <div class="relative min-w-[180px]">
                        <!-- DROPDOWN UNIT -->
                        <select name="unit" onchange="this.form.submit()" 
                                class="w-full pl-3 pr-10 py-2.5 text-[11px] font-black uppercase tracking-widest border-2 border-slate-200 dark:border-white/10 rounded-xl bg-slate-50 dark:bg-slate-900 focus:border-indigo-500 focus:ring-0 text-slate-700 dark:text-slate-200 appearance-none cursor-pointer hover:border-indigo-300"
                                <?= !$isGlobal ? 'disabled' : '' ?>>
                            
                            <option value="GLOBAL" <?= $currentFilter == 'GLOBAL' ? 'selected' : '' ?>>SEMUA UNIT</option>
                            <option value="SD" <?= $currentFilter == 'SD' ? 'selected' : '' ?>>UNIT SD</option>
                            <option value="SMP" <?= $currentFilter == 'SMP' ? 'selected' : '' ?>>UNIT SMP</option>
                            <option value="SMA" <?= $currentFilter == 'SMA' ? 'selected' : '' ?>>UNIT SMA</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                    </div>
                    
                    <?php if (!$isGlobal): ?>
                        <input type="hidden" name="unit" value="<?= esc($currentFilter) ?>">
                        <span class="p-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-xl text-[9px] font-black border border-amber-100" title="Akses dikunci oleh sistem">
                            <i class="fas fa-lock"></i>
                        </span>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-day mr-2 opacity-50"></i> Kalender
        </a>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-clock mr-2 opacity-50"></i> Jadwal
        </a>
        <a href="<?= base_url('app/akademik/absensi-siswa') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-user-check mr-2"></i> Presensi
        </a>
        <a href="<?= base_url('app/akademik/nilai') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-star mr-2 opacity-50"></i> Nilai
        </a>
        <a href="<?= base_url('app/akademik/rapor') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-contract mr-2 opacity-50"></i> E-Rapor
        </a>
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-rocket mr-2 opacity-50"></i> Kenaikan
        </a>
    </div>

    <!-- STATISTIK CARDS (DARI DATA ABSENSI AKTIF) -->
    <?php if ($totalData > 0): ?>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-indigo-600 p-5 rounded-3xl border-b-4 border-indigo-900 text-white relative overflow-hidden group shadow-lg shadow-indigo-100 dark:shadow-none">
                <p class="text-[9px] font-black text-indigo-200 uppercase tracking-widest relative z-10 opacity-80">Total Sesi</p>
                <h3 class="text-3xl font-black mt-1 relative z-10 italic"><?= number_format($totalData) ?></h3>
                <i class="fas fa-list absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-emerald-600 p-5 rounded-3xl border-b-4 border-emerald-900 text-white relative overflow-hidden group shadow-lg shadow-emerald-100 dark:shadow-none">
                <p class="text-[9px] font-black text-emerald-100 uppercase tracking-widest relative z-10 opacity-80">Kehadiran</p>
                <h3 class="text-3xl font-black mt-1 relative z-10 italic"><?= $persenHadir ?>%</h3>
                <i class="fas fa-user-check absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-amber-500 p-5 rounded-3xl border-b-4 border-amber-700 text-white relative overflow-hidden group shadow-lg shadow-amber-100 dark:shadow-none">
                <p class="text-[9px] font-black text-amber-100 uppercase tracking-widest relative z-10 opacity-80">Sakit</p>
                <h3 class="text-3xl font-black mt-1 relative z-10 italic"><?= $stats['sakit'] ?></h3>
                <i class="fas fa-thermometer-half absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-orange-500 p-5 rounded-3xl border-b-4 border-orange-700 text-white relative overflow-hidden group shadow-lg shadow-orange-100 dark:shadow-none">
                <p class="text-[9px] font-black text-orange-100 uppercase tracking-widest relative z-10 opacity-80">Izin</p>
                <h3 class="text-3xl font-black mt-1 relative z-10 italic"><?= $stats['izin'] ?></h3>
                <i class="fas fa-envelope absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-rose-600 p-5 rounded-3xl border-b-4 border-rose-800 text-white relative overflow-hidden group shadow-lg shadow-rose-100 dark:shadow-none">
                <p class="text-[9px] font-black text-rose-100 uppercase tracking-widest relative z-10 opacity-80">Alpa</p>
                <h3 class="text-3xl font-black mt-1 relative z-10 italic"><?= $stats['alpa'] ?></h3>
                <i class="fas fa-user-times absolute -right-2 -bottom-2 text-6xl text-white/10 group-hover:scale-110 transition-transform"></i>
            </div>
        </div>
    <?php endif; ?>

    <!-- MAIN FORM: BUKA PRESENSI -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden mb-10">
        <div class="bg-slate-50 dark:bg-white/5 px-8 py-5 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-[11px] font-black text-slate-800 dark:text-white flex items-center uppercase tracking-widest italic leading-none">
                <i class="fas fa-calendar-plus mr-3 text-indigo-600"></i> Form Kelola Absensi Harian
            </h2>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-black text-slate-400 uppercase italic"><?= esc($currentFilter) ?> Area</span>
            </div>
        </div>
        <div class="p-8">
            <?php if (empty($kelas)): ?>
                <div class="text-center py-10 opacity-50">
                    <i class="fas fa-school text-4xl text-slate-300 mb-3"></i>
                    <p class="text-xs font-black uppercase tracking-widest">Rombel Tidak Ditemukan</p>
                    <p class="text-[10px] mt-1">Pastikan ada kelas/rombel aktif pada unit <?= esc($currentFilter) ?></p>
                </div>
            <?php else: ?>
                <form action="<?= base_url('app/akademik/absensi-siswa/kelola') ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- PILIH KELAS -->
                    <div class="md:col-span-5 space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Rombongan Belajar</label>
                        <select name="id_kelas" required class="w-full px-5 py-4 border-2 border-slate-100 dark:border-white/10 rounded-2xl focus:border-indigo-500 bg-slate-50 dark:bg-slate-900 text-xs font-black uppercase tracking-widest transition-all appearance-none cursor-pointer">
                            <option value="">-- PILIH KELAS --</option>
                            <?php foreach ($kelas as $kls): ?>
                                <option value="<?= esc($kls['id']) ?>" <?= old('id_kelas') == $kls['id'] ? 'selected' : '' ?>>
                                    [<?= esc($kls['kode_jenjang']) ?>] - <?= esc($kls['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- TANGGAL KEGIATAN -->
                    <div class="md:col-span-4 space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" value="<?= old('tanggal') ?? date('Y-m-d') ?>" required
                               class="w-full px-5 py-4 border-2 border-slate-100 dark:border-white/10 rounded-2xl focus:border-indigo-500 bg-slate-50 dark:bg-slate-900 text-xs font-black tracking-widest">
                    </div>

                    <!-- TOMBOL SUBMIT -->
                    <div class="md:col-span-3 flex items-end">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-indigo-100 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center text-[10px] uppercase tracking-[0.2em] border-b-4 border-indigo-800">
                            BUKA LEMBAR PRESENSI <i class="fas fa-chevron-right ml-3 text-[8px]"></i>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIWAYAT TABLE -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-slate-50/50 dark:bg-white/5">
            <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic">
                <i class="fas fa-history mr-2 text-indigo-600"></i> Arsip Presensi Unit <?= esc($currentFilter) ?>
            </h3>
            
            <form method="get" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="unit" value="<?= esc($currentFilter) ?>">
                <div class="relative group">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                    <input type="text" name="search" placeholder="CARI NAMA / NIS..." value="<?= esc($request->getGet('search') ?? '') ?>"
                           class="pl-9 pr-4 py-2.5 border-2 border-slate-100 dark:border-white/10 rounded-xl bg-white dark:bg-slate-900 text-[10px] font-black uppercase tracking-wider min-w-[200px] outline-none focus:border-indigo-500 transition-all">
                </div>
                <input type="date" name="filter_tanggal" value="<?= esc($request->getGet('filter_tanggal') ?? '') ?>"
                       class="px-4 py-2.5 border-2 border-slate-100 dark:border-white/10 rounded-xl bg-white dark:bg-slate-900 text-[10px] font-black uppercase tracking-wider outline-none focus:border-indigo-500 transition-all">
                <button type="submit" class="p-2.5 bg-slate-900 text-white rounded-xl hover:bg-indigo-600 transition-all shadow-md active:scale-90">
                    <i class="fas fa-filter px-2"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest w-40">Tanggal</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Rombongan Belajar</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Data Peserta Didik</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center w-24">Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($dataAbsensi)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <i class="fas fa-calendar-times text-6xl mb-4"></i>
                                    <p class="text-sm font-black uppercase tracking-widest">Database Presensi Kosong</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dataAbsensi as $row): ?>
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-all group">
                                <td class="px-8 py-4 whitespace-nowrap">
                                    <div class="text-[11px] font-black text-slate-800 dark:text-slate-200 uppercase italic group-hover:text-indigo-600 transition-colors">
                                        <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                    </div>
                                    <div class="text-[9px] font-bold text-slate-400 mt-0.5 uppercase italic">Harian Terverifikasi</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9px] font-black rounded-lg uppercase tracking-tighter border border-slate-200 dark:border-slate-600 shadow-sm">
                                        <?= esc($row['nama_kelas']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase italic leading-tight"><?= esc($row['nama_siswa']) ?></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">NIS: <?= esc($row['nis']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php
                                    $statusStyle = match(strtolower($row['status'] ?? '')) {
                                        'hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'sakit' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'izin'  => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'alpa'  => 'bg-rose-100 text-rose-700 border-rose-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border <?= $statusStyle ?> shadow-sm">
                                        <i class="fas fa-circle text-[6px]"></i> <?= esc($row['status'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-8 py-4 text-center whitespace-nowrap">
                                    <a href="<?= base_url('app/akademik/absensi-siswa/kelola?id_kelas=' . ($row['id_kelas'] ?? '') . '&tanggal=' . $row['tanggal']) ?>"
                                       class="w-9 h-9 inline-flex items-center justify-center bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-xl text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm active:scale-90"
                                       title="Sunting Presensi">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="px-10 py-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Menampilkan Riwayat Presensi Unit: <span class="text-indigo-600 font-black"><?= esc($currentFilter) ?></span>
                    </div>
                    <div class="custom-pagination">
                        <?= $pager->links('default', 'tailwind_pagination') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Premium Styling Scrollbar & Navigation */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .custom-pagination nav ul { display: flex; gap: 0.35rem; justify-content: center; }
    .custom-pagination nav ul li a, .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.5rem; height: 2.5rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white;
        border-radius: 0.75rem; transition: all 0.2s; color: #64748b;
    }
    .custom-pagination nav ul li.active span {
        background: #4f46e5; color: white; border-color: #4f46e5;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }
    .custom-pagination nav ul li a:hover { border-color: #4f46e5; color: #4f46e5; background: #f8fafc; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctxSiswa = document.getElementById('chartStatusSiswa');
        if (ctxSiswa) {
            new Chart(ctxSiswa.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Sakit', 'Izin', 'Alpa'],
                    datasets: [{
                        data: [<?= $stats['hadir'] ?>, <?= $stats['sakit'] ?>, <?= $stats['izin'] ?>, <?= $stats['alpa'] ?>],
                        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#e11d48'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>