<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$role = $userRole ?? 'guest';
$unitDisplay = $userUnit ?? 'Global';
$filterSelected = $filterSelected ?? 'ALL';
$isGlobal = $isGlobal ?? false;
$activeTab = $activeTab ?? 'silabus';

// Fallback jika daftar jenjang kosong (misal belum di-seed)
$daftarJenjang = $daftarJenjang ?? [];

$uiConfig = [
    'silabus' => ['gradient' => 'from-blue-500 to-cyan-500', 'text' => 'text-blue-600', 'bg' => 'bg-blue-50'],
    'rpp' => ['gradient' => 'from-emerald-500 to-teal-500', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
    'bahanAjar' => ['gradient' => 'from-amber-400 to-orange-500', 'text' => 'text-amber-600', 'bg' => 'bg-amber-50'],
    'bankSoal' => ['gradient' => 'from-violet-500 to-purple-600', 'text' => 'text-violet-600', 'bg' => 'bg-violet-50'],
    'evaluasi' => ['gradient' => 'from-rose-500 to-pink-600', 'text' => 'text-rose-600', 'bg' => 'bg-rose-50']
];
?>

<div class="font-sans text-slate-800 dark:text-slate-200 animate-fade-in pb-12">

    <!-- 1. HEADER SECTION -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-10">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="h-px w-8 bg-slate-400"></span>
                <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Academic System</span>
            </div>
            <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white mb-2">
                Pusat <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Pembelajaran</span>
            </h1>
            <p class="text-sm font-medium text-slate-500 max-w-xl leading-relaxed">
                Kelola kurikulum, materi ajar, dan evaluasi siswa dalam satu platform terintegrasi. 
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 ml-2 border border-slate-200">
                    <i class="fas fa-building mr-1"></i> <?= esc($unitDisplay) ?>
                </span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
             
             <!-- DROPDOWN FILTER UNIT DINAMIS (Khusus Superuser/Global) -->
             <?php if($isGlobal): ?>
            <form method="get" action="" class="relative group" id="filterForm">
                <input type="hidden" name="tab" value="<?= esc($activeTab) ?>">
                
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-filter text-slate-400 text-xs group-focus-within:text-blue-500 transition-colors"></i>
                </div>
                <select name="filter_jenjang" onchange="document.getElementById('filterForm').submit()" 
                        class="appearance-none pl-9 pr-8 py-2.5 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl text-xs font-bold text-slate-600 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all cursor-pointer shadow-sm hover:border-blue-300 min-w-[160px]">
                    <option value="ALL" <?= ($filterSelected === 'ALL') ? 'selected' : '' ?>>SEMUA UNIT</option>
                    
                    <!-- Loop Data Jenjang dari Database -->
                    <?php foreach ($daftarJenjang as $j): 
                        // Pastikan handle jika array atau object
                        $kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                        // Opsional: tampilkan nama panjang jika ada
                        $nama = is_object($j) ? ($j->nama_jenjang ?? $kode) : ($j['nama_jenjang'] ?? $kode);
                    ?>
                        <option value="<?= esc($kode) ?>" <?= ($filterSelected === $kode) ? 'selected' : '' ?>>
                            UNIT <?= esc($kode) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-chevron-down text-[8px] text-slate-400"></i>
                </div>
            </form>
            <div class="h-8 w-px bg-slate-200 dark:bg-gray-700 mx-1 hidden md:block"></div>
            <?php endif; ?>

            <!-- User Badge -->
            <div class="hidden md:flex flex-col items-end px-2">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Akses</span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <?= esc(strtoupper($role)) ?>
                </span>
            </div>

            <a href="<?= current_url() ?>" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-200 shadow-sm transition-all" title="Refresh">
                <i class="fas fa-sync-alt text-xs"></i>
            </a>
        </div>
    </div>

    <!-- 2. MAIN NAVIGATION GRID & SIDEBAR (Tetap Sama) -->
    <!-- (Kode di bawah sama dengan sebelumnya, tidak diubah agar tetap works) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        
        <div class="xl:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($modules as $key => $mod): 
                $ui = $uiConfig[$key] ?? $uiConfig['silabus'];
                $count = $stats[$key] ?? 0;
            ?>
            <a href="<?= base_url($mod['route']) ?>" class="group relative flex flex-col justify-between p-6 bg-white dark:bg-gray-900 rounded-[2rem] border border-slate-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-gradient-to-br <?= $ui['gradient'] ?> opacity-5 blur-2xl group-hover:opacity-15 transition-opacity duration-500"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?= $ui['gradient'] ?> text-white flex items-center justify-center text-2xl shadow-lg shadow-gray-200 dark:shadow-none group-hover:scale-110 transition-transform duration-300">
                            <i class="fas <?= $mod['icon'] ?>"></i>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 dark:bg-gray-800 flex items-center justify-center text-slate-300 group-hover:text-slate-600 transition-colors">
                            <i class="fas fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-300"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2 group-hover:text-slate-900">
                        <?= $mod['title'] ?>
                    </h3>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed min-h-[40px]">
                        <?= $mod['desc'] ?>
                    </p>
                </div>
                <div class="relative z-10 mt-6 pt-4 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400"><?= $mod['stat_label'] ?></span>
                        <span class="text-lg font-black text-slate-800 dark:text-white"><?= number_format($count) ?></span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg group-hover:bg-slate-100 transition-colors border border-slate-100 dark:border-gray-700">
                        Kelola Data
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="xl:col-span-4 space-y-8">
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-slate-200 dark:border-gray-800 shadow-sm p-6 relative overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-black text-sm uppercase tracking-widest text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        Live Updates
                    </h3>
                    <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded">
                        <?= ($filterSelected !== 'ALL') ? $filterSelected : 'GLOBAL' ?>
                    </span>
                </div>
                <div class="space-y-6 relative ml-1">
                    <div class="absolute left-[5px] top-2 bottom-2 w-0.5 bg-slate-100 dark:bg-gray-800"></div>
                    <?php if(empty($logs)): ?>
                        <div class="text-center py-8 opacity-50 relative z-10">
                            <i class="fas fa-mug-hot text-3xl text-slate-300 mb-2"></i>
                            <p class="text-xs text-slate-400 font-medium">Belum ada aktivitas hari ini.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($logs as $log): 
                            $type = $log['type'];
                            $ui = $uiConfig[$type] ?? $uiConfig['silabus'];
                            $isNew = $log['created_at'] === $log['updated_at'];
                            $actionLabel = $isNew ? 'New' : 'Update';
                            $actionColor = $isNew ? 'text-emerald-600 bg-emerald-50' : 'text-amber-600 bg-amber-50';
                            $timeAgo = date('H:i', strtotime($log['updated_at']));
                        ?>
                        <div class="relative pl-6 group">
                            <div class="absolute left-0 top-1.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 bg-slate-300 group-hover:bg-blue-500 transition-colors z-10"></div>
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400"><?= $timeAgo ?></span>
                                <span class="text-[8px] font-bold px-1.5 py-0.5 rounded <?= $actionColor ?>"><?= $actionLabel ?></span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 line-clamp-1 group-hover:text-blue-600 transition-colors cursor-pointer" title="<?= esc($log['title']) ?>">
                                <?= esc($log['title']) ?>
                            </h4>
                            <p class="text-[10px] text-slate-500 mt-0.5 flex items-center gap-1">
                                <span class="w-1 h-1 rounded-full bg-slate-400"></span> <?= ucfirst($type) ?> &bull; <?= esc($log['kode_jenjang']) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?= $this->endSection() ?>