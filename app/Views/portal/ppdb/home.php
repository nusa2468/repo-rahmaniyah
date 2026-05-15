<?= $this->extend('layout/portal_layout') ?>

<?= $this->section('content') ?>

<!-- STICKY NAVBAR PPDB -->
<!-- Note: Membutuhkan Alpine.js (x-data) untuk toggle mobile menu. -->
<nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800 shadow-sm transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo / Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/30">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="font-black text-xl text-gray-900 dark:text-white tracking-tight hidden sm:block">
                    PPDB <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Online</span>
                </span>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-6 lg:gap-8">
                <!-- Navigasi Spesifik ke Portal Unit Sekolah -->
                <a href="<?= base_url('portal/unit/' . strtolower($settings['kode_jenjang'] ?? 'sma')) ?>" class="flex items-center gap-1.5 text-sm font-bold text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors" title="Kembali ke Web Unit Sekolah">
                    <i class="fas fa-arrow-left"></i> Kembali ke Web Unit
                </a>
                
                <!-- Pembatas (Divider) -->
                <div class="h-4 w-px bg-gray-300 dark:bg-gray-700"></div>

                <a href="<?= base_url('portal/ppdb') ?>" class="text-sm font-bold text-blue-600 dark:text-blue-400 transition-colors">Beranda PPDB</a>
                <a href="<?= base_url('portal/ppdb/register') ?>" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Formulir Pendaftaran</a>
            </div>

            <!-- Desktop Auth Buttons -->
            <div class="hidden md:flex items-center gap-3">
                <a href="<?= base_url('portal/ppdb/login') ?>" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                    <i class="fas fa-sign-in-alt"></i> Login Siswa
                </a>
            </div>

            <!-- Mobile Menu Toggle Button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 focus:outline-none p-2">
                    <i class="fas" :class="mobileMenuOpen ? 'fa-times text-xl' : 'fa-bars text-xl'"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden absolute w-full bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 shadow-xl max-h-[85vh] overflow-y-auto" 
         style="display: none;">
        <div class="px-4 pt-2 pb-6 space-y-2 flex flex-col">
            <!-- Navigasi Mobile Spesifik ke Portal Unit Sekolah -->
            <a href="<?= base_url('portal/unit/' . strtolower($settings['kode_jenjang'] ?? 'sma')) ?>" class="px-4 py-3 text-base font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Web Unit
            </a>
            <div class="h-px w-full bg-gray-100 dark:bg-gray-800 my-1"></div>

            <a @click="mobileMenuOpen = false" href="<?= base_url('portal/ppdb') ?>" class="px-4 py-3 text-base font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl">Beranda PPDB</a>
            <a @click="mobileMenuOpen = false" href="<?= base_url('portal/ppdb/register') ?>" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Formulir Pendaftaran</a>
            
            <div class="h-px w-full bg-gray-100 dark:bg-gray-800 my-2"></div>
            
            <a href="<?= base_url('portal/ppdb/login') ?>" class="px-4 py-3 text-center text-base font-bold text-white bg-blue-600 rounded-xl flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Login Siswa
            </a>
        </div>
    </div>
</nav>

<!-- KONTEN UTAMA -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- WELCOME HERO SECTION -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-700 rounded-[2.5rem] shadow-xl shadow-blue-500/20 mb-10 p-8 md:p-12 text-white">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            <div class="md:col-span-2 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-sm font-medium backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    PPDB Online <?= date('Y') ?>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black tracking-tight leading-tight">
                    Halo, Calon Siswa Hebat!
                </h1>
                
                <p class="text-lg text-blue-100/90 leading-relaxed max-w-2xl">
                    Selamat datang di portal resmi PPDB <?= esc($settings['nama_sekolah'] ?? 'Sekolah Kami') ?>. 
                    Silakan lengkapi pendaftaran dan pantau status seleksi Anda di sini. Masa depan cerah dimulai dari langkah ini.
                </p>

                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="<?= base_url('portal/ppdb/register') ?>" class="inline-flex items-center px-6 py-3.5 bg-white text-blue-600 font-bold rounded-2xl shadow-lg hover:bg-gray-50 transition-all hover:-translate-y-1 active:scale-95">
                        <i class="fas fa-edit mr-2.5"></i> MULAI PENDAFTARAN
                    </a>
                    <a href="<?= base_url('portal/ppdb/login') ?>" class="inline-flex items-center px-6 py-3.5 bg-blue-800/40 text-white font-bold rounded-2xl hover:bg-blue-800/60 transition-all border border-white/10 backdrop-blur-md">
                        <i class="fas fa-sign-in-alt mr-2.5"></i> SUDAH DAFTAR?
                    </a>
                </div>
            </div>

            <!-- Hero Image/Icon -->
            <div class="hidden md:flex justify-center items-center">
                <i class="fas fa-graduation-cap text-[12rem] text-white/20 transform rotate-12 scale-110 drop-shadow-2xl"></i>
            </div>
        </div>
    </div>

    <!-- INFO CARDS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- CARD 1: ALUR PENDAFTARAN -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 h-full">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Alur Pendaftaran</h3>
                </div>

                <div class="space-y-4 relative before:absolute before:inset-0 before:ml-3.5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-blue-100 before:via-blue-50 before:to-transparent dark:before:from-gray-700">
                    <!-- Step 1 -->
                    <div class="relative pl-10">
                        <div class="absolute left-0 top-1 w-7 h-7 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white dark:ring-gray-800">1</div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Isi Biodata Lengkap</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Lengkapi data diri, orang tua, dan asal sekolah sesuai dokumen resmi.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative pl-10">
                        <div class="absolute left-0 top-1 w-7 h-7 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white dark:ring-gray-800">2</div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Pilih Jalur Masuk</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Tentukan jalur pendaftaran (Umum, Prestasi, Afiliasi, dll).</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative pl-10">
                        <div class="absolute left-0 top-1 w-7 h-7 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white dark:ring-gray-800">3</div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Upload Bukti</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Unggah bukti pembayaran biaya pendaftaran untuk verifikasi.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative pl-10">
                        <div class="absolute left-0 top-1 w-7 h-7 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center font-bold text-xs ring-4 ring-white dark:ring-gray-800">4</div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1">Tunggu Verifikasi</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Pantau dashboard secara berkala untuk melihat hasil seleksi.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: INFORMASI TERBARU -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 md:p-8 h-full">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informasi Terbaru</h3>
                    </div>
                    <span class="text-xs font-medium text-gray-400"><?= date('d M Y') ?></span>
                </div>

                <div class="space-y-4">
                    <!-- Info Alert -->
                    <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex gap-4 items-start">
                        <div class="shrink-0 mt-0.5">
                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-1">Pengumuman Penting</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-400 leading-relaxed">
                                Seleksi gelombang 1 akan ditutup pada tanggal <strong>30 Desember <?= date('Y') ?></strong>. Segera lengkapi berkas pendaftaran Anda sebelum batas waktu berakhir.
                            </p>
                        </div>
                    </div>

                    <!-- Warning Alert -->
                    <div class="p-4 rounded-2xl bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-100 dark:border-yellow-500/20 flex gap-4 items-start">
                        <div class="shrink-0 mt-0.5">
                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-300 mb-1">Perhatian Data</h4>
                            <p class="text-sm text-yellow-700 dark:text-yellow-400 leading-relaxed">
                                Pastikan <strong>NIK</strong> dan <strong>NISN</strong> yang Anda masukkan sesuai dengan Kartu Keluarga dan data Dapodik sekolah asal untuk menghindari kegagalan verifikasi.
                            </p>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-white/5">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Butuh bantuan? Hubungi panitia PPDB:</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 text-xs font-bold hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors">
                                <i class="fab fa-whatsapp text-lg"></i> Chat Panitia
                            </a>
                            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <i class="fas fa-envelope text-lg"></i> Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>