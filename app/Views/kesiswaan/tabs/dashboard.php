<?php
    // --- 1. INISIALISASI VARIABEL ---
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');

    // --- 2. EXTRACT STATS ---
    $countEkskul    = $stats['total_ekskul'] ?? 0;
    $countAnggota   = $stats['total_anggota'] ?? 0;
    $countPrestasi  = $stats['total_prestasi'] ?? 0;
    $countKasus     = $stats['total_kasus'] ?? 0;
    $countAlumni    = $stats['total_alumni'] ?? 0;
    $countPresensi  = $stats['total_presensi'] ?? 0;
?>

<!-- KPI DASHBOARD -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Kartu Ekskul (Solid Indigo) -->
    <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-indigo-200 text-sm font-medium uppercase tracking-wider">Total Ekskul</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countEkskul) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Aktif Semester Ini</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 12l-2-2"/><path d="M12 16l-2-2"/></svg></div>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/></svg>
        </div>
    </div>
    
    <!-- Kartu Peserta (Solid Blue) -->
    <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-xl shadow-blue-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-blue-200 text-sm font-medium uppercase tracking-wider">Partisipasi Siswa</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countAnggota) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Anggota Terdaftar</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
    </div>

    <!-- Kartu Prestasi (Solid Amber) -->
    <div class="bg-amber-500 rounded-3xl p-6 text-white shadow-xl shadow-amber-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-amber-100 text-sm font-medium uppercase tracking-wider">Total Prestasi</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countPrestasi) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Akademik & Non-Akademik</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg></div>
        </div>
        <div class="absolute -right-6 -bottom-6 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
    </div>
    
    <!-- Kartu BK (Solid Rose) -->
    <div class="bg-rose-600 rounded-3xl p-6 text-white shadow-xl shadow-rose-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-rose-200 text-sm font-medium uppercase tracking-wider">Kasus BK</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countKasus) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Catatan Pelanggaran</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 12c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        </div>
    </div>

    <!-- Kartu Alumni (Solid Emerald) -->
    <div class="bg-emerald-600 rounded-3xl p-6 text-white shadow-xl shadow-emerald-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-emerald-200 text-sm font-medium uppercase tracking-wider">Database Alumni</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countAlumni) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Tracer Study</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="currentColor"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
        </div>
    </div>

    <!-- Kartu Presensi (Solid Teal) -->
    <div class="bg-teal-600 rounded-3xl p-6 text-white shadow-xl shadow-teal-100 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <p class="text-teal-200 text-sm font-medium uppercase tracking-wider">Aktivitas Presensi</p>
                <h3 class="text-4xl font-black mt-2"><?= number_format($countPresensi) ?></h3>
                <span class="inline-block mt-4 px-3 py-1 bg-white/20 rounded-full text-xs font-semibold backdrop-blur-sm border border-white/10">Kegiatan Terlaksana</span>
            </div>
            <div class="p-3 bg-white/10 rounded-2xl"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg></div>
        </div>
        <div class="absolute -right-4 -bottom-4 opacity-10 transform rotate-12 group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
    </div>
</div>

<!-- GRAFIK KPI -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-lg text-slate-800 mb-4">Tren Pelanggaran vs Prestasi</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="kpiChart1"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <h3 class="font-bold text-lg text-slate-800 mb-4">Sebaran Kategori Ekskul</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="kpiChart2"></canvas>
        </div>
    </div>
</div>

<!-- TABEL PRESTASI TERBARU -->
<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Prestasi Siswa Terbaru</h2>
        <a href="?tab=prestasi" class="text-indigo-600 text-sm font-bold hover:underline">Lihat Semua &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="pb-4 pl-4 w-12">No</th>
                    <th class="pb-4 pl-4">Siswa</th>
                    <th class="pb-4">Nama Prestasi</th>
                    <th class="pb-4">Tingkat/Peringkat</th>
                    <th class="pb-4 text-right pr-4">Tanggal</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($prestasi_list)): ?>
                    <tr><td colspan="5" class="py-8 text-center text-slate-400 italic">Belum ada data prestasi.</td></tr>
                <?php else: ?>
                    <?php $no = 1; 
                    $recentPrestasi = array_slice($prestasi_list, 0, 5);
                    foreach($recentPrestasi as $row): 
                        if($isGlobal && $filterUnit && isset($row['kode_jenjang']) && $row['kode_jenjang'] !== $filterUnit) continue;
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="py-4 pl-4 text-slate-500 font-medium"><?= $no++ ?></td>
                        <td class="py-4 pl-4"><div class="font-bold text-slate-700"><?= $row['nama_siswa'] ?></div></td>
                        <td class="py-4 text-slate-700"><?= $row['nama_prestasi'] ?></td>
                        <td class="py-4">
                            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-1 rounded font-bold"><?= $row['peringkat'] ?></span>
                            <span class="text-xs text-slate-500 ml-1"><?= $row['tingkat'] ?></span>
                        </td>
                        <td class="py-4 text-right pr-4 text-slate-500"><?= date('d/m/Y', strtotime($row['tanggal_prestasi'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SCRIPT CHART (Inject Variabel PHP) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Cek Chart Library
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js belum dimuat. Menggunakan Fallback.');
            // Fallback CDN if local fails
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = function() { initCharts(); };
            document.head.appendChild(script);
        } else {
            initCharts();
        }

        function initCharts() {
            const ctx1 = document.getElementById('kpiChart1');
            if (ctx1) {
                new Chart(ctx1.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Akademik (Presensi)', 'Pelanggaran (BK)', 'Kedisiplinan', 'Prestasi'],
                        datasets: [{
                            label: 'Jumlah Data',
                            data: [<?= $countPresensi ?>, <?= $countKasus ?>, <?= floor($countKasus * 0.8) ?>, <?= $countPrestasi ?>],
                            backgroundColor: ['#0ea5e9', '#f43f5e', '#f59e0b', '#10b981'],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            const ctx2 = document.getElementById('kpiChart2');
            if (ctx2) {
                new Chart(ctx2.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Olahraga', 'Seni', 'Sains', 'Lainnya'],
                        datasets: [{
                            data: [35, 25, 20, 20],
                            backgroundColor: ['#6366f1', '#ec4899', '#14b8a6', '#64748b'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }
    });
</script>