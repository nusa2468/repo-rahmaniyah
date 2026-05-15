<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="font-jakarta min-h-screen bg-gray-50/50 dark:bg-slate-950 p-6 lg:p-8 animate-in fade-in duration-500">
    <div class="max-w-7xl mx-auto">
        
        <!-- HEADER SECTION (Compact & Navigable) -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-5 w-1 bg-sky-600 rounded-full"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-sky-600">Manajemen Data</span>
                </div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                    Identitas <span class="text-sky-600">Lembaga</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 font-medium">
                    Konfigurasi profil resmi, visi, dan misi untuk setiap jenjang pendidikan.
                </p>
            </div>

            <!-- Tombol Navigasi Kembali -->
            <a href="<?= base_url('app/masterdata/dashboard') ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-600 dark:text-gray-300 hover:text-sky-600 hover:border-sky-200 transition-all shadow-sm group">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- NAVIGATION TABS (JENJANG) - Compact & Solid -->
        <div class="flex flex-wrap gap-1.5 mb-8 p-1 bg-gray-100 dark:bg-white/5 rounded-xl border border-gray-200/50 dark:border-white/10">
            <?php foreach ($list_jenjang as $j) : ?>
                <?php 
                    // Fail-safe: Support Array or Object access
                    $kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                    $nama = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                    $isActive = ($current_unit == $kode);
                ?>
                <a href="<?= base_url('app/masterdata/identitas?unit=' . $kode) ?>"
                   class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all duration-200 <?= $isActive ? 'bg-white dark:bg-sky-600 text-sky-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-gray-200' ?>">
                    <?= esc($nama) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- FORM UTAMA (KIRI) - Compact Layout -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
                    <!-- Card Header Solid -->
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center text-sky-600 text-sm">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h2 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-tight">Edit Informasi Unit</h2>
                        </div>
                        <span class="px-2.5 py-1 bg-sky-100 dark:bg-sky-500/20 text-[9px] font-black text-sky-600 rounded-md uppercase tracking-widest">
                            <?= esc($current_unit) ?>
                        </span>
                    </div>

                    <!-- Form Content - Rapat & Solid -->
                    <form action="<?= base_url('app/masterdata/identitas/update') ?>" method="post" class="p-6 space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="jenjang_target" value="<?= esc($current_unit) ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah" value="<?= esc($settings['nama_sekolah'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                       placeholder="Contoh: SMA IT Al-Fatih">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Motto Unit</label>
                                <input type="text" name="motto" value="<?= esc($settings['motto'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm italic focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                       placeholder="Contoh: Unggul, Berakhlak, Berprestasi">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Email Resmi</label>
                                <input type="email" name="email" value="<?= esc($settings['email'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nomor Telepon</label>
                                <input type="text" name="telepon" value="<?= esc($settings['telepon'] ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">
                                <?= ($current_unit == 'GLOBAL' || $current_unit == 'YAYASAN') ? 'Ketua Pengurus Yayasan' : 'Kepala Sekolah' ?>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-user-tie text-sm"></i>
                                </div>
                                <input type="text" name="kepala_sekolah" value="<?= esc($settings['kepala_sekolah'] ?? '') ?>"
                                       class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                       placeholder="Contoh: Dr. H. Ahmad, M.Pd.">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Visi</label>
                                <textarea name="visi" rows="3" class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= esc($settings['visi'] ?? '') ?></textarea>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Misi</label>
                                <textarea name="misi" rows="4" class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= esc($settings['misi'] ?? '') ?></textarea>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Sejarah Singkat</label>
                                <textarea name="sejarah" rows="5" class="w-full px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= esc($settings['sejarah'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Button Solid -->
                        <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                            <button type="submit"
                                    class="w-full sm:w-auto px-8 py-3.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg hover:shadow-sky-500/30 active:scale-98 transition-all duration-200 flex items-center justify-center gap-2 mx-auto">
                                <i class="fas fa-save"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SIDEBAR INFO (KANAN) - Compact & Solid -->
            <div class="lg:col-span-4 space-y-5">
                <!-- Info Card -->
                <div class="p-6 bg-gradient-to-br from-sky-500 to-sky-600 rounded-3xl text-white shadow-xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-white/5 backdrop-blur-[2px]"></div>
                    <div class="relative z-10">
                        <h4 class="text-base font-black mb-3 uppercase tracking-tight">Informasi Unit</h4>
                        <p class="text-xs leading-relaxed opacity-90 mb-4">
                            Data identitas ini akan otomatis digunakan pada:
                        </p>
                        <ul class="space-y-2.5 text-[10px] font-bold uppercase tracking-wide">
                            <li class="flex items-center gap-2.5"><i class="fas fa-globe text-sky-200"></i> Website & Landing Page</li>
                            <li class="flex items-center gap-2.5"><i class="fas fa-file-alt text-sky-200"></i> Header Laporan Resmi</li>
                            <li class="flex items-center gap-2.5"><i class="fas fa-tachometer-alt text-sky-200"></i> Profil di Dashboard</li>
                        </ul>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-2xl">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center text-white text-xs">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h5 class="text-[10px] font-black text-amber-900 dark:text-amber-300 uppercase tracking-widest">Perhatian</h5>
                            <p class="text-[10px] text-amber-800 dark:text-amber-400 mt-1 leading-tight">
                                Perubahan akan langsung tayang di website publik. Pastikan data akurat dan sesuai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>