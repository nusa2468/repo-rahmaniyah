<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8 animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-slate-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">System Core</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Database <span class="text-transparent bg-clip-text bg-gradient-to-r from-slate-600 to-zinc-600">Maintenance</span>
            </h1>
        </div>
        
        <!-- BUTTON ACTIONS -->
        <div class="flex flex-wrap gap-3">
             <a href="<?= base_url('app/database/export') ?>" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:scale-105">
                <i class="fas fa-file-export mr-2"></i> Export Master (Excel)
            </a>
             <a href="<?= base_url('app/database/import') ?>" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:scale-105">
                <i class="fas fa-file-import mr-2"></i> Import Master (Excel)
            </a>
        </div>
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

    <!-- MAIN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- CARD 1: STATUS DATABASE & BACKUP -->
        <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden relative group">
            <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="fas fa-database text-9xl"></i>
            </div>
            
            <div class="p-8">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                    <span class="w-2 h-6 bg-indigo-500 rounded-full"></span>
                    Status & Backup
                </h3>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Nama Database</p>
                        <h4 class="text-sm font-bold text-slate-800 mt-1 truncate"><?= esc($db_name) ?></h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Platform</p>
                        <!-- FIX: Menggunakan variabel yang dikirim controller -->
                        <h4 class="text-sm font-bold text-slate-800 mt-1"><?= esc($platform ?? '-') ?> (<?= esc($version ?? '-') ?>)</h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Ukuran (Estimasi)</p>
                        <h4 class="text-xl font-black text-emerald-600 mt-1"><?= esc($db_size) ?> MB</h4>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Tabel</p>
                        <h4 class="text-xl font-black text-slate-800 mt-1"><?= count($tables) ?> Tabel</h4>
                    </div>
                </div>

                <!-- Bagian Backup (Hanya Global Admin) -->
                <?php if ($isGlobal): ?>
                <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-2xl">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl">
                            <i class="fas fa-download text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-indigo-900 text-sm">Full Backup Database</h4>
                            <p class="text-xs text-indigo-700 mt-1 mb-4 leading-relaxed">
                                Unduh seluruh struktur dan data database dalam format <strong>.SQL</strong>. Disarankan dilakukan rutin.
                            </p>
                            <a href="<?= base_url('app/database/backup') ?>" class="inline-flex w-full justify-center items-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-lg shadow-md transition-all">
                                <i class="fas fa-cloud-download-alt mr-2"></i> Download Backup (.SQL)
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl text-center">
                    <i class="fas fa-lock text-slate-300 text-3xl mb-2"></i>
                    <p class="text-xs text-slate-500 font-bold">Akses Backup Full dibatasi untuk Admin Unit.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 2: RESTORE DATABASE -->
        <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden relative group">
             <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="fas fa-history text-9xl text-rose-500"></i>
            </div>

            <div class="p-8">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                    <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                    Restore System
                </h3>

                <?php if ($isGlobal): ?>
                <div class="bg-rose-50 border border-rose-100 rounded-2xl p-6 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fas fa-exclamation-triangle text-rose-500 text-2xl"></i>
                        <h4 class="font-bold text-rose-900 uppercase tracking-wider text-sm">Perhatian Keras!</h4>
                    </div>
                    <p class="text-xs text-rose-800 leading-relaxed font-medium mb-2">
                        Proses restore akan <strong>MENGHAPUS SELURUH DATA</strong> saat ini dan menggantinya dengan data dari file backup (.SQL).
                    </p>
                    <ul class="text-[10px] text-rose-700 list-disc pl-4 font-bold">
                        <li>Pastikan Anda sudah Backup data terkini.</li>
                        <li>Proses ini tidak dapat dibatalkan.</li>
                    </ul>
                </div>

                <form action="<?= base_url('app/database/restore') ?>" method="post" enctype="multipart/form-data" id="formRestore">
                    <?= csrf_field() ?>
                    
                    <!-- INPUT FILE -->
                    <div class="mb-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">1. Pilih File Backup (.sql)</label>
                        <input type="file" name="backup_file" required accept=".sql"
                            class="block w-full text-xs text-slate-500
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-xl file:border-0
                                file:text-[10px] file:font-black file:uppercase file:tracking-widest
                                file:bg-slate-100 file:text-slate-700
                                hover:file:bg-slate-200 cursor-pointer border border-slate-200 rounded-xl p-1
                            "/>
                    </div>

                    <!-- INPUT KONFIRMASI -->
                    <div class="mb-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">2. Konfirmasi Keamanan</label>
                        <input type="text" id="confirmKeyword" placeholder="Ketik: RESTORE-DATABASE"
                               class="block w-full px-4 py-3 bg-white border-2 border-rose-200 rounded-xl text-sm font-bold text-rose-600 focus:border-rose-500 focus:ring-rose-500 placeholder:text-slate-300 placeholder:font-normal uppercase tracking-widest text-center transition-all"
                               onkeyup="checkConfirmation()" autocomplete="off">
                        <p class="text-[9px] text-slate-400 mt-2 text-center italic">Wajib diketik manual untuk mengaktifkan tombol.</p>
                    </div>

                    <button type="submit" id="btnRestore" disabled
                            class="w-full py-4 bg-slate-300 text-slate-500 font-black text-xs uppercase tracking-widest rounded-xl transition-all cursor-not-allowed flex items-center justify-center gap-2">
                        <i class="fas fa-lock"></i> Jalankan Restore
                    </button>
                </form>
                <?php else: ?>
                <div class="h-full flex flex-col items-center justify-center text-center p-8 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                    <i class="fas fa-user-shield text-slate-300 text-5xl mb-4"></i>
                    <h5 class="text-sm font-black text-slate-600 uppercase tracking-widest">Akses Dibatasi</h5>
                    <p class="text-xs text-slate-400 mt-1 max-w-xs">Hanya Administrator Pusat/Yayasan yang diizinkan melakukan pemulihan database penuh.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    function checkConfirmation() {
        const input = document.getElementById('confirmKeyword');
        const btn = document.getElementById('btnRestore');
        
        if (input.value === 'RESTORE-DATABASE') {
            btn.disabled = false;
            btn.classList.remove('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
            btn.classList.add('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'shadow-lg', 'shadow-rose-200', 'transform', 'active:scale-95');
            btn.innerHTML = '<i class="fas fa-upload"></i> Jalankan Restore';
        } else {
            btn.disabled = true;
            btn.classList.add('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
            btn.classList.remove('bg-rose-600', 'hover:bg-rose-700', 'text-white', 'shadow-lg', 'shadow-rose-200', 'transform', 'active:scale-95');
            btn.innerHTML = '<i class="fas fa-lock"></i> Jalankan Restore';
        }
    }
</script>

<style>
    @keyframes fade-in-down {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.5s ease-out forwards;
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-out forwards;
    }
</style>
<?= $this->endSection() ?>