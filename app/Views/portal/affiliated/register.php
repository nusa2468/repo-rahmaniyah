<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900 transition-colors duration-300">

    <!-- STICKY NAVBAR -->
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
                    <a href="<?= base_url('portal/affiliated') ?>" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Beranda</a>
                    <a href="<?= base_url('portal/affiliated#unit') ?>" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Unit Kami</a>
                    <a href="<?= base_url('portal/affiliated#keuntungan') ?>" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Keuntungan</a>
                    
                    <!-- DROPDOWN PORTAL SEKOLAH -->
                    <div class="relative">
                        <button @click="portalDropdownOpen = !portalDropdownOpen" @click.away="portalDropdownOpen = false" type="button" class="flex items-center gap-1 text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors focus:outline-none">
                            Portal Kami
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
                <a @click="mobileMenuOpen = false" href="<?= base_url('portal/affiliated') ?>" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Beranda</a>
                <a @click="mobileMenuOpen = false" href="<?= base_url('portal/affiliated#unit') ?>" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Unit Kami</a>
                <a @click="mobileMenuOpen = false" href="<?= base_url('portal/affiliated#keuntungan') ?>" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Keuntungan</a>
                
                <!-- Mobile Portal Links -->
                <div class="px-4 py-2 mt-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-2">Portal Kami</span>
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
            </div>
        </div>
    </nav>

    <!-- FORM SECTION -->
    <div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full">
            
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white mb-4 shadow-lg shadow-blue-500/30">
                    <i class="fas fa-handshake text-3xl"></i>
                </div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Formulir Pendaftaran Mitra</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    Bergabunglah bersama kami dan dapatkan benefit dari setiap referensi siswa baru.
                </p>
            </div>

            <!-- Card Form -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-white/5 overflow-hidden">
                
                <!-- Alert Error -->
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/30 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <p class="text-sm text-red-700 dark:text-red-300 font-bold"><?= session()->getFlashdata('error') ?></p>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('portal/affiliated/register/submit') ?>" method="post" class="p-6 md:p-10 space-y-8">
                    <?= csrf_field() ?>

                    <!-- SECTION 1: DATA PRIBADI -->
                    <div>
                        <h3 class="text-sm font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-6 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fas fa-user-circle"></i> Data Pribadi
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_agen" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="Nama sesuai KTP" value="<?= old('nama_agen') ?>" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="tel" name="no_hp" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="08xxxxxxxxxx" value="<?= old('no_hp') ?>" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Alamat Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="alamat@email.com" value="<?= old('email') ?>" required>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Alamat Lengkap</label>
                                <textarea name="alamat" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all resize-none" placeholder="Alamat domisili saat ini"><?= old('alamat') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: BANK -->
                    <div>
                        <h3 class="text-sm font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-6 flex items-center gap-2 border-b border-gray-100 dark:border-gray-700 pb-2">
                            <i class="fas fa-wallet"></i> Informasi Pembayaran (Fee)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nama Bank <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_bank" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="Contoh: BCA / BRI" value="<?= old('nama_bank') ?>" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nomor Rekening <span class="text-red-500">*</span></label>
                                <input type="number" name="nomor_rekening" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="Nomor Rekening" value="<?= old('nomor_rekening') ?>" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Atas Nama <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_rekening" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all" placeholder="Pemilik Rekening" value="<?= old('nama_rekening') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: STRATEGI -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Rencana Strategi Pemasaran</label>
                        <div class="relative">
                            <select name="metode_agen" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none appearance-none cursor-pointer">
                                <option value="Digital Marketing">Digital Marketing (Sosmed/Ads)</option>
                                <option value="Direct Selling">Direct Selling (Kunjungan Langsung)</option>
                                <option value="Networking">Networking / Komunitas</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- CHECKBOX PERSETUJUAN -->
                    <div class="group flex items-start gap-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 transition-colors hover:bg-blue-100/50 dark:hover:bg-blue-900/40">
                        <div class="flex h-6 items-center">
                            <input id="agreement" name="agreement" type="checkbox" required class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer bg-white dark:bg-gray-800">
                        </div>
                        <label for="agreement" class="text-sm font-medium text-blue-900 dark:text-blue-100 leading-relaxed cursor-pointer select-none">
                            Dengan mendaftar, Anda setuju untuk mengikuti aturan promosi sekolah. Akun Anda akan diverifikasi oleh tim administrasi dalam 1x24 jam sebelum dapat digunakan.
                        </label>
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit" class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        <span>DAFTAR SEKARANG</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                </form>

                <!-- Footer Card -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 text-center border-t border-gray-100 dark:border-white/5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Sudah punya akun mitra? 
                        <a href="<?= base_url('portal/affiliated/login') ?>" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Login di sini
                        </a>
                    </p>
                </div>
            </div>

            <!-- Back to Home Link -->
            <div class="text-center mt-8 pb-10">
                <a href="<?= base_url('portal/affiliated') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda Mitra
                </a>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>