<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Master Mata Pelajaran
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // --- 1. DATA PROCESSING ---
    $request     = \Config\Services::request();
    $currentUnit = $request->getGet('unit') ?? '';
    $currentTingkat = $request->getGet('tingkat') ?? '';
    $search      = $request->getGet('search') ?? '';
    $mapelData   = $mapel_list ?? [];
    
    // Perhitungan Ringkasan
    $totalJP = 0;
    $countAktif = 0;
    foreach ($mapelData as $row) {
        $totalJP += (int)($row['jumlah_jp'] ?? 0);
        if (strtolower($row['status'] ?? '') === 'aktif') $countAktif++;
    }

    // Helper badge warna jenjang
    if (!function_exists('getJenjangBadge')) {
        function getJenjangBadge($kode) {
            $kode = strtoupper($kode ?? '');
            return match ($kode) {
                'SD', 'MI'        => 'bg-rose-100 text-rose-700 border-rose-200',
                'SMP', 'MTS'      => 'bg-sky-100 text-sky-700 border-blue-200',
                'SMA', 'SMK', 'MA'=> 'bg-indigo-100 text-indigo-700 border-indigo-200',
                'TK', 'PAUD'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                default           => 'bg-gray-100 text-gray-600 border-gray-200',
            };
        }
    }
    
    // Helper badge warna tingkat
    if (!function_exists('getTingkatBadge')) {
        function getTingkatBadge($tingkat) {
            $t = (int)$tingkat;
            if ($t <= 6) return 'bg-orange-50 text-orange-600 border-orange-100'; // SD
            if ($t <= 9) return 'bg-blue-50 text-blue-600 border-blue-100';     // SMP
            return 'bg-purple-50 text-purple-600 border-purple-100';            // SMA
        }
    }
?>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- 1. HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <nav class="flex text-sm text-slate-500 mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="<?= base_url('app/masterdata/dashboard') ?>" class="inline-flex items-center hover:text-indigo-600 transition-colors text-[10px] font-black uppercase tracking-widest">
                            Master Data
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mx-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 6 10"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                            <span class="ml-1 font-black text-slate-800 dark:text-white md:ml-2 text-[10px] uppercase tracking-widest">Mata Pelajaran</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Master Mata Pelajaran</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manajemen Beban Belajar & Kurikulum</p>
                <?php if(isset($is_restricted) && $is_restricted): ?>
                     <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">Unit: <?= esc(session('kode_jenjang')) ?></span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">Global View</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 h-10">
            <!-- Tombol Tambah -->
            <a href="<?= base_url('app/masterdata/matapelajaran/new') ?>" 
               class="h-10 inline-flex items-center justify-center gap-2 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 border border-transparent box-border leading-none">
                <i class="fas fa-plus-circle"></i>
                <span class="hidden sm:inline pt-0.5">Mapel Baru</span>
                <span class="sm:hidden pt-0.5">Baru</span>
            </a>
        </div>
    </div>

    <!-- 2. STATS CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-sky-600 rounded-2xl p-5 shadow-lg shadow-sky-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-sky-100 uppercase tracking-wider relative z-10">Total Mapel</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= $pager->getTotal('mapel') ?></h3>
                <span class="text-xs text-sky-200 font-medium">Data</span>
            </div>
            <i class="fas fa-book absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Total JP -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider relative z-10">Total Beban</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= $totalJP ?></h3>
                <span class="text-xs text-emerald-200 font-medium">Jam Pelajaran</span>
            </div>
            <i class="fas fa-clock absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>
        
        <!-- Status Aktif -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider relative z-10">Mapel Aktif</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                 <h3 class="text-3xl font-black text-white"><?= $countAktif ?></h3>
                 <span class="text-xs text-indigo-200 font-medium">di Halaman Ini</span>
            </div>
            <i class="fas fa-check-circle absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Info Unit Filter -->
        <div class="bg-amber-500 rounded-2xl p-5 shadow-lg shadow-amber-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-100 uppercase tracking-wider relative z-10">Unit Filter</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                 <h3 class="text-xl font-black text-white"><?= esc($currentUnit ?: 'SEMUA') ?></h3>
            </div>
            <i class="fas fa-school absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>
    </div>

    <!-- Alert Flashdata -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center text-emerald-700 text-sm font-bold">
                <i class="fas fa-check-circle text-lg mr-3"></i> <?= session()->getFlashdata('success') ?>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600 transition-colors"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- 3. MAIN CONTENT (FILTER & TABLE) -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="border-b border-gray-200 dark:border-white/10 p-4 bg-gray-50/50 dark:bg-white/5">
            <form action="" method="get" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto items-center justify-between">
                <?php if ($currentUnit): ?>
                    <input type="hidden" name="unit" value="<?= esc($currentUnit) ?>">
                <?php endif; ?>

                <!-- Filter Unit (Dropdown) - Muncul jika tidak restricted -->
                <?php if ((!isset($is_restricted) || !$is_restricted) && !empty($jenjang_list)): ?>
                    <div class="relative h-full group w-full md:w-48"> 
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i class="fas fa-filter text-gray-400 text-xs group-hover:text-indigo-500 transition-colors"></i>
                        </div>
                        <select name="unit" onchange="this.form.submit()" 
                                class="w-full pl-9 pr-8 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase tracking-wide rounded-xl shadow-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none cursor-pointer hover:bg-gray-50 transition-colors appearance-none">
                            <option value="">Semua Unit</option>
                            <?php foreach ($jenjang_list as $j): ?>
                                <?php 
                                    $val = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                    $lbl = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                                    $sel = ($currentUnit === $val) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($val) ?>" <?= $sel ?>><?= esc($lbl) ?> (<?= esc($val) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter Tingkat (Smart) -->
                <div class="relative w-full md:w-40 group">
                    <select name="tingkat" onchange="this.form.submit()" 
                            class="w-full pl-3 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-sky-500 cursor-pointer outline-none appearance-none">
                        <option value="">Semua Tingkat</option>
                        <?php 
                        // Menggunakan data 'available_levels' dari controller
                        $levels = $available_levels ?? range(1, 12); 
                        foreach($levels as $lvl): 
                        ?>
                            <option value="<?= $lvl ?>" <?= ($currentTingkat == $lvl) ? 'selected' : '' ?>>
                                <?= ($lvl == 0) ? 'TK/PAUD' : 'Tingkat ' . $lvl ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari Mapel / Kode..." class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all outline-none">
                </div>
                
                <!-- Per Page -->
                <div class="flex gap-2">
                     <select name="per_page" onchange="this.form.submit()" class="pl-3 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-sky-500 cursor-pointer appearance-none">
                        <option value="10" <?= ($current_filter['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10 Data</option>
                        <option value="25" <?= ($current_filter['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25 Data</option>
                        <option value="50" <?= ($current_filter['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50 Data</option>
                    </select>
                    <button type="submit" class="px-6 py-2.5 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 transition-all shadow-sm">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-center w-12">No</th>
                        <th class="px-6 py-4 text-center w-24">
                            <span class="flex items-center justify-center gap-1 cursor-pointer group" title="Urutkan Unit">
                                Unit <i class="fas fa-sort text-[8px] text-gray-300 group-hover:text-gray-500"></i>
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center w-20">
                            <span class="flex items-center justify-center gap-1 cursor-pointer group" title="Urutkan Tingkat">
                                Tk <i class="fas fa-sort text-[8px] text-gray-300 group-hover:text-gray-500"></i>
                            </span>
                        </th>
                        <!-- KOLOM BARU SEMESTER -->
                        <th class="px-6 py-4 text-center w-20">
                            <span class="flex items-center justify-center gap-1 cursor-pointer group" title="Urutkan Semester">
                                Smt <i class="fas fa-sort text-[8px] text-gray-300 group-hover:text-gray-500"></i>
                            </span>
                        </th>
                        <th class="px-6 py-4">
                            <span class="flex items-center gap-1 cursor-pointer group">
                                Mata Pelajaran <i class="fas fa-sort text-[8px] text-gray-300 group-hover:text-gray-500"></i>
                            </span>
                        </th>
                        <th class="px-6 py-4 text-center">Kelompok</th>
                        <th class="px-6 py-4 text-center">JP</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php 
                    $pageStart = ($current_filter['per_page'] * ($pager->getCurrentPage('mapel') - 1)) + 1;
                    
                    if (empty($mapelData)): ?>
                        <tr><td colspan="9" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                    <?php else: foreach ($mapelData as $idx => $row): $row = (array)$row; ?>
                        <tr class="hover:bg-sky-50/30 dark:hover:bg-white/5 transition-colors group">
                            <!-- 1. No -->
                            <td class="px-6 py-4 text-center text-gray-400 font-bold"><?= $pageStart + $idx ?></td>
                            
                            <!-- 2. Unit -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black border shadow-sm uppercase tracking-wide <?= getJenjangBadge($row['kode_jenjang']) ?>">
                                    <?= esc($row['kode_jenjang']) ?>
                                </span>
                            </td>

                            <!-- 3. Tingkat -->
                            <td class="px-6 py-4 text-center">
                                <?php if (!empty($row['tingkat'])): ?>
                                    <span class="inline-flex w-7 h-7 items-center justify-center rounded-full text-[10px] font-bold border shadow-sm <?= getTingkatBadge($row['tingkat']) ?>">
                                        <?= esc($row['tingkat']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- 4. Semester (NEW) -->
                            <td class="px-6 py-4 text-center">
                                <?php if (!empty($row['semester'])): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-gray-50 text-gray-600 border border-gray-200 uppercase tracking-wide">
                                        <?= esc($row['semester']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- 5. Mapel -->
                            <td class="px-6 py-4">
                                <div class="font-black text-gray-900 dark:text-white text-sm">
                                    <?= esc($row['nama_mapel']) ?>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5 font-mono flex items-center gap-2">
                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-500 border border-gray-200">
                                        <?= esc($row['kode_mapel']) ?>
                                    </span>
                                    <span class="text-gray-300">|</span>
                                    <span>ID: <?= esc($row['id']) ?></span>
                                </div>
                            </td>

                            <!-- 6. Kelompok -->
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="text-xs font-bold text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-200">
                                        <?= esc($row['kelompok'] ?: '-') ?>
                                    </span>
                                    <span class="text-[9px] text-gray-400 mt-1 truncate max-w-[100px]" title="<?= esc($row['nama_kurikulum']) ?>">
                                        <?= esc($row['nama_kurikulum'] ?? '-') ?>
                                    </span>
                                </div>
                            </td>

                            <!-- 7. JP -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-black text-gray-700"><?= esc($row['jumlah_jp']) ?></span>
                                <span class="text-[9px] text-gray-400">JP</span>
                            </td>

                            <!-- 8. Status -->
                            <td class="px-6 py-4 text-center">
                                <?php if (strtolower($row['status'] ?? '') === 'aktif') : ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> AKTIF
                                    </span>
                                <?php else : ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-500 border border-gray-200 uppercase tracking-wide">
                                        NON-AKTIF
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- 9. Aksi -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <!-- Tombol Detail -->
                                    <a href="<?= base_url('app/masterdata/matapelajaran/show/' . $row['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-white border border-gray-200 text-sky-500 rounded-lg shadow-sm hover:border-sky-400 hover:bg-sky-50 transition-all active:scale-95" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>

                                    <a href="<?= base_url('app/masterdata/matapelajaran/edit/' . $row['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-white border border-gray-200 text-amber-500 rounded-lg shadow-sm hover:border-amber-400 hover:bg-amber-50 transition-all active:scale-95" 
                                       title="Edit Data">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    
                                    <form action="<?= base_url('app/masterdata/matapelajaran/delete/' . $row['id']) ?>" method="post" class="contents" onsubmit="return confirm('Hapus Mata Pelajaran <?= esc($row['nama_mapel']) ?>? Data akademik terkait akan terpengaruh.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-white border border-gray-200 text-rose-500 rounded-lg shadow-sm hover:border-rose-400 hover:bg-rose-50 transition-all active:scale-95" 
                                                title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/30">
            <?= $pager->links('mapel', 'tailwind_pagination') ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>