<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Manajemen Data Pegawai
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $currentJenis = $current_filter['jenis'] ?? 'all';
    $currentUnit  = $current_filter['unit'] ?? '';
    
    // Helper URL untuk tab filter
    $buildTabUrl = function($jenis) use ($current_filter) {
        $params = $current_filter;
        $params['jenis'] = $jenis;
        $params['page_pegawai'] = 1; 
        return current_url() . '?' . http_build_query($params);
    };

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
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Pegawai</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 2. Header & Tombol (Perfect Alignment Fix) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Database Pegawai</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pendidik (Guru) dan Tenaga Kependidikan (Staff)</p>
                <!-- Indikator Role & Unit -->
                <?php if(in_array($role, ['superadmin', 'yayasan'])): ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">Global View</span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">Unit: <?= esc($jenjang) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 h-10">
            <!-- DROPDOWN FILTER UNIT (Khusus Superadmin/Yayasan) -->
            <?php if (in_array($role, ['superadmin', 'yayasan']) && !empty($jenjang_list)): ?>
                <form action="" method="get" class="h-full flex items-center m-0 p-0">
                    <input type="hidden" name="jenis" value="<?= esc($currentJenis) ?>">
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
                                    $val = is_array($j) ? ($j['kode_jenjang'] ?? '-') : ($j->kode_jenjang ?? '-');
                                    $lbl = is_array($j) ? ($j['nama_jenjang'] ?? 'Unit ' . $val) : ($j->nama_jenjang ?? 'Unit ' . $val);
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

            <a href="<?= route_to('pegawai_new') ?>" 
               class="h-full inline-flex items-center justify-center gap-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 border border-transparent box-border leading-none"
               style="height: 40px !important;">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline pt-0.5">Tambah Pegawai</span>
                <span class="sm:hidden pt-0.5">Baru</span>
            </a>
        </div>
    </div>

    <!-- 3. Stats Cards (SOLID MODERAT) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider relative z-10">Total Pegawai</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= number_format($stats['total']) ?></h3>
                <span class="text-xs text-indigo-200 font-medium">Org</span>
            </div>
            <i class="fas fa-users absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Guru -->
        <div class="bg-blue-600 rounded-2xl p-5 shadow-lg shadow-blue-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-blue-100 uppercase tracking-wider relative z-10">Total Guru</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= number_format($stats['total_guru']) ?></h3>
                <span class="text-xs text-blue-200 font-medium">Org</span>
            </div>
            <i class="fas fa-chalkboard-teacher absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Staff -->
        <div class="bg-amber-500 rounded-2xl p-5 shadow-lg shadow-amber-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-100 uppercase tracking-wider relative z-10">Total Staff</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= number_format($stats['total_staff']) ?></h3>
                <span class="text-xs text-amber-100 font-medium">Org</span>
            </div>
            <i class="fas fa-briefcase absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>

        <!-- Aktif -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider relative z-10">Status Aktif</p>
            <div class="flex items-baseline gap-1 mt-1 relative z-10">
                <h3 class="text-3xl font-black text-white"><?= number_format($stats['total_aktif']) ?></h3>
                <span class="text-xs text-emerald-200 font-medium">Org</span>
            </div>
            <i class="fas fa-user-check absolute bottom-4 right-4 text-white/20 text-3xl"></i>
        </div>
    </div>

    <!-- 4. Filter & Table Container -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="border-b border-gray-200 dark:border-white/10 p-4 flex flex-col md:flex-row justify-between gap-4 items-center bg-gray-50/50 dark:bg-white/5">
            <!-- Tabs -->
            <div class="flex p-1 bg-gray-200 dark:bg-gray-700 rounded-xl">
                <a href="<?= $buildTabUrl('all') ?>" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all no-underline <?= $currentJenis == 'all' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700' ?>">Semua</a>
                <a href="<?= $buildTabUrl('guru') ?>" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all no-underline <?= $currentJenis == 'guru' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-indigo-600' ?>">Guru</a>
                <a href="<?= $buildTabUrl('staff') ?>" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all no-underline <?= $currentJenis == 'staff' ? 'bg-white shadow text-amber-600' : 'text-gray-500 hover:text-amber-600' ?>">Tendik/Staff</a>
            </div>

            <!-- Search -->
            <form action="" method="get" class="flex gap-2 w-full md:w-auto">
                <input type="hidden" name="jenis" value="<?= esc($currentJenis) ?>">
                <?php if ($currentUnit): ?>
                    <input type="hidden" name="unit" value="<?= esc($currentUnit) ?>">
                <?php endif; ?>

                <div class="relative w-full md:w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="search" value="<?= esc($current_filter['search']) ?>" placeholder="Cari Nama / NIP..." class="w-full pl-9 pr-4 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none">
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-6 py-4 text-center w-12">No</th>
                        <th class="px-6 py-4">Nama Pegawai</th>
                        <th class="px-6 py-4">Status & Jabatan</th>
                        <th class="px-6 py-4 text-center">Unit</th>
                        <th class="px-6 py-4 text-center">NIP / Identitas</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php 
                    $startNo = ($current_page - 1) * $per_page + 1;
                    
                    if (empty($pegawai_data)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                    <?php else: foreach ($pegawai_data as $row): $p = (array)$row; ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/5 transition-colors group">
                            <td class="px-6 py-4 text-center text-gray-400 font-bold"><?= $startNo++ ?></td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 dark:text-white text-sm">
                                    <?= esc(($p['gelar_depan'] ? $p['gelar_depan'].' ' : '') . $p['nama_lengkap'] . ($p['gelar_belakang'] ? ', '.$p['gelar_belakang'] : '')) ?>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded border uppercase tracking-tight <?= ($p['jenis_pegawai'] == 'guru') ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-amber-50 text-amber-600 border-amber-100' ?>">
                                        <?= strtoupper($p['jenis_pegawai']) ?>
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        <?= ($p['jenis_kelamin'] == 'L') ? '<i class="fas fa-mars text-blue-400" title="Laki-laki"></i>' : '<i class="fas fa-venus text-pink-400" title="Perempuan"></i>' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-gray-700 dark:text-gray-300"><?= esc($p['jenis_ptk'] ?? '-') ?></div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-tight"><?= esc($p['status_kepegawaian'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2 py-1 rounded text-[10px] font-black border shadow-sm uppercase tracking-wide <?= getJenjangBadge($p['kode_jenjang']) ?>">
                                    <?= esc($p['kode_jenjang']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-gray-500 tracking-tight">
                                <?= esc($p['nip'] ?: ($p['nipy'] ?: $p['nik'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= route_to('pegawai_show', $p['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white rounded-lg transition-all shadow-sm active:scale-95" 
                                       title="Detail Profil">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="<?= route_to('pegawai_edit', $p['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition-all shadow-sm active:scale-95" 
                                       title="Edit Data">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form action="<?= route_to('pegawai_delete', $p['id']) ?>" method="post" class="contents" onsubmit="return confirm('Hapus data pegawai ini?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white rounded-lg transition-all shadow-sm active:scale-95" 
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
            <?php if(isset($pager_obj)): ?>
                <?= $pager_obj->links('pegawai', 'tailwind_pagination') ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>