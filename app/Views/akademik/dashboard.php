<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Premium Fonts & Chart.js -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
    // --- SAFE VARIABLE HANDLING ---
    // Mencegah error Undefined Variable jika Controller lupa mengirim data
    $is_global   = $is_global ?? false;
    $user_jenjang = $user_jenjang ?? 'GLOBAL';
    $total_siswa  = $total_siswa ?? 0;
    $total_kelas  = $total_kelas ?? 0;
    $persen_hadir = $persen_hadir ?? 0;
    $stats_rapor  = $stats_rapor ?? ['published' => 0];
    $trend_absensi = $trend_absensi ?? [];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest border border-indigo-200">
                    Academic Intelligence
                </span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none border-l pl-2 border-slate-300">
                    Live Monitor
                </span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic leading-none">
                Dashboard <span class="text-indigo-600 dark:text-indigo-400">Akademik</span>
            </h1>
            <p class="text-sm font-bold text-slate-500 mt-2 uppercase tracking-tight">Monitoring Proses Pembelajaran Periode <?= esc($tahun_ajaran_aktif['tahun_ajaran'] ?? '-') ?></p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Unit Otoritas</p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <!-- FIX: Menggunakan variabel yang sudah di-handle di atas -->
                    <p class="text-sm font-black text-slate-800 dark:text-white uppercase italic"><?= $is_global ? 'Global Access' : 'Unit '.esc($user_jenjang) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. KPI CARDS (PREMIUM SOLID STYLE) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Card Siswa -->
        <div class="bg-indigo-600 rounded-[2.5rem] shadow-xl shadow-indigo-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-indigo-800">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80">Peserta Didik</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= number_format($total_siswa) ?></h3>
                <p class="text-[10px] font-bold text-indigo-100 mt-4 uppercase tracking-widest italic opacity-60">Siswa Terdaftar</p>
            </div>
            <i class="fas fa-user-graduate absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Kelas -->
        <div class="bg-emerald-600 rounded-[2.5rem] shadow-xl shadow-emerald-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-emerald-800">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80">Rombongan Belajar</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= number_format($total_kelas) ?></h3>
                <p class="text-[10px] font-bold text-emerald-50 mt-4 uppercase tracking-widest italic opacity-60">Kelas Aktif</p>
            </div>
            <i class="fas fa-school absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Attendance -->
        <div class="bg-amber-500 rounded-[2.5rem] shadow-xl shadow-amber-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-amber-700">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-100 opacity-80">Presensi Hari Ini</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= $persen_hadir ?>%</h3>
                <p class="text-[10px] font-bold text-amber-50 mt-4 uppercase tracking-widest italic opacity-60">Tingkat Kehadiran</p>
            </div>
            <i class="fas fa-user-check absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Rapor -->
        <div class="bg-slate-900 rounded-[2.5rem] shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-slate-700">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 opacity-80">Penerbitan Rapor</p>
                <h3 class="text-4xl font-black mt-2 italic"><?= number_format($stats_rapor['published'] ?? 0) ?></h3>
                <p class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-widest italic opacity-60">Digital Published</p>
            </div>
            <i class="fas fa-file-contract absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- 2. NAVIGATION GRID & TRENDS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- MATRIKS NAVIGASI AKADEMIK -->
        <div class="lg:col-span-8 space-y-8">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] italic flex items-center gap-3">
                <i class="fas fa-map-marked-alt text-indigo-500"></i> Matriks Alur Akademik
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Grup Perencanaan -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-drafting-compass"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Perencanaan</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="<?= base_url('app/akademik/kalender') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Kalender Pendidikan</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Jadwal Pelajaran</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>

                <!-- Grup Pelaksanaan -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-running"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Pelaksanaan</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="<?= base_url('app/akademik/absensi-siswa') ?>" class="flex items-center justify-between p-4 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 group border-b-4 border-emerald-800">
                            <span class="text-xs font-black uppercase tracking-widest italic">Presensi Harian</span>
                            <i class="fas fa-user-check text-[10px] group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="<?= base_url('app/akademik/absensi-otomatis') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Sync Absensi Mesin</span>
                            <i class="fas fa-fingerprint text-[10px] text-slate-300"></i>
                        </a>
                    </div>
                </div>

                <!-- Grup Penilaian -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Hasil & Evaluasi</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="<?= base_url('app/akademik/nilai') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Input Nilai Siswa</span>
                            <i class="fas fa-edit text-[10px] text-slate-300"></i>
                        </a>
                        <a href="<?= base_url('app/akademik/rapor') ?>" class="flex items-center justify-between p-4 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 group border-b-4 border-indigo-800">
                            <span class="text-xs font-black uppercase tracking-widest italic tracking-wider">E-Rapor Digital</span>
                            <i class="fas fa-file-contract text-[10px]"></i>
                        </a>
                    </div>
                </div>

                <!-- Grup Transisi -->
                <div class="bg-white dark:bg-slate-800 p-8 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h4 class="font-black text-slate-800 dark:text-white uppercase tracking-widest text-sm italic">Transisi Periode</h4>
                    </div>
                    <div class="grid grid-cols-1 gap-3">
                        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Kenaikan Kelas</span>
                            <i class="fas fa-rocket text-[10px] text-slate-300"></i>
                        </a>
                        <a href="<?= base_url('app/akademik/ijazah') ?>" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl hover:bg-indigo-50 transition-all group">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 uppercase tracking-widest">Manajemen Ijazah</span>
                            <i class="fas fa-certificate text-[10px] text-slate-300"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- TRENDS & CHARTS -->
        <div class="lg:col-span-4 space-y-6">
             <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] italic flex items-center gap-3">
                <i class="fas fa-chart-line text-indigo-500"></i> Metrik Tren
            </h3>

            <!-- Trend Absensi -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Grafik Kehadiran (7 Hari)</p>
                <div class="h-56">
                    <canvas id="chartTrendAbsensi"></canvas>
                </div>
            </div>

            <!-- Quick Action Box -->
            <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden group border-b-4 border-indigo-600">
                <div class="relative z-10">
                    <h4 class="text-base font-black italic uppercase tracking-tight">Cetak Leger Nilai</h4>
                    <p class="text-[10px] text-slate-400 mt-2 leading-relaxed uppercase font-bold">Unduh rekapitulasi nilai seluruh kelas dalam format excel.</p>
                    <button class="mt-6 px-6 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all active:scale-95 shadow-lg border-b-4 border-indigo-800">
                        Download Leger
                    </button>
                </div>
                <i class="fas fa-file-excel absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT CHART CONFIG -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#94a3b8';

    // Trend Absensi Chart
    const ctxTrend = document.getElementById('chartTrendAbsensi');
    if (ctxTrend) {
        new Chart(ctxTrend.getContext('2d'), {
            type: 'line',
            data: {
                labels: [<?php foreach(($trend_absensi ?? []) as $t) echo "'".date('d M', strtotime($t['tanggal']))."',"; ?>],
                datasets: [{
                    label: 'Siswa Hadir',
                    data: [<?php foreach(($trend_absensi ?? []) as $t) echo ($t['total_hadir'] ?? 0).","; ?>],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5], drawBorder: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>

<?= $this->endSection() ?>