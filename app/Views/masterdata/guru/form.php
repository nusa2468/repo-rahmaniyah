<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= esc($title ?? 'Form Guru') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Logic PHP tetap dipertahankan
$guru_array = (array) ($guru ?? []);
$is_edit = isset($guru_array['id']) && !empty($guru_array['id']);

// Penentuan URL Action
$url = $is_edit 
    ? base_url('app/masterdata/guru/update/' . $guru_array['id']) 
    : base_url('app/masterdata/guru/create');

// Helper sederhana untuk mengambil value lama atau default
$get_val = fn($field, $default = '') => old($field, $guru_array[$field] ?? $default);
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola informasi biografi dan akses sistem personel pendidik.
            </p>
        </div>
        <a href="<?= base_url('app/masterdata/guru') ?>" 
           class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors shadow-sm">
            <i class="fas fa-arrow-left mr-2 text-xs"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Terdapat kesalahan input:</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 shadow-sm flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            <span class="text-sm font-medium text-red-800 dark:text-red-200"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif ?>

    <!-- Main Form Card -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
        
        <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-clipboard-list text-sky-600 dark:text-sky-400"></i>
                Formulir Personel
            </h2>
        </div>

        <form action="<?= $url ?>" method="post" enctype="multipart/form-data" class="p-6">
            <?= csrf_field() ?>
            <?php if ($is_edit) : ?>
                <input type="hidden" name="_method" value="PUT">
            <?php endif; ?>

            <!-- SECTION 1: IDENTITAS -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold text-sm border border-sky-200 dark:border-sky-800">1</div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Identitas Pokok & Penempatan</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-11">
                    <!-- Nama Lengkap -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Nama Lengkap & Gelar <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" value="<?= esc($get_val('nama_lengkap')) ?>" 
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 transition-shadow"
                               placeholder="Contoh: Budi Santoso, S.Pd." required>
                    </div>

                    <!-- Unit / Jenjang -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Unit Sekolah <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kode_jenjang" required
                                    class="w-full pl-4 pr-10 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white appearance-none cursor-pointer">
                                <option value="">-- Pilih Unit --</option>
                                <?php foreach ($jenjangs as $jenjang): 
                                    $j_kode = is_object($jenjang) ? $jenjang->kode_jenjang : $jenjang['kode_jenjang'];
                                    $j_nama = is_object($jenjang) ? $jenjang->nama_jenjang : $jenjang['nama_jenjang'];
                                ?>
                                    <option value="<?= $j_kode ?>" <?= ($get_val('kode_jenjang') == $j_kode) ? 'selected' : '' ?>>
                                        <?= strtoupper($j_kode) ?> - <?= $j_nama ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- NUPTK -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            NUPTK
                        </label>
                        <input type="text" name="nuptk" value="<?= esc($get_val('nuptk')) ?>" 
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow font-mono"
                               placeholder="16 Digit NUPTK">
                    </div>

                    <!-- NIK -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            NIK (No. KTP) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nik" value="<?= esc($get_val('nik')) ?>" 
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow font-mono"
                               placeholder="16 Digit NIK" required>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_kelamin" required
                                    class="w-full pl-4 pr-10 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white appearance-none cursor-pointer">
                                <option value="">-- Pilih --</option>
                                <option value="L" <?= ($get_val('jenis_kelamin') == 'L') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= ($get_val('jenis_kelamin') == 'P') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Status Keaktifan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="status" required
                                    class="w-full pl-4 pr-10 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white appearance-none cursor-pointer">
                                <option value="aktif" <?= ($get_val('status') == 'aktif') ? 'selected' : '' ?>>Aktif Bekerja</option>
                                <option value="nonaktif" <?= ($get_val('status') == 'nonaktif') ? 'selected' : '' ?>>Nonaktif / Cuti</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- TMT -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            TMT Sekolah Induk
                        </label>
                        <input type="date" name="tmt_sekolah_induk" value="<?= esc($get_val('tmt_sekolah_induk')) ?>" 
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow">
                    </div>

                    <!-- Foto -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Foto Profil (Max 1MB)
                        </label>
                        <input type="file" name="foto" accept="image/*"
                               class="block w-full text-sm text-gray-500 dark:text-gray-400
                                      file:mr-4 file:py-2.5 file:px-4
                                      file:rounded-xl file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-sky-50 file:text-sky-700
                                      dark:file:bg-sky-900/20 dark:file:text-sky-400
                                      hover:file:bg-sky-100 dark:hover:file:bg-sky-900/30
                                      transition-all border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-950">
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-800 my-8">

            <!-- SECTION 2: AKUN -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 font-bold text-sm border border-red-200 dark:border-red-800">2</div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Kredensial Akun Sistem</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-0 md:pl-11 p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" value="<?= esc($get_val('username')) ?>" 
                               <?= $is_edit ? 'readonly' : 'required' ?>
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow 
                                      <?= $is_edit ? 'bg-gray-100 dark:bg-gray-800 text-gray-500 cursor-not-allowed' : 'bg-white dark:bg-gray-950' ?>">
                        <?php if ($is_edit): ?>
                            <p class="mt-1 text-xs text-gray-400 italic">Username dikunci demi keamanan.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="<?= esc($get_val('email')) ?>" required
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Password <?= $is_edit ? '<span class="text-gray-400 font-normal normal-case">(Isi jika ingin ubah)</span>' : '<span class="text-red-500">*</span>' ?>
                        </label>
                        <input type="password" name="password" <?= $is_edit ? '' : 'required' ?>
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow"
                               placeholder="••••••••">
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1.5">
                            Konfirmasi Password
                        </label>
                        <input type="password" name="pass_confirm" <?= $is_edit ? '' : 'required' ?>
                               class="w-full px-4 py-2.5 text-sm bg-white dark:bg-gray-950 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-white transition-shadow"
                               placeholder="••••••••">
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
                <a href="<?= base_url('app/masterdata/guru') ?>" 
                   class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-sky-600/20 transition-all hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-sky-600">
                    <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Update Data Guru' : 'Simpan Guru Baru' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>