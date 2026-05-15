<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Profil: <?= esc($karyawan['nama_lengkap']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    /**
     * Manajemen Karyawan - Detail View (Ultra Solid & Mobile Optimized)
     */
    
    // Helper function untuk warna jenjang (Solid Version)
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

    $kj = strtoupper($karyawan['kode_jenjang'] ?? 'GLOBAL');
    $nama_unit = $karyawan['unit_sekolah'] ?? $kj;
    
    $st = strtolower($karyawan['status'] ?? 'aktif');
    $statusClasses = [
        'aktif' => 'bg-emerald-600 text-white shadow-emerald-500/20',
        'cuti' => 'bg-amber-500 text-white shadow-amber-500/20',
        'tidak aktif' => 'bg-rose-600 text-white shadow-rose-500/20'
    ];
    $currentStatusClass = $statusClasses[$st] ?? 'bg-slate-500 text-white';
?>

<div class="container mx-auto px-4 py-6 mb-12 animate-fade-in">
    <!-- Header: Solid & High Contrast -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-xl shadow-slate-200 shrink-0">
                <i class="fas fa-address-card text-2xl"></i>
            </div>
            <div>
                <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-2">
                        <li><a href="<?= base_url('app/masterdata/karyawan') ?>" class="hover:text-blue-600">Database</a></li>
                        <li><i class="fas fa-chevron-right text-[8px] mx-1 opacity-50"></i></li>
                        <li class="text-slate-600">Berkas Profil</li>
                    </ol>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-none">
                    Detail Personel
                </h1>
            </div>
        </div>

        <!-- Action Buttons Solid -->
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/masterdata/karyawan') ?>" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 text-slate-600 text-xs font-black rounded-xl uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-95 shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?= base_url('app/masterdata/karyawan/edit/' . $karyawan['id']) ?>" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-amber-500 text-white text-xs font-black rounded-xl uppercase tracking-widest hover:bg-amber-600 transition-all active:scale-95 shadow-lg shadow-amber-500/30">
                <i class="fas fa-edit text-xs"></i> Ubah Data
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Sidebar: Solid Card Profile -->
        <aside class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden relative">
                <!-- Solid Header Background -->
                <div class="absolute top-0 left-0 w-full h-32 bg-slate-900"></div>
                
                <div class="relative px-6 pt-12 pb-8 text-center">
                    <!-- Photo Placeholder Solid -->
                    <div class="relative inline-block mb-4">
                        <div class="w-32 h-32 rounded-[2rem] bg-white p-1 shadow-2xl mx-auto">
                            <div class="w-full h-full rounded-[1.8rem] bg-slate-100 flex items-center justify-center overflow-hidden border-2 border-slate-50">
                                <?php if (!empty($karyawan['foto'])): ?>
                                    <img src="<?= base_url('uploads/karyawan/' . $karyawan['foto']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user-tie text-5xl text-slate-300"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-2xl border-4 border-white flex items-center justify-center shadow-lg <?= $currentStatusClass ?>">
                            <i class="fas fa-check-circle text-xs"></i>
                        </div>
                    </div>

                    <h2 class="text-xl font-black text-slate-900 leading-tight mb-1 uppercase tracking-tight"><?= esc($karyawan['nama_lengkap']) ?></h2>
                    <div class="flex items-center justify-center gap-2 mb-4">
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg uppercase">NIP: <?= esc($karyawan['nip'] ?: 'NASIONAL') ?></span>
                    </div>
                    
                    <div class="inline-flex items-center px-5 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg <?= getJenjangBadgeColor($kj) ?>">
                        UNIT <?= esc($nama_unit) ?>
                    </div>

                    <!-- Compact Data Grid -->
                    <div class="grid grid-cols-2 gap-3 mt-8 pt-6 border-t border-slate-50">
                        <div class="bg-slate-50 p-3 rounded-2xl text-left border border-slate-100/50">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Staf</label>
                            <span class="text-[10px] font-black uppercase <?= $currentStatusClass ?> px-2 py-1 rounded-lg block text-center shadow-sm">
                                <?= esc($st) ?>
                            </span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-2xl text-left border border-slate-100/50">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gender</label>
                            <div class="text-[11px] font-black text-slate-800 flex items-center justify-center gap-2 h-7">
                                <i class="fas <?= ($karyawan['jenis_kelamin'] == 'L') ? 'fa-mars text-blue-500' : 'fa-venus text-rose-500' ?> text-sm"></i>
                                <?= ($karyawan['jenis_kelamin'] == 'L') ? 'LAKI-LAKI' : 'PEREMPUAN' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Actions Solid -->
            <div class="space-y-3">
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $karyawan['telepon'] ?? '') ?>" 
                   target="_blank" 
                   class="flex items-center gap-4 p-4 bg-emerald-600 text-white rounded-3xl shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 transition-all hover:-translate-y-1 group">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <span class="block text-[9px] font-black text-emerald-100 uppercase tracking-widest opacity-80">Hubungi WhatsApp</span>
                        <span class="text-sm font-black"><?= esc($karyawan['telepon'] ?: 'TIDAK ADA NOMOR') ?></span>
                    </div>
                    <i class="fas fa-chevron-right text-xs opacity-50 group-hover:translate-x-1 transition-transform"></i>
                </a>
                
                <?php if (!empty($karyawan['email'])): ?>
                <div class="flex items-center gap-4 p-4 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-envelope text-lg"></i>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Alamat Email</span>
                        <span class="text-xs font-bold text-slate-700 block truncate"><?= esc($karyawan['email']) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Info: Solid Sections -->
        <main class="lg:col-span-8 space-y-6">
            
            <!-- Section I: Identitas Resmi -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-slate-900 flex items-center justify-between border-b border-white/5">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-white/70">I. Berkas Identitas Negara</h3>
                    <i class="fas fa-fingerprint text-white/20 text-xl"></i>
                </div>
                <div class="p-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-1">
                        <div class="p-6 rounded-2xl bg-slate-50/50">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Induk Kependudukan (NIK)</label>
                            <span class="text-base font-black text-slate-900 tracking-wider"><?= esc($karyawan['nik']) ?></span>
                        </div>
                        <div class="p-6 rounded-2xl bg-blue-50/30">
                            <label class="block text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">Nomor Induk Pegawai (NIP)</label>
                            <span class="text-base font-black text-blue-700 tracking-wider"><?= esc($karyawan['nip'] ?: 'NON-NIP') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-50">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tempat & Tanggal Lahir</label>
                        <p class="text-sm font-bold text-slate-700">
                            <?= esc($karyawan['tempat_lahir'] ?: '-') ?>, 
                            <span class="text-blue-600"><?= isset($karyawan['tanggal_lahir']) ? date('d F Y', strtotime($karyawan['tanggal_lahir'])) : '-' ?></span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Keyakinan / Agama</label>
                        <p class="text-sm font-bold text-slate-700 uppercase"><?= esc($karyawan['agama'] ?: 'ISLAM') ?></p>
                    </div>
                </div>
            </section>

            <!-- Section II: Jabatan & Penugasan -->
            <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-8 py-5 bg-slate-50 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-500">II. Struktur & Karir</h3>
                    <i class="fas fa-sitemap text-slate-300 text-xl"></i>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Jabatan Struktural</label>
                        <div class="text-lg font-black text-slate-900 uppercase tracking-tight"><?= esc($karyawan['jabatan']) ?></div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Mulai Tugas (TMT)</label>
                        <div class="text-lg font-black text-emerald-600">
                            <?= isset($karyawan['tmt_sekolah_induk']) ? date('d M Y', strtotime($karyawan['tmt_sekolah_induk'])) : 'BELUM TERCATAT' ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section III: Domisili -->
            <section class="bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden text-white group">
                <div class="px-8 py-5 bg-white/5 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-400">III. Domisili Saat Ini</h3>
                    <i class="fas fa-map-marked-alt text-slate-600 group-hover:text-blue-500 transition-colors"></i>
                </div>
                <div class="p-8">
                    <p class="text-sm font-medium leading-relaxed text-slate-300 italic">
                        "<?= esc($karyawan['alamat'] ?: 'Data alamat belum diperbarui oleh admin atau personil terkait.') ?>"
                    </p>
                </div>
            </section>

            <!-- Timeline/History Minimalist -->
            <div class="flex items-center justify-between px-8 py-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                        <i class="fas fa-clock-rotate-left text-xs"></i>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Update Terakhir Sistem</span>
                </div>
                <span class="text-[11px] font-mono text-slate-500 font-bold">
                    <?= isset($karyawan['updated_at']) ? date('d/m/Y - H:i', strtotime($karyawan['updated_at'])) : 'DATA PERDANA' ?>
                </span>
            </div>
        </main>
    </div>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
    
    /* Responsive Adjustments */
    @media (max-width: 640px) {
        .container { padding-bottom: 6rem; }
    }
</style>
<?= $this->endSection() ?>