<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Detail Profil: <?= esc($siswa['nama_lengkap'] ?? 'Siswa') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    /**
     * Manajemen Siswa - Detail View (Solid Design & Enterprise Ready)
     * Status: FIXED (Prioritas Data Utama Siswa vs Demografi)
     */
    
    // 1. PROTEKSI & NORMALISASI DATA
    $siswa = (array) ($siswa ?? []);
    $siswa_relasi = (array) ($siswa_relasi ?? []);
    
    // Extract Data Relasi dengan Fallback Aman
    $demografi = (array) ($siswa_relasi['demografi'] ?? $siswa['demografi'] ?? []);
    $keluarga_list = (array) ($siswa_relasi['keluarga'] ?? $siswa['keluarga'] ?? []);
    
    // Data Akademik & Kesiswaan
    $enrollment_terkini = (array) ($siswa_relasi['enrollment_terkini'] ?? []); 
    if (empty($enrollment_terkini) && !empty($siswa_relasi['akademik_histori'])) {
        $enrollment_terkini = $siswa_relasi['akademik_histori'][0];
    }

    $akademik_histori = $siswa_relasi['akademik_histori'] ?? [];
    
    // Kesiswaan
    $ekskul_list = $siswa_relasi['kesiswaan']['ekskul'] ?? [];
    $prestasi_list = $siswa_relasi['kesiswaan']['prestasi'] ?? [];
    $organisasi_list = $siswa_relasi['kesiswaan']['organisasi'] ?? [];
    
    // Penunjang Lain
    $keuangan_list = $siswa_relasi['keuangan'] ?? [];
    $presensi_stat = $siswa_relasi['presensi_summary'] ?? ['H'=>0, 'S'=>0, 'I'=>0, 'A'=>0];
    $pelanggaran_list = $siswa_relasi['pelanggaran'] ?? [];

    $jurusan_list = $jurusan_list ?? [];

    // Helper: Badge Jenjang Solid
    if (!function_exists('getJenjangBadgeColor')) {
        function getJenjangBadgeColor($kode) {
            $kode = strtoupper($kode ?? 'GLOBAL');
            switch ($kode) {
                case 'GLOBAL': return 'bg-slate-900 text-white shadow-slate-200'; 
                case 'SD': case 'MI': return 'bg-red-600 text-white shadow-red-200'; 
                case 'SMP': case 'MTS': return 'bg-blue-600 text-white shadow-blue-200'; 
                case 'SMA': case 'SMK': case 'MA': return 'bg-slate-700 text-white shadow-slate-300'; 
                case 'TK': case 'PAUD': return 'bg-emerald-600 text-white shadow-emerald-200'; 
                default: return 'bg-slate-500 text-white';
            }
        }
    }

    // Helper: Status Badge Solid
    $st = strtolower($siswa['status'] ?? 'aktif');
    $statusClasses = [
        'aktif' => 'bg-emerald-600 text-white shadow-emerald-500/20',
        'terdaftar' => 'bg-blue-600 text-white shadow-blue-500/20',
        'lulus' => 'bg-indigo-600 text-white shadow-indigo-500/20',
        'pindah' => 'bg-amber-500 text-white shadow-amber-500/20',
        'mutasi' => 'bg-slate-500 text-white shadow-slate-500/20',
        'dikeluarkan' => 'bg-rose-600 text-white shadow-rose-500/20'
    ];
    $currentStatusClass = $statusClasses[$st] ?? 'bg-slate-500 text-white';

    // Helper: Cari data keluarga
    $find_keluarga = function($hubungan) use ($keluarga_list) {
        foreach ($keluarga_list as $k) {
            $k = (array)$k;
            if (isset($k['hubungan']) && strtolower($k['hubungan']) == strtolower($hubungan)) return $k;
        }
        return [];
    };

    $ayah = $find_keluarga('ayah');
    $ibu  = $find_keluarga('ibu');
    $wali = $find_keluarga('wali');

    $kj = strtoupper($siswa['kode_jenjang'] ?? 'GLOBAL');
?>

<div class="container mx-auto px-4 py-6 mb-20 animate-fade-in">
    
    <!-- Solid Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-xl shadow-slate-200 shrink-0 border border-white/10">
                <i class="fas fa-id-badge text-2xl"></i>
            </div>
            <div>
                <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1">
                    <ol class="inline-flex items-center space-x-2">
                        <li><a href="<?= base_url('app/masterdata/siswa') ?>" class="hover:text-blue-600">Database</a></li>
                        <li><i class="fas fa-chevron-right text-[8px] opacity-50"></i></li>
                        <li class="text-slate-600 uppercase">Berkas Profil Siswa</li>
                    </ol>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-none">
                    Profil Lengkap
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/masterdata/siswa') ?>" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 text-slate-600 text-xs font-black rounded-xl uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= base_url('app/masterdata/siswa/edit/' . ($siswa['id'] ?? 0)) ?>" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-500 text-white text-xs font-black rounded-xl uppercase tracking-widest hover:bg-amber-600 transition-all active:scale-95 shadow-lg shadow-amber-500/30">
                <i class="fas fa-edit text-xs"></i> Edit Profil
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-bold"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="mb-8 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-rose-800">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-bold"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar: Profile Banner Solid -->
        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden relative">
                <!-- Solid Banner -->
                <div class="absolute top-0 left-0 w-full h-32 bg-slate-900"></div>
                
                <div class="relative px-6 pt-12 pb-8 text-center">
                    <!-- Avatar Solid -->
                    <div class="relative inline-block mb-4">
                        <div class="w-32 h-32 rounded-[2rem] bg-white p-1 shadow-2xl mx-auto">
                            <div class="w-full h-full rounded-[1.8rem] bg-slate-100 flex items-center justify-center overflow-hidden border-2 border-slate-50 text-slate-300">
                                <?php if (!empty($siswa['foto'])): ?>
                                    <img src="<?= base_url('uploads/siswa/' . $siswa['foto']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user-graduate text-5xl"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-2xl border-4 border-white flex items-center justify-center shadow-lg <?= $currentStatusClass ?>">
                            <i class="fas fa-check-circle text-[10px]"></i>
                        </div>
                    </div>

                    <h2 class="text-xl font-black text-slate-900 leading-tight mb-1 uppercase tracking-tight"><?= esc($siswa['nama_lengkap'] ?? '-') ?></h2>
                    <p class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-lg inline-block mb-4 uppercase tracking-wider">
                        NIS: <?= esc($siswa['nis'] ?? '-') ?> / NISN: <?= esc($siswa['nisn'] ?? '-') ?>
                    </p>
                    
                    <div class="block">
                        <div class="inline-flex items-center px-5 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg <?= getJenjangBadgeColor($kj) ?>">
                            UNIT <?= esc($kj) ?>
                        </div>
                    </div>

                    <!-- Quick Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 mt-8 pt-6 border-t border-slate-50">
                        <div class="bg-slate-50 p-3 rounded-2xl text-left border border-slate-100/50">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</label>
                            <span class="text-[10px] font-black uppercase <?= $currentStatusClass ?> px-2 py-1 rounded-lg block text-center shadow-sm">
                                <?= esc($st) ?>
                            </span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-2xl text-left border border-slate-100/50">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Angkatan</label>
                            <div class="text-[11px] font-black text-slate-800 text-center h-7 flex items-center justify-center">
                                <?= esc($siswa['angkatan'] ?? '-') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solid Info Card: Akademik -->
            <div class="bg-blue-600 rounded-[2rem] p-6 shadow-xl shadow-blue-500/20 text-white relative overflow-hidden group">
                <i class="fas fa-graduation-cap absolute -right-4 -bottom-4 text-7xl opacity-10 group-hover:scale-110 transition-transform"></i>
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Rombongan Belajar (Aktif)</p>
                    <h4 class="text-xl font-black uppercase tracking-tight mb-2">
                        <?= esc($enrollment_terkini['nama_kelas'] ?? 'Belum Diplot') ?>
                    </h4>
                    <div class="text-[11px] font-bold bg-white/20 px-2 py-1 rounded inline-block">
                        T.A <?= esc($enrollment_terkini['tahun_ajaran'] ?? $enrollment_terkini['tahun_ajaran'] ?? '---') ?>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Link Solid -->
            <?php if(!empty($demografi['telepon'])): ?>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $demografi['telepon'] ?? '') ?>" target="_blank"
               class="flex items-center gap-4 p-4 bg-emerald-600 text-white rounded-3xl shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 transition-all hover:-translate-y-1 group">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                    <i class="fab fa-whatsapp text-lg"></i>
                </div>
                <div class="flex-1">
                    <span class="block text-[9px] font-black text-emerald-100 uppercase tracking-widest opacity-80 leading-none mb-1">WhatsApp Siswa</span>
                    <span class="text-sm font-black"><?= esc($demografi['telepon']) ?></span>
                </div>
                <i class="fas fa-external-link-alt text-[10px] opacity-40"></i>
            </a>
            <?php endif; ?>

            <!-- Action: Sync Button (Sidebar) -->
            <form action="<?= base_url('app/masterdata/siswa/sync/' . $siswa['id']) ?>" method="POST" onsubmit="return confirm('Mulai sinkronisasi data siswa ini dengan sistem eksternal?')">
                <?= csrf_field() ?>
                <button type="submit" class="w-full flex items-center justify-center gap-3 p-4 bg-slate-800 text-slate-300 rounded-3xl border border-slate-700 hover:bg-slate-700 hover:text-white transition-all group">
                    <i class="fas fa-sync-alt group-hover:animate-spin"></i>
                    <span class="text-xs font-black uppercase tracking-widest">Sinkronisasi Data</span>
                </button>
            </form>

        </aside>

        <!-- Main Body: Sections Solid -->
        <main class="lg:col-span-8 space-y-6">
            
            <!-- SECTION I: Identitas Negara -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-slate-900 flex items-center justify-between border-b border-white/5">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-white/70">I. Berkas Identitas Utama</h3>
                    <i class="fas fa-fingerprint text-white/20 text-xl"></i>
                </div>
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Induk Kependudukan (NIK)</label>
                            <p class="text-base font-black text-slate-900 tracking-wider"><?= esc($siswa['nik'] ?: '---') ?></p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jenis Kelamin</label>
                            <div class="flex items-center gap-2 text-sm font-black text-slate-800">
                                <i class="fas <?= ($siswa['jenis_kelamin'] == 'L') ? 'fa-mars text-blue-500' : 'fa-venus text-rose-500' ?> text-base"></i>
                                <?= ($siswa['jenis_kelamin'] == 'L') ? 'LAKI-LAKI' : 'PEREMPUAN' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-slate-50">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tempat & Tanggal Lahir</label>
                            <p class="text-sm font-bold text-slate-700">
                                <!-- FIX: Prioritas ambil dari tabel siswa, fallback ke demografi -->
                                <?= esc($siswa['tempat_lahir'] ?? $demografi['tempat_lahir'] ?? '-') ?>, 
                                <span class="text-blue-600">
                                    <?php 
                                        $tgl = $siswa['tanggal_lahir'] ?? $demografi['tanggal_lahir'] ?? null;
                                        echo $tgl ? date('d F Y', strtotime($tgl)) : '-';
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Keyakinan / Agama</label>
                            <p class="text-sm font-bold text-slate-700 uppercase">
                                <?= esc($siswa['agama'] ?? $demografi['agama'] ?? '---') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION II: Domisili & Sekolah Asal -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-slate-100 flex items-center justify-between border-b border-slate-200">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">II. Domisili & Riwayat</h3>
                    <i class="fas fa-map-location-dot text-slate-300 text-xl"></i>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Alamat Tinggal Lengkap</label>
                        <p class="text-sm font-medium leading-relaxed text-slate-700 bg-slate-50 p-4 rounded-2xl border border-slate-100 italic">
                            <!-- FIX: Prioritas ambil dari tabel siswa, fallback ke demografi -->
                            "<?= esc($siswa['alamat'] ?? $demografi['alamat'] ?? 'Data alamat belum diperbarui dalam database.') ?>"
                        </p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase mb-1">Kelurahan</label>
                            <p class="text-xs font-bold text-slate-800 uppercase"><?= esc($demografi['kelurahan'] ?: '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase mb-1">Kecamatan</label>
                            <p class="text-xs font-bold text-slate-800 uppercase"><?= esc($demografi['kecamatan'] ?: '-') ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[9px] font-black text-indigo-500 uppercase mb-1">Sekolah Sebelumnya</label>
                            <p class="text-xs font-black text-indigo-700 uppercase"><?= esc($demografi['asal_sekolah'] ?: '-') ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION III: Orang Tua Solid -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-slate-50 flex items-center justify-between border-b border-slate-200">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">III. Relasi Orang Tua / Wali</h3>
                    <i class="fas fa-users-viewfinder text-slate-300 text-xl"></i>
                </div>
                <div class="p-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <!-- Ayah -->
                        <div class="p-6 rounded-3xl bg-blue-50/50 border border-blue-100/50">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-[10px]"><i class="fas fa-male text-base"></i></span>
                                <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Informasi Ayah</h4>
                            </div>
                            <p class="text-base font-black text-slate-900 uppercase mb-3"><?= esc($ayah['nama_lengkap'] ?? $demografi['nama_ayah'] ?? 'TIDAK ADA DATA') ?></p>
                            <div class="space-y-1 text-xs font-bold text-slate-500">
                                <p>Pekerjaan: <span class="text-slate-700"><?= esc($ayah['pekerjaan'] ?? '-') ?></span></p>
                                <p>No. Telp: <span class="text-slate-700 font-mono"><?= esc($ayah['no_telepon'] ?? '-') ?></span></p>
                            </div>
                        </div>
                        <!-- Ibu -->
                        <div class="p-6 rounded-3xl bg-rose-50/50 border border-rose-100/50">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center text-[10px]"><i class="fas fa-female text-base"></i></span>
                                <h4 class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Informasi Ibu</h4>
                            </div>
                            <p class="text-base font-black text-slate-900 uppercase mb-3"><?= esc($ibu['nama_lengkap'] ?? $demografi['nama_ibu'] ?? 'TIDAK ADA DATA') ?></p>
                            <div class="space-y-1 text-xs font-bold text-slate-500">
                                <p>Pekerjaan: <span class="text-slate-700"><?= esc($ibu['pekerjaan'] ?? '-') ?></span></p>
                                <p>No. Telp: <span class="text-slate-700 font-mono"><?= esc($ibu['no_telepon'] ?? '-') ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION IV: Kesiswaan & Prestasi (NEW) -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-emerald-50 flex items-center justify-between border-b border-emerald-100">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-emerald-600">IV. Aktivitas Kesiswaan</h3>
                    <i class="fas fa-medal text-emerald-300 text-xl"></i>
                </div>
                <div class="p-8">
                    <!-- Tab Content Like Structure -->
                    <div class="space-y-8">
                        
                        <!-- Ekskul -->
                        <div>
                            <h4 class="text-xs font-black text-slate-800 uppercase mb-3 flex items-center gap-2">
                                <i class="fas fa-basketball-ball text-emerald-500"></i> Ekstrakurikuler
                            </h4>
                            <?php if(empty($ekskul_list)): ?>
                                <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-xl border border-dashed border-slate-200">Belum mengikuti ekstrakurikuler.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php foreach($ekskul_list as $ek): ?>
                                    <div class="p-3 bg-white border border-slate-100 rounded-2xl shadow-sm flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                            <i class="fas fa-running"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-800"><?= esc($ek['nama_ekskul']) ?></h5>
                                            <p class="text-[10px] text-slate-500"><?= esc($ek['hari_latihan'] ?? '-') ?>, <?= esc($ek['jam_mulai'] ?? '') ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="border-t border-slate-100 my-4"></div>

                        <!-- Prestasi -->
                        <div>
                            <h4 class="text-xs font-black text-slate-800 uppercase mb-3 flex items-center gap-2">
                                <i class="fas fa-trophy text-amber-500"></i> Prestasi & Penghargaan
                            </h4>
                            <?php if(empty($prestasi_list)): ?>
                                <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-xl border border-dashed border-slate-200">Belum ada data prestasi tercatat.</p>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach($prestasi_list as $pres): ?>
                                    <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-2xl flex gap-4">
                                        <div class="mt-1">
                                            <i class="fas fa-award text-amber-500 text-xl"></i>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-800 leading-tight"><?= esc($pres['nama_prestasi']) ?></h5>
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                <span class="px-2 py-0.5 bg-white rounded border border-amber-200 text-[10px] font-bold text-amber-600 uppercase"><?= esc($pres['tingkat']) ?></span>
                                                <span class="px-2 py-0.5 bg-white rounded border border-amber-200 text-[10px] font-bold text-amber-600 uppercase"><?= esc($pres['peringkat'] ?? 'Partisipan') ?></span>
                                                <span class="text-[10px] text-slate-400 self-center"><?= date('d M Y', strtotime($pres['tanggal_prestasi'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION V: Akademik & Presensi & Keuangan (NEW) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Akademik & Presensi -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 bg-indigo-50 flex items-center justify-between border-b border-indigo-100">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-indigo-600">V. Akademik & Disiplin</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Presensi Stats -->
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Ringkasan Kehadiran</label>
                            <div class="grid grid-cols-4 gap-2 text-center">
                                <div class="p-2 bg-emerald-50 rounded-xl">
                                    <span class="block text-lg font-black text-emerald-600"><?= $presensi_stat['H'] ?? 0 ?></span>
                                    <span class="text-[8px] font-bold text-emerald-400 uppercase">Hadir</span>
                                </div>
                                <div class="p-2 bg-blue-50 rounded-xl">
                                    <span class="block text-lg font-black text-blue-600"><?= $presensi_stat['S'] ?? 0 ?></span>
                                    <span class="text-[8px] font-bold text-blue-400 uppercase">Sakit</span>
                                </div>
                                <div class="p-2 bg-amber-50 rounded-xl">
                                    <span class="block text-lg font-black text-amber-600"><?= $presensi_stat['I'] ?? 0 ?></span>
                                    <span class="text-[8px] font-bold text-amber-400 uppercase">Izin</span>
                                </div>
                                <div class="p-2 bg-rose-50 rounded-xl">
                                    <span class="block text-lg font-black text-rose-600"><?= $presensi_stat['A'] ?? 0 ?></span>
                                    <span class="text-[8px] font-bold text-rose-400 uppercase">Alfa</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pelanggaran -->
                        <?php if(!empty($pelanggaran_list)): ?>
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Konseling</label>
                            <ul class="space-y-2">
                                <?php foreach($pelanggaran_list as $bk): ?>
                                <li class="text-xs text-slate-600 pl-3 border-l-2 border-rose-300">
                                    <span class="font-bold text-rose-600"><?= date('d/m/y', strtotime($bk['tanggal'])) ?></span>: <?= esc($bk['bentuk_pelanggaran']) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                         <!-- History Kelas -->
                         <?php if(!empty($akademik_histori)): ?>
                        <div>
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Riwayat Kelas</label>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach(array_slice($akademik_histori, 0, 4) as $his): ?>
                                    <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200">
                                        <?= esc($his['nama_kelas']) ?> <span class="text-slate-400">(<?= esc($his['tahun_ajaran']) ?>)</span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Keuangan -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-800 flex items-center justify-between border-b border-slate-700">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-white">VI. Tagihan Terakhir</h3>
                    </div>
                    <div class="p-0">
                        <?php if(empty($keuangan_list)): ?>
                            <div class="p-6 text-center text-slate-400 italic text-xs">
                                Tidak ada data tagihan terbaru.
                            </div>
                        <?php else: ?>
                            <table class="w-full text-left border-collapse">
                                <tbody class="text-xs">
                                    <?php foreach($keuangan_list as $tag): ?>
                                    <tr class="border-b border-slate-50 hover:bg-slate-50">
                                        <td class="p-4 font-bold text-slate-700">
                                            <?= esc($tag['nama_tagihan']) ?>
                                            <span class="block text-[9px] text-slate-400 font-normal"><?= date('d M Y', strtotime($tag['created_at'])) ?></span>
                                        </td>
                                        <td class="p-4 text-right">
                                            <span class="block font-mono font-bold text-slate-800">Rp<?= number_format($tag['nominal'],0,',','.') ?></span>
                                            <?php if(($tag['status'] ?? '') == 'lunas'): ?>
                                                <span class="text-[8px] font-black text-emerald-600 uppercase bg-emerald-100 px-1.5 py-0.5 rounded">Lunas</span>
                                            <?php else: ?>
                                                <span class="text-[8px] font-black text-rose-600 uppercase bg-rose-100 px-1.5 py-0.5 rounded">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </section>

            </div>

            <!-- Footer Meta with Action -->
            <div class="flex flex-col sm:flex-row items-center justify-between px-8 py-4 bg-slate-900 rounded-2xl border border-slate-800 shadow-sm text-white/50 gap-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-history text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Sinkronisasi Sistem Terakhir</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-[10px] font-mono font-bold">
                        <?= isset($siswa['updated_at']) ? date('d/m/Y - H:i', strtotime($siswa['updated_at'])) : 'BELUM PERNAH' ?>
                    </span>
                    <!-- Small Sync Button for Mobile/Footer Access -->
                    <form action="<?= base_url('app/masterdata/siswa/sync/' . $siswa['id']) ?>" method="POST" class="sm:hidden">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-white hover:text-blue-400 transition-colors">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.5s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
    
    @media (max-width: 640px) {
        .container { padding-bottom: 6rem; }
    }
</style>
<?= $this->endSection() ?>