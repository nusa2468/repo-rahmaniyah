<?= $this->extend('layout/public_layout') ?>

<?= $this->section('header') ?>
<style type="text/tailwindcss">
    @theme {
        --color-theme-primary: <?= $theme_color ?? '#e11d48' ?>; /* Default Rose YPTP jika null */
        --color-theme-secondary: <?= match(strtoupper($jenjang)) {
            'TK' => '#ec4899',   /* Pink */
            'SD' => '#f59e0b',   /* Amber */
            'SMA' => '#3b82f6',  /* Blue */
            default => '#e11d48' /* Rose (Maron YPTP) */
        } ?>;
    }

    .hero-gradient {
        background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0.85)),
                    url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1740&auto=format&fit=crop');
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

<!-- HERO SECTION - Super Solid & Compact -->
<header class="relative min-h-screen flex items-center justify-center hero-gradient">
    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-black/40"></div>
    <div class="relative z-10 max-w-6xl mx-auto px-6 text-center">
        <span class="inline-block px-5 py-2 rounded-full bg-theme-primary text-white text-xs font-black tracking-widest uppercase mb-8 shadow-lg">
            UNIT <?= esc(strtoupper($jenjang)) ?>
        </span>
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight drop-shadow-2xl leading-tight">
            <?= esc($unit_name ?? 'Sekolah Terpadu Ekasakti') ?>
        </h1>
        <p class="text-xl md:text-3xl text-white/90 font-medium italic max-w-4xl mx-auto mb-12 leading-relaxed">
            "<?= esc($settings['motto'] ?? 'Mencerdaskan Kehidupan Bangsa, Membangun Karakter Mulia') ?>"
        </p>
        <div class="flex flex-col sm:flex-row flex-wrap gap-4 sm:gap-6 justify-center items-center">
            <a href="<?= base_url('portal/ppdb/home') ?>"
               class="group inline-flex items-center gap-4 px-10 py-5 bg-white text-theme-primary font-black text-lg uppercase tracking-widest rounded-2xl shadow-2xl hover:shadow-theme-primary/30 hover:bg-theme-primary hover:text-white transition-all duration-300 active:scale-95">
                <i class="fas fa-user-plus text-2xl group-hover:scale-110 transition-transform"></i>
                Daftar PPDB Online
            </a>
            <a href="#berita"
               class="inline-flex items-center gap-3 px-10 py-5 bg-white/10 backdrop-blur-md border-2 border-white/30 text-white font-bold text-lg uppercase tracking-widest rounded-2xl hover:bg-white/20 transition-all duration-300">
                <i class="fas fa-newspaper"></i>
                Kabar Terbaru
            </a>
            <a href="<?= base_url('portal/affiliated/home') ?>"
               class="inline-flex items-center gap-3 px-10 py-5 bg-amber-500/20 backdrop-blur-md border-2 border-amber-400/50 text-amber-50 font-bold text-lg uppercase tracking-widest rounded-2xl hover:bg-amber-500/40 hover:border-amber-400 hover:text-white transition-all duration-300 active:scale-95">
                <i class="fas fa-handshake"></i>
                Mitra Afiliasi
            </a>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-20">
    <!-- TENTANG KAMI -->
    <section id="profil" class="mb-32">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight section-line">
                Tentang Kami
            </h2>
        </div>

        <!-- Sejarah -->
        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-10 md:p-16 mb-12 border border-gray-100 dark:border-white/10 shadow-lg">
            <div class="flex items-center gap-5 mb-10">
                <div class="w-14 h-14 rounded-2xl bg-theme-primary/10 flex items-center justify-center text-theme-primary text-2xl">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="text-3xl font-black text-gray-900 dark:text-white">
                    Sejarah Unit <?= esc(strtoupper($jenjang)) ?>
                </h3>
            </div>
            <div class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed columns-1 md:columns-2 gap-12 text-justify">
                <?= nl2br(esc($settings['sejarah'] ?? 'Sekolah Terpadu Ekasakti di bawah naungan Yayasan Perguruan Tinggi Padang (YPTP) berkomitmen penuh dalam membangun fondasi generasi bangsa yang unggul sejak usia dini.')) ?>
            </div>
        </div>

        <!-- Visi & Misi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border-t-8 border-theme-primary shadow-lg card-hover">
                <div class="text-theme-primary text-4xl mb-8">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="text-2xl font-black mb-6">Visi Kami</h3>
                <p class="text-gray-600 dark:text-gray-400 text-lg leading-loose italic">
                    "<?= nl2br(esc($settings['visi'] ?? 'Menjadi lembaga pendidikan yang unggul dalam mencetak generasi cerdas, berkarakter, dan berwawasan global.')) ?>"
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 border-t-8 border-theme-secondary shadow-lg card-hover">
                <div class="text-theme-secondary text-4xl mb-8">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="text-2xl font-black mb-6">Misi Kami</h3>
                <div class="text-gray-600 dark:text-gray-400 text-lg leading-relaxed space-y-4">
                    <?= nl2br(esc($settings['misi'] ?? "- Menyelenggarakan pendidikan berkualitas berbasis karakter.\n- Mengembangkan potensi peserta didik secara optimal.\n- Menumbuhkan jiwa kepemimpinan dan kemandirian.")) ?>
                </div>
            </div>
        </div>
    </section>

    <!-- BERITA TERKINI -->
    <section id="berita" class="mb-32">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div>
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                    Berita & Informasi
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-3">
                    Kabar terkini seputar kegiatan akademik dan siswa di Unit <?= esc(strtoupper($jenjang)) ?>
                </p>
            </div>
            <a href="<?= base_url(strtolower($jenjang) . '/berita') ?>"
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
                                     alt="<?= esc($item['judul']) ?>">
                            <?php else: ?>
                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                    <i class="fas fa-image text-5xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                            <span class="absolute top-4 left-4 px-4 py-2 bg-white/90 backdrop-blur text-xs font-black uppercase rounded-full text-gray-900">
                                Artikel
                            </span>
                        </div>
                        <div class="p-8 flex flex-col flex-grow">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-4 line-clamp-2">
                                <?= esc($item['judul']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-3 flex-grow">
                                <?= strip_tags($item['konten']) ?>
                            </p>
                            <a href="<?= base_url(strtolower($jenjang) . '/berita/' . $item['slug']) ?>"
                               class="inline-flex items-center justify-center w-full py-3.5 bg-gray-100 dark:bg-gray-700 hover:bg-theme-primary hover:text-white text-gray-800 dark:text-white font-black text-sm uppercase tracking-widest rounded-xl transition-all mt-auto">
                                Baca Selengkapnya
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-white/10">
                    <i class="fas fa-newspaper text-6xl text-gray-300 dark:text-gray-600 mb-6"></i>
                    <p class="text-gray-500 dark:text-gray-400 font-medium italic text-lg">
                        Belum ada berita terbaru untuk unit ini.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PENGUMUMAN & AGENDA -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-12 mb-32">
        <!-- Pengumuman -->
        <div class="lg:col-span-7 space-y-6">
            <div class="flex items-center gap-4 mb-10">
                <i class="fas fa-bullhorn text-3xl text-theme-primary"></i>
                <h2 class="text-3xl font-black uppercase tracking-tight">Pengumuman Penting</h2>
            </div>
            <?php if (!empty($pengumuman)): ?>
                <div class="space-y-6">
                    <?php foreach ($pengumuman as $p): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border-l-6 border-theme-primary shadow-lg transform transition hover:-translate-y-1">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-4">
                                <?= esc($p['judul']) ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                                <?= esc(strip_tags($p['konten'])) ?>
                            </p>
                            <p class="text-xs font-bold uppercase tracking-widest text-theme-primary flex items-center gap-2">
                                <i class="far fa-clock"></i>
                                <?= date('d F Y', strtotime($p['created_at'])) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-16 bg-gray-50 dark:bg-gray-800 rounded-3xl text-center border-2 border-dashed border-gray-200 dark:border-white/10">
                    <i class="fas fa-volume-mute text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400 italic">Tidak ada pengumuman aktif saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Agenda -->
        <div class="lg:col-span-5">
            <div class="flex items-center gap-4 mb-10">
                <i class="fas fa-calendar-alt text-3xl text-theme-secondary"></i>
                <h2 class="text-3xl font-black uppercase tracking-tight">Agenda Mendatang</h2>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg border border-gray-100 dark:border-white/10 overflow-hidden">
                <?php if (!empty($agenda)): ?>
                    <?php foreach ($agenda as $a): ?>
                        <div class="flex items-center p-6 border-b border-gray-100 dark:border-white/5 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex flex-col items-center justify-center w-20 h-20 bg-theme-secondary/10 rounded-2xl border-2 border-theme-secondary/30 text-theme-secondary mr-6 flex-shrink-0">
                                <span class="text-xs font-black uppercase"><?= date('M', strtotime($a['tanggal_mulai'])) ?></span>
                                <span class="text-3xl font-black"><?= date('d', strtotime($a['tanggal_mulai'])) ?></span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-900 dark:text-white leading-tight">
                                    <?= esc($a['nama_kegiatan']) ?>
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    <?= esc($a['tempat'] ?: 'Lingkungan Sekolah') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-16 text-center text-gray-400">
                        <i class="fas fa-calendar-times text-5xl mb-4"></i>
                        <p class="italic">Belum ada agenda terdaftar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GALERI -->
    <section id="galeri" class="py-20">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white uppercase tracking-tight section-line">
                Galeri Dokumentasi
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <?php if (!empty($albums)): ?>
                <?php foreach (array_slice($albums, 0, 12) as $alb): ?>
                    <a href="<?= base_url('album/' . $alb['slug']) ?>"
                       class="group relative aspect-square rounded-2xl overflow-hidden shadow-lg card-hover block">
                        <?php if (!empty($alb['cover'])): ?>
                            <img src="<?= base_url('uploads/cms/album/cover/' . $alb['cover']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                 alt="<?= esc($alb['judul']) ?>">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                <i class="fas fa-images text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <h3 class="text-white font-black text-sm uppercase tracking-wider">
                                <?= esc($alb['judul']) ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center bg-gray-50 dark:bg-gray-800 rounded-3xl border-2 border-dashed border-gray-200 dark:border-white/10">
                    <i class="fas fa-images text-6xl text-gray-300 mb-6"></i>
                    <p class="text-gray-500 dark:text-gray-400 italic text-lg">
                        Album dokumentasi belum tersedia.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- FOOTER -->
<footer class="bg-slate-950 text-white py-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 mb-16">
            <div>
                <h3 class="text-3xl font-black text-theme-primary mb-6 tracking-tighter uppercase">
                    UNIT <?= esc($jenjang) ?>
                </h3>
                <p class="text-gray-400 leading-relaxed max-w-md">
                    <?= esc($settings['motto'] ?? 'Mencerdaskan Kehidupan Bangsa Sejak Usia Dini') ?>
                </p>
                <div class="flex gap-4 mt-8">
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/10 hover:bg-theme-primary flex items-center justify-center transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/10 hover:bg-pink-600 flex items-center justify-center transition-colors">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-white/10 hover:bg-red-600 flex items-center justify-center transition-colors">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-lg font-black uppercase tracking-widest text-white/60 mb-8">Navigasi</h4>
                <ul class="space-y-4 text-gray-300">
                    <li><a href="#profil" class="hover:text-theme-primary transition-colors font-medium">Profil Unit</a></li>
                    <li><a href="#berita" class="hover:text-theme-primary transition-colors font-medium">Berita & Kabar</a></li>
                    <li><a href="<?= base_url('portal/ppdb/home') ?>" class="hover:text-theme-primary transition-colors font-medium">PPDB Online</a></li>
                    <li><a href="<?= base_url('/') ?>" class="hover:text-theme-primary transition-colors font-medium flex items-center gap-2"><i class="fas fa-home text-xs"></i> Portal Yayasan Utama</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-black uppercase tracking-widest text-white/60 mb-8">Kontak Kami</h4>
                <div class="space-y-6 text-gray-300">
                    <div class="flex gap-4">
                        <i class="fas fa-map-marker-alt text-theme-primary mt-1"></i>
                        <p class="leading-relaxed"><?= esc($settings['alamat'] ?? 'Jl. Veteran Dalam No. 26B, Padang Pasir, Kota Padang') ?></p>
                    </div>
                    <div class="flex gap-4">
                        <i class="fas fa-phone text-theme-primary mt-1"></i>
                        <p><?= esc($settings['telepon'] ?? '(0751) 28312') ?></p>
                    </div>
                    <div class="flex gap-4">
                        <i class="fas fa-envelope text-theme-primary mt-1"></i>
                        <p><?= esc($settings['email'] ?? 'sekolah@unes.ac.id') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-12 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500 font-bold uppercase tracking-widest">
            <p>&copy; <?= date('Y') ?> <?= esc($settings['nama_sekolah'] ?? 'Sekolah Terpadu Ekasakti') ?> • Unit <?= esc(strtoupper($jenjang)) ?></p>
            <p>Powered by ICT YPTP</p>
        </div>
    </div>
</footer>

<?= $this->endSection() ?>