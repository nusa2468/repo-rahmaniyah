<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // Normalisasi data role
    $role_data = is_array($role) ? $role : (array) $role;
    $is_edit = isset($role_data['id']) && $role_data['id'] !== null;
    $action_url = $is_edit
        ? base_url('app/pengaturan/hak_akses/update/' . $role_data['id'])
        : base_url('app/pengaturan/hak_akses/create');

    $validation = \Config\Services::validation();

    // Grouping permissions
    $currentPermissionIds = $rolePermissions ?? [];
    $groupedPermissions = [];

    foreach ($permissions ?? [] as $permission) {
        $key = $permission['permission_key'];
        $parts = explode('.', $key, 2);
        $group = ucfirst($parts[0] ?? 'Umum');
        if (count($parts) < 2) $group = 'Akses Umum';

        $groupedPermissions[$group][] = [
            'id'             => $permission['id'],
            'permission_key' => $key,
            'description'    => $permission['description'],
            'isChecked'      => in_array($permission['id'], $currentPermissionIds),
        ];
    }
?>

<div class="max-w-7xl mx-auto">
    <!-- Header Compact & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <?= $is_edit ? 'Edit role dan pengaturan izin akses' : 'Buat role/hak akses baru untuk pengguna' ?>
            </p>
        </div>
        <a href="<?= base_url('app/pengaturan/hak_akses') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <!-- Form Card - Compact, Rapat, Solid -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">
                Formulir Hak Akses & Izin
            </h3>
        </div>

        <div class="p-6">
            <form action="<?= esc($action_url) ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>
                <?php if ($is_edit): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?= esc($role_data['id'] ?? '') ?>">
                <?php endif; ?>

                <!-- Nama Role & Deskripsi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                            Nama Role <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               value="<?= old('name', $role_data['name'] ?? '') ?>"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation?->hasError('name') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm font-bold focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all"
                               required>
                        <?php if ($validation?->hasError('name')): ?>
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400"><?= esc($validation->getError('name')) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                            Deskripsi Role
                        </label>
                        <textarea name="description"
                                  rows="3"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border <?= $validation?->hasError('description') ? 'border-red-500' : 'border-gray-200 dark:border-white/10' ?> rounded-xl text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all resize-none"><?= old('description', $role_data['description'] ?? '') ?></textarea>
                        <?php if ($validation?->hasError('description')): ?>
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400"><?= esc($validation->getError('description')) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 dark:border-white/10 my-8"></div>

                <!-- Permissions Section -->
                <div>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-5">
                        Pengaturan Izin Akses
                    </h4>

                    <?php if (empty($groupedPermissions)): ?>
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-exclamation-triangle text-3xl mb-3 opacity-50"></i>
                            <p class="text-sm">Tidak ada izin akses terdaftar di sistem.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <?php foreach ($groupedPermissions as $groupName => $perms): ?>
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-white/10 p-5">
                                    <h5 class="text-xs font-black text-sky-600 dark:text-sky-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                        <span class="w-3 h-1 bg-sky-600 rounded-full"></span>
                                        <?= esc($groupName) ?>
                                    </h5>
                                    <div class="space-y-3">
                                        <?php foreach ($perms as $perm): ?>
                                            <label class="flex items-start gap-3 cursor-pointer group">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="<?= esc($perm['id']) ?>"
                                                       class="mt-1 w-4 h-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                                       <?= $perm['isChecked'] ? 'checked' : '' ?>>
                                                <div class="flex-1">
                                                    <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-sky-600 transition-colors">
                                                        <code class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded"><?= esc($perm['permission_key']) ?></code>
                                                    </div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                                        <?= esc($perm['description']) ?>
                                                    </div>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-100 dark:border-white/10 flex justify-end">
                    <button type="submit"
                            class="px-8 py-3.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg hover:shadow-sky-500/30 transition-all active:scale-98 flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <?= $is_edit ? 'Update Role & Izin' : 'Simpan Role Baru' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>