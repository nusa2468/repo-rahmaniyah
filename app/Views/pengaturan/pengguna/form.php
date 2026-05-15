<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // =========================================================================
    // ROBUST DATA HANDLING HELPER
    // =========================================================================
    $get = function($data, $field, $default = '') {
        if (is_array($data) && array_key_exists($field, $data)) return $data[$field];
        if (is_object($data) && property_exists($data, $field)) return $data->$field;
        return $default;
    };

    // Inisialisasi Variable Utama
    $user = $user ?? [];

    // Ekstraksi Data Aman
    $id_user      = $get($user, 'id');
    $nama_lengkap = $get($user, 'nama_lengkap');
    $username     = $get($user, 'username');
    $email        = $get($user, 'email');
    $id_role_db   = $get($user, 'id_role', $get($user, 'role_id')); // Support alias
    $kode_jenjang = $get($user, 'kode_jenjang');
    
    // Logika Status: 
    // - Default '1' jika data baru (id kosong).
    // - Jika edit, ambil dari db. Jika key tidak ada, anggap 1 (fail-safe).
    $status_raw = $get($user, 'is_active', null);
    if ($status_raw === null && empty($id_user)) {
        $status_db = 1; // Mode Create Default
    } elseif ($status_raw === null && !empty($id_user)) {
        $status_db = 1; // Fallback jika kolom is_active tidak ter-select query
    } else {
        $status_db = $status_raw;
    }

    $is_edit = !empty($id_user);

    // Setup Validation & Title
    $validation = \Config\Services::validation();
    $title_form = $is_edit 
        ? 'Edit Pengguna: ' . esc($nama_lengkap ?: $username)
        : 'Tambah Pengguna Baru';
        
    $action_url = $is_edit 
        ? base_url('app/pengaturan/pengguna/update/' . $id_user)
        : base_url('app/pengaturan/pengguna/store');

    // SETUP OLD VALUES
    $val_role    = old('id_role', $id_role_db);
    $val_jenjang = old('kode_jenjang', $kode_jenjang);
    
    // Normalisasi Status ke Integer untuk comparison yang akurat (Handle "1", 1, true)
    $val_active_raw = old('is_active', $status_db);
    $val_active     = (int)$val_active_raw; 

    $roles = $roles ?? [];
    $password_required = $is_edit ? '' : 'required';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= esc($title_form) ?>
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <?= $is_edit ? 'Perbarui data akun pengguna & hak akses' : 'Buat akun baru untuk akses sistem' ?>
            </p>
        </div>
        <a href="<?= base_url('app/pengaturan/pengguna') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <!-- Alert Error Flashdata -->
    <?php if(session()->has('errors')): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-red-800 mb-1">Terjadi Kesalahan:</h3>
                    <ul class="list-disc pl-4 text-sm text-red-700 space-y-1">
                        <?php foreach(session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">
                Formulir Pengguna
            </h3>
            <?php if($is_edit): ?>
                <span class="text-[10px] font-mono bg-blue-100 text-blue-700 px-2 py-1 rounded">ID: <?= esc($id_user) ?></span>
            <?php endif; ?>
        </div>

        <div class="p-6">
            <form action="<?= esc($action_url) ?>" method="post" autocomplete="off" class="space-y-6">
                <?= csrf_field() ?>
                
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?= esc($id_user) ?>">
                <?php endif; ?>

                <!-- Nama Lengkap -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <input type="text"
                               name="nama_lengkap"
                               value="<?= old('nama_lengkap', $nama_lengkap) ?>"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('nama_lengkap') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               required placeholder="Contoh: Administrator Utama">
                    </div>
                </div>

                <!-- Username -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <input type="text"
                               name="username"
                               value="<?= old('username', $username) ?>"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('username') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm font-mono focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               required autocomplete="off" placeholder="username_login">
                    </div>
                </div>

                <!-- Email -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <input type="email"
                               name="email"
                               value="<?= old('email', $email) ?>"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('email') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               required autocomplete="off" placeholder="email@sekolah.sch.id">
                    </div>
                </div>

                <!-- Role -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Role Pengguna <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <select name="id_role"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('id_role') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                                required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <?php
                                    // Handle Role Object/Array
                                    $r_id   = is_object($role) ? $role->id : $role['id'];
                                    $r_name = is_object($role) ? ($role->name ?? $role->role_name) : ($role['name'] ?? $role['role_name']);
                                ?>
                                <option value="<?= esc($r_id) ?>" <?= $val_role == $r_id ? 'selected' : '' ?>>
                                    <?= esc($r_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Unit Sekolah -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Unit Sekolah <span class="text-red-500">*</span>
                    </label>
                    <div class="md:col-span-2">
                        <!-- PERBAIKAN: Value GLOBAL harus diisi 'GLOBAL' agar validasi required terpenuhi -->
                        <select name="kode_jenjang" required class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                            <option value="GLOBAL" <?= ($val_jenjang == 'GLOBAL' || empty($val_jenjang)) ? 'selected' : '' ?>>GLOBAL (Semua Unit)</option>
                            <option value="SD" <?= ($val_jenjang == 'SD') ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= ($val_jenjang == 'SMP') ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= ($val_jenjang == 'SMA') ? 'selected' : '' ?>>SMA</option>
                        </select>
                        <p class="mt-1 text-[10px] text-gray-400 italic">Pilih 'GLOBAL' jika user ini adalah Admin Yayasan.</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-white/10 my-6"></div>

                <!-- Password -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider pt-3">
                        Password <?= $is_edit ? '<span class="text-gray-400 font-normal normal-case text-xs block">(Kosongkan jika tetap)</span>' : '<span class="text-red-500">*</span>' ?>
                    </label>
                    <div class="md:col-span-2">
                        <input type="password"
                               name="password"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('password') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               <?= $password_required ?> autocomplete="new-password"
                               placeholder="<?= $is_edit ? '********' : 'Masukkan password...' ?>">
                        <p class="mt-1 text-[10px] text-gray-400 italic">Minimal 6 karakter.</p>
                        <?php if ($validation->hasError('password')): ?>
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400"><?= esc($validation->getError('password')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        Konfirmasi Password <?= !$is_edit ? '<span class="text-red-500">*</span>' : '' ?>
                    </label>
                    <div class="md:col-span-2">
                        <input type="password"
                               name="confirm_pass"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation->hasError('confirm_pass') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               <?= $password_required ?> autocomplete="new-password"
                               placeholder="Ulangi password...">
                    </div>
                </div>

                <?php if ($is_edit): ?>
                    <!-- Status Akun (Fix Sync Issue) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                        <label class="md:text-right text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Status Akun
                        </label>
                        <div class="md:col-span-2">
                            <select name="is_active"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                                <option value="1" <?= $val_active === 1 ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= $val_active === 0 ? 'selected' : '' ?>>Non-Aktif</option>
                            </select>
                            <p class="mt-1 text-[10px] text-gray-400 italic">User non-aktif tidak dapat login.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-gray-100 dark:border-white/10 flex justify-end gap-3">
                    <button type="submit"
                            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all active:scale-98 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Pengguna' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>