<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-black px-4 sm:px-6 py-12 md:py-20">
    <div class="max-w-7xl w-full">
        
        <!-- Header Utama (Hero Section) -->
        <div class="text-center mb-16 animate-fade-in-down">
            <div class="inline-block px-4 py-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 rounded-full text-xs font-black uppercase tracking-widest mb-6 shadow-sm">
                Portal Informasi Terpadu
            </div>
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-6 leading-tight">
                <?= esc($settings['nama_yayasan'] ?? 'Yayasan Pendidikan Rahmany') ?>
            </h1>
            
            <p class="text-lg md:text-xl lg:text-2xl text-gray-600 dark:text-gray-300 font-medium max-w-4xl mx-auto mb-8 leading-relaxed italic">
                "<?= esc($settings['motto'] ?? 'Membangun Generasi Qur\'ani, Unggul, dan Berkarakter Islami') ?>"
            </p>
            
            <div class="w-24 md:w-32 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full mx-auto"></div>
        </div>

        <!-- Section Nilai Tambah (Keunggulan) - Tampil Berbeda di Desktop vs Mobile -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16 px-2">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-emerald-100 dark:border-gray-700 flex items-start gap-4 transform transition hover:-translate-y-1 hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                    <i class="fas fa-quran text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-1">Pendidikan Berkarakter</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Integrasi nilai Islam dan Al-Qur'an di setiap jenjang pendidikan.</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-emerald-100 dark:border-gray-700 flex items-start gap-4 transform transition hover:-translate-y-1 hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 flex-shrink-0">
                    <i class="fas fa-leaf text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-1">Lingkungan Sehat</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Fasilitas ramah anak dan berwawasan lingkungan hijau (Eco-School).</p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-emerald-100 dark:border-gray-700 flex items-start gap-4 transform transition hover:-translate-y-1 hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/50 flex items-center justify-center text-amber-600 dark:text-amber-400 flex-shrink-0">
                    <i class="fas fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-1">Berkelanjutan</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Kaderisasi utuh dari TK hingga jenjang pendidikan menengah atas.</p>
                </div>
            </div>
        </div>

        <!-- Judul Unit Sekolah -->
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100">Jelajahi Unit Pendidikan Kami</h2>
            <p class="text-sm text-gray-500 mt-2">Pilih unit sekolah untuk informasi pendaftaran dan akademik.</p>
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
                    'desc'  => 'Membangun fondasi karakter anak usia dini melalui metode bermain yang islami dan menyenangkan.'
                ],
                'SD' => [
                    'color' => 'from-amber-400 to-orange-500',
                    'icon'  => 'fa-child',
                    'text'  => 'text-amber-600 dark:text-amber-400',
                    'bg'    => 'bg-amber-50 dark:bg-amber-900/20',
                    'desc'  => 'Pendidikan dasar terpadu yang fokus pada pembiasaan ibadah, akhlak mulia, dan kemampuan dasar akademik.'
                ],
                'SMP' => [
                    'color' => 'from-emerald-500 to-teal-500',
                    'icon'  => 'fa-user-graduate',
                    'text'  => 'text-emerald-600 dark:text-emerald-400',
                    'bg'    => 'bg-emerald-50 dark:bg-emerald-900/20',
                    'desc'  => 'Masa transisi remaja yang dikawal dengan disiplin ilmu agama, tahfiz Qur\'an, dan literasi teknologi.'
                ],
                'SMA' => [
                    'color' => 'from-sky-500 to-indigo-500',
                    'icon'  => 'fa-university',
                    'text'  => 'text-sky-600 dark:text-sky-400',
                    'bg'    => 'bg-sky-50 dark:bg-sky-900/20',
                    'desc'  => 'Inkubasi kepemimpinan dan persiapan masuk perguruan tinggi dengan wawasan global berlandaskan iman.'
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
                <!-- Card Unit -->
                <div class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-21px)] group bg-white dark:bg-gray-800 rounded-3xl shadow-lg hover:shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-all duration-300 hover:-translate-y-2 flex flex-col">
                    <!-- Top Accent Line -->
                    <div class="h-2 w-full bg-gradient-to-r <?= $style['color'] ?>"></div>
                    
                    <div class="p-8 flex flex-col h-full relative">
                        <!-- Icon Badge -->
                        <div class="absolute top-8 right-8 w-12 h-12 rounded-full <?= $style['bg'] ?> flex items-center justify-center opacity-70 group-hover:opacity-100 transition-opacity">
                            <i class="fas <?= $style['icon'] ?> <?= $style['text'] ?> text-xl"></i>
                        </div>

                        <!-- Jenjang Label -->
                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-widest <?= $style['bg'] ?> <?= $style['text'] ?>">
                                Unit <?= esc($kode) ?>
                            </span>
                        </div>
                        
                        <!-- Nama Unit -->
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2 leading-tight">
                            <?= esc($namaUnit) ?>
                        </h3>
                        
                        <!-- Motto Unit -->
                        <p class="text-sm font-semibold italic text-gray-500 dark:text-gray-400 mb-6 line-clamp-2">
                            "<?= esc($mottoUnit) ?>"
                        </p>
                        
                        <!-- Deskripsi / Sejarah -->
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-8 flex-grow line-clamp-4 relative z-10">
                            <?= esc($descUnit) ?>
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3 mt-auto">
                            <a href="<?= base_url(strtolower($kode)) ?>"
                               class="flex items-center justify-center w-full py-3.5 bg-gradient-to-r <?= $style['color'] ?> text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                                Kunjungi Website
                                <i class="fas fa-arrow-right ml-2 text-xs"></i>
                            </a>
                            
                            <a href="<?= base_url('login') ?>"
                               class="flex items-center justify-center w-full py-3 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold text-xs rounded-xl border border-gray-200 dark:border-gray-600 transition-colors">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Login Portal Akademik
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
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Data jenjang pendidikan saat ini belum tersedia atau belum diaktifkan dalam sistem.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer / Informasi Kontak -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 md:p-12 mb-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">
                        <?= esc($settings['nama_yayasan'] ?? 'Yayasan Pendidikan Rahmany') ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex items-center justify-center md:justify-start gap-2 mb-2">
                        <i class="fas fa-map-marker-alt text-emerald-500"></i>
                        <?= esc($settings['alamat'] ?? 'Jl. Lapangan Member Blok C No. 11, Depok') ?>
                    </p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm flex items-center justify-center md:justify-start gap-4">
                        <span><i class="fas fa-phone mr-1"></i> <?= esc($settings['telepon'] ?? '021-77833598') ?></span>
                        <span><i class="fas fa-envelope mr-1"></i> <?= esc($settings['email'] ?? 'info@rahmaniyah.sch.id') ?></span>
                    </p>
                </div>
                
                <div class="flex gap-4">
                    <a href="#" class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-emerald-500 hover:text-white transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-pink-500 hover:text-white transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-red-500 hover:text-white transition-colors">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <div class="border-t border-gray-100 dark:border-gray-700 mt-8 pt-8 text-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-widest">
                    &copy; <?= date('Y') ?> ERP Sekolah Terpadu • Powered by ICT Rahmaniyah
                </p>
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