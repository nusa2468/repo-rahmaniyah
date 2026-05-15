<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-slate-600"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500">System Core</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Database <span class="text-transparent bg-clip-text bg-gradient-to-r from-slate-700 to-zinc-600">Maintenance</span>
            </h1>
        </div>
    </div>

    <!-- ALERT NOTIFIKASI -->
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

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- KOLOM KIRI: STATUS & BACKUP/RESTORE -->
        <div class="flex flex-col gap-8">
            
            <!-- 1. STATUS DATABASE -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden relative group">
                <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="fas fa-server text-9xl"></i>
                </div>
                <div class="p-8">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="w-2 h-6 bg-slate-500 rounded-full"></span>
                        Informasi Server
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Database</p>
                            <h4 class="text-sm font-bold text-slate-800 mt-1 truncate"><?= esc($db_name) ?></h4>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Platform</p>
                            <h4 class="text-sm font-bold text-slate-800 mt-1"><?= esc($platform) ?> (<?= esc($version) ?>)</h4>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ukuran</p>
                            <h4 class="text-xl font-black text-emerald-600 mt-1"><?= esc($db_size) ?> MB</h4>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Tabel</p>
                            <h4 class="text-xl font-black text-indigo-600 mt-1"><?= count($tables) ?></h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. BACKUP & RESTORE -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden relative">
                <div class="p-8">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                        Backup & Restore
                    </h3>

                    <!-- Tombol Backup -->
                    <div class="mb-8">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Export Data</h4>
                        <p class="text-xs text-slate-500 mb-4">Unduh seluruh data database dalam format .SQL untuk cadangan keamanan.</p>
                        <a href="<?= base_url('app/database/backup') ?>" class="w-full flex items-center justify-center gap-2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-200 transition-all">
                            <i class="fas fa-download"></i> Download Backup (.SQL)
                        </a>
                    </div>

                    <hr class="border-dashed border-slate-200 my-6">

                    <!-- Form Restore -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Import / Restore</h4>
                        <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 mb-4">
                            <div class="flex gap-3">
                                <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
                                <p class="text-[10px] text-rose-800 font-bold leading-relaxed">
                                    PERHATIAN: Proses restore akan menimpa seluruh data saat ini. Pastikan Anda memiliki backup terbaru sebelum melanjutkan.
                                </p>
                            </div>
                        </div>
                        <form action="<?= base_url('app/database/restore') ?>" method="post" enctype="multipart/form-data" onsubmit="return confirm('APAKAH ANDA YAKIN? Data saat ini akan ditimpa!');">
                            <?= csrf_field() ?>
                            <input type="file" name="backup_file" required accept=".sql" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 mb-4 border border-slate-200 rounded-xl p-1">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-200 transition-all">
                                <i class="fas fa-upload"></i> Restore Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: IMPORT DATA MASSAL -->
        <div class="flex flex-col gap-8">
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-[2rem] shadow-xl text-white p-8 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:opacity-20 transition-opacity">
                    <i class="fas fa-file-excel text-9xl transform rotate-12"></i>
                </div>
                
                <h3 class="text-lg font-black mb-2 flex items-center gap-3 relative z-10">
                    <i class="fas fa-file-import"></i> Import Data Massal
                </h3>
                <p class="text-xs text-emerald-100 mb-8 relative z-10 font-medium leading-relaxed">
                    Gunakan fitur ini untuk memasukkan data Siswa, Pegawai, atau Mata Pelajaran dalam jumlah banyak sekaligus menggunakan file Excel/CSV.
                </p>

                <form action="<?= base_url('app/database/import') ?>" method="post" enctype="multipart/form-data" class="relative z-10 space-y-6">
                    <?= csrf_field() ?>

                    <!-- Pilihan Tabel -->
                    <div class="bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20">
                        <label class="block text-[10px] font-black uppercase tracking-widest mb-3 text-emerald-100">1. Pilih Target Data</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-white/10 rounded-lg transition-colors">
                                <input type="radio" name="target_table" value="siswa" checked class="text-emerald-500 focus:ring-0">
                                <span class="text-xs font-bold">Data Siswa</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-white/10 rounded-lg transition-colors">
                                <input type="radio" name="target_table" value="pegawai" class="text-emerald-500 focus:ring-0">
                                <span class="text-xs font-bold">Data Pegawai</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer p-2 hover:bg-white/10 rounded-lg transition-colors">
                                <input type="radio" name="target_table" value="mata_pelajaran" class="text-emerald-500 focus:ring-0">
                                <span class="text-xs font-bold">Data Mata Pelajaran</span>
                            </label>
                        </div>
                    </div>

                    <!-- Upload File -->
                    <div class="bg-white/10 p-4 rounded-xl backdrop-blur-sm border border-white/20">
                        <label class="block text-[10px] font-black uppercase tracking-widest mb-3 text-emerald-100">2. Upload File (Excel/CSV)</label>
                        <input type="file" name="import_file" required accept=".xlsx, .xls, .csv" class="block w-full text-xs text-emerald-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-white file:text-emerald-700 hover:file:bg-emerald-50 transition-all cursor-pointer">
                    </div>

                    <button type="submit" class="w-full py-4 bg-white text-emerald-700 hover:bg-emerald-50 text-xs font-black uppercase tracking-widest rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Proses Import
                    </button>
                </form>
            </div>

            <!-- Download Template -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-lg p-8">
                <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-6">Download Template Import</h3>
                <div class="space-y-3">
                    <a href="<?= base_url('app/database/template/siswa') ?>" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm transition-colors">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-700">Template Siswa</span>
                        </div>
                        <i class="fas fa-download text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                    <a href="<?= base_url('app/database/template/pegawai') ?>" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm transition-colors">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-700">Template Pegawai</span>
                        </div>
                        <i class="fas fa-download text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                    <a href="<?= base_url('app/database/template/mata_pelajaran') ?>" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-100 rounded-xl hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm transition-colors">
                                <i class="fas fa-book"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-emerald-700">Template Mapel</span>
                        </div>
                        <i class="fas fa-download text-slate-300 group-hover:text-emerald-500"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>