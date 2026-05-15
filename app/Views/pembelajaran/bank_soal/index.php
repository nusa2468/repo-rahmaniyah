<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    // Inisialisasi Identitas & Filter
    $session_unit = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $is_restricted = ($session_unit !== 'GLOBAL');
    
    $filter_jenjang = $filter_jenjang ?? '';
    $filter_keyword = $filter_keyword ?? '';

    if ($is_restricted) {
        $filter_jenjang = $session_unit;
    }
?>

<div class="container mx-auto px-4 py-8 space-y-6">
    
    <!-- NOTIFIKASI FLASH MESSAGE -->
    <?php foreach (['message' => 'emerald', 'error' => 'rose', 'info' => 'blue', 'warning' => 'amber'] as $key => $color): ?>
        <?php if (session()->getFlashdata($key)) : ?>
            <div class="bg-<?= $color ?>-50 border-l-4 border-<?= $color ?>-500 p-4 rounded-r-xl shadow-sm mb-6 flex items-start gap-3 animate-fade-in-down">
                <div class="text-<?= $color ?>-500 mt-0.5">
                    <?php if($key == 'message'): ?><i class="fas fa-check-circle text-xl"></i><?php endif; ?>
                    <?php if($key == 'error'): ?><i class="fas fa-times-circle text-xl"></i><?php endif; ?>
                    <?php if($key == 'info'): ?><i class="fas fa-info-circle text-xl"></i><?php endif; ?>
                    <?php if($key == 'warning'): ?><i class="fas fa-exclamation-triangle text-xl"></i><?php endif; ?>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-<?= $color ?>-800 uppercase tracking-widest mb-0.5">
                        <?= $key === 'message' ? 'BERHASIL' : ($key === 'info' ? 'INFORMASI' : strtoupper($key)) ?>
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

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 border-b border-gray-100 pb-6">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                    <li class="inline-flex items-center hover:text-primary transition-colors cursor-pointer"><i class="fas fa-home mr-2"></i>Home</li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="inline-flex items-center text-primary">Bank Soal</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manajemen Bank Soal</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="flex h-2 w-2 rounded-full <?= $is_restricted ? 'bg-amber-500' : 'bg-emerald-500' ?>"></span>
                <p class="text-sm font-bold uppercase tracking-wider <?= $is_restricted ? 'text-amber-600' : 'text-emerald-600' ?>">
                    Scope: <?= $is_restricted ? "Unit $session_unit" : "Global Admin" ?>
                </p>
            </div>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <!-- Tombol Tambah Soal -->
            <a href="<?= base_url('app/pembelajaran/bank-soal/new') ?>" class="w-full md:w-auto inline-flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all hover:shadow-md transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Soal
            </a>
        </div>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase">Total Soal</p>
            <p class="text-2xl font-black text-gray-800 dark:text-white mt-1"><?= number_format($stats['total'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-500 uppercase">Pilihan Ganda</p>
            <p class="text-2xl font-black text-gray-800 dark:text-white mt-1"><?= number_format($stats['pg'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-bold text-amber-500 uppercase">Tk. Sedang</p>
            <p class="text-2xl font-black text-gray-800 dark:text-white mt-1"><?= number_format($stats['sedang'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
            <p class="text-xs font-bold text-rose-500 uppercase">Tk. Sukar</p>
            <p class="text-2xl font-black text-gray-800 dark:text-white mt-1"><?= number_format($stats['sukar'] ?? 0) ?></p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenjang</label>
                <?php if (!$is_restricted): ?>
                    <select name="kode_jenjang" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-2.5 border" onchange="this.form.submit()">
                        <option value="">Semua Jenjang</option>
                        <option value="SD" <?= $filter_jenjang == 'SD' ? 'selected' : '' ?>>SD / MI</option>
                        <option value="SMP" <?= $filter_jenjang == 'SMP' ? 'selected' : '' ?>>SMP / MTS</option>
                        <option value="SMA" <?= $filter_jenjang == 'SMA' ? 'selected' : '' ?>>SMA / SMK / MA</option>
                    </select>
                <?php else: ?>
                    <div class="p-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-indigo-700 dark:text-indigo-300 font-bold text-sm flex items-center justify-between">
                        <span><?= esc($session_unit) ?> (Unit Anda)</span>
                        <input type="hidden" name="kode_jenjang" value="<?= esc($session_unit) ?>">
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cari Soal / Topik</label>
                <input type="text" name="keyword" value="<?= esc($filter_keyword) ?>" placeholder="Kata kunci..." class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-2.5 border">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-800 hover:bg-black text-white font-bold py-2.5 px-4 rounded-lg transition-all shadow-sm">
                    Filter Data
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Kode & Mapel</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">Topik & Pertanyaan</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Info</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if(!empty($bank_soal)): ?>
                        <?php foreach ($bank_soal as $item): ?>
                            <?php if ($is_restricted && strtoupper($item['kode_jenjang']) !== $session_unit) continue; ?>
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-[10px] font-black text-gray-600 dark:text-gray-300 mb-1 border border-gray-200 dark:border-gray-600">
                                        <?= esc($item['kode_soal']) ?>
                                    </span>
                                    <div class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase mt-1">
                                        <?= $item['kode_jenjang'] ?> | ID: <?= $item['mata_pelajaran_id'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1"><?= esc($item['topik']) ?></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 italic line-clamp-2">
                                        "<?= strip_tags($item['pertanyaan']) ?>"
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col gap-1 items-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase bg-blue-50 text-blue-600 border border-blue-100">
                                            <?= $item['jenis_soal'] ?>
                                        </span>
                                        <?php 
                                            $diffColor = match($item['tingkat_kesulitan']) {
                                                'Sukar' => 'text-rose-600 bg-rose-50 border-rose-100',
                                                'Sedang' => 'text-amber-600 bg-amber-50 border-amber-100',
                                                default => 'text-emerald-600 bg-emerald-50 border-emerald-100'
                                            };
                                        ?>
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded uppercase border <?= $diffColor ?>">
                                            <?= $item['tingkat_kesulitan'] ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center gap-2">
                                        <a href="<?= base_url('app/pembelajaran/bank-soal/' . $item['id']) ?>" class="p-2 text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                        <a href="<?= base_url('app/pembelajaran/bank-soal/edit/' . $item['id']) ?>" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Soal">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00 2 2h11a2 2 0 00 2-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="<?= base_url('app/pembelajaran/bank-soal/delete/' . $item['id']) ?>" method="post" class="inline" onsubmit="return confirm('Hapus soal ini?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
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
                                    <p class="text-gray-500 font-medium">Belum ada bank soal tersedia.</p>
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
        <?= $pager->links('soal', 'tailwind_pagination') ?>
    </div>
</div>
<?= $this->endSection() ?>