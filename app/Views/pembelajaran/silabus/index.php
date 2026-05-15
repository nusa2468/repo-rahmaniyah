<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    $session_unit = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $is_restricted = ($session_unit !== 'GLOBAL');
    
    $filter_jenjang = $filter_jenjang ?? '';
    $filter_keyword = $filter_keyword ?? '';
    $filter_kurikulum = $filter_kurikulum ?? '';

    if ($is_restricted) {
        $filter_jenjang = $session_unit;
    }

    $list_jenjang = [];
    if (!$is_restricted) {
        try {
            $jenjangModel = new \App\Models\JenjangModel();
            $list_jenjang = $jenjangModel->getDropdownOptions();
        } catch (\Throwable $e) {}
    }
?>

<div class="container mx-auto px-4 py-6 space-y-6 font-sans text-slate-600 dark:text-slate-300">
    
    <!-- 1. FITUR NAVIGASI (BREADCRUMB) -->
    <nav class="flex text-sm text-slate-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="<?= base_url('app/pembelajaran') ?>" class="inline-flex items-center hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/></svg>
                    Pembelajaran
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Silabus</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- NOTIFIKASI -->
    <?php foreach (['message' => 'emerald', 'error' => 'rose', 'info' => 'blue'] as $key => $color): ?>
        <?php if (session()->getFlashdata($key)) : ?>
            <div class="rounded-lg bg-<?= $color ?>-50 border-l-4 border-<?= $color ?>-500 p-4 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-info-circle text-<?= $color ?>-500"></i>
                    <div>
                        <p class="text-xs font-bold text-<?= $color ?>-800 uppercase tracking-wide"><?= strtoupper($key) ?></p>
                        <p class="text-sm text-<?= $color ?>-700"><?= session()->getFlashdata($key) ?></p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-<?= $color ?>-400 hover:text-<?= $color ?>-600"><i class="fas fa-times"></i></button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- 2. HEADER & TOMBOL (LEVEL ALIGNMENT) -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Manajemen Silabus</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    <?= $is_restricted ? "Unit: $session_unit" : "Mode: Superadmin" ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <p class="text-sm text-slate-500">Pusat Data Kurikulum & Rencana Pembelajaran</p>
            </div>
        </div>
        
        <!-- Action Group: Menggunakan h-10 agar semua tinggi sama -->
        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
            
            <?php if (!$is_restricted): ?>
                <form action="" method="get" class="flex-grow lg:flex-none">
                    <div class="relative h-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-slate-400 text-xs"></i>
                        </div>
                        <select name="kode_jenjang" onchange="this.form.submit()" 
                                class="h-full pl-9 pr-8 w-full bg-white dark:bg-gray-800 border border-slate-300 dark:border-gray-600 text-slate-700 dark:text-gray-200 text-sm rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm font-semibold cursor-pointer">
                            <option value="">Semua Unit</option>
                            <?php if (!empty($list_jenjang)): ?>
                                <?php foreach ($list_jenjang as $jenjang): ?>
                                    <option value="<?= esc($jenjang['kode_jenjang']) ?>" <?= $filter_jenjang == $jenjang['kode_jenjang'] ? 'selected' : '' ?>>
                                        <?= esc($jenjang['nama_jenjang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            <?php endif; ?>

            <form action="<?= base_url('app/pembelajaran/rpp/generate') ?>" method="post" class="flex-grow lg:flex-none" onsubmit="return confirm('Generate RPP otomatis untuk silabus yang belum lengkap?');">
                <?= csrf_field() ?>
                <button type="submit" class="h-10 w-full inline-flex justify-center items-center px-4 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-sm font-bold rounded-lg shadow-sm transition-all hover:shadow-md gap-2">
                    <i class="fas fa-magic text-emerald-500"></i>
                    <span>Auto RPP</span>
                </button>
            </form>

            <a href="<?= base_url('app/pembelajaran/silabus/new') ?>" class="h-10 flex-grow lg:flex-none inline-flex justify-center items-center px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg hover:shadow-indigo-500/30 transition-all transform active:scale-95 gap-2">
                <i class="fas fa-plus"></i>
                <span>Buat Baru</span>
            </a>
        </div>
    </div>

    <!-- 3. KARTU KPI (SOLID CALM - UNIFORM STYLE) -->
    <!-- Semua kartu kini memiliki gaya konsisten: Background Putih/Dark, Border Tipis, Aksen Warna pada Angka/Ikon -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-slate-50 dark:bg-slate-700/30 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">Total Dokumen</p>
            <p class="text-3xl font-black text-slate-800 dark:text-white mt-1 relative z-10">
                <?php 
                    if ($is_restricted) {
                        $key = strtolower($session_unit);
                        echo number_format($stats[$key] ?? 0);
                    } else {
                        echo number_format($stats['total'] ?? 0);
                    }
                ?>
            </p>
        </div>

        <!-- Merdeka (Updated Style) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">Kur. Merdeka</p>
            <p class="text-3xl font-black text-blue-600 dark:text-blue-400 mt-1 relative z-10"><?= number_format($stats['merdeka'] ?? 0) ?></p>
        </div>

        <!-- K13 -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 dark:bg-amber-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">Kurikulum 2013</p>
            <p class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-1 relative z-10"><?= number_format($stats['k13'] ?? 0) ?></p>
        </div>

        <!-- Status Final -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-gray-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider relative z-10">Siap Mengajar</p>
            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1 relative z-10"><?= number_format($stats['final'] ?? 0) ?></p>
        </div>
    </div>

    <!-- FILTER & DATA -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-5 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-900/50">
            <form action="" method="get" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="kode_jenjang" value="<?= esc($filter_jenjang) ?>">

                <div class="w-full md:w-1/4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kurikulum</label>
                    <select name="jenis_kurikulum" class="w-full h-10 rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="Merdeka" <?= $filter_kurikulum == 'Merdeka' ? 'selected' : '' ?>>Merdeka</option>
                        <option value="K13" <?= $filter_kurikulum == 'K13' ? 'selected' : '' ?>>K13</option>
                    </select>
                </div>
                
                <div class="w-full md:w-2/4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" name="keyword" value="<?= esc($filter_keyword) ?>" placeholder="Cari mapel, topik, atau kompetensi..." class="w-full h-10 pl-10 rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                    </div>
                </div>
                
                <div class="w-full md:w-auto">
                    <button type="submit" class="h-10 px-6 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white dark:bg-gray-800 border-b border-slate-200 dark:border-gray-700 text-xs uppercase text-slate-400 font-bold">
                        <th class="px-6 py-4">Mata Pelajaran & Identitas</th>
                        <th class="px-6 py-4">Materi & Kompetensi</th>
                        <th class="px-6 py-4 text-center">Status RPP</th>
                        <th class="px-6 py-4 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-gray-700">
                    <?php if(!empty($silabus)): ?>
                        <?php foreach ($silabus as $s): ?>
                            <?php if ($is_restricted && strtoupper($s['kode_jenjang']) !== $session_unit) continue; ?>
                            
                            <!-- Main Row -->
                            <tr class="group hover:bg-slate-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" onclick="toggleRppRow('rpp-row-<?= $s['id'] ?>', this)">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white"><?= esc($s['nama_mapel']) ?></span>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-bold border border-slate-200 uppercase tracking-wide">
                                                <?= $s['kode_jenjang'] ?> • Kls <?= $s['tingkat_kelas'] ?>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wide <?= ($s['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' ?>">
                                                <?= esc($s['jenis_kurikulum'] ?? '-') ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 line-clamp-1" title="<?= esc($s['materi_pokok']) ?>">
                                        <?= esc($s['materi_pokok']) ?>
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if ($s['jenis_kurikulum'] == 'K13'): ?>
                                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-1.5 rounded">KD <?= substr(esc($s['kompetensi_dasar'] ?? '?'), 0, 5) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 rounded">Fase <?= esc($s['fase'] ?? '-') ?></span>
                                        <?php endif; ?>
                                        <span class="text-[10px] text-slate-400">TA <?= $s['tahun_ajaran'] ?> (<?= $s['semester'] ?>)</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 align-top text-center">
                                    <?php $count = $s['jumlah_rpp'] ?? 0; ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= $count ?> Pertemuan
                                    </span>
                                </td>

                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex justify-end gap-1" onclick="event.stopPropagation()">
                                        <button class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" onclick="toggleRppRow('rpp-row-<?= $s['id'] ?>', this.closest('tr'))">
                                            <i class="fas fa-chevron-down transform transition-transform" id="icon-<?= $s['id'] ?>"></i>
                                        </button>
                                        <a href="<?= base_url('app/pembelajaran/silabus/edit/' . $s['id']) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-all">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="<?= base_url('app/pembelajaran/silabus/delete/' . $s['id']) ?>" method="post" onsubmit="return confirm('Hapus data ini?');" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Nested RPP Row -->
                            <tr id="rpp-row-<?= $s['id'] ?>" class="hidden bg-slate-50 dark:bg-gray-900/30">
                                <td colspan="4" class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-3 ml-2">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                                            <i class="fas fa-list-ul"></i> Rincian Pertemuan
                                        </h4>
                                        <form action="<?= base_url('app/pembelajaran/rpp/new') ?>" method="get">
                                            <input type="hidden" name="silabus_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 bg-white px-3 py-1 rounded-md shadow-sm border border-slate-200 hover:shadow-md transition-all">
                                                <i class="fas fa-plus"></i> Tambah
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <?php if(!empty($s['rpp_list'])): ?>
                                        <div class="ml-2 bg-white dark:bg-gray-800 rounded-lg border border-slate-200 dark:border-gray-700 overflow-hidden">
                                            <table class="w-full text-xs">
                                                <thead class="bg-slate-100 dark:bg-gray-700 text-slate-500">
                                                    <tr>
                                                        <th class="px-4 py-2 w-12 text-center">#</th>
                                                        <th class="px-4 py-2 text-left">Topik & Tujuan</th>
                                                        <th class="px-4 py-2 text-center w-24">Status</th>
                                                        <th class="px-4 py-2 text-right w-24">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    <?php foreach($s['rpp_list'] as $rpp): ?>
                                                        <tr class="hover:bg-slate-50">
                                                            <td class="px-4 py-3 text-center font-bold text-slate-400"><?= $rpp['pertemuan_ke'] ?></td>
                                                            <td class="px-4 py-3">
                                                                <p class="font-bold text-slate-700 dark:text-slate-300"><?= esc($rpp['topik']) ?></p>
                                                                <p class="text-slate-500 truncate w-96"><?= strip_tags($rpp['tujuan_pembelajaran'] ?? '') ?></p>
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $rpp['status'] == 'Final' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                                                    <?= $rpp['status'] ?>
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-3 text-right">
                                                                <div class="flex justify-end gap-2">
                                                                    <a href="<?= base_url('app/pembelajaran/rpp/print/'.$rpp['id']) ?>" target="_blank" class="text-slate-400 hover:text-sky-600"><i class="fas fa-print"></i></a>
                                                                    <a href="<?= base_url('app/pembelajaran/rpp/edit/'.$rpp['id']) ?>" class="text-slate-400 hover:text-indigo-600"><i class="fas fa-pen"></i></a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="ml-2 p-4 bg-white border border-dashed border-slate-300 rounded-lg text-center text-xs text-slate-400">
                                            Belum ada data RPP. Klik tombol <b>Tambah</b> di atas.
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?= $pager->links('silabus', 'tailwind_pagination') ?>
    </div>
</div>

<script>
    function toggleRppRow(rowId, mainRow) {
        const row = document.getElementById(rowId);
        const icon = mainRow.querySelector('.fa-chevron-down');
        
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            mainRow.classList.add('bg-slate-50', 'dark:bg-gray-700/50');
            if(icon) icon.style.transform = 'rotate(180deg)';
        } else {
            row.classList.add('hidden');
            mainRow.classList.remove('bg-slate-50', 'dark:bg-gray-700/50');
            if(icon) icon.style.transform = 'rotate(0deg)';
        }
    }
</script>
<?= $this->endSection() ?>