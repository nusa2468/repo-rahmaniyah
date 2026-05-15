<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
        <li class="inline-flex items-center">
            <a href="<?= base_url('app/dashboard') ?>" class="text-gray-500 hover:text-sky-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                <a href="<?= base_url('app/masterdata/tahunajaran') ?>" class="text-sm font-medium text-gray-500 hover:text-sky-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                    Tahun Ajaran
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?= isset($tahun_ajaran['id']) ? 'Edit' : 'Tambah' ?> Data</span>
            </div>
        </li>
    </ol>
</nav>

<!-- Header -->
<div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">
            <?= isset($tahun_ajaran['id']) ? 'Edit' : 'Tambah' ?> Konfigurasi Akademik
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Atur periode tahun ajaran dan semester yang berlaku.
        </p>
    </div>
    <a href="<?= base_url('app/masterdata/tahunajaran') ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none transition-all">
        <i class="fas fa-arrow-left mr-2 text-xs"></i> Kembali
    </a>
</div>

<!-- Form Card -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden max-w-4xl">
    
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 m-6 mb-0 rounded-r-lg flex items-start">
            <i class="fas fa-exclamation-circle text-rose-500 text-lg mt-0.5 mr-3"></i>
            <div>
                <h3 class="text-sm font-bold text-rose-800 dark:text-rose-300">Terjadi Kesalahan</h3>
                <p class="text-sm text-rose-700 dark:text-rose-400 mt-1"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 m-6 mb-0 rounded-r-lg">
            <div class="flex items-center mb-2">
                <i class="fas fa-list text-rose-500 mr-2"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300">Detail Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-rose-700 dark:text-rose-400 pl-2">
                <?php foreach ((array)session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
    $is_edit = isset($tahun_ajaran['id']) && !empty($tahun_ajaran['id']);
    $url = $is_edit 
        ? base_url('app/masterdata/tahunajaran/update/' . $tahun_ajaran['id']) 
        : base_url('app/masterdata/tahunajaran/create'); 
    $validation = \Config\Services::validation();
    
    // Logika Unit/Jenjang
    $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $isSuperAdmin = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);
    ?>

    <form action="<?= $url ?>" method="post" class="p-6 md:p-8">
        <?= csrf_field() ?>
        <?php if ($is_edit) : ?>
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="<?= $tahun_ajaran['id'] ?>">
        <?php endif; ?>

        <!-- BAGIAN 1: UNIT & IDENTITAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Unit Sekolah (Anti Bocor) -->
            <div>
                <label for="kode_jenjang" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Unit Sekolah <span class="text-rose-500">*</span>
                </label>
                <?php if ($isSuperAdmin): ?>
                    <div class="relative">
                        <select name="kode_jenjang" id="kode_jenjang" required 
                                class="block w-full pl-4 pr-10 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm appearance-none cursor-pointer">
                            <option value="">-- Pilih Unit --</option>
                            <?php foreach ($list_jenjang as $j): ?>
                                <?php 
                                    $kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                    $nama = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                                    $selected = (old('kode_jenjang', $tahun_ajaran['kode_jenjang'] ?? '') == $kode) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($kode) ?>" <?= $selected ?>><?= esc($nama) ?> (<?= esc($kode) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs mr-2"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="relative">
                         <input type="text" value="<?= esc($userJenjang) ?>" readonly 
                               class="block w-full px-4 py-2.5 text-sm border-gray-300 bg-gray-100 text-gray-500 rounded-xl cursor-not-allowed font-bold">
                         <input type="hidden" name="kode_jenjang" value="<?= esc($userJenjang) ?>">
                         <div class="absolute inset-y-0 right-0 flex items-center px-3">
                            <i class="fas fa-lock text-gray-400 text-xs"></i>
                         </div>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-400">Unit terkunci sesuai akun login Anda.</p>
                <?php endif; ?>
            </div>

            <!-- Tahun Ajaran -->
            <div>
                <label for="tahun_ajaran" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Tahun Ajaran <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="tahun_ajaran" 
                       name="tahun_ajaran" 
                       value="<?= old('tahun_ajaran', $tahun_ajaran['tahun_ajaran'] ?? '') ?>"
                       class="block w-full px-4 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white transition-shadow shadow-sm placeholder-gray-400 <?= ($validation->hasError('tahun_ajaran')) ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500' : '' ?>"
                       placeholder="Contoh: 2025/2026" required>
                <?php if($validation->hasError('tahun_ajaran')): ?>
                    <p class="mt-1 text-xs text-rose-500"><?= $validation->getError('tahun_ajaran') ?></p>
                <?php endif; ?>
                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Format wajib: YYYY/YYYY (4 digit / 4 digit)</p>
            </div>
        </div>

        <!-- BAGIAN 2: SEMESTER & STATUS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Semester -->
            <div>
                <label for="semester" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Semester <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <?php $current_semester = old('semester', $tahun_ajaran['semester'] ?? ''); ?>
                    <select id="semester" name="semester" required
                            class="block w-full pl-4 pr-10 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm appearance-none cursor-pointer <?= ($validation->hasError('semester')) ? 'border-rose-500' : '' ?>">
                        <option value="">-- Pilih Semester --</option>
                        <option value="Ganjil" <?= ($current_semester == 'Ganjil') ? 'selected' : '' ?>>Ganjil (1)</option>
                        <option value="Genap" <?= ($current_semester == 'Genap') ? 'selected' : '' ?>>Genap (2)</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-xs mr-2"></i>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Status Aktivasi <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <?php $current_status = old('status', $tahun_ajaran['status'] ?? 'tidak aktif'); ?>
                    <select id="status" name="status" required
                            class="block w-full pl-4 pr-10 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm appearance-none cursor-pointer">
                        <option value="aktif" <?= ($current_status == 'aktif') ? 'selected' : '' ?>>Aktif (Berjalan)</option>
                        <option value="tidak aktif" <?= ($current_status == 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-xs mr-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Tanggal Mulai -->
            <div>
                <label for="tanggal_mulai" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Tanggal Mulai
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-alt text-gray-400"></i>
                    </div>
                    <input type="date" 
                           id="tanggal_mulai" 
                           name="tanggal_mulai" 
                           value="<?= old('tanggal_mulai', $tahun_ajaran['tanggal_mulai'] ?? '') ?>"
                           class="block w-full pl-10 pr-4 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>
            </div>

            <!-- Tanggal Selesai -->
            <div>
                <label for="tanggal_selesai" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                    Tanggal Selesai
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-check text-gray-400"></i>
                    </div>
                    <input type="date" 
                           id="tanggal_selesai" 
                           name="tanggal_selesai" 
                           value="<?= old('tanggal_selesai', $tahun_ajaran['tanggal_selesai'] ?? '') ?>"
                           class="block w-full pl-10 pr-4 py-2.5 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm">
                </div>
            </div>
        </div>

        <div class="mt-2 mb-6 flex items-start gap-2 bg-blue-50 dark:bg-blue-900/10 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <p class="text-xs text-blue-700 dark:text-blue-300 leading-snug">
                <strong>Catatan Penting:</strong> Hanya boleh ada <strong>SATU</strong> tahun ajaran yang berstatus <strong>AKTIF</strong> dalam satu Unit Sekolah. 
                Mengaktifkan periode ini akan otomatis menonaktifkan periode lain di unit yang sama.
            </p>
        </div>

        <!-- Keterangan -->
        <div class="mb-8">
            <label for="keterangan" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-2">
                Catatan / Keterangan
            </label>
            <textarea id="keterangan" name="keterangan" rows="3"
                      class="block w-full px-4 py-3 text-sm border-gray-300 dark:border-gray-600 rounded-xl focus:ring-sky-500 focus:border-sky-500 dark:bg-gray-700 dark:text-white shadow-sm placeholder-gray-400"
                      placeholder="Contoh: Jadwal UAS dipercepat, kurikulum merdeka, dll..."><?= old('keterangan', $tahun_ajaran['keterangan'] ?? '') ?></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
            <a href="<?= base_url('app/masterdata/tahunajaran') ?>" 
               class="inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-sm font-bold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none transition-all">
                Batal
            </a>
            <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-xl shadow-lg shadow-emerald-500/30 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform active:scale-95">
                <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data' ?>
            </button>
        </div>

    </form>
</div>

<?= $this->endSection() ?>