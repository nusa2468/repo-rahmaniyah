<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium & Chart.js -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800 animate-in fade-in duration-500">
    
    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">HR Management System</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic leading-none">
                Dashboard <span class="text-indigo-600 dark:text-indigo-400">Kepegawaian</span>
            </h1>
            <p class="text-sm font-bold text-slate-500 mt-2">Pusat monitoring kinerja SDM dan Penggajian.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Unit Otoritas</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <p class="text-sm font-black text-slate-800 dark:text-white uppercase italic">
                        <?= $is_global ? 'Global / Yayasan' : 'Unit '.esc($session_unit) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. KPI CARDS (PREMIUM SOLID STYLE) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Card Total SDM (Indigo) -->
        <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-indigo-800">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80">Total Pegawai</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= number_format($stats_sdm->total ?? 0) ?></h3>
                <div class="mt-4 flex items-center gap-2 text-[9px] font-bold text-indigo-100 uppercase tracking-wide opacity-80">
                    <span><?= $stats_sdm->total_guru ?> Guru</span> • <span><?= $stats_sdm->total_staff ?> Staff</span>
                </div>
            </div>
            <i class="fas fa-users absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Kehadiran (Emerald) -->
        <div class="bg-emerald-600 rounded-[2.5rem] shadow-xl shadow-emerald-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-emerald-800">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80">Presensi Hari Ini</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= $persen_hadir ?>%</h3>
                <p class="text-[10px] font-bold text-emerald-50 mt-4 uppercase tracking-widest italic opacity-60">
                    <?= $stats_absensi->hadir ?? 0 ?> Hadir dari <?= $stats_sdm->total ?? 0 ?> Pegawai
                </p>
            </div>
            <i class="fas fa-fingerprint absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Terlambat (Amber) -->
        <div class="bg-amber-500 rounded-[2.5rem] shadow-xl shadow-amber-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-amber-700">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-100 opacity-80">Terlambat</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= number_format($stats_absensi->terlambat ?? 0) ?></h3>
                <p class="text-[10px] font-bold text-amber-50 mt-4 uppercase tracking-widest italic opacity-60">Perlu Pembinaan</p>
            </div>
            <i class="fas fa-clock absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Payroll (Slate) -->
        <div class="bg-slate-900 rounded-[2.5rem] shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-slate-700">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 opacity-80">Estimasi Gaji</p>
                <h3 class="text-2xl font-black mt-3 italic truncate">Rp <?= number_format($estimasi_gaji, 0, ',', '.') ?></h3>
                <p class="text-[9px] font-bold text-slate-400 mt-3 uppercase tracking-widest italic opacity-60">Bulan Ini (Gross)</p>
            </div>
            <i class="fas fa-wallet absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- 2. NAVIGATION GRID & TRENDS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- MATRIKS NAVIGASI (8 Kolom) -->
        <div class="lg:col-span-8 space-y-8">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] italic flex items-center gap-3">
                <i class="fas fa-sitemap text-indigo-500"></i> Matriks Operasional
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Grup Presensi -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Presensi & Kehadiran</h4>
                    </div>
                    <div class="space-y-3">
                        <a href="<?= base_url('app/kepegawaian/absensi-pegawai') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Input Harian</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="flex items-center justify-between p-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 group border-b-4 border-indigo-800">
                            <span class="text-xs font-black uppercase tracking-widest italic">Rekapitulasi Bulanan</span>
                            <i class="fas fa-file-invoice text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Grup Payroll & Data -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Keuangan & Data</h4>
                    </div>
                    <div class="space-y-3">
                        <a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-emerald-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-600 uppercase tracking-widest">Payroll Management</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="<?= base_url('app/masterdata/pegawai') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-emerald-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-600 uppercase tracking-widest">Master Data Pegawai</span>
                            <i class="fas fa-database text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART SECTION (4 Kolom) -->
        <div class="lg:col-span-4 space-y-6">
             <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] italic flex items-center gap-3">
                <i class="fas fa-chart-line text-indigo-500"></i> Analisis KPI
            </h3>

            <!-- Trend Absensi -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Tren Kehadiran (7 Hari)</p>
                <div class="h-40">
                    <canvas id="chartTrendAbsensi"></canvas>
                </div>
            </div>

            <!-- Chart Proporsi Kehadiran (KPI Penggajian) -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Dampak Gaji Harian</p>
                <div class="flex items-center gap-4">
                    <div class="h-32 w-32 shrink-0">
                        <canvas id="chartProporsiKehadiran"></canvas>
                    </div>
                    <div class="space-y-2 flex-1">
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="font-bold text-emerald-600"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span>Hadir</span>
                            <span class="font-black"><?= $stats_absensi->hadir ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="font-bold text-amber-500"><span class="w-2 h-2 rounded-full bg-amber-500 inline-block mr-1"></span>Telat</span>
                            <span class="font-black"><?= $stats_absensi->terlambat ?? 0 ?></span>
                        </div>
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="font-bold text-rose-500"><span class="w-2 h-2 rounded-full bg-rose-500 inline-block mr-1"></span>Absen</span>
                            <span class="font-black"><?= $stats_absensi->absen_izin ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Komposisi SDM -->
            <div class="bg-slate-900 p-6 rounded-[2.5rem] shadow-xl text-white">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Struktur SDM</p>
                    <span class="text-[9px] font-bold text-indigo-400 uppercase">Total: <?= $stats_sdm->total ?? 0 ?></span>
                </div>
                <div class="h-24">
                     <canvas id="chartKomposisiSDM"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SCRIPT CHART CONFIG -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // 1. Trend Absensi Chart (Line)
    const ctxTrend = document.getElementById('chartTrendAbsensi');
    if(ctxTrend) {
        new Chart(ctxTrend.getContext('2d'), {
            type: 'line',
            data: {
                labels: [<?php foreach($trend_absensi as $t) echo "'".date('d M', strtotime($t['tanggal']))."',"; ?>],
                datasets: [{
                    label: 'Pegawai Hadir',
                    data: [<?php foreach($trend_absensi as $t) echo $t['total_hadir'].","; ?>],
                    borderColor: '#10b981', // Emerald
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { display: false }, grid: { display: false } },
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                }
            }
        });
    }

    // 2. Proporsi Kehadiran (Doughnut) - KPI Gaji Harian
    const ctxProporsi = document.getElementById('chartProporsiKehadiran');
    if(ctxProporsi) {
        new Chart(ctxProporsi.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Absen/Izin'],
                datasets: [{
                    data: [
                        <?= $stats_absensi->hadir ?? 0 ?>, 
                        <?= $stats_absensi->terlambat ?? 0 ?>, 
                        <?= $stats_absensi->absen_izin ?? 0 ?>
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { display: false }, tooltip: { enabled: true } }
            }
        });
    }

    // 3. Komposisi SDM (Bar Horizontal)
    const ctxSDM = document.getElementById('chartKomposisiSDM');
    if(ctxSDM) {
        new Chart(ctxSDM.getContext('2d'), {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: ['Guru', 'Staff'],
                datasets: [{
                    data: [<?= $stats_sdm->total_guru ?? 0 ?>, <?= $stats_sdm->total_staff ?? 0 ?>],
                    backgroundColor: ['#6366f1', '#f59e0b'],
                    borderRadius: 6,
                    barThickness: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false, grid: { display: false } },
                    y: { grid: { display: false }, ticks: { color: '#cbd5e1', font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    }
});
</script>

<?= $this->endSection() ?>