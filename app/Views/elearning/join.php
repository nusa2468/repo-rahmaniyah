<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-white/10 overflow-hidden">
        
        <!-- Header -->
        <div class="p-6 text-center border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 dark:text-blue-400">
                <i class="fas fa-chalkboard-teacher text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Gabung ke Kelas</h2>
            <p class="text-sm text-gray-500 mt-1">Masukkan kode kelas yang diberikan oleh pengajar Anda.</p>
        </div>

        <!-- Form -->
        <div class="p-6">
            <!-- Alert Error -->
            <?php if(session()->getFlashdata('error')): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-4 border border-red-100 flex items-start gap-2">
                    <i class="fas fa-exclamation-circle mt-0.5"></i>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('app/elearning/join') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kode Kelas</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-key"></i>
                        </div>
                        <input type="text" name="kode_kelas" required placeholder="Contoh: X7Y8Z9" 
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all uppercase tracking-widest font-mono text-center text-lg">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 text-center">Kode kelas terdiri dari 6-7 karakter alfanumerik.</p>
                </div>

                <div class="flex gap-3">
                    <a href="<?= base_url('app/elearning') ?>" class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-center">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2">
                        <span>Gabung</span> <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer Info -->
        <div class="bg-gray-50 dark:bg-black/20 p-4 text-center">
            <p class="text-xs text-gray-400">Pastikan Anda login dengan akun yang benar sebelum bergabung.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>