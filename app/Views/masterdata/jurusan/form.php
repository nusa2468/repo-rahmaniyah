<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
// HANDLING DATA (Safe Array Access)
$data = $jurusan ?? []; // Fallback ke array kosong jika null
$id = $data['id'] ?? null;
$is_edit = !empty($id);

// URL Action Form
$url_action = base_url('app/masterdata/jurusan/save');

$validation = \Config\Services::validation();
?>

<div x-data="{ isSubmitting: false }" class="font-jakarta min-h-screen px-4 mb-10">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/masterdata/jurusan') ?>" 
               class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-sky-600 hover:border-sky-200 hover:bg-sky-50 transition-all shadow-sm group">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    <?= $is_edit ? 'Edit Data Jurusan' : 'Tambah Jurusan Baru' ?>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Lengkapi informasi detail program studi atau jurusan di bawah ini.
                </p>
            </div>
        </div>
    </div>

    <!-- Alert Error (PERBAIKAN: Menampilkan Pesan Session Flashdata) -->
    <?php if (session()->getFlashdata('error') || $validation->getErrors()): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 mb-6 rounded-r-xl flex items-start gap-3 animate-pulse">
            <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-rose-800">Peringatan Sistem</h3>
                
                <!-- 1. Tampilkan Pesan Error Global (Flashdata) -->
                <?php if (session()->getFlashdata('error')) : ?>
                    <p class="text-xs text-rose-700 mt-1 font-semibold">
                        <?= session()->getFlashdata('error') ?>
                    </p>
                <?php endif; ?>

                <!-- 2. Tampilkan Error Validasi Form -->
                <?php if ($validation->getErrors()) : ?>
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <form action="<?= $url_action ?>" 
              method="POST" 
              @submit="isSubmitting = true">
            
            <?= csrf_field() ?>
            
            <!-- Input Hidden ID (Penting untuk Edit) -->
            <?php if($is_edit): ?>
                <input type="hidden" name="id" value="<?= esc($id) ?>">
                <!-- Method Spoofing (Opsional, gunakan jika route mengharuskan PUT) -->
                <input type="hidden" name="_method" value="POST"> 
            <?php endif; ?>

            <div class="p-6 sm:p-8 space-y-6">
                <!-- Grid Container -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Kode Jurusan -->
                    <div class="space-y-2">
                        <label for="kode_jurusan" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Kode Jurusan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-fingerprint text-gray-400 group-focus-within:text-sky-600 transition-colors"></i>
                            </div>
                            <input type="text" 
                                   name="kode_jurusan" 
                                   id="kode_jurusan" 
                                   value="<?= old('kode_jurusan', $data['kode_jurusan'] ?? '') ?>"
                                   placeholder="Contoh: TKJ, RPL, dsb."
                                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none"
                                   required>
                        </div>
                    </div>

                    <!-- Nama Jurusan -->
                    <div class="space-y-2">
                        <label for="nama_jurusan" class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Nama Lengkap Jurusan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-graduation-cap text-gray-400 group-focus-within:text-sky-600 transition-colors"></i>
                            </div>
                            <input type="text" 
                                   name="nama_jurusan" 
                                   id="nama_jurusan" 
                                   value="<?= old('nama_jurusan', $data['nama_jurusan'] ?? '') ?>"
                                   placeholder="Contoh: Teknik Komputer dan Jaringan"
                                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none"
                                   required>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2 space-y-2">
                        <label for="keterangan" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Keterangan</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 pt-3 pointer-events-none">
                                <i class="fas fa-info-circle text-gray-400 group-focus-within:text-sky-600 transition-colors"></i>
                            </div>
                            <textarea name="keterangan" 
                                      id="keterangan" 
                                      rows="2"
                                      placeholder="Tambahan keterangan..."
                                      class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all outline-none resize-none"><?= old('keterangan', $data['keterangan'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Status Jurusan -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Status Jurusan <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-6 py-2 bg-gray-50 dark:bg-white/5 p-4 rounded-xl border border-gray-200 dark:border-white/10">
                            <?php 
                                $val = old('status', $data['status'] ?? 'Aktif'); 
                                $isAktif = ($val === 'Aktif' || $val == 1);
                                // Default aktif jika baru
                                if (!$is_edit && empty(old('status'))) $isAktif = true;
                                $isNonAktif = !$isAktif;
                            ?>
                            
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" name="status" value="Aktif" class="sr-only peer" <?= $isAktif ? 'checked' : '' ?>>
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-sky-600 peer-checked:bg-sky-600 flex items-center justify-center transition-all shadow-sm">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Aktif</span>
                            </label>

                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" name="status" value="Non-Aktif" class="sr-only peer" <?= $isNonAktif ? 'checked' : '' ?>>
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-rose-500 peer-checked:bg-rose-500 flex items-center justify-center transition-all shadow-sm">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Non-Aktif</span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer / Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
                <button type="reset" 
                        class="px-6 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Reset
                </button>
                <button type="submit" 
                        :disabled="isSubmitting"
                        class="inline-flex items-center gap-2 px-8 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-sky-500/30 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </span>
                    <span x-show="isSubmitting" class="flex items-center gap-2" style="display: none;">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto uppercase untuk Kode Jurusan
    document.getElementById('kode_jurusan').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
</script>
<?= $this->endSection() ?>