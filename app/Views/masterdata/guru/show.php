<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Profil Guru - <?= esc($guru['nama_lengkap'] ?? 'Detail') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Helper Warna Jenjang (Tailwind - Konsisten dengan index.php)
if (!function_exists('getJenjangBadge')) {
    function getJenjangBadge($kode) {
        $kode = strtoupper($kode ?? '');
        switch ($kode) {
            case 'SD': case 'MI': 
                return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 border-red-200 dark:border-red-500/30';
            case 'SMP': case 'MTS': 
                return 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 border-blue-200 dark:border-blue-500/30';
            case 'SMA': case 'SMK': case 'MA': 
                return 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 border-slate-200 dark:border-slate-500/30';
            case 'TK': case 'PAUD': 
                return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30';
            case 'GLOBAL': case 'YAYASAN': 
                return 'bg-gray-800 text-white dark:bg-gray-700 dark:text-gray-200 border-gray-700';
            default: 
                return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700';
        }
    }
}

$kj = $guru['kode_jenjang'] ?? 'GLOBAL';
$foto_nama = $guru['foto'] ?? '';
$foto_path = !empty($foto_nama) 
    ? base_url('uploads/guru/foto/' . $foto_nama) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($guru['nama_lengkap'] ?? 'N A') . '&size=256&background=random';
?>

<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                Profil Lengkap Personel
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Informasi biografi, akun sistem, dan riwayat karir pendidik.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= route_to('guru_index') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                <i class="fas fa-arrow-left mr-2 text-xs"></i> Kembali
            </a>
            <a href="<?= route_to('guru_edit', $guru['id']) ?>" class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-lg shadow-amber-500/20 transition-all hover:-translate-y-0.5">
                <i class="fas fa-edit mr-2 text-xs"></i> Ubah Data
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT COLUMN: PROFILE CARD -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden relative">
                <!-- Background decoration -->
                <div class="h-24 bg-gradient-to-r from-sky-400 to-blue-600"></div>
                
                <div class="px-6 pb-6 text-center -mt-12 relative">
                    <div class="relative inline-block">
                        <img src="<?= $foto_path ?>" 
                             alt="Profile" 
                             class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-900 shadow-md object-cover bg-white">
                        
                        <?php $is_aktif = (strtolower($guru['status'] ?? 'aktif') == 'aktif'); ?>
                        <span class="absolute bottom-2 right-2 w-5 h-5 border-2 border-white dark:border-gray-900 rounded-full <?= $is_aktif ? 'bg-green-500' : 'bg-gray-400' ?>" 
                              title="Status: <?= esc($guru['status'] ?? 'Aktif') ?>"></span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-gray-900 dark:text-white leading-tight">
                        <?= esc($guru['nama_lengkap'] ?? 'N/A') ?>
                    </h2>
                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400 mt-1">
                        ID: #G-<?= str_pad($guru['id'] ?? 0, 3, '0', STR_PAD_LEFT) ?>
                    </p>
                    
                    <div class="mt-3">
                        <?php $badge = getJenjangBadge($kj); ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $badge ?>">
                            UNIT <?= esc($kj) ?>
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-800 px-6 py-4">
                    <ul class="space-y-4">
                        <li>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Username Sistem</p>
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white">
                                <i class="fas fa-user-circle text-sky-500"></i>
                                <?= esc($guru['username'] ?? '-') ?>
                            </div>
                        </li>
                        <li>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email Institusi</p>
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-900 dark:text-white truncate">
                                <i class="fas fa-envelope text-sky-500"></i>
                                <span class="truncate"><?= esc($guru['email'] ?? '-') ?></span>
                            </div>
                        </li>
                        <li>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Status Wali Kelas</p>
                            <?php if (!empty($guru['is_wali'])): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 text-xs font-bold border border-amber-200 dark:border-amber-800">
                                    <i class="fas fa-star text-amber-500"></i> Aktif Sebagai Wali
                                </span>
                            <?php else: ?>
                                <span class="text-sm text-gray-500 italic">Bukan Wali Kelas</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: DETAILS & TABS -->
        <div class="lg:col-span-8 xl:col-span-9 space-y-6">
            
            <!-- Bio Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 dark:text-sky-400">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Informasi Biografi & Identitas Pokok</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Column 1 -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">NUPTK</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5"><?= esc($guru['nuptk'] ?? '-') ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">NIK (No. KTP)</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5"><?= esc($guru['nik'] ?? '-') ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Tempat, Tgl Lahir</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                    <?= esc($guru['tempat_lahir'] ?? '-') ?>, <?= !empty($guru['tanggal_lahir']) ? date('d M Y', strtotime($guru['tanggal_lahir'])) : '-' ?>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Jenis Kelamin</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                    <?= (($guru['jenis_kelamin'] ?? 'L') == 'L') ? 'Laki-laki' : 'Perempuan' ?>
                                </p>
                            </div>
                        </div>
                        <!-- Column 2 -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Nama Ibu Kandung</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5"><?= esc($guru['nama_ibu_kandung'] ?? '-') ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Telepon / WA</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5"><?= esc($guru['telepon'] ?? '-') ?></p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">TMT Sekolah Induk</label>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                    <?= !empty($guru['tmt_sekolah_induk']) ? date('d M Y', strtotime($guru['tmt_sekolah_induk'])) : '-' ?>
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Alamat Domisili</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5"><?= esc($guru['alamat'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section (Alpine) -->
            <div x-data="{ activeTab: 'kepegawaian' }" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
                
                <!-- Tab Nav -->
                <div class="border-b border-gray-200 dark:border-gray-800 overflow-x-auto custom-scrollbar">
                    <nav class="flex px-4 gap-6 min-w-max" aria-label="Tabs">
                        <button @click="activeTab = 'kepegawaian'" 
                                :class="activeTab === 'kepegawaian' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                            <i class="fas fa-briefcase"></i> Kepegawaian
                        </button>
                        <button @click="activeTab = 'pendidikan'" 
                                :class="activeTab === 'pendidikan' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                            <i class="fas fa-graduation-cap"></i> Pendidikan
                        </button>
                        <button @click="activeTab = 'penugasan'" 
                                :class="activeTab === 'penugasan' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                                class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                            <i class="fas fa-chalkboard-user"></i> Penugasan
                        </button>
                    </nav>
                </div>

                <div class="p-0">
                    
                    <!-- TAB 1: KEPEGAWAIAN -->
                    <div x-show="activeTab === 'kepegawaian'" x-transition:enter.opacity.duration.300ms class="overflow-x-auto">
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                             <h6 class="font-bold text-gray-900 dark:text-white">Riwayat Status & Pangkat</h6>
                        </div>
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                                <tr>
                                    <th class="px-6 py-4 text-xs uppercase">TMT</th>
                                    <th class="px-6 py-4 text-xs uppercase">Status Pegawai</th>
                                    <th class="px-6 py-4 text-xs uppercase">Jenis PTK</th>
                                    <th class="px-6 py-4 text-xs uppercase">NIP / Pangkat</th>
                                    <th class="px-6 py-4 text-xs uppercase text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <?php if (empty($riwayatKepegawaian)): ?>
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Belum ada data riwayat kepegawaian.</td></tr>
                                <?php else: foreach ($riwayatKepegawaian as $rk): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 font-bold text-sky-600 dark:text-sky-400">
                                            <?= !empty($rk['tmt_kepegawaian']) ? date('d M Y', strtotime($rk['tmt_kepegawaian'])) : '-' ?>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white"><?= esc($rk['status_kepegawaian'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400"><?= esc($rk['jenis_ptk'] ?? '-') ?></td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 dark:text-white"><?= esc($rk['nip'] ?? '-') ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?= esc($rk['pangkat_golongan'] ?? 'Non-Golongan') ?></div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button class="text-gray-400 hover:text-sky-500 transition-colors mx-1"><i class="fas fa-edit"></i></button>
                                            <button class="text-gray-400 hover:text-red-500 transition-colors mx-1"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TAB 2: PENDIDIKAN -->
                    <div x-show="activeTab === 'pendidikan'" x-cloak class="overflow-x-auto">
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                             <h6 class="font-bold text-gray-900 dark:text-white">Riwayat Pendidikan Formal</h6>
                        </div>
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                                <tr>
                                    <th class="px-6 py-4 text-xs uppercase">Jenjang</th>
                                    <th class="px-6 py-4 text-xs uppercase">Bidang Studi / Prodi</th>
                                    <th class="px-6 py-4 text-xs uppercase">Lembaga / Kampus</th>
                                    <th class="px-6 py-4 text-xs uppercase text-center">Tahun Lulus</th>
                                    <th class="px-6 py-4 text-xs uppercase text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <?php if (empty($riwayatPendidikan)): ?>
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Belum ada data riwayat pendidikan.</td></tr>
                                <?php else: foreach ($riwayatPendidikan as $rp): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs rounded font-bold border border-violet-200 dark:border-violet-500/20">
                                                <?= esc($rp['jenjang'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white"><?= esc($rp['bidang_studi'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400"><?= esc($rp['lembaga'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-center font-mono font-bold text-gray-600 dark:text-gray-400"><?= esc($rp['tahun_lulus'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <button class="text-gray-400 hover:text-sky-500 transition-colors mx-1"><i class="fas fa-edit"></i></button>
                                            <button class="text-gray-400 hover:text-red-500 transition-colors mx-1"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- TAB 3: PENUGASAN -->
                    <div x-show="activeTab === 'penugasan'" x-cloak class="overflow-x-auto">
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                             <h6 class="font-bold text-gray-900 dark:text-white">Riwayat Tugas Mengajar</h6>
                        </div>
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                                <tr>
                                    <th class="px-6 py-4 text-xs uppercase">Tahun Ajaran</th>
                                    <th class="px-6 py-4 text-xs uppercase">Mapel Diampu</th>
                                    <th class="px-6 py-4 text-xs uppercase">Tugas Tambahan</th>
                                    <th class="px-6 py-4 text-xs uppercase text-center">Total JJM</th>
                                    <th class="px-6 py-4 text-xs uppercase text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <?php if (empty($penugasanMengajar)): ?>
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">Belum ada data penugasan.</td></tr>
                                <?php else: foreach ($penugasanMengajar as $pm): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500 dark:text-gray-400"><?= esc($pm['tahun_ajaran'] ?? '-') ?></td>
                                        <td class="px-6 py-4 font-bold text-sky-600 dark:text-sky-400"><?= esc($pm['mapel_diampu'] ?? '-') ?></td>
                                        <td class="px-6 py-4 text-red-500 dark:text-red-400 font-bold text-xs italic"><?= esc($pm['tugas_tambahan'] ?? 'Tidak ada') ?></td>
                                        <td class="px-6 py-4 text-center font-black text-gray-900 dark:text-white"><?= esc($pm['jjm_total'] ?? '0') ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <button class="text-gray-400 hover:text-sky-500 transition-colors mx-1"><i class="fas fa-edit"></i></button>
                                            <button class="text-gray-400 hover:text-red-500 transition-colors mx-1"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>