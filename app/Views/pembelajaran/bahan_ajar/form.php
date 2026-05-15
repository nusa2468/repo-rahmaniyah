<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-black text-gray-900"><?= isset($bahan) ? 'Edit Bahan Ajar' : 'Tambah Bahan Ajar' ?></h1>
        <a href="<?= base_url('app/pembelajaran/bahan-ajar/update/') ?>" class="text-gray-500 font-bold hover:text-indigo-600 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-8">
        <form action="<?= isset($bahan) ? base_url('app/pembelajaran/bahan-ajar/update/'.$bahan['id']) : base_url('app/pembelajaran/bahan-ajar/update//create') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>
            <?php if(isset($bahan)): ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-indigo-600 uppercase tracking-widest mb-2">Pilih RPP / Pertemuan <span class="text-red-500">*</span></label>
                    <select name="rpp_id" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 outline-none font-bold text-gray-700 transition-all">
                        <option value="" disabled selected>-- Hubungkan dengan RPP --</option>
                        <?php foreach($rpp as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= (old('rpp_id', $bahan['rpp_id'] ?? '') == $r['id']) ? 'selected' : '' ?>>
                                [<?= $r['kode_jenjang'] ?>] P-<?= $r['pertemuan_ke'] ?>: <?= $r['topik'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Judul Bahan Ajar <span class="text-red-500">*</span></label>
                    <input type="text" name="judul_bahan" required placeholder="Nama materi..." value="<?= old('judul_bahan', $bahan['judul_bahan'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 outline-none font-bold transition-all">
                </div>

                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Jenis Materi</label>
                    <select name="jenis_file" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 outline-none font-bold text-gray-700 transition-all">
                        <?php foreach(['PDF', 'Video', 'Tautan', 'Powerpoint', 'Lainnya'] as $j): ?>
                            <option value="<?= $j ?>" <?= (old('jenis_file', $bahan['jenis_file'] ?? '') == $j) ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Lokasi File / Tautan URL <span class="text-red-500">*</span></label>
                    <input type="text" name="file_path" required placeholder="https://youtube.com/... atau path/to/file.pdf" value="<?= old('file_path', $bahan['file_path'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 outline-none font-bold transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Deskripsi Materi</label>
                    <textarea name="deskripsi" class="editor"><?= old('deskripsi', $bahan['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-4">
                <button type="submit" class="bg-indigo-600 hover:bg-black text-white px-10 py-4 rounded-2xl font-black shadow-xl transition-all transform hover:-translate-y-1">
                    <?= isset($bahan) ? 'UPDATE MATERI' : 'TERBITKAN MATERI' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    tinymce.init({
        selector: 'textarea.editor',
        height: 300,
        menubar: false,
        plugins: ['lists', 'link', 'wordcount'],
        toolbar: 'undo redo | bold italic underline | bullist numlist | link removeformat',
        setup: function (editor) { editor.on('change', function () { editor.save(); }); }
    });
</script>
<?= $this->endSection() ?>