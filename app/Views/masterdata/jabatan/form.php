<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// Normalisasi Data: Pastikan $jabatan adalah array
$jabatan = (array) ($jabatan ?? []);
?>

<div class="container-fluid mb-6 px-4">
    <div class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 shadow-lg mb-6 group">
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/5 blur-3xl transition-all group-hover:bg-white/10"></div>
        <div class="relative z-10 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/10 backdrop-blur-md border border-white/20">
                    <i class="fas fa-sitemap text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight leading-none mb-1"><?= esc($title) ?></h1>
                    <p class="text-slate-300 text-[11px] font-medium opacity-80 max-w-sm leading-tight">Konfigurasi struktur hirarki dan relasi jabatan organisasi.</p>
                </div>
            </div>
            <div class="flex shrink-0">
                <a href="<?= base_url('app/masterdata/jabatan') ?>" class="inline-flex items-center gap-2 bg-slate-600/50 backdrop-blur-sm border border-slate-500/50 px-5 py-2.5 rounded-xl text-white font-black uppercase tracking-wider text-[10px] hover:bg-slate-500 transition-all active:scale-95 no-underline">
                    <i class="fas fa-arrow-left text-sm"></i> Kembali Ke Daftar
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                    <div class="flex items-center gap-3">
                        <span class="h-8 w-1 bg-indigo-600 rounded-full"></span>
                        <h6 class="text-[11px] font-black text-gray-800 uppercase tracking-[0.15em]">Informasi Jabatan</h6>
                    </div>
                    <?php if (!empty($jabatan['id'])) : ?>
                        <div class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[9px] font-black border border-indigo-100 uppercase tracking-widest">
                            Mode Edit: #<?= $jabatan['id'] ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-8 md:p-10">
                    <form action="<?= base_url('app/masterdata/jabatan/save') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $jabatan['id'] ?? '' ?>">

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                            <div class="md:col-span-8">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama Jabatan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                        <i class="fas fa-signature text-sm"></i>
                                    </div>
                                    <input type="text" name="nama_jabatan" 
                                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all outline-none" 
                                           value="<?= esc($jabatan['nama_jabatan'] ?? '') ?>" 
                                           placeholder="Misal: Kepala Sekolah SMA" required>
                                </div>
                            </div>
                            
                            <div class="md:col-span-4">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Level Struktur</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                        <i class="fas fa-layer-group text-sm"></i>
                                    </div>
                                    <input type="number" name="level" 
                                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all outline-none" 
                                           value="<?= esc($jabatan['level'] ?? '1') ?>" min="1" required>
                                </div>
                                <p class="mt-2 text-[9px] font-medium text-slate-400 italic px-1">
                                    <i class="fas fa-info-circle mr-1 text-indigo-400"></i> Angka kecil = posisi lebih tinggi.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Dropdown Unit -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Unit / Jenjang Kerja</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300 z-10">
                                        <i class="fas fa-school text-sm"></i>
                                    </div>
                                    <select name="kode_jenjang" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all outline-none appearance-none relative z-0" required>
                                        <?php $curJenjang = $jabatan['kode_jenjang'] ?? ''; ?>
                                        <?php foreach ($list_jenjang as $lj) : ?>
                                            <?php $lj = (array)$lj; // FIX Casting ?>
                                            <option value="<?= $lj['kode_jenjang'] ?>" <?= ($curJenjang == $lj['kode_jenjang']) ? 'selected' : '' ?>>
                                                <?= ($lj['kode_jenjang'] == 'Global') ? '🏛️ GLOBAL' : '🏫 UNIT ' . strtoupper($lj['kode_jenjang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Atasan -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Atasan Langsung</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300 z-10">
                                        <i class="fas fa-user-tie text-sm"></i>
                                    </div>
                                    <select name="atasan" class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border-0 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all outline-none appearance-none relative z-0">
                                        <option value="">-- Tanpa Atasan (Top Level) --</option>
                                        <?php $curAtasan = $jabatan['atasan'] ?? ''; ?>
                                        <?php foreach ($list_atasan as $la) : ?>
                                            <?php $la = (array)$la; // FIX Casting ?>
                                            <option value="<?= $la['id'] ?>" <?= ($curAtasan == $la['id']) ? 'selected' : '' ?>>
                                                <?= esc($la['nama_jabatan']) ?> [<?= esc($la['kode_jenjang']) ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-50 flex flex-col md:flex-row items-center justify-between gap-6">
                            <div class="flex items-center gap-3 px-2">
                                <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                                    <i class="fas fa-shield-alt text-sm"></i>
                                </div>
                                <div class="max-w-[240px]">
                                    <p class="text-[9px] font-bold text-slate-500 leading-tight uppercase tracking-tight">Data Sinkronisasi</p>
                                    <p class="text-[8px] font-medium text-slate-400 leading-tight italic">Relasi hirarki akan mempengaruhi laporan dan workflow persetujuan secara otomatis.</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full md:w-auto px-10 py-4 bg-indigo-600 rounded-2xl text-white text-[11px] font-black uppercase tracking-[0.2em] shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all active:scale-95 flex items-center justify-center gap-3">
                                <i class="fas fa-save text-sm"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Prevent blue highlight on mobile tap */
    input, select, button { -webkit-tap-highlight-color: transparent; }
    
    /* Animation for the card */
    .row > div {
        animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<?= $this->endSection() ?>