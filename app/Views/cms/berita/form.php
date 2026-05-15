<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">CMS</li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase">Berita</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-sky-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2"><?= (isset($berita) && $berita) ? 'Edit' : 'Tulis Baru' ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/cms/berita') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95 rounded-lg">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Error -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm animate-pulse rounded-r-lg">
            <div class="flex items-center mb-1">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight">Terjadi Kesalahan</h3>
            </div>
            <div class="text-xs font-bold text-rose-700 ml-6">
                <?= session()->getFlashdata('error') ?>
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('app/cms/berita/save') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= isset($berita) ? (is_object($berita) ? $berita->id : $berita['id']) : '' ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM UTAMA (EDITOR) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Judul Berita -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-4">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Judul Berita <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" required 
                               placeholder="Masukkan judul berita yang menarik..."
                               class="block w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-500 focus:ring-0 transition-all rounded-lg placeholder:font-normal"
                               value="<?= isset($berita) ? esc(is_object($berita) ? $berita->judul : $berita['judul']) : '' ?>">
                    </div>

                    <!-- Editor Konten -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Konten Berita <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="konten" id="editor_berita" class="w-full h-96"><?= isset($berita) ? (is_object($berita) ? $berita->konten : $berita['konten']) : '' ?></textarea>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR (PUBLIKASI & GAMBAR) -->
            <div class="space-y-6">
                
                <!-- Card Publikasi -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-paper-plane text-sky-500"></i> Publikasi
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <!-- Target Unit -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Target Unit <span class="text-rose-500">*</span>
                            </label>
                            
                            <?php 
                                // Ambil data dari controller jika ada, fallback ke array kosong
                                $listUnit = isset($daftarUnit) ? $daftarUnit : [];
                                $userUnit = session('kode_jenjang');
                                $defaultUnit = isset($berita) ? (is_object($berita) ? $berita->kode_jenjang : $berita['kode_jenjang']) : old('kode_jenjang');
                                
                                // Tentukan apakah user Admin Unit (Locked) atau Superadmin (Selectable)
                                // Gunakan logika yang sama dengan controller
                                $isGlobal = empty($userUnit) || in_array(strtoupper($userUnit), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT']);
                            ?>

                            <?php if (!$isGlobal): ?>
                                <!-- LOCKED FOR ADMIN UNIT -->
                                <div class="relative">
                                    <input type="text" value="<?= $listUnit[$userUnit] ?? $userUnit ?>" disabled 
                                           class="block w-full px-4 py-2 bg-slate-100 border border-slate-200 text-xs font-bold text-slate-500 rounded-lg cursor-not-allowed uppercase">
                                    <input type="hidden" name="kode_jenjang" value="<?= $userUnit ?>">
                                    <div class="absolute right-3 top-2.5 text-slate-400"><i class="fas fa-lock text-xs"></i></div>
                                </div>
                                <p class="mt-1 text-[9px] text-slate-400 italic">Otomatis sesuai unit Anda.</p>
                            <?php else: ?>
                                <!-- SELECTABLE FOR SUPERADMIN -->
                                <div class="relative">
                                    <select name="kode_jenjang" required 
                                            class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-sky-500 focus:ring-0 appearance-none cursor-pointer uppercase">
                                        <option value="Global" <?= ($defaultUnit == 'Global' || empty($defaultUnit)) ? 'selected' : '' ?>>GLOBAL (YAYASAN)</option>
                                        <?php foreach ($listUnit as $kode => $label): ?>
                                            <option value="<?= esc($kode) ?>" <?= ($defaultUnit == $kode) ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute right-3 top-2.5 text-slate-400 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Status Publikasi
                            </label>
                            <div class="relative">
                                <select name="status" class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-sky-500 focus:ring-0 appearance-none cursor-pointer uppercase">
                                    <?php 
                                        $status = isset($berita) ? (is_object($berita) ? $berita->status : $berita['status']) : 'published';
                                    ?>
                                    <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>PUBLISHED (TAYANG)</option>
                                    <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>DRAFT (KONSEP)</option>
                                    <option value="archived" <?= $status == 'archived' ? 'selected' : '' ?>>ARCHIVED (ARSIP)</option>
                                </select>
                                <div class="absolute right-3 top-2.5 text-slate-400 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button type="submit" class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-sky-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Berita
                        </button>

                    </div>
                </div>

                <!-- Card Gambar -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-image text-emerald-500"></i> Gambar Unggulan
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <?php 
                            $gambar = isset($berita) ? (is_object($berita) ? $berita->gambar : $berita['gambar']) : null;
                        ?>
                        <div id="image-preview" class="mb-4 relative group">
                            <?php if ($gambar): ?>
                                <img src="<?= base_url('uploads/berita/' . $gambar) ?>" class="w-full h-48 object-cover rounded-lg border border-slate-200 shadow-sm">
                            <?php else: ?>
                                <div class="w-full h-48 bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                    <i class="fas fa-image text-4xl mb-2 opacity-50"></i>
                                    <span class="text-[10px] uppercase font-bold">Belum ada gambar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <label class="block">
                            <span class="sr-only">Choose profile photo</span>
                            <input type="file" name="gambar" onchange="previewImage(this)"
                                   class="block w-full text-xs text-slate-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-[10px] file:font-black file:uppercase file:tracking-wide
                                          file:bg-sky-50 file:text-sky-700
                                          hover:file:bg-sky-100 cursor-pointer transition-all
                                   "/>
                        </label>
                        <p class="mt-2 text-[9px] text-slate-400 italic">Format: JPG, PNG. Max: 2MB.</p>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
<script>
    // Inisialisasi TinyMCE dengan konfigurasi minimalis tapi powerful
    tinymce.init({
        selector: '#editor_berita',
        license_key: 'gpl',
        height: 500,
        menubar: false,
        plugins: 'lists link image media table code wordcount emoticons anchor fullscreen preview searchreplace autolink directionality visualblocks visualchars',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat code fullscreen',
        content_style: 'body { font-family: "Inter", sans-serif; font-size: 14px; line-height: 1.6; color: #334155; }',
        branding: false,
        promotion: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    // Preview Image Sederhana
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const previewHtml = `<img src="${e.target.result}" class="w-full h-48 object-cover rounded-lg border border-slate-200 shadow-sm animate-fade-in">`;
                document.getElementById('image-preview').innerHTML = previewHtml;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<style>
    /* Custom Scrollbar untuk Editor jika diperlukan */
    .tox-tinymce { border-radius: 0.75rem !important; border-color: #e2e8f0 !important; }
    .tox-statusbar { border-radius: 0 0 0.75rem 0.75rem !important; }
</style>

<?= $this->endSection() ?>