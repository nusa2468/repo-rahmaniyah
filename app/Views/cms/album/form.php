<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto font-sans antialiased text-slate-900">
    
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
                            <span class="uppercase">Galeri</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-amber-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2"><?= (isset($album) && $album) ? 'Edit Metadata' : 'Buat Baru' ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/cms/album') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95 rounded-lg">
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

    <form action="<?= base_url('app/cms/album/save') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= isset($album) ? (is_object($album) ? $album->id : $album['id']) : '' ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM UTAMA (DETAIL ALBUM) -->
            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <!-- Judul Album -->
                    <div class="mb-6">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Judul Album <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul" required 
                               placeholder="Contoh: Dokumentasi Outbound Siswa 2025"
                               class="block w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-0 transition-all rounded-lg placeholder:font-normal"
                               value="<?= isset($album) ? esc(is_object($album) ? $album->judul : $album['judul']) : '' ?>">
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Deskripsi Singkat
                        </label>
                        <textarea name="deskripsi" rows="5" placeholder="Gambarkan sedikit tentang isi album ini..."
                                  class="block w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 text-sm font-medium focus:bg-white focus:border-amber-500 focus:ring-0 transition-all rounded-lg placeholder:font-normal"><?= isset($album) ? esc(is_object($album) ? $album->deskripsi : $album['deskripsi']) : '' ?></textarea>
                    </div>
                </div>

            </div>

            <!-- SIDEBAR (PUBLIKASI & COVER) -->
            <div class="space-y-6">
                
                <!-- Card Publikasi -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-paper-plane text-amber-500"></i> Pengaturan
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <!-- Target Unit (DINAMIS) -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Unit Sekolah <span class="text-rose-500">*</span>
                            </label>
                            
                            <?php 
                                // Ambil data dari controller
                                $listUnit = isset($daftarUnit) ? $daftarUnit : [];
                                $userUnit = session('kode_jenjang');
                                $defaultUnit = isset($album) ? (is_object($album) ? $album->kode_jenjang : $album['kode_jenjang']) : old('kode_jenjang');
                                
                                // Logic Scoping
                                $isGlobal = empty($userUnit) || in_array(strtoupper($userUnit), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT']);
                            ?>

                            <?php if (!$isGlobal): ?>
                                <!-- LOCKED FOR ADMIN UNIT -->
                                <div class="relative">
                                    <input type="text" value="<?= $listUnit[$userUnit] ?? $userUnit ?>" disabled 
                                           class="block w-full px-4 py-2 bg-slate-100 border border-slate-200 text-xs font-bold text-slate-500 rounded-lg cursor-not-allowed uppercase">
                                    <input type="hidden" name="jenjang" value="<?= $userUnit ?>">
                                    <div class="absolute right-3 top-2.5 text-slate-400"><i class="fas fa-lock text-xs"></i></div>
                                </div>
                                <p class="mt-1 text-[9px] text-slate-400 italic">Otomatis sesuai unit Anda.</p>
                            <?php else: ?>
                                <!-- SELECTABLE FOR SUPERADMIN -->
                                <div class="relative">
                                    <select name="jenjang" required 
                                            class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-amber-500 focus:ring-0 appearance-none cursor-pointer uppercase">
                                        <option value="Global" <?= ($defaultUnit == 'Global' || empty($defaultUnit)) ? 'selected' : '' ?>>GLOBAL (SEMUA UNIT)</option>
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

                        <!-- Visibilitas -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Visibilitas
                            </label>
                            <div class="flex items-center gap-4 mt-2">
                                <?php 
                                    $status = isset($album) ? (is_object($album) ? $album->status : $album['status']) : 'publik';
                                ?>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="publik" class="text-amber-600 focus:ring-amber-500" <?= $status == 'publik' ? 'checked' : '' ?>>
                                    <span class="text-xs font-bold text-slate-700">Publik</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="status" value="internal" class="text-slate-600 focus:ring-slate-500" <?= $status == 'internal' ? 'checked' : '' ?>>
                                    <span class="text-xs font-bold text-slate-500">Internal</span>
                                </label>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> 
                            <?= isset($album) ? 'Simpan Perubahan' : 'Buat & Upload Foto' ?>
                        </button>

                    </div>
                </div>

                <!-- Card Cover -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-image text-emerald-500"></i> Sampul Album
                        </h3>
                    </div>
                    <div class="p-6 text-center">
                        <?php 
                            $cover = isset($album) ? (is_object($album) ? $album->cover : $album['cover']) : null;
                        ?>
                        <div id="cover-preview" class="mb-4 relative group">
                            <?php if ($cover): ?>
                                <img src="<?= base_url('uploads/galeri/covers/' . $cover) ?>" class="w-full h-40 object-cover rounded-lg border border-slate-200 shadow-sm">
                            <?php else: ?>
                                <div class="w-full h-40 bg-slate-50 border-2 border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-slate-400">
                                    <i class="fas fa-images text-3xl mb-2 opacity-50"></i>
                                    <span class="text-[10px] uppercase font-bold">Tanpa Sampul</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <label class="block">
                            <span class="sr-only">Upload cover</span>
                            <input type="file" name="cover" onchange="previewCover(this)" accept="image/*"
                                   class="block w-full text-xs text-slate-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-[10px] file:font-black file:uppercase file:tracking-wide
                                          file:bg-amber-50 file:text-amber-700
                                          hover:file:bg-amber-100 cursor-pointer transition-all
                                   "/>
                        </label>
                        <p class="mt-2 text-[9px] text-slate-400 italic">Format: JPG, PNG. Max: 2MB.</p>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
    function previewCover(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const previewHtml = `<img src="${e.target.result}" class="w-full h-40 object-cover rounded-lg border border-slate-200 shadow-sm animate-fade-in">`;
                document.getElementById('cover-preview').innerHTML = previewHtml;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?= $this->endSection() ?>