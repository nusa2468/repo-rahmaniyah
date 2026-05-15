<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="max-w-md w-full space-y-8">
        
        <!-- Header Section -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-6 ring-8 ring-blue-50 dark:ring-blue-900/10">
                <i class="fas fa-handshake text-3xl"></i>
            </div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Portal Mitra Afiliasi</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Silakan masuk untuk mengelola referal dan komisi Anda.
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-gray-100 dark:border-white/5 relative overflow-hidden">
            
            <!-- Decor Line -->
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

            <!-- Alert Error -->
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-500/30 rounded-xl p-4 flex gap-3 animate-pulse">
                    <div class="text-red-500 shrink-0 mt-0.5">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <p class="text-sm text-red-700 dark:text-red-300 font-bold"><?= session()->getFlashdata('error') ?></p>
                </div>
            <?php endif; ?>

            <!-- Alert Success -->
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-500/30 rounded-xl p-4 flex gap-3">
                    <div class="text-green-500 shrink-0 mt-0.5">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="text-sm text-green-700 dark:text-green-300 font-medium">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form class="space-y-6" action="<?= base_url('portal/affiliated/login') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div>
                    <label for="kode_agen" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Kode Agen
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-id-badge text-gray-400"></i>
                        </div>
                        <input id="kode_agen" name="kode_agen" type="text" required 
                            class="block w-full pl-11 pr-3 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-gray-50 dark:bg-gray-900 placeholder-gray-400 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-950 sm:text-sm font-mono tracking-wider transition-all" 
                            placeholder="Contoh: AGN-XXXX">
                    </div>
                </div>

                <div>
                    <label for="no_hp" class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Nomor WhatsApp
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fab fa-whatsapp text-gray-400 text-lg"></i>
                        </div>
                        <input id="no_hp" name="no_hp" type="tel" required 
                            class="block w-full pl-11 pr-3 py-3.5 border border-gray-200 dark:border-gray-700 rounded-xl leading-5 bg-gray-50 dark:bg-gray-900 placeholder-gray-400 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white dark:focus:bg-gray-950 sm:text-sm transition-all" 
                            placeholder="Nomor HP terdaftar">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-lg shadow-blue-500/30 hover:-translate-y-1 active:scale-95">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-blue-300 group-hover:text-blue-100 transition-colors"></i>
                        </span>
                        MASUK DASHBOARD
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white dark:bg-gray-800 text-gray-400 font-medium">
                            Belum punya akun mitra?
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="<?= base_url('portal/affiliated/register') ?>" class="w-full flex items-center justify-center px-4 py-3.5 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all group">
                        <i class="fas fa-user-plus mr-2 text-gray-400 group-hover:text-blue-500 transition-colors"></i> Daftar Sebagai Mitra Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Link -->
        <p class="text-center">
            <a href="<?= base_url('/') ?>" class="text-sm font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors inline-flex items-center gap-2 py-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </p>
    </div>
</div>

<?= $this->endSection() ?>