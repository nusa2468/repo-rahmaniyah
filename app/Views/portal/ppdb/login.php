<?= $this->extend('layout/portal_layout') ?>

<?= $this->section('content') ?>
<div class="flex flex-col justify-center py-12 sm:px-6 lg:px-8 min-h-[calc(100vh-16rem)]">
    
    <!-- Header Halaman Login -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Login Peserta PPDB
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Masuk untuk melanjutkan proses pendaftaran atau
                <a href="<?= base_url('portal/ppdb/daftar') ?>" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                    buat akun baru
                </a>
            </p>
        </div>
    </div>

    <!-- Card Login -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl shadow-gray-200/50 dark:shadow-none sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700 relative overflow-hidden">
            
            <!-- Dekorasi Garis Atas -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

            <form class="space-y-6" action="<?= base_url('portal/ppdb/login') ?>" method="post">
                <?= csrf_field() ?>

                <!-- Input NIK / No. Daftar -->
                <div>
                    <label for="auth_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        NIK / No. Pendaftaran
                    </label>
                    <div class="mt-1 relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="auth_key" 
                               id="auth_key" 
                               required 
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-xl leading-5 bg-gray-50 dark:bg-gray-900/50 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200" 
                               placeholder="Contoh: 3201xxx atau REG-2025xxx">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-blue-500/30">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-blue-400 group-hover:text-blue-300 transition ease-in-out duration-150"></i>
                        </span>
                        Masuk Dashboard
                    </button>
                </div>
            </form>

            <!-- Separator -->
            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-3 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            Butuh bantuan?
                        </span>
                    </div>
                </div>

                <!-- Link Bantuan -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="#" class="w-full flex items-center justify-center px-4 py-2 border border-gray-200 dark:border-gray-700 shadow-sm text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <i class="fab fa-whatsapp text-emerald-500 mr-2"></i>
                        Admin
                    </a>
                    <a href="#" class="w-full flex items-center justify-center px-4 py-2 border border-gray-200 dark:border-gray-700 shadow-sm text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-book-open text-blue-500 mr-2"></i>
                        Panduan
                    </a>
                </div>
            </div>
        </div>
        
        <p class="mt-6 text-center text-xs text-gray-400 dark:text-gray-500">
            Pastikan NIK/Nomor Pendaftaran Anda benar.
        </p>
    </div>
</div>
<?= $this->endSection() ?>