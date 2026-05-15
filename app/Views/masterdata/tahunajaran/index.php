<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Master Tahun Ajaran
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // Ambil variabel dari controller
    $currentUnit = $current_filter['unit'] ?? '';
    $search      = $current_filter['search'] ?? '';
    
    // Helper badge warna
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
?>

<div class="max-w-7xl mx-auto space-y-6">

    <!-- 1. FITUR NAVIGASI (BREADCRUMB) -->
    <nav class="flex text-sm text-slate-500" aria-label="Breadcrumb">
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
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Tahun Ajaran</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 2. Header & Tombol (Perfect Alignment Fix) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Master Tahun Ajaran</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pengaturan Periode Akademik & Semester</p>
                <!-- Indikator Role & Unit -->
                <?php if($is_restricted): ?>
                     <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">Unit: <?= esc(session('kode_jenjang')) ?></span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">Global View</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 h-10">
            
            <!-- DROPDOWN FILTER UNIT (Khusus Superadmin/Yayasan) -->
            <?php if (!$is_restricted && !empty($jenjang_list)): ?>
                <form action="" method="get" class="h-full flex items-center m-0 p-0">
                    <div class="relative h-full group"> 
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i class="fas fa-filter text-gray-400 text-xs group-hover:text-indigo-500 transition-colors"></i>
                        </div>
                        <select name="unit" onchange="this.form.submit()" 
                                class="h-full pl-9 pr-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase tracking-wide rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer hover:bg-gray-50 transition-colors appearance-none flex items-center"
                                style="height: 40px !important;">
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
                </form>
            <?php endif; ?>

            <!-- Tombol Tambah (h-10) -->
            <a href="<?= base_url('app/masterdata/tahunajaran/new') ?>" 
               class="h-10 inline-flex items-center justify-center gap-2 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 border border-transparent box-border leading-none"
               style="height: 40px !important;">
                <i class="fas fa-plus-circle"></i>
                <span class="hidden sm:inline pt-0.5">Periode Baru</span>
                <span class="sm:hidden pt-0.5">Baru</span>
            </a>
        </div>
    </div>

    <!-- 3. Stats Cards (SOLID MODERAT) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider relative z-10">Total Periode</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= $pager->getTotal('tahun_ajaran') ?></h3>
                <span class="text-xs text-indigo-200 font-medium">Data</span>
            </div>
            <i class="fas fa-calendar-alt absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Aktif -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider relative z-10">Status Aktif</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <?php 
                    // Cari data aktif di halaman ini sebagai indikator cepat
                    $activeOnPage = 0;
                    foreach($tahun_ajaran as $t) { if(strtolower($t['status']) == 'aktif') $activeOnPage++; }
                ?>
                <h3 class="text-3xl font-black text-white"><?= $activeOnPage ?></h3>
                <span class="text-xs text-emerald-200 font-medium">di Halaman Ini</span>
            </div>
            <i class="fas fa-check-circle absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>
        
        <!-- Info Ganjil -->
        <div class="bg-blue-600 rounded-2xl p-5 shadow-lg shadow-blue-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-blue-100 uppercase tracking-wider relative z-10">Semester</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                 <h3 class="text-xl font-black text-white">Ganjil/Genap</h3>
            </div>
            <i class="fas fa-adjust absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Info Unit -->
        <div class="bg-amber-500 rounded-2xl p-5 shadow-lg shadow-amber-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-100 uppercase tracking-wider relative z-10">Unit Filter</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                 <h3 class="text-xl font-black text-white"><?= esc($currentUnit ?: 'SEMUA') ?></h3>
            </div>
            <i class="fas fa-school absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>
    </div>

    <!-- 4. Filter & Table Container -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Filter Bar (Search Only - Unit moved to header) -->
        <div class="border-b border-gray-200 dark:border-white/10 p-4 bg-gray-50/50 dark:bg-white/5">
            <form action="" method="get" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto items-center justify-between">
                <?php if ($currentUnit): ?>
                    <input type="hidden" name="unit" value="<?= esc($currentUnit) ?>">
                <?php endif; ?>

                <div class="relative w-full md:w-96">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari Tahun Ajaran / Keterangan..." class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none">
                </div>
                
                <div class="flex gap-2">
                     <select name="per_page" onchange="this.form.submit()" class="pl-3 pr-8 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
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
                        <th class="px-6 py-4 text-center w-16">ID</th> <!-- KOLOM ID BARU -->
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4 text-center">Semester</th>
                        <th class="px-6 py-4 text-center">Periode</th>
                        <th class="px-6 py-4 text-center">Unit</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php 
                    $pageStart = ($current_filter['per_page'] * ($pager->getCurrentPage('tahun_ajaran') - 1)) + 1;
                    
                    if (empty($tahun_ajaran)): ?>
                        <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                    <?php else: foreach ($tahun_ajaran as $idx => $row): $row = (array)$row; ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 text-center text-gray-400 font-bold"><?= $pageStart + $idx ?></td>
                            
                            <!-- DATA ID (Ditambahkan) -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-[10px] font-mono text-gray-400 select-all bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-600">
                                    #<?= esc($row['id']) ?>
                                </span>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="font-black text-gray-900 dark:text-white text-sm">
                                    <?= esc($row['tahun_ajaran']) ?>
                                </div>
                                <?php if(!empty($row['keterangan'])): ?>
                                    <div class="text-[10px] text-gray-400 mt-0.5"><?= esc($row['keterangan']) ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php if (($row['semester'] ?? '') === 'Ganjil') : ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fas fa-sun mr-1.5 text-[8px]"></i> Ganjil
                                    </span>
                                <?php else : ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wide bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fas fa-moon mr-1.5 text-[8px]"></i> Genap
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center text-xs text-gray-500 font-mono">
                                <?php if (!empty($row['tanggal_mulai']) && !empty($row['tanggal_selesai'])) : ?>
                                    <?= date('d/m/y', strtotime($row['tanggal_mulai'])) ?> - <?= date('d/m/y', strtotime($row['tanggal_selesai'])) ?>
                                <?php else : ?>
                                    <span class="text-gray-300">-</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2 py-1 rounded text-[10px] font-black border shadow-sm uppercase tracking-wide <?= getJenjangBadge($row['kode_jenjang']) ?>">
                                    <?= esc($row['kode_jenjang']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <?php if (strtolower($row['status'] ?? '') === 'aktif') : ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 tracking-widest uppercase">
                                        AKTIF
                                    </span>
                                <?php else : ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 tracking-widest uppercase">
                                        NON-AKTIF
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= base_url('app/masterdata/tahunajaran/edit/' . $row['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-all shadow-sm active:scale-95" 
                                       title="Edit Data">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    
                                    <?php if (strtolower($row['status'] ?? '') !== 'aktif') : ?>
                                    <form action="<?= base_url('app/masterdata/tahunajaran/delete/' . $row['id']) ?>" method="post" class="contents" onsubmit="return confirm('Hapus Tahun Ajaran ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-rose-500 hover:bg-rose-600 text-white rounded-lg transition-all shadow-sm active:scale-95" 
                                                title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/30">
            <?= $pager->links('tahun_ajaran', 'tailwind_pagination') ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>