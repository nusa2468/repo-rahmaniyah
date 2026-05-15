<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-6 space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase italic"><?= esc($title) ?></h1>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                Data Real-time per <?= date('d M Y H:i') ?>
            </p>
        </div>

        <!-- Badge Unit -->
        <div class="flex flex-col items-end">
            <span class="px-3 py-1 text-[10px] font-black rounded-sm bg-slate-800 text-white border-b-2 border-indigo-500 uppercase tracking-widest">
                Unit: <?= esc(session('kode_jenjang') ?? 'GLOBAL') ?>
            </span>
        </div>
    </div>

    <!-- KPI Section (Solid Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <?php
        $cards = [
            ['Luas Tanah', $summary['total_tanah_m2'], 'm²', 'Lokasi', $count['tanah'], 'bg-indigo-600', 'fa-map-marked-alt'],
            ['Luas Gedung', $summary['total_gedung_m2'], 'm²', 'Gedung', $count['gedung'], 'bg-emerald-600', 'fa-building'],
            ['Kapasitas', $summary['total_kapasitas'], 'Orang', 'Ruangan', $count['ruangan'], 'bg-cyan-600', 'fa-users'],
            ['Inventaris', $summary['total_item'], 'Item', 'Total Aset', '', 'bg-amber-500', 'fa-boxes'],
        ];
        ?>

        <?php foreach ($cards as [$label,$value,$unit,$sub,$count,$bg,$icon]): ?>
        <div class="<?= $bg ?> rounded-none border-b-4 border-black/20 p-5 shadow-lg relative overflow-hidden group transition-transform hover:scale-[1.02]">
            <!-- Decorative Icon -->
            <i class="fas <?= $icon ?> absolute -right-4 -bottom-4 text-white/10 text-7xl transform -rotate-12 group-hover:scale-110 transition-transform"></i>
            
            <div class="relative z-10">
                <div class="text-[10px] font-black text-white/70 uppercase tracking-widest"><?= $label ?></div>
                <div class="mt-2 flex items-baseline gap-2 text-white">
                    <div class="text-3xl font-black tracking-tighter italic">
                        <?= number_format($value,0,',','.') ?>
                    </div>
                    <span class="text-xs font-bold opacity-80 uppercase"><?= $unit ?></span>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1 w-8 bg-white/30 rounded-full"></div>
                    <div class="text-[10px] font-black text-white/90 uppercase italic tracking-tighter">
                        <?= $count ? "$count $sub" : $sub ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart Section -->
        <div class="lg:col-span-2 rounded-none border-2 border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest italic flex items-center gap-2">
                    <i class="fas fa-chart-bar text-indigo-500"></i> Monitoring Kondisi Peralatan
                </h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Unit Analysis</span>
            </div>

            <div class="h-[280px]">
                <canvas id="conditionChart"></canvas>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center mt-6 pt-4 border-t border-slate-50">
                <div class="p-2 bg-emerald-50 rounded-sm">
                    <div class="text-[10px] font-black text-emerald-700 uppercase tracking-widest mb-1 italic">Kondisi Baik</div>
                    <div class="text-xl font-black text-emerald-600 leading-none"><?= $kondisi['baik'] ?> <span class="text-[10px]">Unit</span></div>
                </div>
                <div class="p-2 bg-amber-50 rounded-sm">
                    <div class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1 italic">Rusak Ringan</div>
                    <div class="text-xl font-black text-amber-600 leading-none"><?= $kondisi['ringan'] ?> <span class="text-[10px]">Unit</span></div>
                </div>
                <div class="p-2 bg-rose-50 rounded-sm">
                    <div class="text-[10px] font-black text-rose-700 uppercase tracking-widest mb-1 italic">Rusak Berat</div>
                    <div class="text-xl font-black text-rose-600 leading-none"><?= $kondisi['berat'] ?> <span class="text-[10px]">Unit</span></div>
                </div>
            </div>
        </div>

        <!-- Quick Access Sidebar -->
        <div class="space-y-6">
            <div class="rounded-none border-2 border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest italic mb-6 border-b border-slate-100 pb-4 flex items-center gap-2">
                    <i class="fas fa-th-large text-indigo-500"></i> Navigasi Modul Sapras
                </h3>

                <ul class="space-y-3">
                    <?php
                    $menus = [
                        ['Manajemen Tanah','map-marked-alt','bg-indigo-50 text-indigo-600','tanah'],
                        ['Daftar Gedung','building','bg-emerald-50 text-emerald-600','gedung'],
                        ['Ruangan Kelas','door-open','bg-cyan-50 text-cyan-600','ruangan'],
                        ['Aset Peralatan','tools','bg-amber-50 text-amber-600','peralatan'],
                        ['Data Inventaris','archive','bg-slate-50 text-slate-600','inventaris'],
                    ];
                    ?>
                    <?php foreach ($menus as [$label,$icon,$colorClass,$url]): ?>
                    <li>
                        <a href="<?= base_url("app/sapras/$url") ?>"
                           class="flex items-center justify-between p-3 rounded-none border border-transparent hover:border-slate-200 hover:bg-slate-50 transition-all group">
                            <span class="flex items-center gap-3">
                                <div class="w-8 h-8 <?= $colorClass ?> flex items-center justify-center text-xs">
                                    <i class="fas fa-<?= $icon ?>"></i>
                                </div>
                                <span class="text-xs font-black text-slate-600 uppercase italic tracking-tight group-hover:text-indigo-600 transition-colors"><?= $label ?></span>
                            </span>
                            <i class="fas fa-arrow-right text-slate-300 text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>

            <!-- Alert Card -->
            <div class="rounded-none border-l-4 border-rose-600 bg-slate-900 p-5 shadow-lg">
                <div class="flex items-start gap-3">
                    <div class="text-rose-500 mt-1">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-1 italic">Rekomendasi Maintenance</h4>
                        <p class="text-[10px] text-slate-400 font-bold leading-relaxed uppercase">
                            Terdapat <span class="text-rose-500"><?= $kondisi['berat'] ?> unit</span> peralatan Rusak Berat. Segera lakukan audit untuk pengajuan penghapusan aset.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('conditionChart').getContext('2d');
    
    // Create gradient for better visual
    const gradientBaik = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBaik.addColorStop(0, '#10b981');
    gradientBaik.addColorStop(1, '#059669');

    const gradientRingan = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRingan.addColorStop(0, '#f59e0b');
    gradientRingan.addColorStop(1, '#d97706');

    const gradientBerat = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBerat.addColorStop(0, '#ef4444');
    gradientBerat.addColorStop(1, '#dc2626');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['BAIK', 'RUSAK RINGAN', 'RUSAK BERAT'],
            datasets: [{
                data: [<?= $kondisi['baik'] ?>, <?= $kondisi['ringan'] ?>, <?= $kondisi['berat'] ?>],
                backgroundColor: [gradientBaik, gradientRingan, gradientBerat],
                borderRadius: 0,
                barThickness: 50
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false } ,
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 10, weight: '900', family: 'sans-serif' },
                    bodyFont: { size: 12, weight: 'bold' },
                    padding: 12,
                    displayColors: false
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f8fafc', drawBorder: false },
                    ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 10, weight: '900' }, color: '#64748b' }
                }
            }
        }
    });
});
</script>

<style>
    /* Custom font-black adjustment for extra punch */
    .font-black { font-weight: 900; }
</style>

<?= $this->endSection() ?>