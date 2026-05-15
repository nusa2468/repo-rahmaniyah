<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // Helper function untuk warna badge jenjang
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
?>

<div class="max-w-7xl mx-auto font-jakarta min-h-screen p-6 lg:p-8 animate-in fade-in duration-500">
    
    <!-- Header Compact & Solid -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <!-- Navigation & Title Group -->
        <div class="flex items-center gap-4">
            <!-- Navigasi Kembali ke Dashboard -->
            <a href="<?= base_url('app/masterdata/dashboard') ?>" 
               class="flex h-12 w-12 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm group"
               title="Kembali ke Dashboard">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            </a>

            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    <?= esc($title ?? 'Data Jenjang') ?>
                </h1>
                <div class="flex items-center gap-2 mt-1 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                    <i class="fas fa-layer-group text-indigo-500"></i>
                    Manajemen Hirarki Pendidikan
                </div>
            </div>
        </div>

        <!-- Actions Group -->
        <div class="flex items-center gap-3">
            <!-- Total Counter -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-black text-gray-600 dark:text-gray-300">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Total: <?= count($jenjang_list ?? []) ?>
            </div>

            <!-- Tambah Button -->
            <a href="<?= base_url('app/masterdata/jenjang/new') ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-indigo-600/30 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                Tambah Jenjang
            </a>
        </div>
    </div>

    <!-- Table Card - Super Compact & Solid -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-16">Urut</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama Jenjang</th>
                        <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-28">Kode</th>
                        <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-24">Status</th>
                        <th class="px-5 py-3.5 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($jenjang_list)): ?>
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center text-gray-400 dark:text-gray-600">
                                    <i class="fas fa-inbox text-5xl mb-4 opacity-30"></i>
                                    <span class="text-xs font-black uppercase tracking-widest">Data Kosong</span>
                                    <p class="text-xs mt-2">Belum ada jenjang terdaftar</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jenjang_list as $row): ?>
                            <?php 
                                // Fail-safe: Konversi ke array jika berupa object
                                $row = (array)$row;
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs font-black text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-white/10">
                                        <?= esc($row['urutan'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">
                                        <?= esc($row['nama_jenjang']) ?>
                                    </div>
                                    <?php if (!empty($row['keterangan'])): ?>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 italic max-w-xs truncate">
                                            <?= esc($row['keterangan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-block px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-wider <?= getJenjangColor($row['kode_jenjang']) ?>">
                                        <?= esc($row['kode_jenjang']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <?php if (($row['status'] ?? 'aktif') === 'aktif'): ?>
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase bg-emerald-50 dark:bg-emerald-900/20 px-2 py-1 rounded">
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-red-500 dark:text-red-400 uppercase bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded">
                                            <i class="fas fa-times-circle"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/masterdata/jenjang/edit/' . $row['id']) ?>"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white border border-amber-200 hover:border-amber-500 transition-all active:scale-95"
                                           title="Edit">
                                            <i class="fas fa-pencil-alt text-xs"></i>
                                        </a>
                                        <a href="<?= base_url('app/masterdata/jenjang/delete/' . $row['id']) ?>"
                                           onclick="return confirm('Hapus jenjang <?= esc($row['nama_jenjang']) ?>? Aksi ini tidak dapat dibatalkan.')"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white border border-red-200 hover:border-red-500 transition-all active:scale-95"
                                           title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Footer Table -->
        <?php if (!empty($jenjang_list)): ?>
        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-white/10 flex justify-between items-center text-[10px] font-medium text-gray-500 dark:text-gray-400">
            <span>Menampilkan <?= count($jenjang_list) ?> data jenjang</span>
            <span>Update Terakhir: <?= date('d M Y') ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>