<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    $is_edit = isset($agen);
    $action_url = $is_edit 
        ? base_url('app/ppdb/affiliate/saveAgen/' . $agen->affiliate_id) 
        : base_url('app/ppdb/affiliate/saveAgen');
        
    // Logic untuk mengambil opsi Jenjang (Khusus Superadmin)
    $listUnit = [];
    if (in_array(session('role_name'), ['superadmin', 'yayasan'])) {
        try {
            $jenjangModel = new \App\Models\JenjangModel();
            $listUnit = $jenjangModel->findAll();
        } catch (\Throwable $e) {}
    }
?>

<div class="max-w-5xl mx-auto">
    <!-- Header Compact & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= $title ?>
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manajemen profil agen marketing dan penetapan metode kerja.
            </p>
        </div>
        <a href="<?= base_url('app/ppdb/affiliate') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
            <i class="fas fa-times"></i>
            Batal
        </a>
    </div>

    <!-- Form Card - Super Compact, Rapat, Solid -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                <i class="fas fa-user-tie text-sky-500"></i>
                Form Profil Agen Marketing
            </h3>
        </div>

        <form action="<?= esc($action_url) ?>" method="post" class="p-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profil & Strategi (2/3) -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h4 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-id-card text-sky-600"></i>
                            Profil & Strategi Marketing
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <!-- UPDATE: INPUT UNIT (Khusus Superadmin) -->
                            <?php if (!empty($listUnit)): ?>
                            <div class="space-y-1.5 md:col-span-2">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Unit Afiliasi <span class="text-red-500">*</span>
                                </label>
                                <select name="kode_jenjang" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all" required>
                                    <option value="GLOBAL" <?= old('kode_jenjang', $agen->kode_jenjang ?? '') === 'GLOBAL' ? 'selected' : '' ?>>GLOBAL / SEMUA UNIT</option>
                                    <?php foreach ($listUnit as $u): ?>
                                        <?php 
                                            $val = is_array($u) ? ($u['kode_jenjang'] ?? $u['nama']) : ($u->kode_jenjang ?? $u->nama); 
                                            $selected = old('kode_jenjang', $agen->kode_jenjang ?? '') === $val ? 'selected' : '';
                                        ?>
                                        <option value="<?= esc($val) ?>" <?= $selected ?>>Unit <?= esc($val) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Pilih 'GLOBAL' jika agen bisa merekrut siswa untuk semua jenjang.</p>
                            </div>
                            <?php endif; ?>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Nama Lengkap Agen <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_agen"
                                       value="<?= old('nama_agen', $agen->nama_agen ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                       required>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Kode Agen <?= $is_edit ? '(Otomatis)' : '' ?>
                                </label>
                                <input type="text" name="kode_agen"
                                       value="<?= old('kode_agen', $agen->kode_agen ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-mono focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                       <?= $is_edit ? 'readonly' : '' ?>
                                       placeholder="AUTO">
                            </div>
                        </div>

                        <!-- DROPDOWN STRATEGI (METODE) -->
                        <div class="mt-5 space-y-1.5">
                            <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                Metode Marketing Utama (KPI)
                            </label>
                            <select name="metode_agen"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                                <option value="" disabled <?= empty($agen->metode_agen) ? 'selected' : '' ?>>-- Pilih Strategi --</option>
                                
                                <?php 
                                    // Daftar strategi sesuai migration comment
                                    $strategies = [
                                        '4P'      => 'Strategi 4P (Product, Price, Place, Promotion)',
                                        'Digital' => 'Digital Marketing (FB/IG Ads, TikTok)',
                                        'Canvas'  => 'School Visit / Kanvasing / Door-to-Door',
                                        'Alumni'  => 'Referal Alumni / Member Get Member',
                                        'Event'   => 'Event & Expo Pendidikan',
                                        'Umum'    => 'Umum / Konvensional'
                                    ];
                                ?>

                                <?php foreach($strategies as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= old('metode_agen', $agen->metode_agen ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Metode ini digunakan untuk mengukur efektivitas jalur pendaftaran di Dashboard.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    WhatsApp
                                </label>
                                <input type="text" name="no_hp"
                                       value="<?= old('no_hp', $agen->no_hp ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Email
                                </label>
                                <input type="email" name="email"
                                       value="<?= old('email', $agen->email ?? '') ?>"
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="mt-5 space-y-1.5">
                            <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                Alamat Domisili
                            </label>
                            <textarea name="alamat" rows="3"
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= old('alamat', $agen->alamat ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Target & Finansial (1/3) -->
                <div class="space-y-6">
                    <div>
                        <h4 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-coins text-emerald-600"></i>
                            Target & Komisi
                        </h4>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Target Siswa
                                </label>
                                <div class="relative">
                                    <input type="number" name="target_pendaftar"
                                           value="<?= old('target_pendaftar', $agen->target_pendaftar ?? 0) ?>"
                                           class="w-full px-4 py-3 pr-16 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold text-right focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500 dark:text-gray-400">Siswa</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                    Status Agen
                                </label>
                                <select name="status"
                                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                                    <option value="Aktif" <?= old('status', $agen->status ?? '') === 'Aktif' ? 'selected' : '' ?>>🟢 Aktif</option>
                                    <option value="Non-Aktif" <?= old('status', $agen->status ?? '') === 'Non-Aktif' ? 'selected' : '' ?>>🔴 Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5 space-y-1.5">
                            <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                Fee per Siswa (Rp)
                            </label>
                            <input type="number" name="fee_per_siswa"
                                   value="<?= old('fee_per_siswa', $agen->fee_per_siswa ?? 0) ?>"
                                   class="w-full px-4 py-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl text-lg font-black text-right text-emerald-700 dark:text-emerald-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
                        </div>

                        <div class="mt-5 p-5 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 space-y-4">
                            <h5 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-university"></i>
                                Rekening Pencairan
                            </h5>
                            <input type="text" name="nama_bank" placeholder="Nama Bank"
                                   value="<?= old('nama_bank', $agen->nama_bank ?? '') ?>"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            <input type="text" name="nomor_rekening" placeholder="Nomor Rekening"
                                   value="<?= old('nomor_rekening', $agen->nomor_rekening ?? '') ?>"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-mono focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            <input type="text" name="nama_rekening" placeholder="Atas Nama"
                                   value="<?= old('nama_rekening', $agen->nama_rekening ?? '') ?>"
                                   class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                        </div>

                        <div class="mt-5 space-y-1.5">
                            <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                Catatan Khusus
                            </label>
                            <textarea name="catatan" rows="3"
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= old('catatan', $agen->catatan ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <?php if ($is_edit): ?>
                        <a href="<?= base_url('app/ppdb/affiliate/deleteAgen/' . $agen->affiliate_id) ?>"
                           onclick="return confirm('Hapus agen ini secara permanen?')"
                           class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                            <i class="fas fa-trash-alt"></i>
                            Hapus Agen
                        </a>
                    <?php endif; ?>
                </div>

                <button type="submit"
                        class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm uppercase tracking-widest rounded-xl shadow-lg hover:shadow-emerald-500/30 transition-all active:scale-98 flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Agen Baru' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>