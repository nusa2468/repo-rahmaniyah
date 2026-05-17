<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight uppercase italic"><?= esc($title) ?></h1>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">
                Data Real-time per <?= date('d M Y H:i') ?>
            </p>
        </div>

        <!-- Badge Unit -->
        <div class="flex items-center">
            <span class="px-4 py-2 text-xs font-black rounded-lg bg-slate-900 dark:bg-black text-white border-b-4 border-indigo-500 uppercase tracking-widest shadow-lg">
                Unit: <?= esc($kodeJenjang) ?>
            </span>
        </div>
    </div>

    <!-- KPI Section (Enterprise Style) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <?php
        // [Label Utama, Value Utama, Satuan, Label Sub, Value Sub, Class BG, Icon]
        $cards = [
            ['Total Valuasi Aset', 'Rp ' . number_format($summary['total_nilai_aset'], 0, ',', '.'), '', 'Katalog Item', number_format($summary['total_aset']) . ' Unit', 'bg-emerald-600', 'fa-money-bill-wave'],
            ['Lokasi & Ruangan', $summary['total_lokasi'], 'Lokasi', 'Kategori', $summary['total_kategori'] . ' Jenis', 'bg-indigo-600', 'fa-map-marker-alt'],
            ['Sedang Dipinjam', $summary['peminjaman_aktif'], 'Item', 'Pemeliharaan', $summary['pemeliharaan_aktif'] . ' Aset', 'bg-amber-500', 'fa-handshake'],
            ['Pengajuan Baru', $summary['pengadaan_pending'], 'Draft', 'Menunggu', 'Approval', 'bg-rose-600', 'fa-shopping-cart'],
        ];
        ?>

        <?php foreach ($cards as [$label,$value,$unit,$sub,$count,$bg,$icon]): ?>
        <div class="<?= $bg ?> rounded-xl border-b-4 border-black/20 p-5 shadow-lg relative overflow-hidden group transition-transform hover:-translate-y-1">
            <!-- Decorative Icon -->
            <i class="fas <?= $icon ?> absolute -right-2 -bottom-2 text-white/10 text-7xl transform -rotate-12 group-hover:scale-110 transition-transform"></i>
            
            <div class="relative z-10">
                <div class="text-[10px] font-black text-white/70 uppercase tracking-widest"><?= $label ?></div>
                <div class="mt-2 flex items-baseline gap-2 text-white">
                    <div class="text-2xl lg:text-3xl font-black tracking-tighter italic whitespace-nowrap">
                        <?= $value ?>
                    </div>
                    <?php if($unit): ?>
                        <span class="text-xs font-bold opacity-80 uppercase"><?= $unit ?></span>
                    <?php endif; ?>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <div class="h-1 w-8 bg-white/30 rounded-full"></div>
                    <div class="text-[10px] font-black text-white/90 uppercase italic tracking-tighter">
                        <?= $sub ?>: <?= $count ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach ?>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart Section -->
        <div class="lg:col-span-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                <h3 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest italic flex items-center gap-2">
                    <i class="fas fa-chart-bar text-indigo-500"></i> Monitoring Kondisi Fisik
                </h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded">Asset Health</span>
            </div>

            <div class="h-[250px] flex-grow relative">
                <canvas id="conditionChart"></canvas>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center mt-6 pt-4 border-t border-slate-50 dark:border-slate-700">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-800">
                    <div class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-1 italic">Kondisi Baik</div>
                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-500 leading-none"><?= $kondisi['baik'] ?> <span class="text-[10px]">Item</span></div>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                    <div class="text-[10px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest mb-1 italic">Rusak Ringan</div>
                    <div class="text-xl font-black text-amber-600 dark:text-amber-500 leading-none"><?= $kondisi['ringan'] ?> <span class="text-[10px]">Item</span></div>
                </div>
                <div class="p-3 bg-rose-50 dark:bg-rose-900/20 rounded-lg border border-rose-100 dark:border-rose-800">
                    <div class="text-[10px] font-black text-rose-700 dark:text-rose-400 uppercase tracking-widest mb-1 italic">Rusak Berat</div>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-500 leading-none"><?= $kondisi['berat'] ?> <span class="text-[10px]">Item</span></div>
                </div>
                <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
                    <div class="text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-1 italic">Afkir / Dihapus</div>
                    <div class="text-xl font-black text-slate-700 dark:text-slate-400 leading-none"><?= $kondisi['afkir'] ?> <span class="text-[10px]">Item</span></div>
                </div>
            </div>
        </div>

        <!-- Quick Access Sidebar -->
        <div class="space-y-6 flex flex-col">
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 shadow-sm flex-grow">
                <h3 class="text-sm font-black text-slate-700 dark:text-white uppercase tracking-widest italic mb-6 border-b border-slate-100 dark:border-slate-700 pb-4 flex items-center gap-2">
                    <i class="fas fa-th-large text-indigo-500"></i> Navigasi Modul Aset
                </h3>

                <ul class="space-y-2">
                    <?php
                    $menus = [
                        ['Kategori Aset', 'tags', 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300', 'kategori'],
                        ['Lokasi & Ruangan', 'door-open', 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400', 'lokasi'],
                        ['Katalog Barang', 'boxes', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400', 'barang'],
                        ['Pengadaan Baru', 'shopping-cart', 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400', 'pengadaan'],
                        ['Peminjaman Aset', 'handshake', 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400', 'peminjaman'],
                        ['Pemeliharaan (Servis)', 'tools', 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-400', 'pemeliharaan'],
                    ];
                    ?>
                    <?php foreach ($menus as [$label,$icon,$colorClass,$url]): ?>
                    <li>
                        <!-- Link mengarah ke rute baru, misalnya app/sapras/barang -->
                        <a href="<?= base_url("app/sapras/$url") ?>"
                           class="flex items-center justify-between p-3 rounded-lg border border-transparent hover:border-slate-200 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all group">
                            <span class="flex items-center gap-3">
                                <div class="w-8 h-8 <?= $colorClass ?> rounded-lg flex items-center justify-center text-xs shadow-sm">
                                    <i class="fas fa-<?= $icon ?>"></i>
                                </div>
                                <span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase italic tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"><?= $label ?></span>
                            </span>
                            <i class="fas fa-chevron-right text-slate-300 dark:text-slate-600 text-[10px] group-hover:translate-x-1 group-hover:text-indigo-500 transition-all"></i>
                        </a>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>

            <!-- Smart Alert Cards -->
            <div class="space-y-3">
                <?php if($summary['pengadaan_pending'] > 0): ?>
                <div class="rounded-xl border-l-4 border-blue-500 bg-slate-900 p-4 shadow-md">
                    <div class="flex items-start gap-3">
                        <div class="text-blue-400 mt-0.5"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-1 italic">Approval Tertunda</h4>
                            <p class="text-[10px] text-slate-400 font-bold leading-relaxed uppercase">
                                Terdapat <span class="text-blue-400"><?= $summary['pengadaan_pending'] ?> pengajuan</span> pengadaan barang yang menunggu disetujui.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($kondisi['berat'] > 0): ?>
                <div class="rounded-xl border-l-4 border-rose-600 bg-slate-900 p-4 shadow-md animate-pulse">
                    <div class="flex items-start gap-3">
                        <div class="text-rose-500 mt-0.5"><i class="fas fa-exclamation-triangle"></i></div>
                        <div>
                            <h4 class="text-[10px] font-black text-white uppercase tracking-widest mb-1 italic">Peringatan Aset</h4>
                            <p class="text-[10px] text-slate-400 font-bold leading-relaxed uppercase">
                                Terdapat <span class="text-rose-500"><?= $kondisi['berat'] ?> unit</span> aset Rusak Berat. Segera audit untuk proses <span class="text-white">Afkir</span>.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
        </div>

    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('conditionChart').getContext('2d');
    
    // Create gradients for better visual
    const gradientBaik = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBaik.addColorStop(0, '#10b981'); // emerald-500
    gradientBaik.addColorStop(1, '#059669'); // emerald-600

    const gradientRingan = ctx.createLinearGradient(0, 0, 0, 400);
    gradientRingan.addColorStop(0, '#f59e0b'); // amber-500
    gradientRingan.addColorStop(1, '#d97706'); // amber-600

    const gradientBerat = ctx.createLinearGradient(0, 0, 0, 400);
    gradientBerat.addColorStop(0, '#ef4444'); // rose-500
    gradientBerat.addColorStop(1, '#dc2626'); // rose-600

    const gradientAfkir = ctx.createLinearGradient(0, 0, 0, 400);
    gradientAfkir.addColorStop(0, '#94a3b8'); // slate-400
    gradientAfkir.addColorStop(1, '#475569'); // slate-600

    // Tambahkan label Afkir
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['BAIK', 'RUSAK RINGAN', 'RUSAK BERAT', 'AFKIR'],
            datasets: [{
                data: [<?= $kondisi['baik'] ?>, <?= $kondisi['ringan'] ?>, <?= $kondisi['berat'] ?>, <?= $kondisi['afkir'] ?>],
                backgroundColor: [gradientBaik, gradientRingan, gradientBerat, gradientAfkir],
                borderRadius: 4,
                barThickness: 45
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false } ,
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 11, weight: '900', family: 'sans-serif' },
                    bodyFont: { size: 13, weight: 'bold' },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' Item';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#e2e8f0', drawBorder: false }, // Ubah warna grid agar pas di mode light/dark
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