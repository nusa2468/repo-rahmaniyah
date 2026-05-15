<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    // Inisialisasi Identitas & Filter
    $session_unit = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $is_restricted = ($session_unit !== 'GLOBAL');
    
    // Ambil data filter dari URL
    $filter_jenjang = $filter_jenjang ?? '';
    $filter_keyword = $filter_keyword ?? '';

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
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Evaluasi Belajar</span>
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
            <h1 class="text-3xl font-black text-gray-800 dark:text-white tracking-tight">Evaluasi Belajar</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <?= $is_restricted ? "Unit: $session_unit" : "Mode: Superadmin" ?>
                </span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <p class="text-sm text-gray-500">Manajemen Jadwal Ujian & Tugas</p>
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

            <!-- 2. Tombol Tambah Manual -->
            <a href="<?= base_url('app/pembelajaran/evaluasi-belajar/new') ?>" class="h-10 flex-grow lg:flex-none inline-flex justify-center items-center px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg hover:shadow-indigo-500/30 transition-all transform active:scale-95 gap-2">
                <i class="fas fa-plus"></i>
                <span>Buat Jadwal</span>
            </a>
        </div>
    </div>

    <!-- 2. STATISTIK (SOLID MODERAT) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider relative z-10">Total Evaluasi</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10">
                <?= number_format($stats['total'] ?? 0) ?>
            </p>
        </div>
        
        <!-- Kuis (Blue) -->
        <div class="bg-blue-600 rounded-2xl p-5 shadow-lg shadow-blue-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-blue-100 uppercase tracking-wider relative z-10">Kuis / Latihan</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= number_format($stats['kuis'] ?? 0) ?></p>
        </div>
        
        <!-- Tugas (Amber) -->
        <div class="bg-amber-500 rounded-2xl p-5 shadow-lg shadow-amber-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-amber-100 uppercase tracking-wider relative z-10">Tugas / Proyek</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= number_format($stats['tugas'] ?? 0) ?></p>
        </div>
        
        <!-- Published (Emerald) -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-500/30 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider relative z-10">Published</p>
            <p class="text-3xl font-black text-white mt-1 relative z-10"><?= number_format($stats['published'] ?? 0) ?></p>
        </div>
    </div>

    <!-- FILTER SECTION (Unit Selection Removed) -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Hidden Input: Pertahankan filter unit saat filter lain digunakan -->
            <input type="hidden" name="kode_jenjang" value="<?= esc($filter_jenjang) ?>">

            <!-- Search Only -->
            <div class="w-full">
                <label for="keyword" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pencarian</label>
                <div class="relative">
                    <input type="text" name="keyword" id="keyword" value="<?= esc($filter_keyword) ?>" placeholder="Cari Judul Evaluasi..." class="block w-full h-10 pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-2.5 border focus:ring-indigo-500 focus:border-indigo-500">
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
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Judul & Jenis</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Waktu & Durasi</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(!empty($evaluasi)): ?>
                        <?php foreach ($evaluasi as $item): ?>
                            <?php if ($is_restricted && strtoupper($item['kode_jenjang']) !== $session_unit) continue; ?>
                            
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100"><?= esc($item['judul_evaluasi']) ?></div>
                                    <div class="flex gap-2 mt-1">
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded bg-indigo-100 text-indigo-700 uppercase"><?= $item['jenis_evaluasi'] ?></span>
                                        <span class="text-[9px] text-gray-400 font-bold uppercase"><?= $item['nama_mapel'] ?></span>
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-1"><?= $item['kode_jenjang'] ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-gray-600 dark:text-gray-300">
                                        <i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y H:i', strtotime($item['tanggal_mulai'])) ?>
                                    </div>
                                    <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase">
                                        <i class="far fa-clock mr-1"></i> <?= $item['durasi'] ?> Menit | KKM: <?= $item['kkm'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold <?= ($item['status'] == 'Published') ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $item['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center gap-2">
                                        <!-- Detail -->
                                        <a href="<?= base_url('app/pembelajaran/evaluasi-belajar/' . $item['id']) ?>" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="<?= base_url('app/pembelajaran/evaluasi-belajar/edit/' . $item['id']) ?>" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Data">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00 2 2h11a2 2 0 00 2-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="<?= base_url('app/pembelajaran/evaluasi-belajar/delete/' . $item['id']) ?>" method="post" class="inline" onsubmit="return confirm('Hapus jadwal evaluasi ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Data">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
                                    <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    <p class="text-gray-500 font-medium">Belum ada jadwal evaluasi.</p>
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
        <?= $pager->links('evaluasi', 'tailwind_pagination') ?>
    </div>
</div>
<?= $this->endSection() ?>