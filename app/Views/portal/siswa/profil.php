<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Profil Saya') ?></title>
    
    <!-- Include Assets Partial -->
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar -->
    <?= view('portal/siswa/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Include Sidebar -->
        <?= view('portal/siswa/partials/sidebar'); ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- BANNER HEADER (Konsisten dengan Dashboard) -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm">
                                AKADEMIK • IDENTITAS DIRI
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2">
                                Profil Saya 👤
                            </h1>
                            <p class="text-indigo-100 text-sm md:text-base max-w-lg">
                                Kelola informasi akun dan data pribadi Anda di sini. Pastikan data yang tercatat di sistem selalu valid.
                            </p>
                        </div>
                        
                        <!-- Jam & Tanggal -->
                        <div class="text-white text-right hidden md:block">
                            <div class="text-3xl font-black"><?= date('H:i') ?></div>
                            <div class="text-indigo-200 text-sm font-medium"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-6 relative">
                        <!-- Cover Image -->
                        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 h-40 relative">
                            <div class="absolute inset-0 bg-black/10"></div>
                        </div>
                        
                        <div class="px-8 pb-8 relative">
                            <!-- Avatar -->
                            <!-- FIX: Menambahkan z-10 agar foto selalu di atas -->
                            <div class="absolute -top-16 left-8 z-10">
                                <div class="w-32 h-32 rounded-3xl border-4 border-white shadow-xl overflow-hidden bg-white">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama_lengkap']) ?>&background=4f46e5&color=fff&size=256&bold=true" 
                                         class="w-full h-full object-cover" alt="Avatar">
                                </div>
                            </div>

                            <!-- Header Info -->
                            <!-- FIX: Mengubah mt-20 menjadi mt-24 agar nama tidak menabrak foto -->
                            <div class="mt-24 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-6 mb-6">
                                <div>
                                    <h1 class="text-3xl font-black text-slate-800 leading-tight"><?= esc($siswa['nama_lengkap']) ?></h1>
                                    <p class="text-slate-500 font-medium mt-1 flex items-center gap-2">
                                        <i class="fas fa-graduation-cap text-indigo-500"></i>
                                        Siswa Aktif • <?= esc($sekolah->nama_sekolah ?? 'Sekolah') ?>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <span class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-100 flex items-center gap-2">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                        <?= esc($siswa['status'] ?? 'Aktif') ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Detail Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- NIS -->
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-colors">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nomor Induk Siswa</label>
                                    </div>
                                    <div class="font-black text-slate-800 text-xl pl-11"><?= esc($siswa['nis']) ?></div>
                                </div>
                                
                                <!-- NISN -->
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-colors">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-fingerprint"></i>
                                        </div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">NISN</label>
                                    </div>
                                    <div class="font-black text-slate-800 text-xl pl-11"><?= esc($siswa['nisn'] ?? '-') ?></div>
                                </div>

                                <!-- Email -->
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-colors">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Terdaftar</label>
                                    </div>
                                    <div class="font-bold text-slate-800 text-lg pl-11 break-all"><?= esc($siswa['email'] ?? 'Belum diatur') ?></div>
                                </div>

                                <!-- Kelas -->
                                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-indigo-200 transition-colors">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                            <i class="fas fa-chalkboard"></i>
                                        </div>
                                        <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas Saat Ini</label>
                                    </div>
                                    <div class="font-bold text-slate-800 text-lg pl-11">
                                        ID Kelas: #<?= esc($siswa['id_kelas'] ?? '-') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div class="text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                                    System ID: <?= hash('crc32', $siswa['id'] . $siswa['nis']) ?>
                                </div>
                                <a href="<?= base_url('portal/siswa/logout') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-600 hover:text-white transition-all shadow-sm w-full sm:w-auto justify-center">
                                    <i class="fas fa-sign-out-alt"></i> Keluar Akun
                                </a>
                            </div>
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
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024) { 
                    if (!sidebar.contains(e.target) && !btn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.add('-translate-x-full');
                    }
                }
            });
        }
    </script>
</body>
</html>