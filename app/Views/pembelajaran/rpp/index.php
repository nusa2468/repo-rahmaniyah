<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    // Inisialisasi Identitas & Filter
    $session_unit = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $is_restricted = ($session_unit !== 'GLOBAL');
    
    // Ambil data filter dari URL
    $filter_jenjang = $filter_jenjang ?? '';
    $filter_keyword = $filter_keyword ?? '';
    $filter_kurikulum = $filter_kurikulum ?? '';

    // Proteksi: Jika admin unit, paksa filter_jenjang ke unitnya sendiri
    if ($is_restricted) {
        $filter_jenjang = $session_unit;
    }

    // [UPDATE] Ambil Data Jenjang Dinamis (Khusus Admin Pusat)
    $list_jenjang = [];
    if (!$is_restricted) {
        try {
            $jenjangModel = new \App\Models\JenjangModel();
            $list_jenjang = $jenjangModel->getDropdownOptions();
        } catch (\Throwable $e) {}
    }
?>

<div class="container mx-auto px-4 py-8 space-y-6">

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
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">RPP</span>
                </div>
            </li>
        </ol>
    </nav>
    
    <!-- NOTIFIKASI FLASH MESSAGE -->
    <?php foreach (['message' => 'emerald', 'error' => 'rose', 'info' => 'blue', 'warning' => 'amber'] as $key => $color): ?>
        <?php if (session()->getFlashdata($key)) : ?>
            <div class="bg-<?= $color ?>-50 border-l-4 border-<?= $color ?>-500 p-4 rounded-r-xl shadow-sm mb-6 flex items-start gap-3 animate-fade-in-down">
                <div class="text-<?= $color ?>-500 mt-0.5">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-<?= $color ?>-800 uppercase tracking-widest mb-0.5">
                        <?= strtoupper($key) ?>
                    </p>
                    <p class="text-sm font-medium text-<?= $color ?>-700 leading-relaxed">
                        <?= session()->getFlashdata($key) ?>
                    </p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-<?= $color ?>-400 hover:text-<?= $color ?>-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- HEADER HALAMAN (LEVEL ALIGNMENT) -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
        <div>
            <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">RPP & Modul Ajar</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <?= $is_restricted ? "Unit: $session_unit" : "Mode: Superadmin" ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <p class="text-sm text-gray-500">Manajemen Rencana Pelaksanaan Pembelajaran</p>
            </div>
        </div>
        
        <!-- ACTION GROUP (Rata Tinggi h-10) -->
        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
            
            <!-- 1. DROPDOWN UNIT DINAMIS (Khusus Superadmin) -->
            <?php if (!$is_restricted): ?>
                <form action="" method="get" class="flex-grow lg:flex-none">
                    <div class="relative h-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-gray-400 text-xs"></i>
                        </div>
                        <select name="kode_jenjang" onchange="this.form.submit()" 
                                class="h-full pl-9 pr-8 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm font-semibold cursor-pointer">
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

            <!-- 2. Tombol Generate Bahan Ajar Massal -->
            <form action="<?= base_url('app/pembelajaran/bahan-ajar/generate_massal') ?>" method="post" class="flex-grow lg:flex-none" onsubmit="return confirm('Sistem akan membuat draf BAHAN AJAR untuk semua RPP yang belum memiliki materi. Lanjutkan?');">
                <?= csrf_field() ?>
                <button type="submit" class="h-10 w-full inline-flex justify-center items-center px-4 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-bold rounded-lg shadow-sm transition-all hover:shadow-md gap-2">
                    <i class="fas fa-book-open text-emerald-500"></i>
                    <span>Auto Bahan Ajar</span>
                </button>
            </form>

            <!-- 3. Tombol Tambah Manual -->
            <a href="<?= base_url('app/pembelajaran/rpp/new') ?>" class="h-10 flex-grow lg:flex-none inline-flex justify-center items-center px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg hover:shadow-indigo-500/30 transition-all transform active:scale-95 gap-2">
                <i class="fas fa-plus"></i>
                <span>Buat RPP</span>
            </a>
        </div>
    </div>

    <!-- 2. STATISTIK (SOLID MODERAT - Vibrant Colors) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider relative z-10">Total RPP</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10">
                <?= number_format($stats['total'] ?? 0) ?>
            </p>
        </div>
        
        <!-- Merdeka -->
        <div class="bg-blue-600 rounded-2xl p-5 shadow-lg shadow-blue-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-blue-100 uppercase tracking-wider relative z-10">Kur. Merdeka</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= number_format($stats['merdeka'] ?? 0) ?></p>
        </div>
        
        <!-- K13 -->
        <div class="bg-amber-600 rounded-2xl p-5 shadow-lg shadow-amber-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-100 uppercase tracking-wider relative z-10">Kurikulum 2013</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= number_format($stats['k13'] ?? 0) ?></p>
        </div>
        
        <!-- Unit Info -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider relative z-10">Unit Sekolah</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= esc($session_unit) ?></p>
        </div>
    </div>

    <!-- FILTER SECTION (Unit Selection Removed from Here) -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Hidden Input: Pertahankan filter unit saat filter lain digunakan -->
            <input type="hidden" name="kode_jenjang" value="<?= esc($filter_jenjang) ?>">

            <div>
                <label for="jenis_kurikulum" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Kurikulum</label>
                <select name="jenis_kurikulum" id="jenis_kurikulum" class="block w-full h-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-2.5 border focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                    <option value="">Semua Kurikulum</option>
                    <option value="Merdeka" <?= $filter_kurikulum == 'Merdeka' ? 'selected' : '' ?>>Merdeka</option>
                    <option value="K13" <?= $filter_kurikulum == 'K13' ? 'selected' : '' ?>>K13</option>
                </select>
            </div>
            
            <div>
                <label for="keyword" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pencarian</label>
                <div class="relative">
                    <input type="text" name="keyword" id="keyword" value="<?= esc($filter_keyword) ?>" placeholder="Cari Topik / Tujuan Pembelajaran..." class="block w-full h-10 pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full h-10 px-6 bg-gray-800 hover:bg-gray-900 text-white font-bold text-sm rounded-lg transition-colors shadow-sm flex justify-center items-center gap-2">
                    <i class="fas fa-filter text-xs"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Unit & Kurikulum</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Pertemuan & Topik</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(!empty($rpp)): ?>
                        <?php foreach ($rpp as $item): ?>
                            <?php 
                                // Double Check Filter (Backup)
                                if ($is_restricted && strtoupper($item['kode_jenjang']) !== $session_unit) continue; 
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white"><?= esc($item['kode_jenjang']) ?></span>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase tracking-wide w-max <?= $item['jenis_kurikulum'] == 'Merdeka' ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-amber-50 text-amber-600 border-amber-100' ?>">
                                            <?= esc($item['jenis_kurikulum']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            Pertemuan Ke-<?= $item['pertemuan_ke'] ?>: <?= esc($item['topik']) ?>
                                        </span>
                                        <span class="text-xs text-gray-500 mt-1 uppercase font-bold tracking-wide">
                                            Silabus: <?= esc($item['silabus_materi'] ?? 'N/A') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $item['status'] == 'Final' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-600 border border-gray-200' ?>">
                                        <?= esc($item['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-top text-right">
                                    <div class="flex justify-end gap-1">
                                        <!-- Detail -->
                                        <a href="<?= base_url('app/pembelajaran/rpp/' . $item['id']) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-600 hover:bg-indigo-50 transition-all" title="Detail RPP">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Cetak PDF -->
                                        <a href="<?= base_url('app/pembelajaran/rpp/print/' . $item['id']) ?>" target="_blank" class="w-8 h-8 rounded-lg flex items-center justify-center text-cyan-600 hover:bg-cyan-50 transition-all" title="Cetak PDF">
                                            <i class="fas fa-print"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="<?= base_url('app/pembelajaran/rpp/edit/' . $item['id']) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-amber-500 hover:bg-amber-50 transition-all" title="Edit RPP">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        <!-- Generate Bahan Ajar -->
                                        <form action="<?= base_url('app/pembelajaran/bahan-ajar/generate/' . $item['id']) ?>" method="post" class="inline" onsubmit="return confirm('Buat draf bahan ajar otomatis dari RPP ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-rose-600 hover:bg-rose-50 transition-all" title="Generate Bahan Ajar">
                                                <i class="fas fa-magic"></i>
                                            </button>
                                        </form>

                                        <!-- Hapus -->
                                        <form action="<?= base_url('app/pembelajaran/rpp/delete/' . $item['id']) ?>" method="post" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus RPP ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition-all" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-gray-500 font-medium">Belum ada data RPP untuk filter ini.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <?= $pager->links('rpp', 'tailwind_pagination') ?>
    </div>
</div>
<?= $this->endSection() ?>