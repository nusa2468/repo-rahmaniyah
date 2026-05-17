<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<!-- HACK CSS: Menyembunyikan Navbar bawaan dari public_layout agar tidak bentrok dengan Smart Navbar di bawah -->
<style>
    /* Mengincar header/nav bawaan layout lama agar tidak muncul khusus di halaman ini */
    header, .navbar, nav:not(.smart-hub-nav) { display: none !important; }
    html { scroll-behavior: smooth; }
</style>

<!-- ========================================================================= -->
<!-- 1. SMART LOGIN HUB NAVBAR (Eksklusif NusantaraERP Style) -->
<!-- ========================================================================= -->
<nav x-data="{ loginOpen: false, scrolled: false }" 
     @scroll.window="scrolled = (window.pageYOffset > 20) ? true : false"
     :class="{ 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg shadow-md': scrolled, 'bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800': !scrolled }"
     class="smart-hub-nav fixed top-0 left-0 w-full z-[100] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            
            <!-- Logo Kiri -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg shadow-blue-500/30">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="flex flex-col leading-none">
                    <span class="font-black text-xl text-gray-900 dark:text-white tracking-tight hidden sm:block">
                        Nusantara<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">ERP</span>
                    </span>
                    <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-0.5 hidden sm:block">Portal Akademik</span>
                </div>
            </div>

            <!-- Menu Kanan -->
            <div class="flex items-center gap-4">
                <a href="<?= base_url('portal/affiliated') ?>" target="_blank" rel="noopener noreferrer" 
                   class="hidden sm:inline-flex items-center gap-2 px-5 py-2 text-sm font-bold text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors border-r border-gray-200 dark:border-gray-700 pr-6">
                    <i class="fas fa-handshake text-blue-500"></i> Kemitraan Afiliasi
                </a>

                <!-- DROPDOWN PUSAT LOGIN (SMART HUB) -->
                <div class="relative">
                    <button @click="loginOpen = !loginOpen" @click.away="loginOpen = false" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95 group">
                        <i class="fas fa-th-large group-hover:rotate-90 transition-transform duration-300"></i> Pusat Akses
                        <i class="fas fa-chevron-down text-xs ml-1 transition-transform duration-200 opacity-70" :class="{'rotate-180': loginOpen}"></i>
                    </button>

                    <!-- Menu Dropdown Layanan -->
                    <div x-show="loginOpen" style="display: none;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute right-0 mt-3 w-72 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 py-2 z-50 overflow-hidden">
                        
                        <div class="px-4 py-3 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
                            <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Pilih Portal Layanan</p>
                        </div>

                        <!-- Opsi 1: Siswa & Ortu -->
                        <a href="<?= base_url('portal/siswa/login') ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-0.5 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Portal Siswa & Ortu</h4>
                                <p class="text-[10px] text-gray-500">Cek Nilai, Rapor, Jadwal & SPP</p>
                            </div>
                        </a>

                        <!-- Opsi 2: Guru & Staf -->
                        <a href="<?= base_url('portal/pegawai/login') ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors group">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-0.5 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Portal Pegawai (Guru)</h4>
                                <p class="text-[10px] text-gray-500">Jadwal Mengajar, Presensi, E-Learning</p>
                            </div>
                        </a>

                        <!-- Opsi 3: Pendaftaran Baru -->
                        <a href="<?= base_url('portal/ppdb/login') ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-orange-50 dark:hover:bg-gray-700 transition-colors group border-t border-gray-50 dark:border-gray-700/50">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform shadow-sm">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-0.5 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Portal PPDB Online</h4>
                                <p class="text-[10px] text-gray-500">Pendaftaran & Cek Status Kelulusan</p>
                            </div>
                        </a>

                        <!-- Opsi 4: Admin Backoffice (NusantaraERP Core) -->
                        <div class="px-4 py-3 bg-gray-900 dark:bg-black mt-2">
                            <a href="<?= base_url('login') ?>" class="flex items-center justify-between w-full text-left group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-white group-hover:bg-indigo-600 transition-all">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-200 group-hover:text-white transition-colors uppercase tracking-widest">Backoffice ERP</span>
                                        <span class="text-[9px] text-gray-500">Manajemen Yayasan & TU</span>
                                    </div>
                                </div>
                                <i class="fas fa-arrow-right text-[10px] text-gray-500 group-hover:text-indigo-400 transition-colors transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ========================================================================= -->
<!-- 2. KONTEN UTAMA HERO SECTION -->
<!-- ========================================================================= -->
<div class="min-h-screen flex flex-col items-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-black px-4 sm:px-6 pt-32 pb-12 md:pb-20 relative">
    
    <!-- Pattern Latar Belakang -->
    <div class="absolute inset-0 z-0 opacity-[0.03] dark:opacity-10 pointer-events-none" style="background-image: radial-gradient(#4f46e5 1px, transparent 1px); background-size: 30px 30px;"></div>

    <div class="max-w-7xl w-full relative z-10">
        
        <!-- Header Utama -->
        <div class="text-center mb-16 animate-fade-in-down">
            <div class="inline-flex items-center gap-2 px-5 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-6 shadow-sm border border-blue-200 dark:border-blue-800">
                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                Portal Informasi Terpadu
            </div>
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-6 leading-tight">
                <?= esc($settings['nama_yayasan'] ?? 'Yayasan Pendidikan Nusantara') ?>
            </h1>
            
            <p class="text-lg md:text-xl lg:text-2xl text-gray-600 dark:text-gray-300 font-medium max-w-4xl mx-auto mb-8 leading-relaxed italic">
                "<?= esc($settings['motto'] ?? 'Membangun Generasi Cerdas, Unggul, dan Berkarakter Islami') ?>"
            </p>
            
            <div class="w-24 md:w-32 h-1.5 bg-gradient-to-r from-blue-600 to-indigo-500 rounded-full mx-auto"></div>
        </div>

        <!-- Section Nilai Tambah (Keunggulan) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20 px-2">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-xl group">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-laptop-house text-3xl"></i>
                </div>
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-3">Ekosistem Digital</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Pembelajaran dan administrasi terintegrasi dalam satu platform cerdas E-Learning & ERP.</p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-xl group">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-quran text-3xl"></i>
                </div>
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-3">Pendidikan Berkarakter</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Keseimbangan antara ilmu pengetahuan modern sains dan pembinaan budi pekerti agama.</p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2rem] shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center text-center transform transition duration-300 hover:-translate-y-2 hover:shadow-xl group">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-6 group-hover:scale-110 transition-transform">
                    <i class="fas fa-graduation-cap text-3xl"></i>
                </div>
                <h4 class="text-lg font-black text-gray-900 dark:text-white mb-3">Kaderisasi Berkelanjutan</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Pembinaan utuh dan berkesinambungan mulai dari jenjang Pendidikan Dasar hingga Menengah Atas.</p>
            </div>
        </div>

        <!-- Judul Unit Sekolah -->
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Jelajahi Unit Pendidikan Kami</h2>
            <p class="text-sm text-gray-500 mt-2 font-medium">Pilih unit sekolah untuk melihat informasi akademik dan website spesifik.</p>
        </div>

        <!-- Unit Sekolah Cards -->
        <div class="flex flex-wrap justify-center gap-6 md:gap-8 mb-20">
            <?php
            // Pemetaan Gaya dan Deskripsi Default Berdasarkan Jenjang
            $styleMap = [
                'TK' => [
                    'color' => 'from-pink-500 to-rose-500',
                    'icon'  => 'fa-shapes',
                    'text'  => 'text-pink-600 dark:text-pink-400',
                    'bg'    => 'bg-pink-50 dark:bg-pink-900/20',
                    'desc'  => 'Membangun fondasi karakter anak usia dini melalui metode bermain yang menyenangkan.'
                ],
                'SD' => [
                    'color' => 'from-amber-400 to-orange-500',
                    'icon'  => 'fa-child',
                    'text'  => 'text-amber-600 dark:text-amber-400',
                    'bg'    => 'bg-amber-50 dark:bg-amber-900/20',
                    'desc'  => 'Pendidikan dasar terpadu yang fokus pada pembiasaan karakter dan kemampuan dasar akademik.'
                ],
                'SMP' => [
                    'color' => 'from-emerald-500 to-teal-500',
                    'icon'  => 'fa-user-graduate',
                    'text'  => 'text-emerald-600 dark:text-emerald-400',
                    'bg'    => 'bg-emerald-50 dark:bg-emerald-900/20',
                    'desc'  => 'Masa transisi remaja yang dikawal dengan kedisiplinan dan literasi teknologi.'
                ],
                'SMA' => [
                    'color' => 'from-sky-500 to-indigo-500',
                    'icon'  => 'fa-university',
                    'text'  => 'text-sky-600 dark:text-sky-400',
                    'bg'    => 'bg-sky-50 dark:bg-sky-900/20',
                    'desc'  => 'Inkubasi kepemimpinan dan persiapan masuk perguruan tinggi dengan wawasan global.'
                ]
            ];

            if (!empty($units) && is_array($units)) :
                foreach ($units as $u):
                    $kode = strtoupper($u['kode_jenjang'] ?? '');
                    if ($kode === 'GLOBAL') continue;

                    $style = $styleMap[$kode] ?? [
                        'color' => 'from-slate-500 to-gray-600',
                        'icon'  => 'fa-school',
                        'text'  => 'text-slate-600 dark:text-slate-400',
                        'bg'    => 'bg-slate-50 dark:bg-slate-900/20',
                        'desc'  => 'Layanan pendidikan berkualitas untuk masa depan unggul.'
                    ];
                    
                    $config = $u['config'] ?? [];
                    $namaUnit = $config['nama_sekolah'] ?? $u['nama_jenjang'] ?? "Unit $kode";
                    $mottoUnit = $config['motto'] ?? 'Berprestasi & Berakhlak';
                    
                    // Prioritas Deskripsi: Sejarah dari DB -> Deskripsi Default Jenjang
                    $descUnit = $config['sejarah'] ?? $style['desc'];
            ?>
                <!-- Card Unit (Tombol Login Dihapus) -->
                <div class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-21px)] group bg-white dark:bg-gray-800 rounded-[2rem] shadow-lg hover:shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:-translate-y-2 flex flex-col">
                    <!-- Top Accent Line -->
                    <div class="h-2.5 w-full bg-gradient-to-r <?= $style['color'] ?>"></div>
                    
                    <div class="p-8 flex flex-col h-full relative">
                        <!-- Icon Badge -->
                        <div class="absolute top-8 right-8 w-14 h-14 rounded-2xl <?= $style['bg'] ?> flex items-center justify-center opacity-70 group-hover:opacity-100 transition-opacity transform group-hover:rotate-6">
                            <i class="fas <?= $style['icon'] ?> <?= $style['text'] ?> text-2xl"></i>
                        </div>

                        <!-- Jenjang Label -->
                        <div class="mb-5">
                            <span class="inline-block px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest <?= $style['bg'] ?> <?= $style['text'] ?>">
                                Unit <?= esc($kode) ?>
                            </span>
                        </div>
                        
                        <!-- Nama Unit -->
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2 leading-tight pr-12">
                            <?= esc($namaUnit) ?>
                        </h3>
                        
                        <!-- Motto Unit -->
                        <p class="text-sm font-bold italic text-gray-400 dark:text-gray-500 mb-6 line-clamp-2">
                            "<?= esc($mottoUnit) ?>"
                        </p>
                        
                        <!-- Deskripsi / Sejarah -->
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-8 flex-grow line-clamp-4 relative z-10">
                            <?= esc($descUnit) ?>
                        </p>
                        
                        <!-- KUNCI PERUBAHAN: Hanya Tombol Kunjungi Website (Login sudah di Navbar) -->
                        <div class="mt-auto">
                            <a href="<?= base_url(strtolower($kode)) ?>"
                               class="flex items-center justify-between w-full px-6 py-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-gradient-to-r hover:<?= $style['color'] ?> text-gray-700 dark:text-gray-300 hover:text-white font-bold text-sm rounded-xl transition-all duration-300 group/btn">
                                <span>Kunjungi Laman Unit</span>
                                <i class="fas fa-arrow-right text-xs opacity-50 group-hover/btn:opacity-100 group-hover/btn:translate-x-1 transition-all"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach; 
            else:
            ?>
                <!-- Empty State -->
                <div class="w-full text-center py-16 px-4 bg-white dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 max-w-2xl mx-auto">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-school text-4xl text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Belum Ada Unit Sekolah</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Data jenjang pendidikan saat ini belum tersedia atau belum diaktifkan dalam sistem database.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer / Informasi Kontak -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-700 p-8 md:p-12 mb-8 relative overflow-hidden">
            <!-- Background Element -->
            <div class="absolute right-0 bottom-0 w-64 h-64 bg-blue-50 dark:bg-gray-700/30 rounded-tl-full opacity-50 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center justify-between gap-8 relative z-10">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">
                        <?= esc($settings['nama_yayasan'] ?? 'Yayasan Pendidikan Nusantara') ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex items-center justify-center md:justify-start gap-2 mb-2 font-medium">
                        <i class="fas fa-map-marker-alt text-rose-500 w-4 text-center"></i>
                        <?= esc($settings['alamat'] ?? 'Jl. Pendidikan No. 1, Kota Impian') ?>
                    </p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex flex-col sm:flex-row items-center justify-center md:justify-start gap-2 sm:gap-6 font-medium">
                        <span class="flex items-center gap-2"><i class="fas fa-phone text-blue-500 w-4 text-center"></i> <?= esc($settings['telepon'] ?? '(021) 1234567') ?></span>
                        <span class="flex items-center gap-2"><i class="fas fa-envelope text-blue-500 w-4 text-center"></i> <?= esc($settings['email'] ?? 'info@nusantara.sch.id') ?></span>
                    </p>
                </div>
                
                <div class="flex gap-3">
                    <a href="#" class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-pink-600 hover:text-white hover:border-pink-600 transition-all shadow-sm">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-red-600 hover:text-white hover:border-red-600 transition-all shadow-sm">
                        <i class="fab fa-youtube text-lg"></i>
                    </a>
                </div>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-700 mt-10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 relative z-10">
                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest">
                    &copy; <?= date('Y') ?> ERP Sekolah Terpadu Versi 1.0
                </p>
                <div class="flex items-center gap-2 opacity-60">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Powered By</span>
                    <span class="text-xs font-black text-gray-500 tracking-tighter">Nusantara<span class="text-blue-500">ERP</span></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.8s ease-out forwards;
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    
    .line-clamp-4 {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>

<?= $this->endSection() ?>