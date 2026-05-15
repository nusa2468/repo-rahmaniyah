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
                    <li><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase">Inventaris</span></li>
                    <li aria-current="page"><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase italic underline decoration-2 text-indigo-600">Form Input</span></li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/sapras/inventaris') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm animate-pulse">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight italic">Kesalahan Validasi Data</h3>
            </div>
            <div class="text-[11px] font-bold text-rose-700 space-y-1 ml-6 uppercase tracking-tight italic">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p>• <?= $error ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white shadow-2xl border-2 border-slate-200 overflow-hidden rounded-none">
        <div class="bg-slate-900 border-b-2 border-slate-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-archive text-indigo-400 mr-3"></i>
                <h2 class="text-xs font-black text-white uppercase tracking-widest italic">
                    <?= (isset($inventaris) && $inventaris) ? 'Ubah Informasi Inventaris' : 'Registrasi Barang Inventaris Baru' ?>
                </h2>
            </div>
            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic leading-none">Inventory Asset v4.0</span>
        </div>

        <div class="p-6 sm:p-10">
            <?php 
                $is_edit = (isset($inventaris) && $inventaris);
                // FIX: Gunakan single action 'save'
                $url = base_url('app/sapras/inventaris/save');
                
                // Ambil daftar unit dari Controller
                $listUnit = isset($daftarUnit) ? $daftarUnit : [];
                
                // Cek Session User
                $userUnit = session('kode_jenjang'); 
                $defaultUnit = old('kode_jenjang', $inventaris->kode_jenjang ?? '');
            ?>
            <form action="<?= $url ?>" method="post" class="space-y-8">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $is_edit ? $inventaris->id : '' ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- KODE JENJANG (Scope Unit Dinamis) -->
                    <div class="md:col-span-2">
                        <label for="kode_jenjang" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Unit / Jenjang Pengelola <span class="text-rose-600">*</span>
                        </label>
                        
                        <?php if (!empty($userUnit) && array_key_exists(strtoupper($userUnit), $listUnit)): ?>
                            <!-- KASUS 1: ADMIN UNIT (LOCKED) -->
                            <div class="flex items-center p-4 bg-slate-50 border-2 border-slate-200 border-l-4 border-l-indigo-500">
                                <i class="fas fa-shield-alt text-indigo-500 mr-4"></i>
                                <div>
                                    <p class="text-sm font-black text-slate-800 uppercase italic leading-none"><?= esc(strtoupper($userUnit)) ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic leading-none">Unit terdeteksi otomatis sesuai otoritas login</p>
                                </div>
                            </div>
                            <input type="hidden" name="kode_jenjang" value="<?= esc($userUnit) ?>">
                        
                        <?php else: ?>
                            <!-- KASUS 2: SUPERADMIN (SELECTABLE) -->
                            <select name="kode_jenjang" id="kode_jenjang" required
                                    class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black uppercase tracking-tight focus:ring-0 focus:border-indigo-500 transition-all rounded-none cursor-pointer">
                                <option value="" disabled <?= empty($defaultUnit) ? 'selected' : '' ?>>-- Pilih Unit Kerja --</option>
                                <?php if (!empty($listUnit)): ?>
                                    <?php foreach ($listUnit as $kode => $label): ?>
                                        <option value="<?= esc($kode) ?>" <?= ($defaultUnit == $kode) ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="mt-2 text-[9px] font-bold text-indigo-400 uppercase italic">Mode Superadmin: Silakan tentukan kepemilikan unit.</p>
                        <?php endif; ?>
                    </div>

                    <!-- NAMA INVENTARIS -->
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Identitas / Nama Inventaris <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" name="nama" id="nama" required
                               placeholder="Contoh: MEJA GURU / KURSI SISWA / LEMARI ARSIP"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tight focus:ring-0 focus:border-indigo-500 transition-all rounded-none uppercase italic"
                               value="<?= old('nama', $inventaris->nama ?? '') ?>">
                        <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic tracking-tighter">Gunakan nama kategori barang yang umum dan jelas.</p>
                    </div>

                    <!-- JUMLAH / KUANTITAS -->
                    <div>
                        <label for="jumlah" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Kuantitas / Jumlah Unit
                        </label>
                        <div class="flex gap-0 ring-2 ring-slate-100">
                            <input type="number" name="jumlah" id="jumlah" min="1"
                                   placeholder="0"
                                   class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tighter focus:ring-0 focus:border-indigo-500 transition-all rounded-none italic"
                                   value="<?= old('jumlah', $inventaris->jumlah ?? '1') ?>">
                            <div class="flex items-center px-4 bg-slate-100 border-y-2 border-r-2 border-slate-200 text-[10px] font-black text-slate-400 uppercase italic">
                                Satuan
                            </div>
                        </div>
                    </div>

                    <!-- KONDISI BARANG -->
                    <div>
                        <label for="kondisi" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Kondisi Inventaris <span class="text-rose-600">*</span>
                        </label>
                        <select name="kondisi" id="kondisi" required
                                class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black uppercase tracking-tight focus:ring-0 focus:border-indigo-500 transition-all rounded-none italic cursor-pointer">
                            <?php 
                                $valKondisi = old('kondisi', $inventaris->kondisi ?? 'Baik');
                                $opts = ['Baik', 'Rusak Ringan', 'Rusak Berat'];
                            ?>
                            <?php foreach($opts as $opt): ?>
                                <option value="<?= $opt ?>" <?= $valKondisi == $opt ? 'selected' : '' ?>><?= strtoupper($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- KETERANGAN -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Informasi Tambahan / Spesifikasi
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="4"
                                  placeholder="Contoh: Lokasi Ruang Guru, Pengadaan Tahun 2024, Kondisi Kayu Baik..."
                                  class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-medium tracking-tight focus:ring-0 focus:border-indigo-500 transition-all rounded-none italic"><?= old('keterangan', $inventaris->keterangan ?? '') ?></textarea>
                    </div>

                </div>

                <!-- Action Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-10 border-t-2 border-slate-100 mt-10">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-tight">
                        <span class="text-rose-600">*</span> Field wajib untuk audit inventaris berkala.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="<?= base_url('app/sapras/inventaris') ?>" 
                           class="flex-1 sm:flex-none text-center px-6 py-3 bg-white border-2 border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                            Batalkan
                        </a>
                        <button type="submit" 
                                class="flex-1 sm:flex-none px-10 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all border-b-4 border-indigo-900">
                            <i class="fas fa-save mr-2 text-[10px]"></i> <?= $is_edit ? 'Update Inventaris' : 'Simpan Inventaris' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .font-black { font-weight: 900; }
</style>

<?= $this->endSection() ?>