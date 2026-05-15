<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<!-- STICKY NAVBAR -->
<!-- Note: Membutuhkan Alpine.js (x-data) untuk toggle mobile menu dan dropdown. -->
<nav x-data="{ mobileMenuOpen: false, portalDropdownOpen: false }" class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800 shadow-sm transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo / Brand -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/30">
                    <i class="fas fa-handshake"></i>
                </div>
                <span class="font-black text-xl text-gray-900 dark:text-white tracking-tight hidden sm:block">
                    Mitra <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Edukasi</span>
                </span>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-6 lg:gap-8">
                <!-- Navigasi Standar Kembali ke Portal Sekolah -->
                <a href="<?= base_url('/') ?>" class="flex items-center gap-1.5 text-sm font-bold text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors" title="Kembali ke Portal Sekolah Utama">
                    <i class="fas fa-arrow-left"></i> Portal Sekolah
                </a>
                
                <!-- Pembatas (Divider) -->
                <div class="h-4 w-px bg-gray-300 dark:bg-gray-700"></div>

                <a href="#home" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Beranda</a>
                <a href="#unit" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Unit Kami</a>
                <a href="#keuntungan" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Keuntungan</a>
                
                <!-- DROPDOWN PORTAL SEKOLAH -->
                <div class="relative">
                    <button @click="portalDropdownOpen = !portalDropdownOpen" @click.away="portalDropdownOpen = false" type="button" class="flex items-center gap-1 text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors focus:outline-none">
                        Unit Terkait
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': portalDropdownOpen}"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="portalDropdownOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50"
                         style="display: none;">
                        
                        <div class="px-4 py-2 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            Website Utama
                        </div>
                        <a href="<?= base_url('/') ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-gray-700 dark:hover:text-white transition-colors">
                            <i class="fas fa-building w-5 text-center mr-2"></i> Portal Yayasan
                        </a>
                        
                        <?php if (!empty($units)): ?>
                            <div class="h-px bg-gray-100 dark:bg-gray-700 my-2 mx-4"></div>
                            <div class="px-4 py-2 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                Website Unit
                            </div>
                            <?php foreach($units as $unit): ?>
                                <a href="<?= base_url('portal/unit/' . strtolower($unit['kode_jenjang'])) ?>" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-gray-700 dark:hover:text-white transition-colors">
                                    <i class="fas fa-graduation-cap w-5 text-center mr-2"></i> Unit <?= esc($unit['kode_jenjang']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Desktop Auth Buttons -->
            <div class="hidden md:flex items-center gap-3">
                <a href="<?= base_url('portal/affiliated/login') ?>" class="px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-xl transition-all">
                    Masuk
                </a>
                <a href="<?= base_url('portal/affiliated/register') ?>" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-500/20 transition-all">
                    Daftar Mitra
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
            <!-- Navigasi Standar Kembali ke Portal -->
            <a href="<?= base_url('/') ?>" class="px-4 py-3 text-base font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Portal Sekolah Utama
            </a>
            <div class="h-px w-full bg-gray-100 dark:bg-gray-800 my-1"></div>

            <a @click="mobileMenuOpen = false" href="#home" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Beranda</a>
            <a @click="mobileMenuOpen = false" href="#unit" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Unit Kami</a>
            <a @click="mobileMenuOpen = false" href="#keuntungan" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Keuntungan</a>
            
            <!-- Mobile Portal Links -->
            <div class="px-4 py-2 mt-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Unit Terkait</span>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-2 space-y-1">
                    <a href="<?= base_url('/') ?>" class="block px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-lg">
                        <i class="fas fa-building w-5 text-center mr-2"></i> Portal Yayasan
                    </a>
                    <?php if (!empty($units)): ?>
                        <?php foreach($units as $unit): ?>
                            <a href="<?= base_url('portal/unit/' . strtolower($unit['kode_jenjang'])) ?>" class="block px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-lg">
                                <i class="fas fa-graduation-cap w-5 text-center mr-2"></i> Unit <?= esc($unit['kode_jenjang']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="h-px w-full bg-gray-100 dark:bg-gray-800 my-2"></div>
            
            <a href="<?= base_url('portal/affiliated/login') ?>" class="px-4 py-3 text-center text-base font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 rounded-xl">Masuk</a>
            <a href="<?= base_url('portal/affiliated/register') ?>" class="px-4 py-3 text-center text-base font-bold text-white bg-blue-600 rounded-xl mt-2">Daftar Mitra</a>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<div id="home" class="relative bg-white dark:bg-gray-900 overflow-hidden scroll-mt-20">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[url('https://source.unsplash.com/1600x900/?education,building')] bg-cover bg-center opacity-5 dark:opacity-10"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-100/50 dark:from-gray-900 dark:to-gray-800/50"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
        <div class="text-center max-w-4xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 font-bold text-xs uppercase tracking-wider mb-6 animate-pulse border border-orange-200 dark:border-orange-800">
                <i class="fas fa-fire"></i> Program Fee Baru & Lebih Menguntungkan
            </div>
            
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-6 leading-tight">
                Maksimalkan Pendapatan dengan <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Komisi Progresif & Bonus Target</span>
            </h1>
            
            <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 mb-10 leading-relaxed max-w-2xl mx-auto">
                Bergabunglah dengan <strong><?= esc($foundation['nama_sekolah'] ?? 'Institusi Pendidikan Kami') ?></strong>. Nikmati fee lebih besar di periode <em>Early Bird</em> dan raih <strong>Bonus Tunai</strong> setiap kelipatan target.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?= base_url('portal/affiliated/register') ?>" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-1 flex items-center justify-center gap-2">
                    Daftar Mitra Sekarang
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="<?= base_url('portal/affiliated/login') ?>" class="w-full sm:w-auto px-8 py-4 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-bold rounded-2xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk Akun
                </a>
            </div>
        </div>
    </div>
</div>

<!-- INFO YAYASAN & UNIT -->
<div id="unit" class="py-16 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Foundation Header (Info Yayasan) -->
        <div class="text-center mb-16 max-w-4xl mx-auto">
            <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-3">
                <?= esc($foundation['nama_sekolah'] ?? 'Yayasan Pendidikan') ?>
            </h2>
            
            <?php if(!empty($foundation['motto'])): ?>
                <p class="text-xl text-blue-600 dark:text-blue-400 italic font-serif mb-6">
                    "<?= esc($foundation['motto']) ?>"
                </p>
            <?php endif; ?>

            <!-- VISI YAYASAN -->
            <?php if(!empty($foundation['visi'])): ?>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 relative shadow-sm inline-block text-left mx-auto max-w-3xl">
                    <div class="flex gap-4">
                        <i class="fas fa-quote-left text-gray-300 dark:text-gray-600 text-3xl"></i>
                        <div>
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Visi Kami</h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                                <?= nl2br(esc($foundation['visi'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($foundation['alamat'])): ?>
                <div class="mt-8 flex justify-center">
                    <div class="inline-flex items-center gap-2 text-sm text-gray-500 bg-white dark:bg-gray-900 px-5 py-2.5 rounded-full border border-gray-200 dark:border-gray-700 shadow-sm hover:border-blue-300 transition-colors">
                        <i class="fas fa-map-marker-alt text-red-500"></i>
                        <?= esc($foundation['alamat']) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Units Grid -->
        <div class="text-center mb-10">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-md">
                Unit Sekolah Terbuka
            </span>
        </div>

        <?php if (!empty($units)): ?>
            <div class="flex flex-wrap justify-center gap-6">
                <?php foreach($units as $unit): ?>
                    <div class="group w-full sm:w-80 bg-white dark:bg-gray-800 rounded-3xl p-6 text-center hover:bg-gradient-to-br hover:from-blue-600 hover:to-indigo-600 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 cursor-default border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col items-center h-auto min-h-[320px]">
                        
                        <!-- Icon Circle -->
                        <div class="w-16 h-16 bg-blue-50 dark:bg-gray-700 text-blue-600 group-hover:bg-white group-hover:text-blue-600 rounded-full flex items-center justify-center mb-5 shadow-sm group-hover:shadow-lg transition-colors shrink-0">
                            <span class="text-xl font-black font-mono"><?= esc($unit['kode_jenjang']) ?></span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-white mb-3">
                            <?= esc($unit['nama_jenjang']) ?>
                        </h3>

                        <!-- CONTENT AREA -->
                        <div class="flex-grow flex flex-col justify-start items-center w-full space-y-3 mb-6">
                            <?php if(!empty($unit['motto_unit'])): ?>
                                <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-100 italic">
                                    "<?= esc($unit['motto_unit']) ?>"
                                </p>
                            <?php endif; ?>

                            <?php if(!empty($unit['visi_unit'])): ?>
                                <div class="w-full pt-3 border-t border-gray-100 dark:border-white/10 group-hover:border-white/20 mt-auto">
                                    <p class="text-[10px] uppercase font-bold text-gray-400 group-hover:text-blue-200 mb-1">Visi Unit</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 group-hover:text-white line-clamp-3 leading-relaxed">
                                        <?= esc($unit['visi_unit']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mt-auto">
                            <div class="text-xs font-bold text-green-600 dark:text-green-400 group-hover:text-white flex items-center justify-center gap-2 bg-green-50 dark:bg-green-900/20 group-hover:bg-white/20 py-1.5 px-3 rounded-full">
                                <span class="w-2 h-2 rounded-full bg-green-500 group-hover:bg-white animate-pulse"></span>
                                Pendaftaran Dibuka
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center p-12 bg-gray-50 dark:bg-gray-800 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700 max-w-2xl mx-auto">
                <i class="fas fa-school text-gray-300 text-4xl mb-4"></i>
                <p class="text-gray-500 font-medium">Belum ada data unit sekolah yang ditampilkan saat ini.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- BENEFITS HIGHLIGHT -->
<div id="keuntungan" class="py-20 bg-gray-50 dark:bg-gray-800/50 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-4">Keuntungan Eksklusif Mitra</h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Kami menghargai kerja keras Anda dengan sistem reward yang transparan dan kompetitif.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Benefit 1 -->
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 relative group hover:-translate-y-2 transition-transform duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-lg shadow-blue-500/30 relative z-10">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Fee Dinamis (Early Bird)</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                    Dapatkan komisi tertinggi dengan mendaftarkan siswa lebih awal. Manfaatkan momen <strong>Gelombang 1</strong> untuk pendapatan maksimal!
                </p>
            </div>

            <!-- Benefit 2 -->
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 relative group hover:-translate-y-2 transition-transform duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-purple-50 dark:bg-purple-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 bg-purple-600 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-lg shadow-purple-500/30 relative z-10">
                    <i class="fas fa-gift"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Bonus Kelipatan Target</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                    Dapatkan <strong>Bonus Tambahan Rp 1 Juta</strong> untuk setiap kelipatan <strong>3 siswa valid</strong> yang Anda daftarkan, di luar fee dasar!
                </p>
            </div>

            <!-- Benefit 3 -->
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 relative group hover:-translate-y-2 transition-transform duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="w-14 h-14 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-lg shadow-emerald-500/30 relative z-10">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Pantau Progress Real-Time</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                    Lihat statistik pendaftar, status pembayaran siswa, dan progress bonus target Anda secara langsung melalui dashboard.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- CTA FOOTER -->
<div class="py-20 bg-white dark:bg-gray-900 text-center border-t border-gray-100 dark:border-gray-800">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Siap Mencetak Penghasilan Tambahan?</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-8 text-lg">Jangan lewatkan kesempatan periode Early Bird dengan fee tertinggi.</p>
        <a href="<?= base_url('portal/affiliated/register') ?>" class="inline-block px-10 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-2xl shadow-xl hover:scale-105 transition-transform mb-10">
            Gabung Mitra Sekarang
        </a>
        
        <!-- NAVIGASI KEMBALI DI FOOTER -->
        <div class="mt-8 border-t border-gray-200 dark:border-gray-800 pt-8">
            <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i> Kembali ke Portal Sekolah Utama
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>