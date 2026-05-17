<?= $this->extend('layout/public_layout') ?>

<?= $this->section('header') ?>
<style>
    /* Mengincar header/nav bawaan layout lama agar tidak muncul khusus di halaman ini */
    .navbar, nav.navbar, nav:not(.smart-hub-nav) { display: none !important; }
    html { scroll-behavior: smooth; }
</style>
<style type="text/tailwindcss">
    @theme {
        --color-theme-primary: <?= $theme_color ?? '#2563eb' ?>; /* Default Blue jika null */
        --color-theme-secondary: <?= match(strtoupper($jenjang ?? 'SD')) {
            'TK' => '#ec4899',   /* Pink */
            'SD' => '#f59e0b',   /* Amber */
            'SMP' => '#10b981',  /* Emerald */
            'SMA' => '#8b5cf6',  /* Violet */
            default => '#2563eb' /* Blue */
        } ?>;
    }

    .hero-gradient {
        background: linear-gradient(to bottom, rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.95)),
                    url('https://source.unsplash.com/1600x900/?education,school,<?= strtolower($jenjang ?? 'school') ?>');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .section-line::after {
        content: '';
        @apply block w-20 h-1.5 bg-theme-primary mx-auto mt-6 rounded-full;
    }

    .card-hover {
        @apply transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ========================================================================= -->
<!-- 1. SMART LOGIN HUB NAVBAR (Eksklusif NusantaraERP Style) -->
<!-- ========================================================================= -->
<nav x-data="{ loginOpen: false, scrolled: false }" 
     @scroll.window="scrolled = (window.pageYOffset > 20) ? true : false"
     :class="{ 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg shadow-md': scrolled, 'bg-transparent border-b border-white/10': !scrolled }"
     class="smart-hub-nav fixed top-0 left-0 w-full z-[100] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            
            <!-- Logo Kiri -->
            <div class="flex items-center gap-3">
                <a href="<?= base_url('/') ?>" class="w-10 h-10 bg-gradient-to-br from-theme-primary to-theme-secondary rounded-xl flex items-center justify-center text-white font-black text-xl shadow-lg hover:scale-105 transition-transform" title="Kembali ke Portal Yayasan">
                    <i class="fas fa-school"></i>
                </a>
                <div class="flex flex-col leading-none">
                    <span class="font-black text-xl tracking-tight hidden sm:block transition-colors" :class="{ 'text-white': !scrolled, 'text-gray-900 dark:text-white': scrolled }">
                        Unit <span class="text-transparent bg-clip-text bg-gradient-to-r from-theme-primary to-theme-secondary"><?= esc(strtoupper($jenjang ?? '')) ?></span>
                    </span>
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 hidden sm:block">Portal Sekolah Terpadu</span>
                </div>
            </div>

            <!-- Menu Kanan -->
            <div class="flex items-center gap-4">
                <a href="<?= base_url('/') ?>" 
                   class="hidden sm:inline-flex items-center gap-2 px-5 py-2 text-sm font-bold transition-colors border-r pr-6"
                   :class="{ 'text-white/80 hover:text-white border-white/20': !scrolled, 'text-gray-600 dark:text-gray-300 hover:text-theme-primary border-gray-200 dark:border-gray-700': scrolled }">
                    <i class="fas fa-home"></i> Beranda Yayasan
                </a>

                <!-- DROPDOWN PUSAT LOGIN (SMART HUB) -->
                <div class="relative">
                    <button @click="loginOpen = !loginOpen" @click.away="loginOpen = false" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95 group"
                            :class="{ 'bg-white text-gray-900': !scrolled, 'bg-gray-900 dark:bg-white text-white dark:text-gray-900': scrolled }">
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
                                    <div class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-white group-hover:bg-theme-primary transition-all">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-200 group-hover:text-white transition-colors uppercase tracking-widest">Backoffice ERP</span>
                                        <span class="text-[9px] text-gray-500">Manajemen Yayasan & TU</span>
                                    </div>
                                </div>
                                <i class="fas fa-arrow-right text-[10px] text-gray-500 group-hover:text-theme-primary transition-colors transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- HERO SECTION - Super Solid & Compact -->
<header class="relative min-h-screen flex items-center justify-center hero-gradient pt-20">
    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-black/60 z-0"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-6 text-center mt-10 md:mt-0">
        <span class="inline-block px-5 py-2 rounded-full bg-theme-primary text-white text-xs font-black tracking-widest uppercase mb-8 shadow-lg shadow-theme-primary/30 border border-white/20">
            UNIT <?= esc(strtoupper($jenjang ?? 'SD')) ?>
        </span>
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight drop-shadow-2xl leading-tight">
            <?= esc($unit_name ?? 'Sekolah Terpadu Nusantara') ?>
        </h1>
        <p class="text-xl md:text-3xl text-white/90 font-medium italic max-w-4xl mx-auto mb-12 leading-relaxed">
            "<?= esc($settings['motto'] ?? 'Mencerdaskan Kehidupan Bangsa, Membangun Karakter Mulia') ?>"
        </p>
        <div class="flex flex-col sm:flex-row flex-wrap gap-4 sm:gap-6 justify-center items-center">
            <a href="<?= base_url('portal/ppdb/home') ?>"
               class="group inline-flex items-center gap-4 px-10 py-5 bg-white text-theme-primary font-black text-lg uppercase tracking-widest rounded-2xl shadow-2xl hover:shadow-theme-primary/30 hover:bg-theme-primary hover:text-white transition-all duration-300 active:scale-95 w-full sm:w-auto justify-center">
                <i class="fas fa-user-plus text-2xl group-hover:scale-110 transition-transform"></i>
                Daftar PPDB Online
            </a>
            <a href="#berita"
               class="inline-flex items-center gap-3 px-10 py-5 bg-white/10 backdrop-blur-md border-2 border-white/30 text-white font-bold text-lg uppercase tracking-widest rounded-2xl hover:bg-white/20 transition-all duration-300 w-full sm:w-auto justify-center">
                <i class="fas fa-newspaper"></i>
                Kabar Terbaru
            </a>
            <a href="<?= base_url('portal/affiliated/home') ?>"
               class="inline-flex items-center gap-3 px-10 py-5 bg-theme-secondary/20 backdrop-blur-md border-2 border-theme-secondary/50 text-white font-bold text-lg uppercase tracking-widest rounded-2xl hover:bg-theme-secondary hover:border-theme-secondary transition-all duration-300 active:scale-95 w-full sm:w-auto justify-center">
                <i class="fas fa-handshake"></i>
                Mitra Afiliasi
            </a>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-20 relative z-10 bg-white dark:bg-slate-900 mt-[-50px] rounded-t-[3rem] shadow-2xl">
    <!-- TENTANG KAMI -->
    <section id="profil" class="mb-32 pt-10 scroll-mt-24">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight section-line">
                Tentang Kami
            </h2>
        </div>

        <!-- Sejarah -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-10 md:p-16 mb-12 border border-gray-100 dark:border-white/10 shadow-lg">
            <div class="flex flex-col md:flex-row items-center gap-5 mb-10 text-center md:text-left">
                <div class="w-14 h-14 rounded-2xl bg-theme-primary/10 flex items-center justify-center text-theme-primary text-2xl shrink-0">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white">
                    Sejarah Unit <?= esc(strtoupper($jenjang ?? 'SD')) ?>
                </h3>
            </div>
            <div class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed columns-1 md:columns-2 gap-12 text-justify">
                <?= nl2br(esc($settings['sejarah'] ?? 'Kami berkomitmen penuh dalam membangun fondasi generasi bangsa yang unggul sejak usia dini, berfokus pada keseimbangan ilmu pengetahuan dan akhlak mulia.')) ?>
            </div>
        </div>

        <!-- Visi & Misi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border-t-8 border-theme-primary shadow-lg card-hover">
                <div class="text-theme-primary text-4xl mb-8 text-center md:text-left">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6 text-center md:text-left">Visi Kami</h3>
                <p class="text-gray-600 dark:text-gray-400 text-lg leading-loose italic font-medium text-center md:text-left">
                    "<?= nl2br(esc($settings['visi'] ?? 'Menjadi lembaga pendidikan yang unggul dalam mencetak generasi cerdas, berkarakter, dan berwawasan global.')) ?>"
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border-t-8 border-theme-secondary shadow-lg card-hover">
                <div class="text-theme-secondary text-4xl mb-8 text-center md:text-left">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-6 text-center md:text-left">Misi Kami</h3>
                <div class="text-gray-600 dark:text-gray-400 text-lg leading-relaxed space-y-4 font-medium text-center md:text-left">
                    <?= nl2br(esc($settings['misi'] ?? "- Menyelenggarakan pendidikan berkualitas berbasis karakter.\n- Mengembangkan potensi peserta didik secara optimal.\n- Menumbuhkan jiwa kepemimpinan dan kemandirian.")) ?>
                </div>
            </div>
        </div>
    </section>

    <!-- BERITA TERKINI -->
    <section id="berita" class="mb-32 scroll-mt-24">
        <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 gap-6 text-center md:text-left">
            <div>
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                    Berita & Informasi
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-3 font-medium">
                    Kabar terkini seputar kegiatan akademik dan siswa di Unit <?= esc(strtoupper($jenjang ?? 'SD')) ?>
                </p>
            </div>
            <a href="<?= base_url(strtolower($jenjang ?? 'sd') . '/berita') ?>"
               class="group inline-flex items-center gap-3 text-theme-primary font-black text-lg uppercase tracking-wider hover:gap-5 transition-all">
                Lihat Semua
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!empty($berita)): ?>
                <?php foreach (array_slice($berita, 0, 6) as $item): ?>
                    <article class="group bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-lg card-hover border border-gray-100 dark:border-white/10 flex flex-col h-full">
                        <div class="relative h-64 overflow-hidden flex-shrink-0">
                            <?php if (!empty($item['gambar'])): ?>
                                <img src="<?= base_url('uploads/berita/' . $item['gambar']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                     alt="<?= esc($item['judul']) ?>"
                                     onerror="this.src='https://source.unsplash.com/800x600/?school,education';">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                    <i class="fas fa-image text-5xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <span class="absolute top-4 left-4 px-4 py-2 bg-white/90 backdrop-blur text-xs font-black uppercase rounded-full text-gray-900 shadow-sm">
                                Artikel
                            </span>
                        </div>
                        <div class="p-8 flex flex-col flex-grow">
                            <!-- MENAMPILKAN TANGGAL & PENULIS -->
                            <div class="flex items-center gap-3 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">
                                <span class="flex items-center gap-1">
                                    <i class="far fa-calendar-alt text-theme-primary"></i>
                                    <?= date('d M Y', strtotime($item['created_at'])) ?>
                                </span>
                                <span>&bull;</span>
                                <span class="flex items-center gap-1">
                                    <i class="far fa-user text-theme-primary"></i>
                                    <?= esc($item['author_fullname'] ?? $item['penulis'] ?? $item['author_name'] ?? 'Admin') ?>
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-4 line-clamp-2 leading-tight">
                                <?= esc($item['judul']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-3 flex-grow leading-relaxed">
                                <?= strip_tags($item['konten']) ?>
                            </p>
                            <a href="<?= base_url(strtolower($jenjang ?? 'sd') . '/berita/' . ($item['slug'] ?? '')) ?>"
                               class="inline-flex items-center justify-center w-full py-3.5 bg-gray-50 dark:bg-gray-700/50 hover:bg-theme-primary hover:text-white text-gray-800 dark:text-white font-black text-sm uppercase tracking-widest rounded-xl transition-all mt-auto group/btn">
                                Baca Selengkapnya
                                <i class="fas fa-arrow-right ml-2 transform group-hover/btn:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800/50 rounded-3xl border-2 border-dashed border-gray-200 dark:border-white/10">
                    <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <i class="fas fa-newspaper text-3xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-bold text-lg">
                        Belum ada berita terbaru untuk unit ini.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PENGUMUMAN & AGENDA -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-12 mb-32 scroll-mt-24" id="informasi">
        <!-- Pengumuman -->
        <div class="lg:col-span-7 space-y-6">
            <div class="flex flex-col md:flex-row items-center gap-4 mb-10 text-center md:text-left">
                <div class="w-12 h-12 bg-theme-primary/10 text-theme-primary rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tight text-gray-900 dark:text-white">Pengumuman Penting</h2>
            </div>
            <?php if (!empty($pengumuman)): ?>
                <div class="space-y-6">
                    <?php foreach ($pengumuman as $p): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border-l-8 border-theme-primary shadow-lg transform transition hover:-translate-y-1">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-4 leading-tight">
                                <?= esc($p['judul']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                                <?= esc(strip_tags($p['konten'])) ?>
                            </p>
                            <div class="flex items-center justify-center md:justify-start gap-2 text-xs font-bold uppercase tracking-widest text-theme-primary bg-theme-primary/5 w-fit px-3 py-1.5 rounded-lg mx-auto md:mx-0">
                                <i class="far fa-clock"></i>
                                <?= date('d F Y', strtotime($p['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-16 bg-gray-50 dark:bg-gray-800/50 rounded-3xl text-center border-2 border-dashed border-gray-200 dark:border-white/10">
                    <i class="fas fa-volume-mute text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada pengumuman aktif saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Agenda -->
        <div class="lg:col-span-5">
            <div class="flex flex-col md:flex-row items-center gap-4 mb-10 text-center md:text-left">
                <div class="w-12 h-12 bg-theme-secondary/10 text-theme-secondary rounded-xl flex items-center justify-center shrink-0">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                </div>
                <h2 class="text-3xl font-black uppercase tracking-tight text-gray-900 dark:text-white">Agenda Mendatang</h2>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-white/10 overflow-hidden">
                <?php if (!empty($agenda)): ?>
                    <?php foreach ($agenda as $a): ?>
                        <div class="flex flex-col md:flex-row items-center p-6 border-b border-gray-50 dark:border-white/5 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group text-center md:text-left">
                            <div class="flex flex-col items-center justify-center w-20 h-20 bg-theme-secondary/5 group-hover:bg-theme-secondary/10 rounded-2xl border border-theme-secondary/20 text-theme-secondary md:mr-6 mb-4 md:mb-0 flex-shrink-0 transition-colors">
                                <span class="text-xs font-black uppercase"><?= date('M', strtotime($a['tanggal_mulai'])) ?></span>
                                <span class="text-3xl font-black"><?= date('d', strtotime($a['tanggal_mulai'])) ?></span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-900 dark:text-white leading-tight mb-2">
                                    <?= esc($a['nama_kegiatan']) ?>
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center justify-center md:justify-start gap-2">
                                    <i class="fas fa-map-marker-alt text-theme-secondary opacity-70"></i>
                                    <?= esc($a['tempat'] ?: 'Lingkungan Sekolah') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-16 text-center text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800/50">
                        <i class="fas fa-calendar-times text-5xl mb-4"></i>
                        <p class="font-medium">Belum ada agenda terdaftar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    <section id="galeri" class="py-20 border-t border-gray-100 dark:border-slate-800 scroll-mt-24">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight section-line">
                Galeri Dokumentasi
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            <?php if (!empty($albums)): ?>
                <?php foreach (array_slice($albums, 0, 12) as $alb): ?>
                    <a href="<?= base_url('album/' . ($alb['slug'] ?? '')) ?>"
                       class="group relative aspect-square rounded-2xl overflow-hidden shadow-md card-hover block border border-gray-100 dark:border-slate-700">
                        <?php if (!empty($alb['cover'])): ?>
                            <img src="<?= base_url('uploads/cms/album/cover/' . $alb['cover']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                 alt="<?= esc($alb['judul']) ?>"
                                 onerror="this.src='https://source.unsplash.com/400x400/?school,students';">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                <i class="fas fa-images text-3xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                            <span class="text-[9px] font-bold text-theme-primary uppercase tracking-widest mb-1">Album</span>
                            <h3 class="text-white font-black text-xs uppercase tracking-wider line-clamp-2 leading-tight">
                                <?= esc($alb['judul']) ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800/50 rounded-3xl border-2 border-dashed border-gray-200 dark:border-white/10">
                    <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <i class="fas fa-images text-3xl text-gray-300 dark:text-gray-500"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-bold text-lg">
                        Album dokumentasi belum tersedia.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- FOOTER -->
<footer class="bg-slate-950 text-white pt-24 pb-12 relative overflow-hidden mt-20">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-theme-primary/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-theme-secondary/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-16 mb-16">
            
            <div class="md:col-span-5 text-center md:text-left">
                <h3 class="text-3xl font-black text-theme-primary mb-2 tracking-tighter uppercase flex items-center justify-center md:justify-start gap-3">
                    <i class="fas fa-school text-2xl"></i> UNIT <?= esc(strtoupper($jenjang ?? 'SD')) ?>
                </h3>
                <h4 class="text-lg font-bold text-white mb-6"><?= esc($unit_name ?? 'Sekolah Terpadu Nusantara') ?></h4>
                <p class="text-gray-400 leading-relaxed font-medium mb-8">
                    <?= esc($settings['motto'] ?? 'Mencerdaskan Kehidupan Bangsa Sejak Usia Dini') ?>
                </p>
                <div class="flex gap-4 justify-center md:justify-start">
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 hover:bg-theme-primary hover:border-theme-primary flex items-center justify-center transition-all">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 hover:bg-pink-600 hover:border-pink-600 flex items-center justify-center transition-all">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/5 border border-white/10 hover:bg-red-600 hover:border-red-600 flex items-center justify-center transition-all">
                        <i class="fab fa-youtube text-lg"></i>
                    </a>
                </div>
            </div>

            <div class="md:col-span-3 text-center md:text-left">
                <h4 class="text-sm font-black uppercase tracking-widest text-white/50 mb-8 border-b border-white/10 pb-4">Navigasi Cepat</h4>
                <ul class="space-y-4 text-gray-300">
                    <li><a href="#profil" class="hover:text-theme-primary transition-colors font-medium flex items-center justify-center md:justify-start gap-2"><i class="fas fa-angle-right text-[10px] opacity-50"></i> Profil Unit</a></li>
                    <li><a href="#berita" class="hover:text-theme-primary transition-colors font-medium flex items-center justify-center md:justify-start gap-2"><i class="fas fa-angle-right text-[10px] opacity-50"></i> Berita & Kabar</a></li>
                    <li><a href="#galeri" class="hover:text-theme-primary transition-colors font-medium flex items-center justify-center md:justify-start gap-2"><i class="fas fa-angle-right text-[10px] opacity-50"></i> Galeri Dokumentasi</a></li>
                    <li><a href="<?= base_url('portal/ppdb/home') ?>" class="hover:text-theme-primary transition-colors font-bold text-white flex items-center justify-center md:justify-start gap-2"><i class="fas fa-user-plus text-[10px] text-theme-primary"></i> Pendaftaran PPDB</a></li>
                </ul>
            </div>

            <div class="md:col-span-4 text-center md:text-left">
                <h4 class="text-sm font-black uppercase tracking-widest text-white/50 mb-8 border-b border-white/10 pb-4">Hubungi Kami</h4>
                <div class="space-y-6 text-gray-300 font-medium">
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center shrink-0 text-theme-primary">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <p class="leading-relaxed mt-2 md:mt-1 text-sm"><?= esc($settings['alamat'] ?? 'Jl. Pendidikan No. 1, Kota Impian') ?></p>
                    </div>
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center shrink-0 text-theme-primary">
                            <i class="fas fa-phone"></i>
                        </div>
                        <p class="mt-2 md:mt-0 text-sm"><?= esc($settings['telepon'] ?? '(021) 1234567') ?></p>
                    </div>
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center shrink-0 text-theme-primary">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <p class="mt-2 md:mt-0 text-sm"><?= esc($settings['email'] ?? 'info@nusantara.sch.id') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold uppercase tracking-widest">
            <p class="text-gray-500">&copy; <?= date('Y') ?> <?= esc($unit_name ?? 'Sekolah Terpadu Nusantara') ?></p>
            <div class="flex items-center gap-2 opacity-60">
                <span class="text-gray-500">Powered By</span>
                <span class="text-white tracking-tighter">Nusantara<span class="text-theme-primary">ERP</span></span>
            </div>
        </div>
    </div>
</footer>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
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