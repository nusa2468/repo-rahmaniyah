<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Profil Saya') ?></title>
    
    <!-- Include Assets Partial -->
    <?= view('portal/siswa/partials/script'); ?>
    
    <!-- Alpine.js untuk Tab Interaktif -->
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar -->
    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Include Sidebar -->
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                
                <?php
                    $p = (array) $pegawai;
                    
                    // Helper Nama Lengkap
                    $fullname = trim(($p['gelar_depan'] ?? '') . ' ' . $p['nama_lengkap'] . ' ' . ($p['gelar_belakang'] ?? ''));
                    
                    // Logic Foto Cerdas
                    $fotoSrc = 'https://ui-avatars.com/api/?name=' . urlencode($p['nama_lengkap']) . '&background=ecfdf5&color=047857&size=256&bold=true';
                    if (!empty($p['foto']) && $p['foto'] !== 'default.png' && file_exists(FCPATH . 'uploads/pegawai/' . $p['foto'])) {
                        $fotoSrc = base_url('uploads/pegawai/' . $p['foto']);
                    }
                
                    // Status Badge
                    $statusClass = ($p['status_aktif'] == 'aktif') ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200';
                ?>

                <!-- NOTIFIKASI -->
                <?php if(session()->getFlashdata('success_password')): ?>
                    <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-xl border border-emerald-100 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-check-circle text-xl"></i> <?= session()->getFlashdata('success_password') ?>
                    </div>
                <?php endif; ?>

                <?php if(session()->getFlashdata('error_password')): ?>
                    <div class="mb-6 p-4 bg-rose-50 text-rose-700 text-sm font-bold rounded-xl border border-rose-100 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-exclamation-circle text-xl"></i> <?= session()->getFlashdata('error_password') ?>
                    </div>
                <?php endif; ?>

                <div class="container-fluid mb-10" x-data="{ tab: 'biodata' }">

                    <!-- Header Ringkas -->
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Profil Saya</h1>
                            <p class="text-sm text-slate-500 font-medium">Kelola informasi pribadi dan keamanan akun.</p>
                        </div>
                        <div class="text-right hidden sm:block">
                            <span class="text-[10px] font-bold text-slate-400 bg-white border border-slate-200 px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">
                                SYSTEM ID: <?= esc($p['id']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        <!-- KOLOM KIRI: FOTO & NAVIGASI (Sticky) -->
                        <div class="lg:col-span-3 lg:sticky lg:top-6 space-y-4">
                            
                            <!-- Kartu Identitas -->
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 text-center relative overflow-hidden group">
                                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-emerald-500 to-teal-600"></div>
                                
                                <!-- Foto Pegawai -->
                                <div class="relative w-28 h-28 mx-auto -mt-2 mb-3">
                                    <div class="w-full h-full rounded-full p-1 bg-white shadow-lg">
                                        <img src="<?= $fotoSrc ?>" class="w-full h-full rounded-full object-cover border border-slate-100" alt="Foto Profil">
                                    </div>
                                </div>

                                <h2 class="text-base font-black text-slate-800 leading-tight mb-1"><?= esc($fullname) ?></h2>
                                <p class="text-xs text-slate-500 font-medium mb-3"><?= esc($p['jenis_ptk'] ?: 'Pegawai') ?></p>
                                
                                <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $statusClass ?>">
                                    <?= esc($p['status_aktif']) ?>
                                </span>
                            </div>

                            <!-- Menu Navigasi -->
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                <nav class="flex flex-col text-sm font-bold text-slate-600">
                                    <button @click="tab = 'biodata'" 
                                            :class="tab === 'biodata' ? 'bg-emerald-50 text-emerald-700 border-l-4 border-emerald-500' : 'hover:bg-slate-50 border-l-4 border-transparent'"
                                            class="px-5 py-3.5 text-left flex items-center gap-3 transition-all">
                                        <i class="fas fa-id-card w-5 text-center opacity-70"></i> Biodata Diri
                                    </button>
                                    <button @click="tab = 'kepegawaian'" 
                                            :class="tab === 'kepegawaian' ? 'bg-amber-50 text-amber-700 border-l-4 border-amber-500' : 'hover:bg-slate-50 border-l-4 border-transparent'"
                                            class="px-5 py-3.5 text-left flex items-center gap-3 transition-all">
                                        <i class="fas fa-briefcase w-5 text-center opacity-70"></i> Data Kepegawaian
                                    </button>
                                    <button @click="tab = 'akun'" 
                                            :class="tab === 'akun' ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-500' : 'hover:bg-slate-50 border-l-4 border-transparent'"
                                            class="px-5 py-3.5 text-left flex items-center gap-3 transition-all">
                                        <i class="fas fa-lock w-5 text-center opacity-70"></i> Keamanan Akun
                                    </button>
                                </nav>
                            </div>

                        </div>

                        <!-- KOLOM KANAN: DETAIL KONTEN -->
                        <div class="lg:col-span-9 space-y-6">
                            
                            <!-- PANEL 1: BIODATA -->
                            <div x-show="tab === 'biodata'" x-transition:enter.opacity.duration.300ms>
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <h3 class="font-black text-slate-700 text-sm uppercase tracking-wider">Identitas Pribadi</h3>
                                        <i class="fas fa-user-check text-slate-300"></i>
                                    </div>
                                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">NIK (KTP)</label>
                                            <p class="text-sm font-bold text-slate-800 font-mono"><?= esc($p['nik'] ?: '-') ?></p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Tempat, Tanggal Lahir</label>
                                            <p class="text-sm font-bold text-slate-800">
                                                <?= esc($p['tempat_lahir']) ?>, <?= ($p['tanggal_lahir']) ? date('d F Y', strtotime($p['tanggal_lahir'])) : '-' ?>
                                            </p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Jenis Kelamin</label>
                                            <p class="text-sm font-bold text-slate-800">
                                                <?= ($p['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan' ?>
                                            </p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Agama</label>
                                            <p class="text-sm font-bold text-slate-800"><?= esc($p['agama'] ?: '-') ?></p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Alamat</label>
                                            <p class="text-sm font-bold text-slate-800 truncate"><?= esc($p['alamat_jalan'] ?: '-') ?></p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Email</label>
                                            <p class="text-sm font-bold text-slate-800"><?= esc($p['email'] ?: '-') ?></p>
                                        </div>
                                        <div class="border-b border-slate-100 pb-2">
                                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">No. HP</label>
                                            <p class="text-sm font-bold text-slate-800 font-mono"><?= esc($p['no_hp'] ?: '-') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PANEL 2: KEPEGAWAIAN -->
                            <div x-show="tab === 'kepegawaian'" x-cloak x-transition:enter.opacity.duration.300ms>
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <h3 class="font-black text-slate-700 text-sm uppercase tracking-wider">Data Administrasi</h3>
                                        <i class="fas fa-briefcase text-slate-300"></i>
                                    </div>
                                    <div class="p-6">
                                        <!-- ID Cards -->
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                                            <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-100">
                                                <p class="text-[9px] font-black text-indigo-400 uppercase mb-1">NUPTK</p>
                                                <p class="text-sm font-black text-indigo-700 font-mono"><?= esc($p['nuptk'] ?: '-') ?></p>
                                            </div>
                                            <div class="p-3 rounded-xl bg-slate-100 border border-slate-200">
                                                <p class="text-[9px] font-black text-slate-400 uppercase mb-1">NIP (Negeri)</p>
                                                <p class="text-sm font-black text-slate-700 font-mono"><?= esc($p['nip'] ?: '-') ?></p>
                                            </div>
                                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                                                <p class="text-[9px] font-black text-emerald-400 uppercase mb-1">NIP Yayasan</p>
                                                <p class="text-sm font-black text-emerald-700 font-mono"><?= esc($p['nipy'] ?: '-') ?></p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Status Kepegawaian</label>
                                                <p class="text-sm font-bold text-slate-800"><?= esc($p['status_kepegawaian'] ?: '-') ?></p>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Unit Kerja</label>
                                                <p class="text-sm font-bold text-slate-800">Unit <?= esc($p['kode_jenjang'] ?: '-') ?></p>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Jabatan Struktural</label>
                                                <p class="text-sm font-bold text-slate-800"><?= esc($p['jabatan_struktural'] ?? '-') ?></p>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Tugas Tambahan</label>
                                                <p class="text-sm font-bold text-slate-800"><?= esc($p['tugas_tambahan'] ?: '-') ?></p>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">SK Pengangkatan</label>
                                                <p class="text-xs font-mono text-slate-600 bg-slate-50 px-2 py-1 rounded inline-block"><?= esc($p['sk_pengangkatan'] ?: '-') ?></p>
                                            </div>
                                            <div class="border-b border-slate-100 pb-2">
                                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">TMT Kerja</label>
                                                <p class="text-sm font-bold text-slate-800"><?= ($p['tmt_pengangkatan']) ? date('d F Y', strtotime($p['tmt_pengangkatan'])) : '-' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PANEL 3: KEAMANAN AKUN (GANTI PASSWORD) -->
                            <div x-show="tab === 'akun'" x-cloak x-transition:enter.opacity.duration.300ms>
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <h3 class="font-black text-slate-700 text-sm uppercase tracking-wider">Update Kata Sandi</h3>
                                        <i class="fas fa-lock text-slate-300"></i>
                                    </div>
                                    <div class="p-8">
                                        <form action="<?= base_url('portal/pegawai/update-password') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="space-y-4 max-w-lg">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Password Lama</label>
                                                    <input type="password" name="old_password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all" placeholder="••••••••" required>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Password Baru</label>
                                                        <input type="password" name="new_password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all" placeholder="Min. 6 Karakter" required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Ulangi Password</label>
                                                        <input type="password" name="confirm_password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all" placeholder="Ketik Ulang" required>
                                                    </div>
                                                </div>
                                                <div class="pt-2">
                                                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-200 transition-all transform active:scale-95">
                                                        Simpan Password
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
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