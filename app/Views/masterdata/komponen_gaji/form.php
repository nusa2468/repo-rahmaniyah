<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    $errors = session()->getFlashdata('errors') ?? [];
    
    // Perbaikan Logika: Cek apakah ID ada untuk menentukan mode Edit
    $isEdit = isset($komponen['id']) && !empty($komponen['id']);
    
    $url = $isEdit
        ? base_url('app/masterdata/komponen-gaji/update/' . $komponen['id'])
        : base_url('app/masterdata/komponen-gaji/create');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white"><?= esc($title) ?></h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <?= $isEdit ? 'Perbarui parameter komponen gaji yang sudah terdaftar.' : 'Konfigurasi komponen gaji baru untuk sistem penggajian.' ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Utama -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                    <h2 class="font-bold text-sm text-gray-700 dark:text-white flex items-center gap-2 uppercase tracking-wider">
                        <i class="fas fa-edit text-sky-600"></i>
                        Informasi Detail Komponen
                    </h2>
                </div>

                <div class="p-6">
                    <form action="<?= $url ?>" method="POST" class="space-y-5">
                        <?= csrf_field() ?>
                        
                        <!-- ID Hidden Field untuk keperluan validasi is_unique saat edit -->
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?= $komponen['id'] ?>">
                        <?php endif; ?>

                        <!-- Row 1: Kode & Nama -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Kode Komponen <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="kode_komponen" name="kode_komponen"
                                       placeholder="MISAL: TUNJ_JABATAN"
                                       value="<?= old('kode_komponen', $komponen['kode_komponen'] ?? '') ?>"
                                       class="w-full px-4 py-2 text-sm font-mono uppercase border rounded-lg focus:ring-2 focus:ring-sky-500 transition-all <?= isset($errors['kode_komponen']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-900"
                                       required>
                                <?php if (isset($errors['kode_komponen'])): ?>
                                    <p class="mt-1 text-[10px] text-red-600"><?= esc($errors['kode_komponen']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Nama Komponen <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_komponen"
                                       placeholder="Misal: Tunjangan Jabatan"
                                       value="<?= old('nama_komponen', $komponen['nama_komponen'] ?? '') ?>"
                                       class="w-full px-4 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-sky-500 transition-all <?= isset($errors['nama_komponen']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?> bg-white dark:bg-gray-900"
                                       required>
                                <?php if (isset($errors['nama_komponen'])): ?>
                                    <p class="mt-1 text-[10px] text-red-600"><?= esc($errors['nama_komponen']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Row 2: Unit (Scope) & Tipe -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Unit / Scope Kerja <span class="text-red-500">*</span>
                                </label>
                                <select name="kode_jenjang" 
                                        class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 focus:ring-2 focus:ring-sky-500">
                                    <option value="GLOBAL">GLOBAL (Semua Unit/Pusat)</option>
                                    <?php foreach ($jenjang_list as $j): ?>
                                        <?php 
                                            $kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                            $nama = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                                        ?>
                                        <option value="<?= esc($kode) ?>" <?= old('kode_jenjang', $komponen['kode_jenjang'] ?? '') == $kode ? 'selected' : '' ?>>
                                            <?= esc($kode) ?> - <?= esc($nama) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Tipe Komponen <span class="text-red-500">*</span>
                                </label>
                                <select name="tipe" class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 focus:ring-2 focus:ring-sky-500" required>
                                    <option value="1" <?= old('tipe', $komponen['tipe'] ?? '') == '1' ? 'selected' : '' ?>>Pendapatan (Income)</option>
                                    <option value="2" <?= old('tipe', $komponen['tipe'] ?? '') == '2' ? 'selected' : '' ?>>Potongan (Deduct)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Metode & Nominal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Metode Hitung <span class="text-red-500">*</span>
                                </label>
                                <select name="metode_hitung" class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 focus:ring-2 focus:ring-sky-500" required>
                                    <option value="fixed" <?= old('metode_hitung', $komponen['metode_hitung'] ?? 'fixed') == 'fixed' ? 'selected' : '' ?>>Fixed (Tetap Bulanan)</option>
                                    <option value="variabel" <?= old('metode_hitung', $komponen['metode_hitung'] ?? '') == 'variabel' ? 'selected' : '' ?>>Variabel (Harian/Kehadiran)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    Nominal Default (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs font-bold">Rp</div>
                                    <input type="number" step="0.01" name="nominal_default"
                                           value="<?= old('nominal_default', $komponen['nominal_default'] ?? '0') ?>"
                                           class="w-full pl-9 pr-4 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-sky-500 bg-white dark:bg-gray-900 <?= isset($errors['nominal_default']) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Default Switches -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">Set as Default</p>
                                    <p class="text-[10px] text-gray-500">Otomatis masuk slip gaji baru</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_default" value="1" class="sr-only peer" <?= old('is_default', $komponen['is_default'] ?? '0') == '1' ? 'checked' : '' ?>>
                                    <div class="w-9 h-5 bg-gray-300 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-sky-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-100 dark:border-white/5">
                                <div>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">Status Aktif</p>
                                    <p class="text-[10px] text-gray-500">Komponen siap digunakan</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_aktif" value="1" class="sr-only peer" <?= old('is_aktif', $komponen['is_aktif'] ?? '1') == '1' ? 'checked' : '' ?>>
                                    <div class="w-9 h-5 bg-gray-300 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                            <a href="<?= base_url('app/masterdata/komponen-gaji') ?>" 
                               class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Batalkan
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all active:scale-95">
                                <i class="fas fa-save mr-1.5"></i>
                                <?= $isEdit ? 'Simpan Perubahan' : 'Proses Simpan' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-4">
            <div class="bg-indigo-600 p-5 rounded-xl shadow-md border-b-4 border-indigo-800">
                <h3 class="font-black text-white text-sm uppercase tracking-widest flex items-center gap-2 mb-3">
                    <i class="fas fa-info-circle"></i> Bantuan Scoping
                </h3>
                <div class="space-y-3">
                    <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                        <p class="text-xs font-bold text-indigo-100 uppercase">Unit Global</p>
                        <p class="text-[11px] text-white/80 mt-1 leading-relaxed">Gunakan <b>GLOBAL</b> jika komponen ini berlaku untuk semua guru & karyawan di seluruh yayasan tanpa terkecuali.</p>
                    </div>
                    <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm">
                        <p class="text-xs font-bold text-indigo-100 uppercase">Unit Spesifik</p>
                        <p class="text-[11px] text-white/80 mt-1 leading-relaxed">Pilih unit (misal: SD) jika komponen hanya berlaku di unit tersebut (misal: Tunjangan Wali Kelas SD).</p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-500 p-5 rounded-xl shadow-md border-b-4 border-amber-700">
                <h3 class="font-black text-white text-sm uppercase tracking-widest flex items-center gap-2 mb-2">
                    <i class="fas fa-calculator"></i> Metode Hitung
                </h3>
                <p class="text-[11px] text-white/90 leading-relaxed font-medium">
                    <b>Fixed:</b> Nominal akan tetap dibayarkan penuh setiap bulan.<br><br>
                    <b>Variabel:</b> Bergantung pada absensi. Sistem akan mengalikan nominal ini dengan jumlah hari hadir karyawan.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('kode_komponen')?.addEventListener('input', function(e) {
        this.value = this.value.toUpperCase()
            .replace(/\s+/g, '_')
            .replace(/[^A-Z0-9_]/g, '');
    });
</script>

<?= $this->endSection() ?>