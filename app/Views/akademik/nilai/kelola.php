<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- LOGIKA PROTEKSI SCOPE ---
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased">
    
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white dark:bg-slate-800 px-4 py-2 rounded-2xl shadow-sm border border-slate-200 dark:border-white/10">
                    <li class="inline-flex items-center">
                        <a href="<?= base_url('app/dashboard') ?>" class="text-xs font-bold text-slate-500 hover:text-indigo-600 dark:text-slate-400 transition-colors uppercase tracking-widest">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center text-slate-400">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <a href="<?= site_url('app/akademik/nilai') ?>" class="text-xs font-bold uppercase tracking-widest hover:text-indigo-600 transition-colors">Nilai Siswa</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[10px]"></i>
                            <span class="text-xs font-black uppercase tracking-widest italic">Input Lembar Nilai</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                Lembar Nilai: <span class="text-indigo-600 dark:text-indigo-400"><?= esc($kelas_info['nama_kelas'] ?? 'N/A') ?></span>
            </h1>
        </div>

        <a href="<?= site_url('app/akademik/nilai') ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-white/10 rounded-2xl text-xs font-black text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
            <i class="fas fa-arrow-left mr-2 transition-transform group-hover:-translate-x-1"></i> KEMBALI
        </a>
    </div>

    <form action="<?= site_url('app/akademik/nilai/simpan') ?>" method="post" id="formNilai">
        <?= csrf_field() ?>
        
        <!-- Hidden Inputs -->
        <input type="hidden" name="id_kelas" value="<?= esc($kelas_info['id'] ?? '') ?>">
        <input type="hidden" name="id_mapel" value="<?= esc($mapel_info['id'] ?? '') ?>">
        <input type="hidden" name="id_tahun_ajaran" value="<?= esc($tahun_ajaran_aktif['id'] ?? '') ?>">
        <input type="hidden" name="semester" value="<?= esc($semester ?? '') ?>">

        <!-- INFO CARDS SECTION -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Mapel -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border-2 border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest relative z-10">Mata Pelajaran</p>
                <h3 class="text-sm font-black text-slate-800 dark:text-white mt-1 relative z-10 truncate"><?= esc($mapel_info['nama_mapel'] ?? '-') ?></h3>
                <span class="text-[10px] font-bold text-indigo-500 uppercase italic opacity-80"><?= esc($mapel_info['kode_mapel'] ?? '-') ?></span>
                <i class="fas fa-book-open absolute -right-2 -bottom-2 text-5xl text-slate-50 dark:text-white/5 group-hover:scale-110 transition-transform"></i>
            </div>
            
            <!-- Guru -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border-2 border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest relative z-10">Guru Pengampu</p>
                <h3 class="text-sm font-black text-slate-800 dark:text-white mt-1 relative z-10 truncate"><?= esc($guru_info['nama_lengkap'] ?? '-') ?></h3>
                <span class="text-[10px] font-bold text-emerald-500 uppercase italic opacity-80">Verified Teacher</span>
                <i class="fas fa-user-tie absolute -right-2 -bottom-2 text-5xl text-slate-50 dark:text-white/5 group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- TA & SMT -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border-2 border-slate-100 dark:border-white/5 shadow-sm relative overflow-hidden group">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest relative z-10">Periode Akademik</p>
                <h3 class="text-sm font-black text-slate-800 dark:text-white mt-1 relative z-10"><?= esc($tahun_ajaran_aktif['tahun_ajaran'] ?? '-') ?></h3>
                <span class="text-[10px] font-bold text-amber-500 uppercase italic opacity-80">Semester <?= esc($semester) ?></span>
                <i class="fas fa-calendar-alt absolute -right-2 -bottom-2 text-5xl text-slate-50 dark:text-white/5 group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Unit Lock -->
            <div class="bg-slate-900 p-5 rounded-3xl border-b-4 border-indigo-600 shadow-xl text-white relative overflow-hidden">
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest relative z-10">Unit Otoritas</p>
                <h3 class="text-sm font-black mt-1 relative z-10 italic">UNIT <?= esc($kelas_info['kode_jenjang'] ?? 'N/A') ?></h3>
                <div class="mt-1 flex items-center gap-1.5 relative z-10">
                    <i class="fas fa-lock text-[8px] text-amber-400"></i>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Strict Access Control</span>
                </div>
                <i class="fas fa-shield-alt absolute -right-2 -bottom-2 text-5xl text-white/5"></i>
            </div>
        </div>

        <!-- BOBOT CONFIG BAR (TECHNICAL INFO) -->
        <?php
            $bTugas = (float)($mapel_info['bobot_tugas'] ?? 0);
            $bUts = (float)($mapel_info['bobot_uts'] ?? 0);
            $bUas = (float)($mapel_info['bobot_uas'] ?? 0);
            $bAbsensi = (float)($mapel_info['bobot_absensi'] ?? 0);
            $totalBobot = $bTugas + $bUts + $bUas + $bAbsensi;
            $isError = abs($totalBobot - 1.0) > 0.001;
        ?>
        <div id="bobot-config" 
             data-tugas="<?= $bTugas ?>" data-uts="<?= $bUts ?>" data-uas="<?= $bUas ?>" data-absensi="<?= $bAbsensi ?>"
             class="bg-indigo-600 rounded-3xl p-6 mb-8 text-white shadow-lg shadow-indigo-200 dark:shadow-none flex flex-wrap items-center justify-between gap-6 border-b-4 border-indigo-800">
            
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md">
                    <i class="fas fa-balance-scale text-lg"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-200">Konfigurasi Bobot Mapel</p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs font-black italic">ABS: <?= number_format($bAbsensi * 100, 0) ?>%</span>
                        <span class="text-slate-400 text-xs opacity-50">/</span>
                        <span class="text-xs font-black italic">TGS: <?= number_format($bTugas * 100, 0) ?>%</span>
                        <span class="text-slate-400 text-xs opacity-50">/</span>
                        <span class="text-xs font-black italic">UTS: <?= number_format($bUts * 100, 0) ?>%</span>
                        <span class="text-slate-400 text-xs opacity-50">/</span>
                        <span class="text-xs font-black italic">UAS: <?= number_format($bUas * 100, 0) ?>%</span>
                    </div>
                </div>
            </div>

            <?php if ($isError) : ?>
                <div class="bg-rose-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest animate-bounce flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Kesalahan Bobot (Total != 100%)
                </div>
            <?php else: ?>
                <div class="bg-white/10 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 backdrop-blur-sm border border-white/20">
                    <i class="fas fa-check-circle text-emerald-400"></i> Validated by System
                </div>
            <?php endif; ?>
        </div>

        <!-- MAIN TABLE SECTION -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
            <?php if (empty($siswa_di_kelas)) : ?>
                <div class="p-20 text-center">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-slate-200 dark:border-slate-700">
                        <i class="fas fa-user-slash text-slate-300 text-3xl"></i>
                    </div>
                    <p class="text-sm font-black text-slate-500 uppercase tracking-widest italic">Data Siswa Tidak Ditemukan</p>
                </div>
            <?php else : ?>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-white/5 border-b-2 border-slate-100 dark:border-white/10">
                                <th class="px-6 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-16 border-r border-slate-50 dark:border-white/5">No</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest min-w-[250px]">Identitas Siswa</th>
                                <th class="px-4 py-5 text-center text-[10px] font-black text-indigo-400 uppercase tracking-widest bg-indigo-50/30 dark:bg-indigo-900/10">Absensi</th>
                                <th class="px-4 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Harian / Tugas</th>
                                <th class="px-4 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">UTS</th>
                                <th class="px-4 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">UAS</th>
                                <th class="px-8 py-5 text-center text-[10px] font-black text-slate-900 dark:text-white uppercase tracking-widest bg-slate-50 dark:bg-white/5 border-l-2 border-slate-100 dark:border-white/10">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <?php 
                                $no = 1;
                                $nilai_map = $nilai_tersimpan ?? [];
                                foreach ($siswa_di_kelas as $siswa) :
                                    $nilai_siswa = $nilai_map[$siswa['id']] ?? [];
                                    
                                    // Fallback value handling
                                    $valAbsensi = isset($nilai_siswa['nilai_absensi']) ? esc($nilai_siswa['nilai_absensi']) : '0';
                                    $valTugas   = isset($nilai_siswa['nilai_tugas']) ? esc($nilai_siswa['nilai_tugas']) : '';
                                    $valUts     = isset($nilai_siswa['nilai_uts']) ? esc($nilai_siswa['nilai_uts']) : '';
                                    $valUas     = isset($nilai_siswa['nilai_uas']) ? esc($nilai_siswa['nilai_uas']) : '';
                            ?>
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-colors group" data-student-id="<?= $siswa['id'] ?>">
                                    <td class="px-6 py-4 text-center border-r border-slate-50 dark:border-white/5 bg-slate-50/30 dark:bg-white/[0.01]">
                                        <span class="text-xs font-black text-slate-400 italic"><?= $no++ ?></span>
                                    </td>
                                    <td class="px-8 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-slate-100 tracking-tight uppercase"><?= esc($siswa['nama_siswa'] ?? ($siswa['nama_lengkap'] ?? '-')) ?></span>
                                            <span class="text-[9px] font-bold text-slate-400 tracking-[0.1em] uppercase">NIS: <?= esc($siswa['nis'] ?? '-') ?></span>
                                        </div>
                                    </td>

                                    <!-- INPUT ABSENSI (Locked Style) -->
                                    <td class="px-4 py-4 bg-indigo-50/20 dark:bg-indigo-900/5">
                                        <div class="relative group/input">
                                            <input type="number" step="0.01" readonly data-type="absensi"
                                                   name="nilai[<?= $siswa['id'] ?>][absensi]" value="<?= $valAbsensi ?>" 
                                                   class="w-full bg-slate-100 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-center text-xs font-black text-slate-400 py-3 cursor-not-allowed transition-all"
                                                   title="Nilai kehadiran dikunci (otomatis dari modul absensi)">
                                            <div class="absolute right-2 top-1/2 -translate-y-1/2 opacity-30">
                                                <i class="fas fa-lock text-[8px]"></i>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- INPUT TUGAS -->
                                    <td class="px-4 py-4">
                                        <input type="number" step="0.01" min="0" max="100" data-type="tugas"
                                               name="nilai[<?= $siswa['id'] ?>][tugas]" value="<?= $valTugas ?>" 
                                               placeholder="0"
                                               class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-center text-xs font-black text-slate-800 dark:text-white py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                    </td>

                                    <!-- INPUT UTS -->
                                    <td class="px-4 py-4">
                                        <input type="number" step="0.01" min="0" max="100" data-type="uts"
                                               name="nilai[<?= $siswa['id'] ?>][uts]" value="<?= $valUts ?>" 
                                               placeholder="0"
                                               class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-center text-xs font-black text-slate-800 dark:text-white py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                    </td>

                                    <!-- INPUT UAS -->
                                    <td class="px-4 py-4">
                                        <input type="number" step="0.01" min="0" max="100" data-type="uas"
                                               name="nilai[<?= $siswa['id'] ?>][uas]" value="<?= $valUas ?>" 
                                               placeholder="0"
                                               class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-center text-xs font-black text-slate-800 dark:text-white py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                    </td>

                                    <!-- OUTPUT DISPLAY -->
                                    <td class="px-8 py-4 text-center bg-slate-50/50 dark:bg-white/[0.01] border-l-2 border-slate-100 dark:border-white/10">
                                        <div class="inline-flex items-center justify-center min-w-[3.5rem] h-11 bg-white dark:bg-slate-900 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm">
                                            <span class="nilai-akhir-display text-sm font-black tracking-tighter">0.00</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Section & Actions -->
                <div class="px-8 py-6 bg-slate-50 dark:bg-white/5 border-t-2 border-slate-100 dark:border-white/10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white dark:bg-slate-900 rounded-xl flex items-center justify-center shadow-sm border border-slate-200 dark:border-white/10">
                            <i class="fas fa-info-circle text-indigo-500"></i>
                        </div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase leading-relaxed max-w-sm tracking-tight">
                            Pastikan semua nilai telah diisi dengan benar. Nilai akhir dihitung secara otomatis berdasarkan bobot konfigurasi mata pelajaran.
                        </p>
                    </div>
                    
                    <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-3 px-12 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-indigo-200 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 border-b-4 border-indigo-800">
                        <i class="fas fa-save"></i> SIMPAN SEMUA NILAI
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- STYLING KHUSUS -->
<style>
    /* Styling Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

    /* Fix Input Number Appearance */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<!-- REALTIME LOGIC SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const config = document.getElementById('bobot-config');
    const weights = {
        tugas: parseFloat(config.dataset.tugas) || 0,
        uts: parseFloat(config.dataset.uts) || 0,
        uas: parseFloat(config.dataset.uas) || 0,
        absensi: parseFloat(config.dataset.absensi) || 0
    };

    const rows = document.querySelectorAll('tr[data-student-id]');

    const calculateRow = (row) => {
        const inputs = row.querySelectorAll('input[type="number"]');
        const display = row.querySelector('.nilai-akhir-display');
        let total = 0;

        inputs.forEach(input => {
            let val = parseFloat(input.value);
            if (isNaN(val)) val = 0;
            
            // Constraint 0-100
            if(val < 0) val = 0;
            if(val > 100) val = 100;

            const type = input.dataset.type;
            if (weights[type] !== undefined) {
                total += val * weights[type];
            }
        });

        // Tampilkan Hasil
        display.textContent = total.toFixed(2);
        
        // Dynamic Color Logic
        let colorClass = 'text-slate-800 dark:text-white';
        if (total < 75) {
            colorClass = 'text-rose-600';
        } else if (total >= 90) {
            colorClass = 'text-emerald-500';
        } else if (total >= 75) {
            colorClass = 'text-indigo-600';
        }
        
        display.className = `nilai-akhir-display text-sm font-black tracking-tighter ${colorClass}`;
    };

    // Initialize & Listen
    rows.forEach(row => {
        calculateRow(row); // Initial Load
        const inputs = row.querySelectorAll('input[type="number"]');
        inputs.forEach(input => {
            input.addEventListener('input', () => calculateRow(row));
            input.addEventListener('change', () => calculateRow(row));
        });
    });
});
</script>

<?= $this->endSection() ?>