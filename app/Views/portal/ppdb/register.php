<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<style>
    html { scroll-behavior: smooth; }
    /* Menghilangkan spinner pada input number */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    /* Kustomisasi Select Option */
    select option {
        padding: 10px;
        background-color: #fff;
        color: #333;
    }
</style>

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

                <a href="<?= base_url('portal/ppdb') ?>" class="text-sm font-bold text-gray-600 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">Beranda PPDB</a>
                <a href="<?= base_url('portal/ppdb/register') ?>" class="text-sm font-bold text-blue-600 dark:text-blue-400 transition-colors">Formulir Pendaftaran</a>
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

            <a @click="mobileMenuOpen = false" href="<?= base_url('portal/ppdb') ?>" class="px-4 py-3 text-base font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl">Beranda PPDB</a>
            <a @click="mobileMenuOpen = false" href="<?= base_url('portal/ppdb/register') ?>" class="px-4 py-3 text-base font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl">Formulir Pendaftaran</a>
            
            <div class="h-px w-full bg-gray-100 dark:bg-gray-800 my-2"></div>
            
            <a href="<?= base_url('portal/ppdb/login') ?>" class="px-4 py-3 text-center text-base font-bold text-white bg-blue-600 rounded-xl flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Login Siswa
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- STEPPER NAVIGATION -->
    <div class="mb-12 max-w-3xl mx-auto">
        <div class="relative flex items-center justify-between">
            <div class="absolute left-0 top-1/2 w-full h-0.5 bg-gray-200 dark:bg-gray-700 -translate-y-1/2"></div>
            <div class="absolute left-0 top-1/2 w-1/2 h-0.5 bg-blue-600 -translate-y-1/2 transition-all duration-500"></div>

            <!-- Step 1: Info -->
            <div class="relative z-10 flex flex-col items-center">
                <a href="<?= base_url('portal/ppdb') ?>" class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg shadow-blue-500/30 transition-transform hover:scale-110">
                    <i class="fas fa-info-circle"></i>
                </a>
                <span class="absolute -bottom-7 text-[10px] font-bold text-gray-500 uppercase tracking-wider">Informasi</span>
            </div>

            <!-- Step 2: Registrasi (Active) -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 border-2 border-blue-600 text-blue-600 rounded-full shadow-xl">
                    <span class="font-bold">02</span>
                </div>
                <span class="absolute -bottom-7 text-[10px] font-bold text-blue-600 uppercase tracking-wider">Registrasi</span>
            </div>

            <!-- Step 3: Selesai -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-300 rounded-full">
                    <i class="fas fa-check"></i>
                </div>
                <span class="absolute -bottom-7 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Selesai</span>
            </div>
        </div>
    </div>

    <!-- HEADER TEXT -->
    <div class="mb-10 text-center max-w-2xl mx-auto pt-4">
        <div class="inline-flex items-center justify-center p-3 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-2xl mb-4 shadow-sm">
            <i class="fas fa-user-plus fa-lg"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-3">Formulir Pendaftaran</h2>
        <p class="text-gray-500 dark:text-gray-400 text-lg leading-relaxed">Pilih unit sekolah tujuan dan lengkapi biodata calon siswa.</p>
    </div>

    <!-- ALERT ERROR -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div x-data="{ show: true }" x-show="show" class="mb-8 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl flex items-start gap-3 shadow-sm">
            <div class="text-red-500 shrink-0 mt-0.5">
                <i class="fas fa-exclamation-circle fa-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-red-800 dark:text-red-400 mb-1">Terjadi Kesalahan</h4>
                <div class="text-sm text-red-700 dark:text-red-300">
                    <?= session()->getFlashdata('error') ?>
                </div>
            </div>
            <button @click="show = false" type="button" class="text-red-400 hover:text-red-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- FORM START -->
    <form action="<?= base_url('portal/ppdb/submit') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- LEFT COLUMN: INPUT FIELDS -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- === UNIT SELECTION CARD (DROPDOWN AKTIF) === -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl shadow-lg shadow-blue-500/30 p-6 md:p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
                    
                    <div class="relative z-10">
                        <label class="flex items-center gap-2 text-blue-100 text-xs font-bold uppercase tracking-widest mb-3">
                            <i class="fas fa-university"></i> Pilih Unit Sekolah Tujuan <span class="text-red-300">*</span>
                        </label>
                        
                        <div class="relative">
                            <select name="kode_jenjang" class="w-full px-5 py-4 pr-12 rounded-2xl bg-white text-gray-900 font-bold text-lg focus:ring-4 focus:ring-blue-400/50 outline-none appearance-none shadow-xl cursor-pointer" required>
                                <option value="" disabled selected>-- Klik untuk Memilih Unit --</option>
                                <?php if (!empty($units)): ?>
                                    <?php foreach ($units as $unit) : ?>
                                        <option value="<?= esc($unit['kode_jenjang']) ?>" <?= old('kode_jenjang') == $unit['kode_jenjang'] ? 'selected' : '' ?>>
                                            <?= esc($unit['nama_sekolah']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Belum ada unit yang membuka pendaftaran</option>
                                <?php endif; ?>
                            </select>
                            
                            <div class="absolute inset-y-0 right-0 flex items-center px-5 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-lg"></i>
                            </div>
                        </div>
                        <p class="text-blue-100 text-xs mt-3 opacity-80">* Pastikan Anda memilih unit/jenjang yang benar sesuai tujuan pendaftaran.</p>
                    </div>
                </div>

                <!-- IDENTITAS -->
                <div id="section-identitas" class="scroll-mt-24 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-white/5 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">Data Identitas Calon Siswa</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" placeholder="Masukkan nama sesuai ijazah" value="<?= old('nama_lengkap') ?>" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">NIK (16 Digit) <span class="text-red-500">*</span></label>
                            <input type="number" name="nik" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" value="<?= old('nik') ?>" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">NISN (10 Digit) <span class="text-red-500">*</span></label>
                            <input type="number" name="nisn" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" value="<?= old('nisn') ?>" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="jenis_kelamin" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none appearance-none font-medium">
                                    <option value="L" <?= old('jenis_kelamin') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= old('jenis_kelamin') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="no_hp_whatsapp" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" placeholder="08xxxx" value="<?= old('no_hp_whatsapp') ?>" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Asal Sekolah <span class="text-red-500">*</span></label>
                            <input type="text" name="asal_sekolah" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none" value="<?= old('asal_sekolah') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- DATA ORTU -->
                <div id="section-ortu" class="scroll-mt-24 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-white/5 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">Data Orang Tua</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none" value="<?= old('nama_ayah') ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all outline-none" value="<?= old('nama_ibu') ?>">
                        </div>
                    </div>
                </div>

                <!-- PEMBAYARAN -->
                <div id="section-payment" class="scroll-mt-24 bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-yellow-400"></div>
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 dark:border-white/5 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400 flex items-center justify-center">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">Bukti Pembayaran</h3>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-500/10 rounded-2xl p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            Unggah bukti transfer (JPG/PNG/PDF, Max 2MB).
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Pilih Berkas <span class="text-red-500">*</span></label>
                        <input type="file" name="bukti_setor" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition-all cursor-pointer bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: SIDEBAR -->
            <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">
                
                <!-- CARD NAVIGASI -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6">
                    <h3 class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Navigasi</h3>
                    <nav class="space-y-1" id="side-nav">
                        <a href="#section-identitas" class="nav-link flex items-center gap-3 p-3 rounded-xl text-blue-600 bg-blue-50 dark:bg-blue-500/10 font-bold transition-all hover:translate-x-1 group">
                            <i class="fas fa-id-card w-5 text-center"></i>
                            <span class="text-sm">Identitas Siswa</span>
                        </a>
                        <a href="#section-ortu" class="nav-link flex items-center gap-3 p-3 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 font-bold transition-all hover:translate-x-1 group">
                            <i class="fas fa-users w-5 text-center"></i>
                            <span class="text-sm">Data Orang Tua</span>
                        </a>
                        <a href="#section-payment" class="nav-link flex items-center gap-3 p-3 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 font-bold transition-all hover:translate-x-1 group">
                            <i class="fas fa-receipt w-5 text-center"></i>
                            <span class="text-sm">Bukti Pembayaran</span>
                        </a>
                    </nav>
                </div>

                <!-- CARD JALUR & KODE AGEN (AUTO-REFILLED) -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-blue-100 dark:border-blue-500/20 p-6">
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Jalur Masuk</label>
                    <select name="jalur_masuk" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white mb-4 appearance-none outline-none font-bold">
                        <option value="Umum" <?= old('jalur_masuk') == 'Umum' ? 'selected' : '' ?>>Umum (Reguler)</option>
                        <option value="Prestasi" <?= old('jalur_masuk') == 'Prestasi' ? 'selected' : '' ?>>Jalur Prestasi</option>
                        <option value="Afirmasi" <?= old('jalur_masuk') == 'Afirmasi' ? 'selected' : '' ?>>Afirmasi</option>
                    </select>

                    <label class="block text-[10px] font-extrabold text-blue-600 dark:text-blue-400 uppercase mb-2">Kode Agen</label>
                    
                    <?php 
                        // LOGIKA AUTO-REF: Ambil parameter 'ref' dari URL
                        $request = \Config\Services::request();
                        $refCode = $request->getGet('ref');
                        
                        // Prioritas: Old Input > URL Param > Kosong
                        $valKode = old('kode_afiliasi') ?: $refCode;
                        
                        // Jika ada kode (dari link), buat readonly dan styling khusus
                        $isReadOnly = !empty($refCode) ? 'readonly' : '';
                        $bgClass    = !empty($refCode) ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 border-blue-200' : 'bg-white dark:bg-gray-800 text-gray-900 border-gray-200 dark:border-gray-700';
                    ?>
                    
                    <div class="relative">
                        <input type="text" name="kode_afiliasi" 
                               class="w-full px-3 py-2 rounded-lg border <?= $bgClass ?> text-sm outline-none font-mono font-bold tracking-wider uppercase transition-colors" 
                               placeholder="MKT-001" 
                               value="<?= esc($valKode) ?>" 
                               <?= $isReadOnly ?>>
                        
                        <?php if(!empty($refCode)): ?>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-500">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($refCode)): ?>
                        <p class="text-[10px] text-blue-500 mt-1 italic">
                            *Kode agen otomatis terisi dari link referral.
                        </p>
                    <?php else: ?>
                        <p class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak memiliki kode agen.</p>
                    <?php endif; ?>
                </div>

                <!-- ACTIONS -->
                <div class="space-y-3">
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 group">
                        <span>KIRIM PENDAFTARAN</span>
                        <i class="fas fa-paper-plane group-hover:translate-x-1 transition-transform"></i>
                    </button>
                    <a href="<?= base_url('portal/ppdb') ?>" class="w-full py-3 block text-center bg-white dark:bg-gray-800 text-gray-500 font-bold rounded-2xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Script Scroll Spy
    window.addEventListener('scroll', () => {
        const sections = ['identitas', 'ortu', 'payment'];
        const navLinks = document.querySelectorAll('.nav-link');
        let currentSection = '';

        sections.forEach(id => {
            const el = document.getElementById('section-' + id);
            if (el) {
                const offset = el.offsetTop - 150;
                if (window.pageYOffset >= offset) {
                    currentSection = 'section-' + id;
                }
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('bg-blue-50', 'dark:bg-blue-500/10', 'text-blue-600');
            link.classList.add('text-gray-500', 'dark:text-gray-400');
            if (link.getAttribute('href') === '#' + currentSection) {
                link.classList.add('bg-blue-50', 'dark:bg-blue-500/10', 'text-blue-600');
                link.classList.remove('text-gray-500', 'dark:text-gray-400');
            }
        });
    });
</script>

<?= $this->endSection() ?>