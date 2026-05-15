<!-- HOTFIX: Load FontAwesome via CDN langsung di sini untuk memastikan ikon muncul -->
<!-- Jika file lokal (assets/vendor/...) bermasalah, baris ini akan menyelamatkannya -->
<link href="<?= base_url('assets/fontawesome/css/all.min.css'); ?>" rel="stylesheet">

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-[#0f172a] border-r border-slate-800 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out lg:static lg:block flex-shrink-0 shadow-2xl">
    <div class="h-full flex flex-col">
        
        <!-- 1. BRAND HEADER -->
        <div class="h-24 flex items-center px-8 border-b border-slate-800/60 bg-slate-900/50 backdrop-blur-md relative overflow-hidden">
            <!-- Glow Effect -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-transparent opacity-50"></div>
            
            <div class="flex items-center gap-4 w-full relative z-10">
                <div class="w-11 h-11 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/30 text-white border border-white/10 group">
                    <?php if(!empty($sekolah->logo) && file_exists(FCPATH . 'uploads/logo/' . $sekolah->logo)): ?>
                        <img src="<?= base_url('uploads/logo/' . $sekolah->logo) ?>" class="w-full h-full object-contain p-1.5 group-hover:scale-110 transition-transform" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-chalkboard-teacher text-xl group-hover:scale-110 transition-transform"></i>
                    <?php endif; ?>
                </div>
                <div class="overflow-hidden">
                    <span class="block text-white font-black text-xl tracking-tight leading-none font-sans">PORTAL</span>
                    <span class="block text-emerald-400 text-[11px] font-bold tracking-[0.2em] uppercase mt-0.5">Pegawai</span>
                </div>
            </div>
        </div>

        <!-- 2. PROFILE BRIEF CARD -->
        <div class="px-6 py-6">
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-r from-slate-800 to-slate-800/50 border border-slate-700/50 relative overflow-hidden group hover:border-emerald-500/30 transition-colors duration-300">
                <!-- Background Decor -->
                <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>

                <!-- Foto Profil -->
                <div class="shrink-0 relative">
                    <?php 
                    $fotoPath = 'uploads/pegawai/' . ($pegawai['foto'] ?? '');
                    if(!empty($pegawai['foto']) && file_exists(FCPATH . $fotoPath)): 
                    ?>
                        <img src="<?= base_url($fotoPath) ?>" class="w-12 h-12 rounded-full border-2 border-slate-600 shadow-md object-cover group-hover:border-emerald-500 transition-colors" alt="Foto Profil">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($pegawai['nama_lengkap'] ?? 'Pegawai') ?>&background=10b981&color=fff&bold=true" 
                             class="w-12 h-12 rounded-full border-2 border-slate-600 shadow-md group-hover:border-emerald-500 transition-colors" alt="Avatar">
                    <?php endif; ?>
                    <!-- Status Indicator -->
                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-800 rounded-full" title="Online"></span>
                </div>

                <!-- Info User -->
                <div class="overflow-hidden w-full relative z-10">
                    <h4 class="text-sm font-bold text-white truncate group-hover:text-emerald-300 transition-colors" title="<?= esc($pegawai['nama_lengkap'] ?? '-') ?>">
                        <?= esc($pegawai['nama_lengkap'] ?? 'Pegawai') ?>
                    </h4>
                    
                    <?php $nomorInduk = !empty($pegawai['nip']) ? $pegawai['nip'] : ($pegawai['nipy'] ?? '-'); ?>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="px-1.5 py-0.5 rounded-md bg-slate-700/50 border border-slate-600 text-[10px] font-mono text-slate-300 truncate">
                            <?= esc($nomorInduk) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. NAVIGATION MENU -->
        <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto custom-scrollbar pb-6">
            <?php 
                $uri = service('uri'); 
                $segment = $uri->getSegment(3); 

                // Helper function untuk styling menu
                function menuClass($active, $theme = 'emerald') {
                    $base = "flex items-center gap-3.5 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden ";
                    if ($active) {
                        return $base . "bg-gradient-to-r from-{$theme}-600 to-{$theme}-700 text-white shadow-lg shadow-{$theme}-900/40 ring-1 ring-{$theme}-500/50";
                    }
                    return $base . "text-slate-400 hover:text-white hover:bg-slate-800/80";
                }

                $isGuru = (isset($pegawai['jenis_pegawai']) && strtolower($pegawai['jenis_pegawai']) === 'guru') ||
                          (isset($pegawai['jenis_ptk']) && str_contains(strtolower($pegawai['jenis_ptk'] ?? ''), 'guru'));
            ?>

            <!-- SECTION: UTAMA -->
            <div class="px-4 mt-2 mb-2 flex items-center gap-2">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Utama</span>
                <div class="h-px bg-slate-800 flex-1"></div>
            </div>

            <a href="<?= base_url('portal/pegawai/dashboard') ?>" class="<?= menuClass($segment == 'dashboard', 'emerald') ?>">
                <!-- FIX: Menggunakan fa-home (standar v5 free) -->
                <i class="fas fa-home w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                <span>Dashboard</span>
                <?php if($segment == 'dashboard'): ?>
                    <i class="fas fa-chevron-right ml-auto text-[10px] opacity-70"></i>
                <?php endif; ?>
            </a>
            
            <!-- SECTION: AKADEMIK (GURU ONLY) -->
            <?php if ($isGuru): ?>
                <div class="px-4 mt-6 mb-2 flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Akademik</span>
                    <div class="h-px bg-slate-800 flex-1"></div>
                </div>
                
                <a href="<?= base_url('portal/pegawai/jadwal') ?>" class="<?= menuClass($segment == 'jadwal', 'indigo') ?>">
                    <i class="fas fa-calendar-alt w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                    <span>Jadwal Mengajar</span>
                </a>
                <a href="<?= base_url('portal/pegawai/nilai') ?>" class="<?= menuClass($segment == 'nilai', 'indigo') ?>">
                    <!-- FIX: Menggunakan fa-pen (standar v5 free) -->
                    <i class="fas fa-pen w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                    <span>Input Nilai</span>
                </a>
                <a href="<?= base_url('portal/pegawai/siswa') ?>" class="<?= menuClass($segment == 'siswa', 'indigo') ?>">
                    <!-- FIX: Menggunakan fa-user-graduate (standar v5 free, bukan fa-users-class) -->
                    <i class="fas fa-user-graduate w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                    <span>Data Siswa</span>
                </a>
            <?php endif; ?>

            <!-- SECTION: KEPEGAWAIAN -->
            <div class="px-4 mt-6 mb-2 flex items-center gap-2">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Kepegawaian</span>
                <div class="h-px bg-slate-800 flex-1"></div>
            </div>

            <a href="<?= base_url('portal/pegawai/presensi') ?>" class="<?= menuClass($segment == 'presensi', 'amber') ?>">
                <!-- FIX: Menggunakan fa-clock (standar v5 free, fingerprint kadang pro) -->
                <i class="fas fa-clock w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                <span>Riwayat Presensi</span>
            </a>
            <a href="<?= base_url('portal/pegawai/keuangan') ?>" class="<?= menuClass($segment == 'keuangan', 'amber') ?>">
                <!-- FIX: Menggunakan fa-file-invoice-dollar (standar v5 free) -->
                <i class="fas fa-file-invoice-dollar w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                <span>Slip Gaji & Honor</span>
            </a>
            <a href="<?= base_url('portal/pegawai/profil') ?>" class="<?= menuClass($segment == 'profil', 'rose') ?>">
                <!-- FIX: Menggunakan fa-user-circle (standar v5 free) -->
                <i class="fas fa-user-circle w-5 text-center transition-transform group-hover:-translate-y-0.5"></i> 
                <span>Profil & Akun</span>
            </a>
        </nav>

        <!-- 4. FOOTER LOGOUT -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/30">
            <a href="<?= base_url('portal/pegawai/logout') ?>" class="flex items-center justify-between w-full px-4 py-3 text-xs font-bold text-rose-400 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-600 hover:text-white hover:border-rose-600 rounded-xl transition-all duration-300 group">
                <span class="flex items-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </span>
                <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity -translate-x-2 group-hover:translate-x-0"></i>
            </a>
        </div>
    </div>
</aside>