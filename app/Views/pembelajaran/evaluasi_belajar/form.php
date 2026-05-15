<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="max-w-4xl mx-auto px-4 py-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">
                <?= isset($evaluasi) ? 'Edit Jadwal Evaluasi' : 'Jadwal Evaluasi Baru' ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">Atur pelaksanaan ujian, kuis, atau tugas.</p>
        </div>
        <a href="<?= base_url('app/pembelajaran/evaluasi-belajar') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-bold text-sm rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:bg-gray-50 transition-all">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 md:p-8">
        
        <?php 
            // Menggunakan route 'evaluasi-belajar' yang sudah pasti ada di Routes.php
            $action = isset($evaluasi) 
                ? base_url('app/pembelajaran/evaluasi-belajar/update/' . $evaluasi['id']) 
                : base_url('app/pembelajaran/evaluasi-belajar/create');
        ?>

        <form action="<?= $action ?>" method="post">
            <?= csrf_field() ?>
            <?php if(isset($evaluasi)): ?> 
                <!-- Spoofing PUT untuk update -->
                <input type="hidden" name="_method" value="PUT"> 
                <input type="hidden" name="id" value="<?= $evaluasi['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- SECTION 1: IDENTITAS -->
                <div class="md:col-span-2 space-y-4">
                    <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Informasi Dasar</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Materi Silabus <span class="text-red-500">*</span></label>
                        <select name="silabus_id" required class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            <option value="" disabled selected>-- Pilih Materi Pembelajaran --</option>
                            <?php foreach($silabus as $s): ?>
                                <?php $selected = (old('silabus_id', $evaluasi['silabus_id'] ?? '') == $s['id']) ? 'selected' : ''; ?>
                                <option value="<?= $s['id'] ?>" <?= $selected ?>>
                                    [<?= $s['kode_jenjang'] ?>] <?= esc($s['materi_pokok']) ?> (<?= $s['kode_mapel'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1">* Pastikan silabus sudah dibuat sebelumnya.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Judul Evaluasi <span class="text-red-500">*</span></label>
                        <input type="text" name="judul_evaluasi" required 
                               value="<?= old('judul_evaluasi', $evaluasi['judul_evaluasi'] ?? '') ?>" 
                               placeholder="Contoh: Ulangan Harian Bab 1" 
                               class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-indigo-500 focus:border-indigo-500 font-bold text-gray-800 dark:text-white">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Evaluasi</label>
                            <select name="jenis_evaluasi" class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                <?php foreach(['Tugas', 'Kuis', 'UTS', 'UAS', 'Tryout', 'Proyek'] as $j): ?>
                                    <option value="<?= $j ?>" <?= (old('jenis_evaluasi', $evaluasi['jenis_evaluasi'] ?? '') == $j) ? 'selected' : '' ?>><?= $j ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status Publikasi</label>
                            <select name="status" class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                <option value="Draft" <?= (old('status', $evaluasi['status'] ?? '') == 'Draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                                <option value="Published" <?= (old('status', $evaluasi['status'] ?? '') == 'Published') ? 'selected' : '' ?>>Published (Tampilkan)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: WAKTU & TEKNIS -->
                <div class="md:col-span-2 space-y-4 mt-2">
                    <h3 class="text-xs font-black text-amber-500 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Pengaturan Waktu & Skor</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Mulai Mengerjakan</label>
                            <input type="datetime-local" name="tanggal_mulai" 
                                   value="<?= old('tanggal_mulai', isset($evaluasi['tanggal_mulai']) ? date('Y-m-d\TH:i', strtotime($evaluasi['tanggal_mulai'])) : '') ?>" 
                                   class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-amber-500 focus:border-amber-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Batas Akhir</label>
                            <input type="datetime-local" name="tanggal_selesai" 
                                   value="<?= old('tanggal_selesai', isset($evaluasi['tanggal_selesai']) ? date('Y-m-d\TH:i', strtotime($evaluasi['tanggal_selesai'])) : '') ?>" 
                                   class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-amber-500 focus:border-amber-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Durasi Pengerjaan (Menit)</label>
                            <div class="relative">
                                <input type="number" name="durasi" placeholder="60" min="0" 
                                       value="<?= old('durasi', $evaluasi['durasi'] ?? '') ?>" 
                                       class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 pr-12 border focus:ring-amber-500 focus:border-amber-500 font-bold">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">MENIT</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">KKM / Nilai Lulus</label>
                            <input type="number" name="kkm" placeholder="75" min="0" max="100" 
                                   value="<?= old('kkm', $evaluasi['kkm'] ?? '75') ?>" 
                                   class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 sm:text-sm p-3 border focus:ring-amber-500 focus:border-amber-500 font-bold text-emerald-600">
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: INSTRUKSI -->
                <div class="md:col-span-2 mt-2">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-4">Instruksi Soal</h3>
                    <textarea name="instruksi" class="editor"><?= old('instruksi', $evaluasi['instruksi'] ?? '') ?></textarea>
                </div>

            </div>

            <!-- Footer Action -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <a href="<?= base_url('app/pembelajaran/evaluasi-belajar') ?>" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <?= isset($evaluasi) ? 'Simpan Perubahan' : 'Terbitkan Evaluasi' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    tinymce.init({
        selector: 'textarea.editor',
        height: 250,
        menubar: false,
        plugins: ['lists', 'link', 'wordcount'],
        toolbar: 'undo redo | bold italic underline | bullist numlist | alignleft aligncenter alignright | link removeformat',
        setup: function (editor) { editor.on('change', function () { editor.save(); }); }
    });
</script>
<?= $this->endSection() ?>