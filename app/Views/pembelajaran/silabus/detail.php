<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Detail Silabus Pembelajaran</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informasi lengkap mengenai silabus dan kurikulum.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= base_url('silabus') ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <!-- Tombol Print (Diselipkan) -->
            <a href="<?= base_url('silabus/print/' . $silabus['id']) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors shadow-sm">
                <i class="fas fa-print"></i> Cetak PDF
            </a>
            
            <a href="<?= base_url('silabus/edit/' . $silabus['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-500 border border-transparent rounded-lg hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-sm transition-colors">
                <i class="fas fa-edit"></i> Edit Data
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Informasi Meta Data -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center gap-3">
                    <div class="p-2 bg-sky-100 dark:bg-sky-900/30 rounded-lg text-sky-600 dark:text-sky-400">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Umum</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Item -->
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Jenjang</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                                <?= esc($silabus['kode_jenjang']) ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Tingkat Kelas</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Kelas <?= esc($silabus['tingkat_kelas']) ?></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Semester</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Semester <?= esc($silabus['semester']) ?></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Tahun Ajaran</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($silabus['tahun_ajaran']) ?></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Mata Pelajaran</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white text-right"><?= esc($silabus['nama_mapel'] ?? $silabus['mata_pelajaran_id']) ?></span>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Kurikulum</span>
                            <?php if ($silabus['jenis_kurikulum'] == 'Merdeka') : ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                    Merdeka
                                </span>
                            <?php else : ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                    K13
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <?php if ($silabus['status'] == 'Final') : ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Final
                                </span>
                            <?php else : ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Draft
                                </span>
                            <?php endif; ?>
                        </div>
                         <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Dibuat Oleh</span>
                            <span class="text-sm text-gray-900 dark:text-white text-right truncate max-w-[150px]"><?= esc($silabus['created_by']) ?></span>
                        </div>
                         <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Terakhir Update</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 text-right"><?= esc($silabus['updated_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Isi Silabus -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- BLOCK 1: Materi & Alokasi -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center gap-3">
                     <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600 dark:text-purple-400">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Materi Pokok & Alokasi</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-1">Materi Pokok</label>
                        <div class="sm:col-span-3">
                            <p class="text-base font-medium text-gray-900 dark:text-white">
                                <?= esc($silabus['materi_pokok']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-1">Alokasi Waktu</label>
                        <div class="sm:col-span-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                <i class="far fa-clock mr-2"></i> <?= esc($silabus['alokasi_waktu']) ?> JP
                            </span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 sm:mt-1">Sumber Belajar</label>
                        <div class="sm:col-span-3">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-sm text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['sumber_belajar'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 2: Logic Hybrid -->
            <?php if ($silabus['jenis_kurikulum'] === 'Merdeka') : ?>
                <!-- TAMPILAN KHUSUS KURIKULUM MERDEKA -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-l-4 border-l-emerald-500 border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-emerald-50/50 dark:bg-emerald-900/10 flex items-center justify-between">
                         <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <h2 class="text-base font-semibold text-emerald-900 dark:text-emerald-100">Komponen Kurikulum Merdeka</h2>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/30 px-2 py-1 rounded">
                            Fase <?= esc($silabus['fase']) ?>
                        </span>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">Capaian Pembelajaran (CP)</label>
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['capaian_pembelajaran'])) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">Alur Tujuan Pembelajaran (ATP)</label>
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['alur_tujuan_pembelajaran'])) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">Profil Pelajar Pancasila (P5)</label>
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['profil_pelajar_pancasila'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($silabus['jenis_kurikulum'] === 'K13') : ?>
                <!-- TAMPILAN KHUSUS KURIKULUM 2013 -->
                 <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-l-4 border-l-amber-500 border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-amber-50/50 dark:bg-amber-900/10 flex items-center gap-3">
                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400">
                            <i class="fas fa-book"></i>
                        </div>
                        <h2 class="text-base font-semibold text-amber-900 dark:text-amber-100">Komponen Kurikulum 2013</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        <?php if(!empty($silabus['tema']) || !empty($silabus['subtema'])): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Tema</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($silabus['tema'] ?? '-') ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-1">Subtema</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?= esc($silabus['subtema'] ?? '-') ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Kompetensi Inti (KI)</label>
                             <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['kompetensi_inti'])) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Kompetensi Dasar (KD)</label>
                             <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['kompetensi_dasar'])) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Indikator Pencapaian Kompetensi (IPK)</label>
                             <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 text-gray-800 dark:text-gray-200 text-sm leading-relaxed border border-gray-100 dark:border-gray-700">
                                <?= nl2br(esc($silabus['indikator'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?= $this->endSection() ?>