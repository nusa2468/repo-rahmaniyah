<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // Helper warna jenjang (dipertahankan)
    if (!function_exists('getJenjangColor')) {
        function getJenjangColor($kode) {
            $kode = strtoupper($kode ?? '');
            return match ($kode) {
                'GLOBAL', 'PUSAT' => 'bg-gray-800 text-white',
                'SD', 'MI'        => 'bg-rose-600 text-white',
                'SMP', 'MTS'      => 'bg-sky-600 text-white',
                'SMA', 'SMK', 'MA' => 'bg-indigo-600 text-white',
                'TK', 'PAUD'      => 'bg-emerald-600 text-white',
                default           => 'bg-gray-200 text-gray-700',
            };
        }
    }
    
    // Safety Variables
    $role           = $role ?? '';
    $jenjang        = $jenjang ?? '';
    $listJenjang    = $listJenjang ?? [];
    $filter_jenjang = $filter_jenjang ?? '';
    $stats          = $stats ?? ['total' => 0, 'unit_aktif' => 0, 'level_count' => 0, 'personel' => '-'];
    $chartUnit      = $chartUnit ?? [];
    $jabatan        = $jabatan ?? [];
?>

<div class="max-w-7xl mx-auto">
    
    <!-- 1. FITUR NAVIGASI (BREADCRUMB) -->
    <nav class="flex text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="<?= base_url('app/masterdata/dashboard') ?>" class="inline-flex items-center hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/></svg>
                    Master Data
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Jabatan</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 2. Header & Tombol (Perfect Alignment Fix v3 - Inline Style) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">
                <?= esc($title ?? 'Daftar Jabatan') ?>
            </h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Struktur Organisasi & Hirarki</p>
                
                <!-- Indikator Role & Unit (Anti Bocor UI) -->
                <?php if(in_array($role, ['superadmin', 'yayasan'])): ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                        Unit: <?= esc($jenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Container Tombol: Flex wrap dengan items center -->
        <div class="flex flex-wrap items-center gap-2">
            
            <!-- DROPDOWN FILTER UNIT (Khusus Superadmin/Yayasan) -->
            <?php if (in_array($role, ['superadmin', 'yayasan']) && !empty($listJenjang)): ?>
                <!-- FIX: Menggunakan Inline Style height: 40px untuk memaksa rata -->
                <form action="" method="get" class="flex items-center m-0 p-0" style="height: 40px;">
                    <div class="relative w-full md:w-48 h-full group"> 
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i class="fas fa-filter text-gray-400 text-xs group-hover:text-indigo-500 transition-colors"></i>
                        </div>
                        
                        <select name="kode_jenjang" onchange="this.form.submit()" 
                                class="w-full h-full pl-9 pr-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase tracking-wide rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer hover:bg-gray-50 transition-colors appearance-none flex items-center"
                                style="height: 40px !important;">
                            <option value="">Semua Unit</option>
                            <?php foreach ($listJenjang as $j): ?>
                                <?php 
                                    $val = is_array($j) ? ($j['kode_jenjang'] ?? '-') : ($j->kode_jenjang ?? '-');
                                    $lbl = is_array($j) ? ($j['nama_jenjang'] ?? 'Unit ' . $val) : ($j->nama_jenjang ?? 'Unit ' . $val);
                                    $sel = ($filter_jenjang === $val) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($val) ?>" <?= $sel ?>>
                                    <?= esc($lbl) ?> (<?= esc($val) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Tombol Tambah: Inline Style height: 40px -->
            <a href="<?= base_url('app/masterdata/jabatan/new') ?>"
               class="inline-flex items-center justify-center gap-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 border border-transparent box-border leading-none"
               style="height: 40px !important;">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline pt-0.5">Tambah Jabatan</span>
                <span class="sm:hidden pt-0.5">Baru</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards - Compact Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-award text-sm"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Total Jabatan</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= $stats['total'] ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <i class="fas fa-building text-sm"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Unit Terdata</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= $stats['unit_aktif'] ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Level Hirarki</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= $stats['level_count'] ?> Tier</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-400">
                <i class="fas fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Personel</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= $stats['personel'] ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Row - Compact -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-3 h-1 bg-indigo-600 rounded-full"></span>
                Distribusi Jabatan per Unit
            </h3>
            <div class="h-56">
                <canvas id="unitChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-3 h-1 bg-amber-500 rounded-full"></span>
                Proporsi Level
            </h3>
            <div class="h-56">
                <canvas id="levelChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Table Card - Ultra Compact & Solid -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">
                Hirarki Jabatan
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-20">Level</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Jabatan</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-24">Unit</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Atasan</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($jabatan)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400 dark:text-gray-600">
                                    <i class="fas fa-sitemap text-4xl mb-3 opacity-40"></i>
                                    <p class="text-xs font-black uppercase tracking-widest">Belum Ada Data Jabatan</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jabatan as $j): $j = (object)$j; ?>
                            <?php
                                $lvl = $j->level ?? 1;
                                $tier_bg = match(true) {
                                    $lvl == 1 => 'bg-rose-600',
                                    $lvl <= 3 => 'bg-indigo-600',
                                    default   => 'bg-emerald-600',
                                };
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-3.5 text-center">
                                    <div class="w-8 h-8 mx-auto rounded-lg <?= $tier_bg ?> text-white text-xs font-black flex items-center justify-center shadow-sm">
                                        <?= $lvl ?>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-id-badge text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-white">
                                                <?= esc($j->nama_jabatan) ?>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                ID: JAB-<?= str_pad($j->id ?? 0, 3, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black uppercase <?= getJenjangColor($j->kode_jenjang ?? '') ?>">
                                        <?= esc($j->kode_jenjang ?? 'GLOBAL') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <?php if (!empty($j->nama_atasan)): ?>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            <?= esc($j->nama_atasan) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-black text-amber-600 uppercase">Top Authority</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/masterdata/jabatan/edit/' . $j->id) ?>"
                                           class="w-9 h-9 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center shadow-sm hover:shadow transition-all active:scale-95">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= base_url('app/masterdata/jabatan/delete/' . $j->id) ?>"
                                           onclick="return confirm('Hapus jabatan ini?')"
                                           class="w-9 h-9 rounded-lg bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-sm hover:shadow transition-all active:scale-95">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Data dari Controller (Dinamis)
        const chartUnitData = <?= json_encode($chartUnit ?? []) ?>;
        const labels = Object.keys(chartUnitData);
        const data = Object.values(chartUnitData);

        // Bar Chart - Distribusi per Unit
        const unitCtx = document.getElementById('unitChart')?.getContext('2d');
        if (unitCtx) {
            new Chart(unitCtx, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        data: data.length ? data : [0],
                        backgroundColor: ['#1e293b', '#6366f1', '#0284c7', '#f43f5e', '#10b981'],
                        borderRadius: 6,
                        barThickness: 24,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Doughnut Chart - Proporsi Level
        const levelCtx = document.getElementById('levelChart')?.getContext('2d');
        if (levelCtx) {
            new Chart(levelCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Tier 1', 'Tier 2', 'Tier 3', 'Tier 4+'],
                    datasets: [{
                        data: [1, 5, 15, 25],
                        backgroundColor: ['#f43f5e', '#6366f1', '#10b981', '#f59e0b'],
                        borderWidth: 3,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 15 }
                        }
                    }
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>