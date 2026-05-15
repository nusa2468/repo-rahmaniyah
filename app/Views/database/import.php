<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8 animate-fade-in">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-emerald-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">Data Importer</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                Import <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Data Master</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- CARD FORM IMPORT (KIRI) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b-2 border-slate-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-file-upload"></i>
                    </div>
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest italic">Form Upload Data Massal</h3>
                </div>
                
                <div class="p-8">
                    <form action="<?= base_url('app/database/import_process') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <!-- Pilihan Tabel -->
                        <div class="mb-8">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">1. Pilih Target Data</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="target_table" value="siswa" class="peer sr-only" checked>
                                    <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-slate-50 transition-all text-center h-full flex flex-col items-center justify-center relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-bl-full -mr-8 -mt-8 transition-all peer-checked:bg-emerald-500/20"></div>
                                        <i class="fas fa-user-graduate text-3xl text-slate-300 peer-checked:text-emerald-600 mb-2 transition-colors"></i>
                                        <span class="block text-xs font-black text-slate-600 peer-checked:text-emerald-800 uppercase tracking-tight">Data Siswa</span>
                                        <span class="text-[9px] text-slate-400 mt-1 font-medium peer-checked:text-emerald-600">Full (Profil+Keluarga+Akademik)</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="target_table" value="pegawai" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-slate-50 transition-all text-center h-full flex flex-col items-center justify-center relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-bl-full -mr-8 -mt-8 transition-all peer-checked:bg-emerald-500/20"></div>
                                        <i class="fas fa-chalkboard-teacher text-3xl text-slate-300 peer-checked:text-emerald-600 mb-2 transition-colors"></i>
                                        <span class="block text-xs font-black text-slate-600 peer-checked:text-emerald-800 uppercase tracking-tight">Data Pegawai</span>
                                        <span class="text-[9px] text-slate-400 mt-1 font-medium peer-checked:text-emerald-600">Format GTK</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="target_table" value="mata_pelajaran" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-slate-50 transition-all text-center h-full flex flex-col items-center justify-center relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-500/10 rounded-bl-full -mr-8 -mt-8 transition-all peer-checked:bg-emerald-500/20"></div>
                                        <i class="fas fa-book text-3xl text-slate-300 peer-checked:text-emerald-600 mb-2 transition-colors"></i>
                                        <span class="block text-xs font-black text-slate-600 peer-checked:text-emerald-800 uppercase tracking-tight">Mata Pelajaran</span>
                                        <span class="text-[9px] text-slate-400 mt-1 font-medium peer-checked:text-emerald-600">Kurikulum</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Upload File -->
                        <div class="mb-8">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">2. Upload File Excel / CSV</label>
                            <div class="relative group">
                                <input type="file" name="file_excel" required accept=".xlsx, .xls, .csv"
                                    class="block w-full text-xs text-slate-500
                                    file:mr-4 file:py-3 file:px-6
                                    file:rounded-l-lg file:border-0
                                    file:text-xs file:font-black file:uppercase file:tracking-widest
                                    file:bg-emerald-600 file:text-white
                                    hover:file:bg-emerald-700 cursor-pointer border-2 border-slate-200 rounded-xl bg-slate-50
                                    "/>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400 font-medium italic flex items-center gap-1">
                                <i class="fas fa-info-circle"></i> Sistem menggunakan logika UPSERT (Update jika ada, Insert jika baru).
                            </p>
                        </div>
                        
                        <?php 
                            $userUnit = session('kode_jenjang');
                            if (!empty($userUnit) && !in_array(strtoupper($userUnit), ['GLOBAL', 'YAYASAN', 'PUSAT'])): 
                        ?>
                            <div class="mb-6 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-lg">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-lock text-amber-500 text-xs"></i>
                                    <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wide">Mode Terbatas: Unit <?= esc($userUnit) ?></p>
                                </div>
                                <p class="text-[10px] text-amber-700 mt-1 leading-snug">Data yang diimport akan otomatis terkunci ke unit <strong><?= esc($userUnit) ?></strong>, mengabaikan kolom unit di dalam file Excel.</p>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-emerald-200 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                            <i class="fas fa-sync-alt"></i> Mulai Proses Sinkronisasi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- CARD INFO & TEMPLATE (KANAN) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Info Panel -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-800 p-6 rounded-2xl shadow-xl text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10">
                    <i class="fas fa-clipboard-list text-8xl transform -rotate-12"></i>
                </div>
                <h4 class="font-black text-sm uppercase tracking-widest mb-4 border-b border-white/20 pb-2 relative z-10">Petunjuk Import</h4>
                <ol class="text-xs font-medium list-decimal pl-4 space-y-3 text-emerald-50 relative z-10 leading-relaxed">
                    <li>Pilih <b>Target Tabel</b> yang sesuai.</li>
                    <li>Unduh <b>Template Terbaru</b> di bawah ini.</li>
                    <li>Isi data tanpa mengubah <b>Header Kolom</b>.</li>
                    <li>Kolom NIS/NIP/Kode Mapel adalah <b>Kunci Unik</b>.</li>
                    <li>Upload file untuk memulai sinkronisasi.</li>
                </ol>
            </div>

            <!-- Template Download -->
            <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-lg p-6">
                <h4 class="font-black text-xs text-slate-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <i class="fas fa-download text-emerald-500"></i> Download Template
                </h4>
                <div class="space-y-3">
                    <a href="<?= base_url('app/database/template/siswa') ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm border border-slate-100">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 group-hover:text-emerald-700">Template Siswa</span>
                                <span class="text-[9px] text-slate-400 font-mono">Full (73 Kolom)</span>
                            </div>
                        </div>
                        <i class="fas fa-cloud-download-alt text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                    
                    <a href="<?= base_url('app/database/template/pegawai') ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm border border-slate-100">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 group-hover:text-emerald-700">Template Pegawai</span>
                                <span class="text-[9px] text-slate-400 font-mono">Format GTK</span>
                            </div>
                        </div>
                        <i class="fas fa-cloud-download-alt text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                    
                    <a href="<?= base_url('app/database/template/mata_pelajaran') ?>" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm border border-slate-100">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-700 group-hover:text-emerald-700">Template Mapel</span>
                                <span class="text-[9px] text-slate-400 font-mono">Kurikulum</span>
                            </div>
                        </div>
                        <i class="fas fa-cloud-download-alt text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
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