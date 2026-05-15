<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Detail Mata Pelajaran
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    // Helper Format Tingkat
    $formatTingkat = function($t) {
        if ($t === '0' || $t === 0) return 'TK/PAUD';
        if (empty($t)) return '-';
        return 'Tingkat ' . $t;
    };
?>

<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-200 dark:border-gray-700 pb-6">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-xs font-medium text-gray-500">
                    <li class="inline-flex items-center hover:text-indigo-600 transition-colors cursor-pointer">
                        <i class="fas fa-database mr-2"></i>Master Data
                    </li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="inline-flex items-center hover:text-indigo-600 transition-colors cursor-pointer">
                        <a href="<?= base_url('app/masterdata/matapelajaran') ?>">Mata Pelajaran</a>
                    </li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="inline-flex items-center text-indigo-600 font-bold">Detail</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                <?= esc($mapel['nama_mapel']) ?>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium flex items-center gap-2">
                <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600 font-mono text-xs"><?= esc($mapel['kode_mapel']) ?></span>
                <span>•</span>
                <span><?= $formatTingkat($mapel['tingkat'] ?? '') ?></span>
                <span>•</span>
                <span>Semester <?= esc($mapel['semester'] ?? 'Semua') ?></span>
            </p>
        </div>
        
        <div class="flex gap-2">
            <a href="<?= base_url('app/masterdata/matapelajaran') ?>" class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            <a href="<?= base_url('app/masterdata/matapelajaran/edit/' . $mapel['id']) ?>" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-bold hover:bg-amber-600 transition-all shadow-md shadow-amber-500/20">
                <i class="fas fa-edit mr-2"></i> Edit Data
            </a>
        </div>
    </div>

    <!-- Main Info Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Informasi Utama -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 flex justify-between items-center">
                    <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider">Informasi Akademik</h3>
                    <?php if (strtolower($mapel['status'] ?? '') === 'aktif') : ?>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-100 text-emerald-700 border border-emerald-200">
                            AKTIF
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wide bg-gray-100 text-gray-500 border border-gray-200">
                            NON-AKTIF
                        </span>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8">
                        <!-- Unit Sekolah -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Unit Sekolah</span>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                <?= esc($mapel['unit_sekolah'] ?? $mapel['kode_jenjang']) ?>
                            </div>
                        </div>

                        <!-- Tingkat Kelas (DISISIPKAN DI SINI) -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Tingkat Kelas</span>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                <?= $formatTingkat($mapel['tingkat'] ?? '') ?>
                            </div>
                        </div>

                        <!-- Kurikulum -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Kurikulum</span>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                <?= esc($mapel['nama_kurikulum'] ?? '-') ?>
                            </div>
                        </div>

                        <!-- Semester -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Semester Berlaku</span>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                <?= esc($mapel['semester'] ?? 'Semua Semester') ?>
                            </div>
                        </div>

                        <!-- Kelompok -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Kelompok Mapel</span>
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-black text-xs border border-indigo-100 dark:border-indigo-800">
                                    <?= esc($mapel['kelompok']) ?>
                                </span>
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Kelompok <?= esc($mapel['kelompok']) ?></span>
                            </div>
                        </div>

                        <!-- Beban Belajar -->
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1 tracking-wider">Beban Belajar</span>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-baseline gap-1">
                                <span class="text-xl"><?= esc($mapel['jumlah_jp']) ?></span> 
                                <span class="text-xs text-gray-400 font-medium">Jam Pelajaran / Minggu</span>
                            </div>
                        </div>
                        
                        <!-- Metadata -->
                        <div class="md:col-span-2 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-wider">Meta Data</span>
                            <div class="flex flex-col sm:flex-row gap-4 text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                <div class="flex items-center gap-2 bg-gray-50 px-2 py-1 rounded">
                                    <i class="far fa-clock"></i> Dibuat: <?= date('d M Y H:i', strtotime($mapel['created_at'])) ?>
                                </div>
                                <div class="flex items-center gap-2 bg-gray-50 px-2 py-1 rounded">
                                    <i class="fas fa-history"></i> Diperbarui: <?= date('d M Y H:i', strtotime($mapel['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Bobot Penilaian -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden h-full flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/5">
                    <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-chart-pie text-emerald-500"></i> Bobot Penilaian
                    </h3>
                </div>
                <div class="p-6 space-y-5 flex-1">
                    <!-- Tugas -->
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Tugas Harian</span>
                            <span class="text-xs font-black text-sky-600"><?= ($mapel['bobot_tugas'] ?? 0) * 100 ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-sky-500 h-1.5 rounded-full" style="width: <?= ($mapel['bobot_tugas'] ?? 0) * 100 ?>%"></div>
                        </div>
                    </div>

                    <!-- UTS -->
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">UTS (Tengah Semester)</span>
                            <span class="text-xs font-black text-amber-500"><?= ($mapel['bobot_uts'] ?? 0) * 100 ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-amber-400 h-1.5 rounded-full" style="width: <?= ($mapel['bobot_uts'] ?? 0) * 100 ?>%"></div>
                        </div>
                    </div>

                    <!-- UAS -->
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">UAS (Akhir Semester)</span>
                            <span class="text-xs font-black text-rose-600"><?= ($mapel['bobot_uas'] ?? 0) * 100 ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-rose-500 h-1.5 rounded-full" style="width: <?= ($mapel['bobot_uas'] ?? 0) * 100 ?>%"></div>
                        </div>
                    </div>

                    <!-- Absensi -->
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Kehadiran</span>
                            <span class="text-xs font-black text-emerald-600"><?= ($mapel['bobot_absensi'] ?? 0) * 100 ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= ($mapel['bobot_absensi'] ?? 0) * 100 ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/10">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Akumulasi</span>
                        <span class="text-base font-black text-gray-900 dark:text-white">100%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>