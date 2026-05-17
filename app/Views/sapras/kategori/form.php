<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// Deteksi Role untuk membatasi Dropdown Unit
$sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
$globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];
$isGlobalUser = in_array($sessionJenjang, $globalIdentifiers);
?>

<div class="px-4 py-6 max-w-4xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <?= $kategori ? 'Perbarui informasi kategori aset yang sudah ada.' : 'Tambahkan klasifikasi master baru ke dalam sistem ERP.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/sapras/kategori') ?>" 
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- ERROR VALIDATION ALERT -->
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-widest mb-1">Gagal Menyimpan</h3>
                    <ul class="list-disc list-inside text-xs text-rose-700 dark:text-rose-400 space-y-1">
                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <!-- Form Accent Line -->
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-blue-500"></div>

        <form action="<?= base_url('app/sapras/kategori/save') ?>" method="post" class="p-6 md:p-8 space-y-6">
            <?= csrf_field() ?>
            
            <?php if ($kategori): ?>
                <input type="hidden" name="id" value="<?= esc($kategori->id) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- DROPDOWN UNIT KEPEMILIKAN -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        Unit Pemilik Kategori <span class="text-rose-500">*</span>
                    </label>
                    
                    <?php if ($isGlobalUser): ?>
                        <div class="relative">
                            <select name="kode_jenjang" required
                                    class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase">
                                <option value="GLOBAL" <?= old('kode_jenjang', $kategori->kode_jenjang ?? '') == 'GLOBAL' ? 'selected' : '' ?>>GLOBAL (Berlaku untuk Semua Unit)</option>
                                <?php foreach ($daftarUnit as $kode => $nama): ?>
                                    <option value="<?= $kode ?>" <?= old('kode_jenjang', $kategori->kode_jenjang ?? '') == $kode ? 'selected' : '' ?>>
                                        <?= esc($nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5">
                            Jika dipilih GLOBAL, maka seluruh unit sekolah dapat menggunakan kategori ini.
                        </p>
                    <?php else: ?>
                        <!-- Jika Admin Unit, Paksa Readonly Sesuai Unitnya -->
                        <input type="hidden" name="kode_jenjang" value="<?= esc($sessionJenjang) ?>">
                        <div class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-2 cursor-not-allowed">
                            <i class="fas fa-lock text-slate-400"></i> Unit <?= esc($sessionJenjang) ?> (Terikat Hak Akses)
                        </div>
                    <?php endif; ?>
                </div>

                <!-- KODE KATEGORI -->
                <div>
                    <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        Kode Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kode_kategori" required
                           class="w-full px-4 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all text-sm font-mono font-bold text-slate-900 dark:text-white uppercase placeholder-slate-300 dark:placeholder-slate-600"
                           placeholder="Contoh: KTG-ELK"
                           value="<?= old('kode_kategori', $kategori->kode_kategori ?? '') ?>">
                </div>

                <!-- TIPE ASET MASTER -->
                <div>
                    <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        Tipe Aset Utama <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="tipe_aset" required
                                class="w-full pl-4 pr-10 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none">
                            <?php 
                            $tipe_opts = ['Bangunan/Tanah', 'Elektronik', 'Furniture', 'Kendaraan', 'Lainnya'];
                            $selectedTipe = old('tipe_aset', $kategori->tipe_aset ?? '');
                            foreach ($tipe_opts as $opt): 
                            ?>
                                <option value="<?= $opt ?>" <?= $selectedTipe == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <!-- NAMA KATEGORI -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                        Nama Klasifikasi / Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" required
                           class="w-full px-4 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all text-sm font-bold text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-slate-600"
                           placeholder="Contoh: Peralatan Elektronik & Komputer"
                           value="<?= old('nama_kategori', $kategori->nama_kategori ?? '') ?>">
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/sapras/kategori') ?>" class="px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-indigo-800">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
            
        </form>
    </div>
</div>

<?= $this->endSection() ?>