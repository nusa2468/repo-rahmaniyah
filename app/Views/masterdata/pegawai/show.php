<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Profil Pegawai: <?= esc($pegawai['nama_lengkap']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    // --- PREPARE DATA ---
    $p = (array) $pegawai;
    $docs = $dokumen ?? [];
    $edu = $pendidikan ?? []; 
    $career = $riwayat_kepegawaian ?? []; 
    
    // Helper Nama Lengkap dengan Gelar
    $gDepan = $p['gelar_depan'] ?? '';
    $gBelakang = $p['gelar_belakang'] ?? '';
    $fullname = trim(($gDepan ? $gDepan . ' ' : '') . $p['nama_lengkap'] . ($gBelakang ? ', ' . $gBelakang : ''));
    
    // Helper Badge Warna & Style
    $statusColor = ($p['status_aktif'] == 'aktif') ? 'bg-emerald-500 shadow-emerald-200' : 'bg-rose-500 shadow-rose-200';
    $jenisColor  = ($p['jenis_pegawai'] == 'guru') ? 'bg-indigo-600' : 'bg-amber-500';

    // --- LOGIKA PENCARIAN FOTO CERDAS (SMART PHOTO LOOKUP) ---
    $fotoSrc = '';
    
    // 1. Cek Foto Utama di Folder Public (Prioritas Tertinggi)
    $fotoName = $p['foto'] ?? '';
    if (!empty($fotoName) && $fotoName !== 'default.png' && file_exists(FCPATH . 'uploads/pegawai/' . $fotoName)) {
        $fotoSrc = base_url('uploads/pegawai/' . $fotoName);
    } 
    // 2. Jika Gagal, Cari di Arsip Dokumen (Fallback ke Writable via Controller)
    else {
        foreach ($docs as $d) {
            // Cari dokumen dengan jenis FOTO
            if (in_array(strtoupper($d['jenis_dokumen']), ['FOTO', 'PAS FOTO', 'FOTO PROFIL'])) {
                // Gunakan URL Controller untuk memuat gambar dari folder writable
                $fotoSrc = base_url('app/masterdata/pegawai/download_dokumen/' . $d['id']);
                break;
            }
        }
    }
    
    // 3. Fallback Terakhir (Avatar Inisial)
    if (empty($fotoSrc)) {
        $fotoSrc = 'https://ui-avatars.com/api/?name=' . urlencode($p['nama_lengkap']) . '&background=cbd5e1&color=64748b&size=256&bold=true';
    }
?>

<div class="container-fluid mb-10 px-4" x-data="{ activeTab: 'profile' }">

    <!-- ======================================================================= -->
    <!-- 1. HERO HEADER PROFILE -->
    <!-- ======================================================================= -->
    <div class="relative rounded-[2.5rem] overflow-hidden bg-slate-900 shadow-2xl mb-8">
        <!-- Background Decor -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-600/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

        <div class="relative z-10 px-8 py-10 flex flex-col md:flex-row items-center md:items-end gap-8">
            
            <!-- A. Avatar Section (FIXED IMAGE SOURCE) -->
            <div class="relative group">
                <div class="w-32 h-32 rounded-[2rem] bg-white p-1.5 shadow-2xl rotate-3 group-hover:rotate-0 transition-transform duration-300">
                    <div class="w-full h-full rounded-[1.7rem] bg-slate-200 overflow-hidden flex items-center justify-center text-slate-400 relative">
                        <!-- TAMPILKAN FOTO HASIL LOGIKA DI ATAS -->
                        <img src="<?= $fotoSrc ?>" class="w-full h-full object-cover absolute inset-0" alt="Foto Profil">
                    </div>
                </div>
                <!-- Status Badge -->
                <div class="absolute -bottom-2 -right-2 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest text-white shadow-lg border-2 border-slate-900 <?= $statusColor ?>">
                    <?= esc($p['status_aktif']) ?>
                </div>
            </div>

            <!-- B. Identity Info -->
            <div class="flex-1 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-white/10 text-white text-[10px] font-bold uppercase tracking-widest mb-3 backdrop-blur-md border border-white/10">
                    <span class="w-2 h-2 rounded-full <?= $jenisColor ?>"></span>
                    <?= strtoupper($p['jenis_pegawai']) ?>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight leading-tight mb-2">
                    <?= esc($fullname) ?>
                </h1>
                <div class="flex flex-wrap justify-center md:justify-start gap-4 text-slate-400 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-id-card-alt text-indigo-400"></i>
                        <span>NIP/NIK: <span class="text-white font-mono"><?= esc($p['nip'] ?: ($p['nipy'] ?: $p['nik'])) ?></span></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-building text-emerald-400"></i>
                        <span>Unit: <span class="text-white font-bold">Unit <?= esc($p['kode_jenjang']) ?></span></span>
                    </div>
                    <?php if(!empty($p['jenis_ptk'])): ?>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-briefcase text-amber-400"></i>
                        <span><?= esc($p['jenis_ptk']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- C. Action Buttons -->
            <div class="flex flex-col gap-3 min-w-[140px]">
                <a href="<?= base_url('app/masterdata/pegawai/edit/' . $p['id']) ?>" class="px-6 py-3 bg-white text-slate-900 rounded-xl font-bold text-xs uppercase tracking-wider shadow-lg hover:bg-indigo-50 transition-all text-center no-underline">
                    <i class="fas fa-pen mr-2"></i> Edit Profil
                </a>
                <a href="<?= base_url('app/masterdata/pegawai') ?>" class="px-6 py-3 bg-slate-800 text-slate-300 border border-slate-700 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-700 transition-all text-center no-underline">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- 2. CONTENT TABS & PANELS -->
    <!-- ======================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT SIDEBAR / NAVIGATION -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-slate-200 dark:border-gray-800 overflow-hidden sticky top-6">
                <nav class="flex flex-col p-2 gap-1">
                    <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-gray-800'"
                            class="px-4 py-3 rounded-xl text-left text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-indigo-500"><i class="fas fa-user"></i></div>
                        Biodata Diri
                    </button>
                    <button @click="activeTab = 'job'" 
                            :class="activeTab === 'job' ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-gray-800'"
                            class="px-4 py-3 rounded-xl text-left text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-amber-500"><i class="fas fa-briefcase"></i></div>
                        Status Kepegawaian
                    </button>
                    <button @click="activeTab = 'education'" 
                            :class="activeTab === 'education' ? 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-gray-800'"
                            class="px-4 py-3 rounded-xl text-left text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-sky-500"><i class="fas fa-graduation-cap"></i></div>
                        Riwayat Pendidikan
                    </button>
                    <button @click="activeTab = 'career'" 
                            :class="activeTab === 'career' ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-gray-800'"
                            class="px-4 py-3 rounded-xl text-left text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-purple-500"><i class="fas fa-history"></i></div>
                        Riwayat Karir
                    </button>
                    <button @click="activeTab = 'docs'" 
                            :class="activeTab === 'docs' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-gray-800'"
                            class="px-4 py-3 rounded-xl text-left text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center text-emerald-500"><i class="fas fa-folder-open"></i></div>
                        Arsip Dokumen 
                        <span class="ml-auto bg-slate-200 dark:bg-gray-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded text-[10px]"><?= count($docs) ?></span>
                    </button>
                </nav>
            </div>

            <!-- CONTACT CARD -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 relative z-10">Kontak Cepat</h4>
                <ul class="space-y-4 relative z-10">
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shadow-inner"><i class="fas fa-phone-alt text-xs"></i></div>
                        <span class="text-sm font-bold font-mono tracking-wide"><?= esc($p['no_hp'] ?: '-') ?></span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center shadow-inner"><i class="fas fa-envelope text-xs"></i></div>
                        <span class="text-sm font-bold truncate max-w-[180px]" title="<?= esc($p['email']) ?>"><?= esc($p['email'] ?: '-') ?></span>
                    </li>
                </ul>
                <?php if($p['no_hp']): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['no_hp']) ?>" target="_blank" class="mt-6 flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg hover:shadow-emerald-500/40 hover:-translate-y-0.5 no-underline relative z-10">
                    <i class="fab fa-whatsapp text-lg"></i> Hubungi
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT CONTENT PANELS -->
        <div class="lg:col-span-9 space-y-6">
            
            <!-- =============================================================== -->
            <!-- PANEL 1: BIODATA -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'profile'" x-transition:enter.opacity.duration.300ms>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-8 shadow-sm">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2"><i class="fas fa-id-card text-indigo-500"></i> Detail Identitas</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Lengkap</label>
                                <p class="text-base font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2"><?= esc($fullname) ?></p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">NIK (KTP)</label>
                                <p class="text-base font-bold text-slate-800 dark:text-white font-mono border-b border-slate-100 pb-2"><?= esc($p['nik']) ?></p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tempat, Tanggal Lahir</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2">
                                    <?= esc($p['tempat_lahir'] ?: '-') ?>, <?= ($p['tanggal_lahir']) ? date('d F Y', strtotime($p['tanggal_lahir'])) : '-' ?>
                                </p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jenis Kelamin</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2">
                                    <?= ($p['jenis_kelamin'] == 'L') ? '<i class="fas fa-mars text-blue-500 mr-2"></i> Laki-laki' : '<i class="fas fa-venus text-pink-500 mr-2"></i> Perempuan' ?>
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Ibu Kandung</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2"><?= esc($p['nama_ibu_kandung'] ?: '-') ?></p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Agama</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2"><?= esc($p['agama'] ?: '-') ?></p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Perkawinan</label>
                                <p class="text-sm font-bold text-slate-800 dark:text-white border-b border-slate-100 pb-2"><?= esc($p['status_perkawinan'] ?: '-') ?></p>
                            </div>
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Alamat Tinggal</label>
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300 italic bg-slate-50 dark:bg-gray-800 p-3 rounded-lg">
                                    <?= esc($p['alamat_jalan'] ?: 'Alamat belum diisi') ?>
                                    <br>
                                    <span class="text-xs">
                                        <?= esc($p['desa_kelurahan'] ? "Kel. {$p['desa_kelurahan']}" : '') ?> 
                                        <?= esc($p['kecamatan'] ? "Kec. {$p['kecamatan']}" : '') ?> 
                                        <?= esc($p['kode_pos']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- PANEL 2: KEPEGAWAIAN -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'job'" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-8 shadow-sm">
                 <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2"><i class="fas fa-briefcase text-amber-500"></i> Data Kepegawaian</h3>

                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                        <p class="text-[10px] font-black text-indigo-400 uppercase mb-1">NUPTK (Pendidik)</p>
                        <p class="text-lg font-black text-indigo-700 font-mono"><?= esc($p['nuptk'] ?: '-') ?></p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">NIP (PNS)</p>
                        <p class="text-lg font-black text-slate-700 font-mono"><?= esc($p['nip'] ?: '-') ?></p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <p class="text-[10px] font-black text-emerald-400 uppercase mb-1">NIP Yayasan</p>
                        <p class="text-lg font-black text-emerald-700 font-mono"><?= esc($p['nipy'] ?: '-') ?></p>
                    </div>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Kepegawaian</label>
                        <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase"><?= esc($p['status_kepegawaian'] ?: '-') ?></span>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jenis PTK / Jabatan</label>
                        <p class="text-sm font-bold text-slate-800 dark:text-white"><?= esc($p['jenis_ptk'] ?: '-') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tugas Tambahan</label>
                        <p class="text-sm font-bold text-indigo-600"><?= esc($p['tugas_tambahan'] ?: '-') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">SK Pengangkatan Terakhir</label>
                        <p class="text-xs font-mono text-slate-600 bg-slate-50 p-2 rounded border border-slate-100"><?= esc($p['sk_pengangkatan'] ?: '-') ?></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">TMT Pengangkatan</label>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">
                            <?= ($p['tmt_pengangkatan']) ? date('d F Y', strtotime($p['tmt_pengangkatan'])) : '-' ?>
                        </p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Sumber Gaji</label>
                        <p class="text-sm font-bold text-slate-800 dark:text-white"><?= esc($p['sumber_gaji'] ?: '-') ?></p>
                    </div>
                 </div>
            </div>

            <!-- =============================================================== -->
            <!-- PANEL 3: PENDIDIKAN -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'education'" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2"><i class="fas fa-graduation-cap text-sky-500"></i> Pendidikan Formal</h3>
                
                <?php if(empty($edu)): ?>
                    <div class="text-center py-8 text-slate-400 text-sm italic">Belum ada data riwayat pendidikan.</div>
                <?php else: ?>
                    <div class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-3 space-y-8">
                        <?php foreach($edu as $e): $e = (array)$e; ?>
                            <div class="relative pl-6">
                                <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-sky-500"></div>
                                <div class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wide">
                                    <?= esc($e['jenjang']) ?> - <?= esc($e['jurusan'] ?: 'Umum') ?>
                                </div>
                                <div class="text-xs font-bold text-slate-500 mt-1"><?= esc($e['nama_sekolah']) ?></div>
                                <div class="text-[10px] font-mono text-slate-400 mt-1">
                                    Lulus Tahun: <?= esc($e['tahun_lulus']) ?> | Nilai: <?= esc($e['nilai_akhir'] ?: '-') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- =============================================================== -->
            <!-- PANEL 4: KARIR -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'career'" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-8 shadow-sm">
                 <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2"><i class="fas fa-history text-purple-500"></i> Riwayat Karir</h3>
                 
                 <?php if(empty($career)): ?>
                    <div class="text-center py-8 text-slate-400 text-sm italic">Belum ada data riwayat karir/SK.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="px-4 py-3">Jenis SK / Nomor</th>
                                    <th class="px-4 py-3">TMT</th>
                                    <th class="px-4 py-3">Jabatan / Pangkat</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($career as $c): $c = (array)$c; ?>
                                    <tr class="<?= ($c['is_aktif'] == 1) ? 'bg-indigo-50/50' : '' ?>">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-800"><?= esc($c['jenis_sk']) ?></div>
                                            <div class="text-[10px] font-mono text-slate-500"><?= esc($c['no_sk']) ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <?= date('d/m/Y', strtotime($c['tmt_sk'])) ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs"><?= esc($c['jabatan_fungsional'] ?: '-') ?></div>
                                            <div class="text-[10px] text-slate-400"><?= esc($c['pangkat_golongan'] ?: '-') ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <?= ($c['is_aktif']==1) ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Aktif</span>' : '<span class="text-[10px] text-slate-400">Arsip</span>' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- =============================================================== -->
            <!-- PANEL 5: DOKUMEN -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'docs'" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2"><i class="fas fa-folder-open text-emerald-500"></i> Dokumen Digital</h3>
                
                <?php if(empty($docs)): ?>
                    <div class="text-center py-12 border-2 border-dashed border-slate-100 rounded-2xl">
                        <i class="fas fa-file-invoice text-4xl text-slate-300 mb-3"></i>
                        <p class="text-slate-500 text-sm font-medium">Belum ada dokumen yang diunggah.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach($docs as $doc): ?>
                            <div class="flex items-center p-4 bg-slate-50 dark:bg-gray-800 rounded-xl border border-slate-100 dark:border-gray-700 justify-between group hover:border-indigo-200 transition-colors">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded bg-white dark:bg-gray-700 flex items-center justify-center font-bold text-xs shadow-sm uppercase <?= ($doc['tipe_file'] == 'pdf') ? 'text-rose-500' : 'text-blue-500' ?>">
                                        <?= esc($doc['tipe_file']) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black truncate text-slate-800 dark:text-white"><?= esc($doc['jenis_dokumen']) ?></p>
                                        <p class="text-[10px] text-slate-500 truncate"><?= esc($doc['nama_file']) ?></p>
                                        <div class="text-[9px] font-mono text-slate-300">
                                            <?= number_format($doc['ukuran_file']) ?> KB • <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <a href="<?= base_url('app/masterdata/pegawai/download_dokumen/' . $doc['id']) ?>" class="text-indigo-600 hover:text-indigo-800 text-xs font-bold bg-indigo-50 px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all">Unduh</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</div>
<?= $this->endSection() ?>