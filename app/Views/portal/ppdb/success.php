<?= $this->extend('layout/portal_layout') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- STEPPER: Progress Selesai -->
    <div class="mb-12 max-w-3xl mx-auto">
        <div class="relative flex items-center justify-between">
            <!-- Progress Bar Background -->
            <div class="absolute left-0 top-1/2 w-full h-0.5 bg-gray-200 dark:bg-gray-700 -translate-y-1/2"></div>
            <!-- Progress Bar Active (Full) -->
            <div class="absolute left-0 top-1/2 w-full h-0.5 bg-blue-600 -translate-y-1/2 transition-all duration-500"></div>

            <!-- Step 1: Info (Done) -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg shadow-blue-500/30">
                    <i class="fas fa-check"></i>
                </div>
                <span class="absolute -bottom-7 text-[10px] font-bold text-blue-600 uppercase tracking-wider">Informasi</span>
            </div>

            <!-- Step 2: Form (Done) -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg shadow-blue-500/30">
                    <i class="fas fa-check"></i>
                </div>
                <span class="absolute -bottom-7 text-[10px] font-bold text-blue-600 uppercase tracking-wider">Registrasi</span>
            </div>

            <!-- Step 3: Finish (Active) -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center bg-white dark:bg-gray-800 border-2 border-blue-600 text-blue-600 rounded-full shadow-xl animate-bounce-slow">
                    <span class="font-bold">03</span>
                </div>
                <span class="absolute -bottom-7 text-[10px] font-bold text-blue-600 uppercase tracking-wider">Selesai</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex items-center justify-center py-4">
        <div class="max-w-lg w-full text-center">
            
            <!-- SUCCESS CARD -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-2xl shadow-blue-500/10 border border-gray-100 dark:border-white/5 p-8 md:p-12 relative overflow-hidden">
                
                <!-- Decor Header -->
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-400 to-teal-500"></div>

                <!-- Animated Icon -->
                <div class="mx-auto flex items-center justify-center h-28 w-28 rounded-full bg-emerald-50 dark:bg-emerald-500/10 mb-8 animate-in zoom-in duration-500">
                    <i class="fas fa-check text-5xl text-emerald-500"></i>
                </div>

                <!-- Title & Desc -->
                <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-3 tracking-tight">Pendaftaran Berhasil!</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-10 leading-relaxed text-lg">
                    Selamat, data pendaftaran Anda telah berhasil kami terima dan sedang dalam antrean verifikasi.
                </p>

                <!-- Registration Number Box -->
                <div class="mb-10 relative group">
                    <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-[0.2em] block mb-3">Nomor Pendaftaran Anda</span>
                    
                    <div class="bg-gray-50 dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-5 relative transition-all duration-300 group-hover:border-blue-400 group-hover:bg-blue-50/30 dark:group-hover:bg-blue-900/10">
                        <span class="text-3xl md:text-4xl font-mono font-black text-blue-600 dark:text-blue-400 tracking-widest select-all">
                            <?= is_object($siswa) ? esc($siswa->no_pendaftaran) : esc($siswa['no_pendaftaran']) ?>
                        </span>
                        
                        <!-- Tooltip hint -->
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-white dark:bg-gray-800 px-3 py-0.5 rounded-full border border-gray-100 dark:border-gray-700 shadow-sm">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Simpan Ini</span>
                        </div>
                    </div>

                    <p class="text-xs text-red-500 mt-3 font-bold flex items-center justify-center gap-1.5 bg-red-50 dark:bg-red-500/10 py-2 rounded-lg mx-auto max-w-xs">
                        <i class="fas fa-camera"></i> Mohon screenshot atau catat nomor ini!
                    </p>
                </div>

                <!-- Next Steps -->
                <div class="text-left bg-gray-50 dark:bg-gray-900/50 rounded-3xl p-6 mb-8 border border-gray-100 dark:border-white/5">
                    <h6 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider mb-5 flex items-center gap-2">
                        <i class="fas fa-list-ol text-blue-500"></i> Langkah Selanjutnya
                    </h6>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold mt-0.5 shadow-md shadow-blue-500/30">1</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300 font-medium leading-tight">Cetak bukti pendaftaran (jika tersedia).</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold mt-0.5 shadow-md shadow-blue-500/30">2</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300 font-medium leading-tight">Lakukan pembayaran biaya pendaftaran (jika ada tagihan).</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold mt-0.5 shadow-md shadow-blue-500/30">3</span>
                            <span class="text-sm text-gray-600 dark:text-gray-300 font-medium leading-tight">Pantau status kelulusan secara berkala di portal ini.</span>
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="space-y-4">
                    <a href="<?= base_url('portal/ppdb/login') ?>" class="group w-full flex items-center justify-center gap-3 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-1 active:scale-95">
                        <span>MASUK KE PORTAL SISWA</span>
                        <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    
                    <a href="<?= base_url('/') ?>" class="inline-block text-sm font-semibold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors py-2">
                        Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>