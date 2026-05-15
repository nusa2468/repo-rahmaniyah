<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= isset($kurikulum['id']) ? 'Edit' : 'Tambah' ?> Kurikulum
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="w-full max-w-3xl mx-auto pb-10">
    
    <!-- ALERT SECTION -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 p-3 bg-emerald-500 text-white rounded-xl flex items-center gap-2 shadow-md shadow-emerald-500/20 animate-fade-in">
            <i class="fas fa-check-circle text-lg"></i>
            <p class="text-xs font-bold uppercase tracking-wide"><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors') || session()->getFlashdata('error')): ?>
        <div class="mb-4 p-4 bg-rose-50 border border-rose-100 dark:bg-rose-500/10 dark:border-rose-500/20 rounded-xl animate-fade-in">
            <div class="flex items-center gap-2 mb-2 text-rose-600 dark:text-rose-400">
                <i class="fas fa-exclamation-circle"></i>
                <h4 class="font-black uppercase tracking-widest text-[10px]">Terjadi Kesalahan</h4>
            </div>
            <ul class="list-disc list-inside text-xs font-medium text-rose-500 space-y-0.5 ml-1">
                <?php if (session()->getFlashdata('error')): ?>
                    <li><?= session()->getFlashdata('error') ?></li>
                <?php endif; ?>
                <?php if (session()->getFlashdata('errors')): foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="<?= base_url('app/masterdata/kurikulum') ?>" class="text-gray-400 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Master Data Kurikulum</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= isset($kurikulum['id']) ? 'Sunting Kurikulum' : 'Buat Kurikulum Baru' ?>
            </h1>
        </div>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/5 overflow-hidden">
        <div class="p-6">
            <?php
            $is_edit = isset($kurikulum['id']) && $kurikulum['id'] != '';
            $url = $is_edit 
                ? base_url('app/masterdata/kurikulum/update/' . $kurikulum['id']) 
                : base_url('app/masterdata/kurikulum/create');

            // Data Values (Logic: Old Input > DB Value > Default)
            $kode_jenjang_val = old('kode_jenjang', $kurikulum['kode_jenjang'] ?? '');
            $kode_kurikulum_val = old('kode_kurikulum', $kurikulum['kode_kurikulum'] ?? '');
            $nama_kurikulum_val = old('nama_kurikulum', $kurikulum['nama_kurikulum'] ?? '');
            $status_val = old('status', isset($kurikulum['status']) ? strtolower($kurikulum['status']) : 'aktif');
            $deskripsi_val = old('deskripsi', $kurikulum['deskripsi'] ?? '');
            $keterangan_val = old('keterangan', $kurikulum['keterangan'] ?? '');
            
            // Scope Unit Logic
            $sessionJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
            $isRestricted = !in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);
            ?>
            
            <form action="<?= $url ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>
                
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= esc($kurikulum['id']) ?>">
                <?php endif; ?>

                <!-- SECTION 1: Identitas & Scope -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- UNIT / JENJANG (Locked if restricted) -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Unit Afiliasi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kode_jenjang" required <?= $isRestricted ? 'disabled' : '' ?>
                                    class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-bold rounded-xl focus:ring-2 focus:ring-indigo-500 block p-3 appearance-none cursor-pointer <?= $isRestricted ? 'opacity-60 cursor-not-allowed' : '' ?>">
                                <option value="" disabled <?= empty($kode_jenjang_val) ? 'selected' : '' ?>>Pilih Unit...</option>
                                <?php if (!empty($jenjang_list)): ?>
                                    <?php foreach ($jenjang_list as $j): 
                                        $j_kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                        $j_nama = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                                    ?>
                                        <option value="<?= $j_kode ?>" <?= ($kode_jenjang_val == $j_kode || ($isRestricted && $sessionJenjang == $j_kode)) ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($j_kode) ?> - <?= $j_nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <?php if($isRestricted): ?>
                            <input type="hidden" name="kode_jenjang" value="<?= $sessionJenjang ?>">
                            <p class="text-[9px] text-amber-600 font-bold italic ml-1">* Unit terkunci sesuai otoritas akun Anda</p>
                        <?php endif; ?>
                    </div>

                    <!-- KODE KURIKULUM -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Kode Kurikulum <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="kode_kurikulum" value="<?= esc($kode_kurikulum_val) ?>" 
                               placeholder="MISAL: KM-SD-2024"
                               class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-black rounded-xl focus:ring-2 focus:ring-indigo-500 block w-full p-3 uppercase transition-all shadow-sm" required>
                    </div>
                </div>

                <!-- SECTION 2: Nama & Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NAMA KURIKULUM -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Nama Lengkap Kurikulum <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_kurikulum" value="<?= esc($nama_kurikulum_val) ?>" 
                               placeholder="Contoh: Kurikulum Merdeka Fase A"
                               class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-bold rounded-xl focus:ring-2 focus:ring-indigo-500 block w-full p-3 transition-all shadow-sm" required>
                    </div>

                    <!-- STATUS -->
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Status Penggunaan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="status" required
                                    class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-bold rounded-xl focus:ring-2 focus:ring-indigo-500 block p-3 appearance-none cursor-pointer shadow-sm">
                                <option value="aktif" <?= ($status_val == 'aktif') ? 'selected' : '' ?>>AKTIF (SEDANG BERJALAN)</option>
                                <option value="tidak aktif" <?= ($status_val == 'tidak aktif') ? 'selected' : '' ?>>TIDAK AKTIF (ARSIP)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Deskripsi & Catatan -->
                <div class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Deskripsi Kurikulum
                        </label>
                        <textarea name="deskripsi" rows="3" 
                                  placeholder="Jelaskan fokus utama atau dasar hukum kurikulum ini..."
                                  class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded-xl focus:ring-2 focus:ring-indigo-500 block w-full p-3 resize-none transition-all"><?= esc($deskripsi_val) ?></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider ml-1">
                            Keterangan Internal
                        </label>
                        <textarea name="keterangan" rows="2" 
                                  placeholder="Catatan tambahan (misal: Berlaku untuk angkatan 2024)"
                                  class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm font-medium rounded-xl focus:ring-2 focus:ring-indigo-500 block w-full p-3 resize-none transition-all"><?= esc($keterangan_val) ?></textarea>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex items-center justify-end gap-3">
                    <a href="<?= base_url('app/masterdata/kurikulum') ?>" 
                       class="px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:-translate-y-0.5 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fas fa-save text-xs"></i>
                        <?= $is_edit ? 'Simpan Perubahan' : 'Buat Kurikulum' ?>
                    </button>
                </div>

            </form>
        </div>
    </div>
    
    <?php if ($is_edit): ?>
        <div class="mt-8 flex flex-col items-center gap-1">
            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-gray-400">Security Identifier</p>
            <p class="text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700"><?= esc($kurikulum['id']) ?></p>
        </div>
    <?php endif; ?>

</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }
</style>

<?= $this->endSection() ?>