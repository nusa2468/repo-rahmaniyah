<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in font-sans antialiased text-slate-800">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app') ?>" class="hover:text-indigo-600 transition-colors">DASHBOARD</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li><a href="<?= base_url('app/pengaturan/pengguna') ?>" class="hover:text-indigo-600 transition-colors">PENGATURAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 tracking-widest">PROFIL SAYA</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-none uppercase italic">
                Informasi <span class="text-indigo-600 font-medium opacity-50 ml-1">Akun Profil</span>
            </h1>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- SIDE CARD: IDENTITY -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border-2 border-slate-50 dark:border-slate-800 overflow-hidden relative">
                <!-- Cover Decoration -->
                <div class="h-28 bg-gradient-to-br from-indigo-600 via-sky-500 to-emerald-400"></div>
                
                <div class="px-6 pb-8 text-center">
                    <!-- Avatar -->
                    <div class="relative inline-block -mt-14 mb-4">
                        <img src="<?= $avatar ?>" alt="User Avatar" class="w-28 h-28 rounded-[2rem] border-4 border-white dark:border-slate-900 shadow-2xl bg-white object-cover">
                        <div class="absolute bottom-1 right-1 w-6 h-6 bg-emerald-500 border-4 border-white dark:border-slate-900 rounded-full shadow-lg"></div>
                    </div>
                    
                    <h2 class="text-xl font-black text-slate-900 dark:text-white uppercase italic leading-tight"><?= esc($user['nama_lengkap']) ?></h2>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">@<?= esc($user['username']) ?></p>
                    
                    <!-- Role Badge -->
                    <div class="mt-4 inline-flex items-center px-5 py-2 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 text-[10px] font-black uppercase tracking-[0.15em] shadow-sm">
                        <i class="fas fa-id-badge mr-2 opacity-50"></i> <?= esc($user['role_name'] ?? 'PENGGUNA') ?>
                    </div>

                    <!-- Quick Stats -->
                    <div class="mt-10 grid grid-cols-1 gap-3 text-left border-t border-slate-100 dark:border-slate-800 pt-8">
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Unit Kerja</span>
                            <span class="text-[10px] font-black text-slate-700 dark:text-slate-300 px-2 py-1 rounded-lg bg-white dark:bg-slate-900 shadow-sm border border-slate-100 dark:border-slate-700"><?= esc($user['kode_jenjang']) ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Status</span>
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> AKTIF
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECURITY CARD -->
            <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-2xl text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-400 mb-4">Sandi & Keamanan</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed italic opacity-80 mb-6">Disarankan untuk mengganti kata sandi minimal 3 bulan sekali demi keamanan data sistem.</p>
                    <button class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-indigo-900/50 border border-indigo-400/20 flex items-center justify-center gap-3">
                        <i class="fas fa-key text-[12px]"></i> Ganti Password
                    </button>
                </div>
                <i class="fas fa-shield-alt absolute -right-6 -bottom-6 text-9xl opacity-5 group-hover:scale-110 transition-transform duration-700"></i>
            </div>
        </div>

        <!-- MAIN CARD: FORM DETAILS -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-sm border-2 border-slate-50 dark:border-slate-800 p-8 md:p-12 relative overflow-hidden">
                <!-- Header Form -->
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase italic tracking-tight leading-none">Detail Akun Utama</h3>
                        <div class="h-1.5 w-16 bg-indigo-500 rounded-full mt-3"></div>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                         <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400"><i class="fas fa-info-circle text-xs"></i></span>
                         <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Informasi Terproteksi</span>
                    </div>
                </div>

                <!-- Form Fields (Read Only Styled) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Nama Lengkap</label>
                        <div class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 shadow-inner">
                            <?= esc($user['nama_lengkap']) ?>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Email Terdaftar</label>
                        <div class="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-700 dark:text-slate-200 shadow-inner">
                            <?= esc($user['email']) ?>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Username Akses</label>
                        <div class="w-full px-6 py-4 bg-slate-100/50 dark:bg-slate-800/80 border-2 border-slate-200/50 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-500 dark:text-slate-400 font-mono italic">
                            @<?= esc($user['username']) ?>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">ID Pengguna</label>
                        <div class="w-full px-6 py-4 bg-slate-100/50 dark:bg-slate-800/80 border-2 border-slate-200/50 dark:border-slate-700 rounded-2xl text-sm font-bold text-slate-500 dark:text-slate-400 font-mono">
                            #0000<?= esc($user['id'] ?? '0') ?>
                        </div>
                    </div>
                </div>

                <!-- HISTORY SECTION -->
                <div class="mt-16 pt-10 border-t-2 border-slate-50 dark:border-slate-800 relative">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-6 py-1 bg-white dark:bg-slate-900 border-2 border-slate-50 dark:border-slate-800 rounded-full text-[9px] font-black text-slate-400 uppercase tracking-widest shadow-sm">
                        Catatan Sesi
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-6 items-center justify-between p-6 bg-indigo-50/30 dark:bg-indigo-900/10 rounded-[2rem] border border-indigo-100 dark:border-indigo-900/50">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 shadow-xl flex items-center justify-center text-indigo-500 shrink-0 border border-indigo-50 dark:border-slate-700">
                                <i class="fas fa-history text-lg"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-tight">Sesi Terakhir Terdeteksi</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold italic mt-0.5">Baru saja melalui Perangkat Desktop • IP: <?= $_SERVER['REMOTE_ADDR'] ?></p>
                            </div>
                        </div>
                        <a href="<?= base_url('logout') ?>" class="px-6 py-2.5 bg-white dark:bg-slate-800 text-rose-500 text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-sm border border-rose-100 dark:border-rose-900/50">
                            Akhiri Semua Sesi
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>