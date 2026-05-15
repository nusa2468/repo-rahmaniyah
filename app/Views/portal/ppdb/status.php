<?= $this->extend('layout/portal_layout') ?>

<?= $this->section('content') ?>

<?php
/**
 * Helper Robust untuk mengambil data dari Object atau Array
 */
if (!function_exists('safe_get')) {
    function safe_get($data, $field) {
        if (!$data) return '-';
        if (is_object($data) && isset($data->$field)) {
            return ($data->$field !== '' && $data->$field !== null) ? $data->$field : '-';
        }
        if (is_array($data) && isset($data[$field])) {
            return ($data[$field] !== '' && $data[$field] !== null) ? $data[$field] : '-';
        }
        return '-';
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- WELCOME HEADER -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 mb-10 text-center text-white shadow-xl shadow-blue-500/20 ring-1 ring-white/10">
        <!-- Decoration BG -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl"></div>
        
        <div class="relative z-10">
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-2 uppercase">Portal Dashboard Siswa</h1>
            <p class="text-blue-100 text-lg">Selamat Datang, <strong class="text-white"><?= esc(safe_get($siswa, 'nama_lengkap')) ?></strong></p>
        </div>
    </div>

    <!-- CONTENT -->
    <?php if (!$siswa): ?>
        <!-- ERROR STATE -->
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-200 dark:border-white/10 p-10 text-center">
            <div class="w-20 h-20 bg-red-50 dark:bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Data Tidak Ditemukan</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">Mohon maaf, sesi Anda telah berakhir atau data pendaftaran tidak ditemukan. Silakan login kembali untuk mengakses dashboard.</p>
            <a href="<?= base_url('portal/ppdb/login') ?>" class="inline-flex items-center px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 hover:-translate-y-1">
                <i class="fas fa-sign-in-alt mr-2"></i> Log In Kembali
            </a>
        </div>
    <?php else: ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- LEFT COLUMN: STATUS CARD -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 h-full flex flex-col relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>

                    <h3 class="text-xs font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-8 flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Hasil Seleksi
                    </h3>
                    
                    <?php 
                        $s_seleksi = safe_get($siswa, 'status_seleksi');
                        $s_bayar   = safe_get($siswa, 'status_pembayaran');
                        
                        // Badge Styles Tailwind
                        $cls_seleksi = 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20';
                        if (in_array($s_seleksi, ['Lolos', 'Lulus', 'Lolos Seleksi'])) $cls_seleksi = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20';
                        if (in_array($s_seleksi, ['Gagal', 'Tidak Lulus'])) $cls_seleksi = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20';

                        $cls_bayar = ($s_bayar == 'Lunas') 
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' 
                            : 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
                    ?>

                    <div class="space-y-8 flex-1">
                        <div>
                            <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-2">Status Kelulusan</label>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold border <?= $cls_seleksi ?>">
                                <?php if(in_array($s_seleksi, ['Lolos', 'Lulus', 'Lolos Seleksi'])): ?>
                                    <i class="fas fa-check-circle mr-2"></i>
                                <?php elseif(in_array($s_seleksi, ['Gagal', 'Tidak Lulus'])): ?>
                                    <i class="fas fa-times-circle mr-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock mr-2"></i>
                                <?php endif; ?>
                                <?= $s_seleksi ?>
                            </span>
                        </div>

                        <div>
                            <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-2">Status Keuangan</label>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-bold border <?= $cls_bayar ?>">
                                <i class="fas fa-money-bill-wave mr-2"></i> <?= $s_bayar ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 dark:border-white/5 space-y-3">
                        <button onclick="window.print()" class="group w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                            <i class="fas fa-print group-hover:scale-110 transition-transform"></i> CETAK BUKTI
                        </button>
                        <a href="<?= base_url('portal/ppdb/logout') ?>" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-transparent hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600 dark:text-red-400 font-bold rounded-xl transition-colors border-2 border-transparent hover:border-red-100 dark:hover:border-red-500/20">
                            <i class="fas fa-sign-out-alt"></i> Keluar Portal
                        </a>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: DETAIL DATA -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 p-6 md:p-8 relative overflow-hidden h-full">
                    
                    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100 dark:border-white/5">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">Detail Data Pendaftar</h3>
                            <p class="text-xs text-gray-500 mt-1">Informasi biodata siswa dan wali</p>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-500/10 rounded-2xl text-blue-600 dark:text-blue-400 shadow-sm">
                            <i class="fas fa-id-card fa-xl"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8 mb-8">
                        <!-- Kolom 1 -->
                        <div class="space-y-6">
                            <div class="group">
                                <label class="text-[10px] font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wider block mb-1">No. Registrasi</label>
                                <p class="text-xl font-black text-gray-900 dark:text-white tracking-tight group-hover:text-blue-600 transition-colors"><?= esc(safe_get($siswa, 'no_pendaftaran')) ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">NIK (Identitas)</label>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200 font-mono"><?= esc(safe_get($siswa, 'nik')) ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">NISN (Siswa)</label>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200 font-mono"><?= esc(safe_get($siswa, 'nisn')) ?></p>
                            </div>
                        </div>

                        <!-- Kolom 2 -->
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">Jalur Pendaftaran</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-bold">
                                    <?= esc(safe_get($siswa, 'jalur_masuk')) ?>
                                </span>
                            </div>
                            <div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">Asal Sekolah</label>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200"><?= esc(safe_get($siswa, 'asal_sekolah')) ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">Kontak WhatsApp</label>
                                <div class="flex items-center gap-2">
                                    <i class="fab fa-whatsapp text-emerald-500"></i>
                                    <p class="text-base font-bold text-gray-800 dark:text-gray-200"><?= esc(safe_get($siswa, 'no_hp_whatsapp')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Orang Tua -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 border border-gray-100 dark:border-white/5">
                        <h4 class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-users"></i> Informasi Orang Tua
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Nama Ayah</label>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200"><?= esc(safe_get($siswa, 'nama_ayah')) ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Nama Ibu</label>
                                <p class="text-base font-bold text-gray-800 dark:text-gray-200"><?= esc(safe_get($siswa, 'nama_ibu')) ?></p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Print Styles -->
<style>
    @media print {
        nav, footer, .no-print, button { display: none !important; }
        body { background: white !important; color: black !important; }
        .bg-white { box-shadow: none !important; border: 1px solid #ddd !important; }
        .text-white { color: black !important; }
        .bg-gradient-to-br { background: none !important; color: black !important; border-bottom: 2px solid black; }
    }
</style>

<?= $this->endSection() ?>