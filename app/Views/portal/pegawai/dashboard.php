<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard Pegawai') ?></title>
    
    <!-- Reuse Assets dari Portal Siswa -->
    <?= view('portal/siswa/partials/script'); ?> 
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar Mobile -->
    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Sidebar Pegawai -->
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Security Alert -->
                <?php if(session()->getFlashdata('security_alert')): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start gap-3 animate-fade-in-down mb-6">
                        <i class="fas fa-shield-halved text-amber-500 mt-0.5 text-lg"></i>
                        <div>
                            <h4 class="text-sm font-bold text-amber-800">Pemberitahuan Keamanan</h4>
                            <p class="text-xs text-amber-700 mt-1"><?= session()->getFlashdata('security_alert') ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Welcome Banner (Emerald Theme) -->
                <div class="relative bg-emerald-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-emerald-600/20">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        
                        <!-- Kiri: Foto & Teks -->
                        <div class="flex items-center gap-6 w-full">
                            <!-- (2) FOTO PROFIL -->
                            <div class="hidden md:block shrink-0">
                                <?php if(!empty($pegawai['foto']) && file_exists('uploads/pegawai/'.$pegawai['foto'])): ?>
                                    <img src="<?= base_url('uploads/pegawai/'.$pegawai['foto']) ?>" class="w-20 h-20 rounded-full border-4 border-white/30 shadow-md object-cover">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($pegawai['nama_lengkap']) ?>&background=047857&color=fff&size=128" 
                                         class="w-20 h-20 rounded-full border-4 border-white/30 shadow-md">
                                <?php endif; ?>
                            </div>

                            <div>
                                <!-- (1) LABEL TAHUN AJARAN -->
                                <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-2 backdrop-blur-sm border border-white/10">
                                    TAHUN AJARAN: <?= esc($tahun_ajaran_label ?? 'Aktif') ?>
                                </span>
                                
                                <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-1">
                                    Halo, <?= esc(strtok($pegawai['nama_lengkap'] ?? 'Bapak/Ibu', " ")) ?>! 👋
                                </h1>
                                <p class="text-emerald-100 text-sm md:text-base max-w-lg leading-relaxed">
                                    Selamat bertugas. Semoga hari ini produktif dan menyenangkan.
                                </p>
                            </div>
                        </div>

                        <!-- Kanan: Jam -->
                        <div class="text-white text-right hidden lg:block shrink-0">
                            <div class="text-4xl font-black tracking-tight"><?= date('H:i') ?></div>
                            <div class="text-emerald-200 text-sm font-medium uppercase tracking-wide"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid (Presensi Bulanan) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Hadir -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-emerald-300 transition-colors">
                        <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Hadir Bulan Ini</p>
                            <h4 class="text-2xl font-black text-slate-800"><?= $presensi['hadir'] ?? 0 ?> <span class="text-sm font-normal text-slate-400">Hari</span></h4>
                        </div>
                    </div>
                    <!-- Izin -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-amber-300 transition-colors">
                        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Izin / Cuti</p>
                            <h4 class="text-2xl font-black text-slate-800"><?= $presensi['izin'] ?? 0 ?> <span class="text-sm font-normal text-slate-400">Hari</span></h4>
                        </div>
                    </div>
                    <!-- Sakit/Alpha -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4 hover:border-rose-300 transition-colors">
                        <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-xl shadow-sm">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Sakit / Alpha</p>
                            <h4 class="text-2xl font-black text-slate-800"><?= ($presensi['sakit'] ?? 0) + ($presensi['alpha'] ?? 0) ?> <span class="text-sm font-normal text-slate-400">Hari</span></h4>
                        </div>
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Jadwal Mengajar (Kiri) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-chalkboard-teacher text-emerald-500"></i> Jadwal Mengajar Hari Ini
                                </h3>
                                <span class="text-xs font-bold bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md border border-emerald-100"><?= $hari_ini ?></span>
                            </div>

                            <?php if(empty($jadwal_hari_ini)): ?>
                                <div class="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-mug-hot text-2xl text-slate-400"></i>
                                    </div>
                                    <p class="text-slate-600 font-bold">Tidak ada jadwal mengajar.</p>
                                    <p class="text-slate-400 text-xs mt-1 max-w-xs mx-auto">Anda tidak memiliki jam mengajar hari ini.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach($jadwal_hari_ini as $jadwal): ?>
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-emerald-200 hover:bg-white hover:shadow-sm transition-all group">
                                            <div class="flex items-center gap-4 mb-3 sm:mb-0">
                                                <div class="text-center w-14 shrink-0">
                                                    <div class="text-[10px] font-bold text-slate-400 uppercase">MULAI</div>
                                                    <div class="text-base font-black text-emerald-600"><?= date('H:i', strtotime($jadwal['jam_mulai'])) ?></div>
                                                </div>
                                                <div class="h-8 w-[2px] bg-slate-200 hidden sm:block"></div>
                                                <div>
                                                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-700 transition-colors">
                                                        <?= esc($jadwal['nama_mapel']) ?>
                                                    </h4>
                                                    <p class="text-xs text-slate-500 mt-0.5">
                                                        Kelas: <span class="font-bold text-slate-700 bg-slate-200 px-1.5 rounded"><?= esc($jadwal['nama_kelas']) ?></span>
                                                        <?php if(!empty($jadwal['nama_grup'])): ?>
                                                            <span class="text-slate-400 mx-1">•</span> <?= esc($jadwal['nama_grup']) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right pl-14 sm:pl-0">
                                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 shadow-sm">
                                                    <i class="fas fa-door-open text-slate-400"></i>
                                                    <?= esc($jadwal['ruangan'] ?? '-') ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar Kanan (Pengumuman) -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-bullhorn text-amber-500"></i> Pengumuman
                                </h3>
                                <a href="#" class="text-xs font-bold text-slate-400 hover:text-emerald-600">Arsip</a>
                            </div>
                            
                            <?php if(empty($pengumuman)): ?>
                                <div class="p-6 text-center bg-slate-50 rounded-xl">
                                    <p class="text-sm text-slate-400 italic">Tidak ada pengumuman terbaru.</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach($pengumuman as $info): ?>
                                        <div class="group cursor-pointer">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                    <?= date('d M Y', strtotime($info['created_at'] ?? 'now')) ?>
                                                </span>
                                            </div>
                                            <h4 class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition-colors line-clamp-1">
                                                <?= esc($info['judul'] ?? 'Info Akademik') ?>
                                            </h4>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                                <?= esc($info['isi'] ?? 'Silakan cek detail pengumuman ini.') ?>
                                            </p>
                                            <div class="h-px bg-slate-100 mt-3 group-last:hidden"></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Script Handling -->
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        if(btn && sidebar) {
            btn.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); });
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !btn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    </script>
</body>
</html>