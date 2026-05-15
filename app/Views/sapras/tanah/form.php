<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Sapras</li>
                    <li><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase">Tanah</span></li>
                    <li aria-current="page"><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase italic underline decoration-2 text-indigo-600">Form Input</span></li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/sapras/tanah') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Error -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm animate-pulse">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight">Periksa Inputan Anda</h3>
            </div>
            <div class="text-xs font-bold text-rose-700 space-y-1 ml-6 uppercase tracking-tight">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p>• <?= $error ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white shadow-2xl border-t-4 border-indigo-600 overflow-hidden rounded-sm">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center text-indigo-600">
                <i class="fas fa-edit mr-2"></i>
                <h2 class="text-xs font-black uppercase tracking-widest italic">Detail Aset Lahan</h2>
            </div>
        </div>

        <div class="p-6 sm:p-10">
            <?php 
                $is_edit = (isset($tanah) && $tanah);
                $url = base_url('app/sapras/tanah/save');
                
                // Ambil daftar unit dari Controller
                $listUnit = isset($daftarUnit) ? $daftarUnit : [];
                
                // Cek Session User
                $userUnit = session('kode_jenjang'); 
                
                // Nilai default untuk Unit (Prioritas: Old Input -> Database -> Session)
                $defaultUnit = old('kode_jenjang', $tanah->kode_jenjang ?? '');
            ?>
            
            <form action="<?= $url ?>" method="post" class="space-y-8">
                <?= csrf_field() ?>
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?= $tanah->id ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- FIELD UNIT KERJA (DINAMIS) -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Unit / Jenjang Kerja <span class="text-rose-600">*</span>
                        </label>
                        
                        <?php if (!empty($userUnit) && array_key_exists($userUnit, $listUnit)): ?>
                            <!-- KASUS 1: ADMIN UNIT (LOCKED) -->
                            <div class="relative">
                                <input type="text" value="<?= $listUnit[$userUnit] ?? $userUnit ?>" disabled 
                                       class="block w-full px-4 py-3 bg-slate-100 border-2 border-slate-200 text-slate-500 font-bold cursor-not-allowed uppercase">
                                <input type="hidden" name="kode_jenjang" value="<?= $userUnit ?>">
                                <div class="absolute right-3 top-3 text-slate-400">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic">Anda hanya dapat menambahkan aset untuk unit <?= $userUnit ?>.</p>
                        
                        <?php else: ?>
                            <!-- KASUS 2: SUPERADMIN (SELECTABLE) -->
                            <div class="relative">
                                <select name="kode_jenjang" required class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold uppercase tracking-wide focus:ring-0 focus:border-indigo-600 transition-all appearance-none cursor-pointer">
                                    <option value="" disabled <?= empty($defaultUnit) ? 'selected' : '' ?>>-- PILIH UNIT PEMILIK ASET --</option>
                                    
                                    <?php if (!empty($listUnit)): ?>
                                        <?php foreach($listUnit as $kode => $label): ?>
                                            <option value="<?= esc($kode) ?>" <?= ($defaultUnit == $kode) ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="absolute right-3 top-3.5 text-slate-400 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-[9px] font-bold text-indigo-400 uppercase italic">Mode Superadmin: Silakan tentukan kepemilikan unit.</p>
                        <?php endif; ?>
                    </div>

                    <!-- NAMA TANAH -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">Nama Lahan / Persil <span class="text-rose-600">*</span></label>
                        <input type="text" name="nama" required placeholder="CONTOH: LAHAN KAMPUS TIMUR"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold uppercase focus:border-indigo-600 focus:ring-0 transition-all"
                               value="<?= old('nama', $tanah->nama ?? '') ?>">
                    </div>

                    <!-- LUAS -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">Luas Tanah</label>
                        <div class="flex">
                            <input type="number" step="0.01" name="luas" placeholder="0.00"
                                   class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold focus:border-indigo-600 focus:ring-0 transition-all border-r-0"
                                   value="<?= old('luas', $tanah->luas ?? '') ?>">
                            <span class="inline-flex items-center px-4 bg-slate-100 border-2 border-slate-200 text-slate-500 text-xs font-black uppercase">M²</span>
                        </div>
                    </div>

                    <!-- SERTIFIKAT -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">No. Sertifikat</label>
                        <input type="text" name="sertifikat" placeholder="NOMOR DOKUMEN LEGALITAS"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold uppercase focus:border-indigo-600 focus:ring-0 transition-all"
                               value="<?= old('sertifikat', $tanah->sertifikat ?? '') ?>">
                    </div>

                    <!-- KETERANGAN -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">Keterangan Tambahan</label>
                        <textarea name="keterangan" rows="3" placeholder="Deskripsi kondisi lahan atau batas wilayah..."
                                  class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-medium focus:border-indigo-600 focus:ring-0 transition-all"><?= old('keterangan', $tanah->keterangan ?? '') ?></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <button type="submit" class="px-8 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all border-b-4 border-indigo-800 active:scale-95">
                        <i class="fas fa-save mr-2"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>