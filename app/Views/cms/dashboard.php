<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8">
    
    <!-- 1. HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-sky-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-sky-600">Control Center</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Web Content <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-600">Management</span>
            </h1>
        </div>
    </div>

    <!-- 2. NAVIGASI TAB CMS -->
    <div class="border-b border-gray-200 dark:border-white/10 mb-2 overflow-x-auto">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="<?= base_url('app/cms/dashboard') ?>" 
               class="border-sky-500 text-sky-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="<?= base_url('app/cms/berita') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-newspaper mr-2"></i> Berita
            </a>
            <a href="<?= base_url('app/cms/pengumuman') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-bullhorn mr-2"></i> Pengumuman
            </a>
            <a href="<?= base_url('app/cms/agenda') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-calendar-alt mr-2"></i> Agenda
            </a>
            <a href="<?= base_url('app/cms/album') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-images mr-2"></i> Galeri
            </a>
        </nav>
    </div>

    <?php
        // Logika Tampilan Scope
        $request = \Config\Services::request();
        $filterJenjang = $request->getGet('jenjang');
        
        // $user_unit dikirim dari controller
        $userLabel = $user_unit ?? 'GLOBAL / YAYASAN';
        $isGlobal = (strpos($userLabel, 'GLOBAL') !== false);
        
        // Daftar Unit Dinamis dari Controller
        // Jika controller tidak mengirim (fallback), array kosong agar tidak error loop
        $listUnit = isset($daftarUnit) ? $daftarUnit : [];
    ?>

    <!-- 3. INFO BAR & FILTER (SOLID UI - MATCHING SAPRAS) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <!-- Kartu Scope -->
        <div class="md:col-span-2 bg-slate-900 p-5 border-l-4 <?= $isGlobal ? 'border-sky-500' : 'border-emerald-500' ?> shadow-lg flex flex-col sm:flex-row items-center justify-between relative overflow-hidden group rounded-2xl">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas <?= $isGlobal ? 'fa-globe' : 'fa-building-lock' ?> text-9xl text-white transform rotate-12"></i>
            </div>
            
            <div class="flex items-center gap-5 z-10 w-full">
                <div class="w-12 h-12 <?= $isGlobal ? 'bg-sky-500' : 'bg-emerald-500' ?> flex items-center justify-center text-white shadow-lg rounded-xl">
                    <i class="fas <?= $isGlobal ? 'fa-globe-asia' : 'fa-lock' ?> text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black <?= $isGlobal ? 'text-sky-300' : 'text-emerald-300' ?> uppercase tracking-widest leading-none mb-1">
                        <?= $isGlobal ? 'MODE SUPERADMIN / YAYASAN' : 'MODE AKSES TERBATAS' ?>
                    </p>
                    <h3 class="text-xl font-black text-white uppercase italic leading-none tracking-tight">
                        <?= esc($userLabel) ?>
                    </h3>
                </div>
                
                <!-- Filter Dropdown (Hanya untuk Global/Superadmin) -->
                <?php if ($isGlobal) : ?>
                    <form action="<?= current_url() ?>" method="get" class="z-20 w-full sm:w-auto mt-3 sm:mt-0">
                        <div class="flex items-center bg-slate-800 p-1 rounded-lg border border-slate-700">
                            <select name="jenjang" onchange="this.form.submit()" class="bg-transparent text-white text-xs font-bold uppercase tracking-wide border-none focus:ring-0 cursor-pointer w-full sm:w-40 appearance-none pl-3 pr-8">
                                <option value="" <?= empty($filterJenjang) ? 'selected' : '' ?>>- SEMUA UNIT -</option>
                                <?php if (!empty($listUnit)): ?>
                                    <?php foreach($listUnit as $kode => $label): ?>
                                        <option value="<?= esc($kode) ?>" <?= $filterJenjang == $kode ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="absolute right-3 pointer-events-none text-slate-400"><i class="fas fa-filter text-xs"></i></div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kartu Tanggal / Info Tambahan -->
        <div class="bg-white dark:bg-gray-800 p-5 border-l-4 border-gray-300 dark:border-gray-600 shadow-md flex items-center justify-center rounded-2xl">
            <div class="text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Filter Aktif</p>
                <h3 class="text-xl font-black text-gray-800 dark:text-white mt-1 italic leading-none">
                    <?= !empty($filterJenjang) ? esc($filterJenjang) : 'SEMUA DATA' ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- 4. STATS ROW (SOLID COLORS) -->
    <div class="flex flex-row flex-wrap lg:flex-nowrap gap-4 w-full">
        <?php 
        $cards = [
            ['label' => 'Berita', 'val' => $stats['berita'] ?? 0, 'bg' => 'bg-sky-500', 'border' => 'border-sky-700', 'icon' => 'newspaper'],
            ['label' => 'Info', 'val' => $stats['pengumuman'] ?? 0, 'bg' => 'bg-emerald-500', 'border' => 'border-emerald-700', 'icon' => 'bullhorn'],
            ['label' => 'Agenda', 'val' => $stats['agenda'] ?? 0, 'bg' => 'bg-indigo-500', 'border' => 'border-indigo-700', 'icon' => 'calendar-check'],
            ['label' => 'Galeri', 'val' => $stats['album'] ?? 0, 'bg' => 'bg-amber-500', 'border' => 'border-amber-700', 'icon' => 'images'],
        ];
        foreach($cards as $c): 
        ?>
        <div class="basis-[calc(50%-0.5rem)] lg:basis-1/4 flex-grow <?= $c['bg'] ?> p-5 rounded-2xl border-b-4 <?= $c['border'] ?> shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 text-white opacity-20 transform rotate-12 group-hover:scale-110 transition-transform">
                <i class="fas fa-<?= $c['icon'] ?> text-8xl"></i>
            </div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shadow-inner">
                    <i class="fas fa-<?= $c['icon'] ?> text-sm"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-black text-white/80 uppercase tracking-widest truncate leading-tight"><?= $c['label'] ?></p>
                    <h3 class="text-2xl font-black text-white leading-tight"><?= number_format($c['val'], 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 5. ANALYTICS SECTION -->
    <div class="flex flex-col xl:flex-row gap-8 mt-4">
        <!-- Main Chart -->
        <div class="flex-1 bg-white dark:bg-gray-900 p-8 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                <span class="w-2 h-6 bg-sky-500 rounded-full"></span>
                Tren Publikasi
            </h3>
            <div class="h-[300px] w-full relative">
                <canvas id="publicationTrendChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="w-full xl:w-80 bg-white dark:bg-gray-900 p-8 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                Rasio Konten
            </h3>
            <div class="h-[200px] w-full relative flex items-center justify-center">
                <canvas id="contentDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 6. RECENT POSTS -->
    <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden mt-4">
        <div class="p-6 border-b border-gray-50 dark:border-white/5 flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fas fa-history text-sky-500"></i>
                Postingan Terbaru
            </h3>
            <a href="<?= base_url('app/cms/berita') ?>" class="text-xs font-bold text-sky-500 hover:text-sky-600 transition-colors uppercase tracking-wider">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    <?php if (!empty($recent_news)): ?>
                        <?php foreach($recent_news as $news): ?>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 font-bold text-gray-800 dark:text-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-400 text-xs">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <span class="line-clamp-1"><?= esc($news['judul']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if(isset($news['status'])): ?>
                                    <span class="px-2 py-1 text-[9px] font-bold uppercase rounded <?= $news['status'] == 'published' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $news['status'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-gray-500 font-mono">
                                <?= date('d M Y', strtotime($news['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="<?= base_url('app/cms/berita/edit/'.$news['id']) ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-400 hover:text-sky-500 hover:border-sky-500 transition-all shadow-sm">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-xs italic">Belum ada postingan terbaru.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)';
        const textColor = isDark ? '#94a3b8' : '#64748b';

        // Chart 1: Publication Trend
        const ctxTrend = document.getElementById('publicationTrendChart');
        if (ctxTrend) {
            new Chart(ctxTrend.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_trend['labels'] ?? ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"]) ?>,
                    datasets: [{
                        label: "Postingan",
                        borderColor: "#0ea5e9", // Sky-500
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, "rgba(14, 165, 233, 0.2)");
                            gradient.addColorStop(1, "rgba(14, 165, 233, 0)");
                            return gradient;
                        },
                        data: <?= json_encode($chart_trend['data'] ?? [0, 0, 0, 0, 0, 0]) ?>,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: "#fff",
                        pointBorderColor: "#0ea5e9",
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }],
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#fff',
                            titleColor: isDark ? '#fff' : '#0f172a',
                            bodyColor: isDark ? '#cbd5e1' : '#334155',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                        }
                    }, 
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            grid: { color: gridColor, drawBorder: false }, 
                            ticks: { color: textColor, font: {size: 10, weight: 'bold'} } 
                        }, 
                        x: { 
                            grid: { display: false, drawBorder: false }, 
                            ticks: { color: textColor, font: {size: 10, weight: 'bold'} } 
                        } 
                    } 
                }
            });
        }

        // Chart 2: Content Distribution
        const ctxDist = document.getElementById('contentDistributionChart');
        if (ctxDist) {
            new Chart(ctxDist.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ["Berita", "Info", "Agenda", "Galeri"],
                    datasets: [{
                        data: [<?= $stats['berita'] ?? 0 ?>, <?= $stats['pengumuman'] ?? 0 ?>, <?= $stats['agenda'] ?? 0 ?>, <?= $stats['album'] ?? 0 ?>],
                        backgroundColor: ['#0ea5e9', '#10b981', '#6366f1', '#f59e0b'], // Sky, Emerald, Indigo, Amber
                        borderWidth: 0,
                        hoverOffset: 4
                    }],
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false, 
                    cutout: '75%', 
                    plugins: { 
                        legend: { 
                            position: 'bottom', 
                            labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', font: { size: 10, weight: 'bold', family: 'sans-serif' }, padding: 20 } 
                        },
                        tooltip: {
                            backgroundColor: isDark ? '#1e293b' : '#fff',
                            bodyColor: isDark ? '#fff' : '#0f172a',
                            borderColor: isDark ? '#334155' : '#e2e8f0',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    let value = context.raw || 0;
                                    let total = context.chart._metasets[context.datasetIndex].total;
                                    let percentage = Math.round((value / total) * 100) + '%';
                                    return label + value + ' (' + percentage + ')';
                                }
                            }
                        }
                    } 
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>