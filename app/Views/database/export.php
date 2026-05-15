<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8 animate-fade-in">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-indigo-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-600">Data Exporter</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                Export <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-600">Data Master</span>
            </h1>
        </div>
        <a href="<?= base_url('app/database') ?>" 
           class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95 rounded-lg">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center rounded-r-lg animate-fade-in-down">
            <div class="bg-emerald-100 p-2 rounded-full mr-3 text-emerald-600"><i class="fas fa-check"></i></div>
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide"><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 shadow-sm flex items-center rounded-r-lg animate-fade-in-down">
            <div class="bg-rose-100 p-2 rounded-full mr-3 text-rose-600"><i class="fas fa-times"></i></div>
            <p class="text-xs font-bold text-rose-800 uppercase tracking-wide"><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <?php
        // Logika Scope Unit untuk Info
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = empty($sessionJenjang) || in_array(strtoupper($sessionJenjang), ['GLOBAL', 'YAYASAN', 'PUSAT']);
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- CARD INFO & INSTRUCTIONS (KIRI) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Info Scope -->
            <div class="bg-slate-900 p-6 rounded-[2rem] border-l-4 <?= $isGlobal ? 'border-indigo-500' : 'border-emerald-500' ?> shadow-lg relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-6 opacity-10">
                    <i class="fas <?= $isGlobal ? 'fa-globe' : 'fa-building-lock' ?> text-8xl text-white transform rotate-12"></i>
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 <?= $isGlobal ? 'bg-indigo-500' : 'bg-emerald-500' ?> rounded-xl flex items-center justify-center text-white shadow-lg">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Cakupan Data</p>
                            <h3 class="text-sm font-black text-white uppercase italic mt-1 leading-none">
                                <?= $isGlobal ? 'DATA GLOBAL / SEMUA UNIT' : 'DATA UNIT: ' . esc($sessionJenjang) ?>
                            </h3>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-[10px] text-slate-300 leading-relaxed font-medium">
                            <?php if ($isGlobal): ?>
                                Anda memiliki akses <strong>Superadmin</strong>. File export akan berisi seluruh data dari semua unit sekolah (TK, SD, SMP, SMA).
                            <?php else: ?>
                                Anda login sebagai <strong>Admin Unit</strong>. File export hanya akan berisi data yang terdaftar pada unit <strong><?= esc($sessionJenjang) ?></strong> saja.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Petunjuk -->
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-md p-6">
                <h4 class="font-black text-xs text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> Informasi Format
                </h4>
                <ul class="text-xs text-slate-600 space-y-3 font-medium">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                        <span>File akan diunduh dalam format <strong>.xlsx</strong> (Excel).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                        <span>Struktur kolom disesuaikan dengan format <strong>Database & Dapodik</strong> untuk kemudahan sinkronisasi (Round-trip compatible).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                        <span>Gunakan fitur ini untuk backup data parsial atau pelaporan.</span>
                    </li>
                </ul>
            </div>

        </div>

        <!-- GRID PILIHAN EXPORT (KANAN) -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <!-- 1. DATA SISWA -->
                <div class="bg-white rounded-2xl border-2 border-slate-100 hover:border-indigo-500 shadow-lg overflow-hidden group transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-user-graduate text-8xl text-indigo-600"></i>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Data Siswa</h3>
                                <p class="text-[10px] text-slate-500 font-bold">Identitas, Keluarga, Rombel</p>
                            </div>
                        </div>
                        
                        <p class="text-[11px] text-slate-600 leading-relaxed mb-6 h-10">
                            Export data lengkap siswa termasuk biodata, demografi, data orang tua (Ayah/Ibu/Wali), dan data akademik (Enrollment).
                        </p>

                        <div class="flex gap-2">
                            <a href="<?= base_url('app/database/template/siswa') ?>" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider text-center rounded-lg transition-colors" title="Download Template Kosong">
                                <i class="fas fa-file-csv mr-1"></i> Template
                            </a>
                            <a href="<?= base_url('app/database/export/siswa') ?>" class="flex-[2] py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black uppercase tracking-widest text-center rounded-lg shadow-lg shadow-indigo-200 transition-all transform active:scale-95">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. DATA PEGAWAI -->
                <div class="bg-white rounded-2xl border-2 border-slate-100 hover:border-emerald-500 shadow-lg overflow-hidden group transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-chalkboard-teacher text-8xl text-emerald-600"></i>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Data Pegawai</h3>
                                <p class="text-[10px] text-slate-500 font-bold">GTK, NUPTK, Jabatan</p>
                            </div>
                        </div>
                        
                        <p class="text-[11px] text-slate-600 leading-relaxed mb-6 h-10">
                            Export data Guru dan Tenaga Kependidikan (GTK) meliputi NUPTK, Status Kepegawaian, dan data pribadi.
                        </p>

                        <div class="flex gap-2">
                            <a href="<?= base_url('app/database/template/pegawai') ?>" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider text-center rounded-lg transition-colors">
                                <i class="fas fa-file-csv mr-1"></i> Template
                            </a>
                            <a href="<?= base_url('app/database/export/pegawai') ?>" class="flex-[2] py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase tracking-widest text-center rounded-lg shadow-lg shadow-emerald-200 transition-all transform active:scale-95">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3. MATA PELAJARAN -->
                <div class="bg-white rounded-2xl border-2 border-slate-100 hover:border-amber-500 shadow-lg overflow-hidden group transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-book text-8xl text-amber-600"></i>
                        </div>
                        
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <i class="fas fa-book"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Mata Pelajaran</h3>
                                <p class="text-[10px] text-slate-500 font-bold">Kurikulum, KKM</p>
                            </div>
                        </div>
                        
                        <p class="text-[11px] text-slate-600 leading-relaxed mb-6 h-10">
                            Export daftar mata pelajaran, kode mapel, kelompok (A/B/C), dan KKM/KKTP yang berlaku.
                        </p>

                        <div class="flex gap-2">
                            <a href="<?= base_url('app/database/template/mata_pelajaran') ?>" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider text-center rounded-lg transition-colors">
                                <i class="fas fa-file-csv mr-1"></i> Template
                            </a>
                            <a href="<?= base_url('app/database/export/mata_pelajaran') ?>" class="flex-[2] py-2 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest text-center rounded-lg shadow-lg shadow-amber-200 transition-all transform active:scale-95">
                                <i class="fas fa-download mr-2"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 4. COMING SOON -->
                <div class="bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 flex flex-col items-center justify-center text-center p-6 opacity-60">
                    <i class="fas fa-database text-4xl text-slate-300 mb-3"></i>
                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Data Lainnya</h5>
                    <p class="text-[10px] text-slate-400 mt-1">Modul export nilai & absensi segera hadir.</p>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.5s ease-out forwards;
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out forwards;
    }
</style>
<?= $this->endSection() ?>