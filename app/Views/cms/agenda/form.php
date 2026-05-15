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
                            <span class="uppercase">Agenda</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2"><?= (isset($agenda) && $agenda) ? 'Edit' : 'Tulis Baru' ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/cms/agenda') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95 rounded-lg">
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

    <form action="<?= base_url('app/cms/agenda/save') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= isset($agenda) ? (is_object($agenda) ? $agenda->id : $agenda['id']) : '' ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM UTAMA (EDITOR & DETAIL) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Nama & Waktu -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="mb-6">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Nama Kegiatan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_kegiatan" required 
                               placeholder="Contoh: Rapat Pleno Yayasan"
                               class="block w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all rounded-lg placeholder:font-normal"
                               value="<?= isset($agenda) ? esc(is_object($agenda) ? $agenda->nama_kegiatan : $agenda['nama_kegiatan']) : '' ?>">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="tanggal_mulai" required
                                   class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-indigo-500 focus:ring-0"
                                   value="<?= isset($agenda) ? date('Y-m-d\TH:i', strtotime(is_object($agenda) ? $agenda->tanggal_mulai : $agenda['tanggal_mulai'])) : '' ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                                Selesai (Opsional)
                            </label>
                            <?php 
                                $tglSelesai = isset($agenda) ? (is_object($agenda) ? $agenda->tanggal_selesai : $agenda['tanggal_selesai']) : null;
                                $valSelesai = $tglSelesai ? date('Y-m-d\TH:i', strtotime($tglSelesai)) : '';
                            ?>
                            <input type="datetime-local" name="tanggal_selesai"
                                   class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-indigo-500 focus:ring-0"
                                   value="<?= $valSelesai ?>">
                        </div>
                    </div>

                    <!-- Tempat -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Tempat / Lokasi
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <input type="text" name="tempat" 
                                   placeholder="Gedung Utama / Zoom Meeting"
                                   class="block w-full pl-10 pr-4 py-3 bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-0 transition-all rounded-lg placeholder:font-normal"
                                   value="<?= isset($agenda) ? esc(is_object($agenda) ? $agenda->tempat : $agenda['tempat']) : '' ?>">
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">
                        Keterangan Tambahan
                    </label>
                    <textarea name="keterangan" id="editor_agenda" class="w-full h-64"><?= isset($agenda) ? (is_object($agenda) ? $agenda->keterangan : $agenda['keterangan']) : '' ?></textarea>
                </div>

            </div>

            <!-- SIDEBAR (PUBLIKASI) -->
            <div class="space-y-6">
                
                <!-- Card Publikasi -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-paper-plane text-indigo-500"></i> Publikasi
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <!-- Target Unit -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Target Unit <span class="text-rose-500">*</span>
                            </label>
                            
                            <?php 
                                // Ambil data dari controller
                                $listUnit = isset($daftarUnit) ? $daftarUnit : [];
                                $userUnit = session('kode_jenjang');
                                $defaultUnit = isset($agenda) ? (is_object($agenda) ? $agenda->kode_jenjang : $agenda['kode_jenjang']) : old('kode_jenjang');
                                
                                // Logic Scoping
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
                                            class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-indigo-500 focus:ring-0 appearance-none cursor-pointer uppercase">
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

                        <!-- Status -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">
                                Status Publikasi
                            </label>
                            <div class="relative">
                                <select name="status" class="block w-full px-4 py-2 bg-white border border-slate-300 text-xs font-bold text-slate-700 rounded-lg focus:border-indigo-500 focus:ring-0 appearance-none cursor-pointer uppercase">
                                    <?php 
                                        $status = isset($agenda) ? (is_object($agenda) ? $agenda->status : $agenda['status']) : 'published';
                                    ?>
                                    <option value="published" <?= $status == 'published' ? 'selected' : '' ?>>PUBLISHED (TAYANG)</option>
                                    <option value="draft" <?= $status == 'draft' ? 'selected' : '' ?>>DRAFT (KONSEP)</option>
                                </select>
                                <div class="absolute right-3 top-2.5 text-slate-400 pointer-events-none"><i class="fas fa-chevron-down text-xs"></i></div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Agenda
                        </button>

                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-sky-50 border border-sky-100 rounded-2xl p-4 flex items-start gap-3">
                    <div class="text-sky-500 mt-0.5"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h4 class="text-[10px] font-bold text-sky-800 uppercase">Info Otomatis</h4>
                        <p class="text-[10px] text-sky-600 leading-snug">
                            Agenda yang disimpan akan otomatis muncul di kalender akademik unit terkait dan dashboard siswa.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
<script>
    tinymce.init({
        selector: '#editor_agenda',
        license_key: 'gpl',
        height: 300,
        menubar: false,
        plugins: 'lists link code wordcount emoticons',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link emoticons code',
        content_style: 'body { font-family: "Inter", sans-serif; font-size: 14px; line-height: 1.6; color: #334155; }',
        branding: false,
        promotion: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });
</script>

<style>
    .tox-tinymce { border-radius: 0.75rem !important; border-color: #e2e8f0 !important; }
    .tox-statusbar { border-radius: 0 0 0.75rem 0.75rem !important; }
</style>

<?= $this->endSection() ?>