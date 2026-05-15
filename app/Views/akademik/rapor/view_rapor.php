<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- SAFE DATA HANDLING & PRE-CALCULATION ---
    $siswa = $siswa ?? [];
    $raport = $raport ?? [];
    $tahun_ajaran = $tahun_ajaran ?? [];
    $nilai_list = $nilai_list ?? [];
    $semester = $semester ?? 'Ganjil';

    // Hitung jumlah mata pelajaran unik yang ada dalam data nilai
    $mapel_ids = !empty($nilai_list) ? array_unique(array_column($nilai_list, 'id_mata_pelajaran')) : [];
    $jumlah_mapel = count($mapel_ids);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-900">
    
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white dark:bg-slate-800 px-4 py-2 rounded-2xl shadow-sm border border-slate-200 dark:border-white/10">
                    <li class="inline-flex items-center">
                        <a href="<?= base_url('app/dashboard') ?>" class="text-xs font-bold text-slate-500 hover:text-indigo-600 dark:text-slate-400 transition-colors uppercase tracking-widest">
                            <i class="fas fa-home mr-2 text-[10px]"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center text-slate-400">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <a href="<?= base_url('app/akademik/rapor') ?>" class="text-xs font-bold uppercase tracking-widest hover:text-indigo-600 transition-colors">E-Rapor</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="text-xs font-black uppercase tracking-widest italic">Detail Hasil Belajar</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                    Rapor: <span class="text-indigo-600 dark:text-indigo-400"><?= esc($siswa['nama_siswa'] ?? 'Siswa') ?></span>
                </h1>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-black rounded-lg uppercase tracking-widest border border-slate-200 dark:border-slate-600">
                    <?= esc($raport['status_raport'] ?? 'Draft') ?>
                </span>
            </div>
        </div>

        <a href="<?= base_url('app/akademik/rapor') ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-white/10 rounded-2xl text-xs font-black text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
            <i class="fas fa-arrow-left mr-2 transition-transform group-hover:-translate-x-1"></i> KEMBALI
        </a>
    </div>

    <!-- 1. INFORMASI IDENTITAS & STATISTIK (MODERN SOLID STYLE) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Card Siswa -->
        <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-200 dark:shadow-none p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80 leading-none">Identitas Siswa</p>
                <h3 class="text-lg font-black mt-3 italic tracking-tight truncate"><?= esc($siswa['nama_siswa'] ?? '-') ?></h3>
                <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest mt-1">NIS: <?= esc($siswa['nis'] ?? '-') ?></p>
            </div>
            <i class="fas fa-user-graduate absolute -right-4 -bottom-4 text-white/10 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card Kelas -->
        <div class="bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-200 dark:shadow-none p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80 leading-none">Kelas & Wali</p>
                <h3 class="text-lg font-black mt-3 italic tracking-tight"><?= esc($siswa['nama_kelas'] ?? '-') ?></h3>
                <p class="text-[10px] font-bold text-emerald-50 uppercase tracking-tight mt-1 truncate"><?= esc($siswa['nama_wali'] ?? 'Belum ditentukan') ?></p>
            </div>
            <i class="fas fa-school absolute -right-4 -bottom-4 text-white/10 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card Periode -->
        <div class="bg-slate-900 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 opacity-80 leading-none">Periode</p>
                <h3 class="text-lg font-black mt-3 italic tracking-tight"><?= esc($tahun_ajaran['tahun_ajaran'] ?? '-') ?></h3>
                <span class="mt-2 inline-block px-2 py-0.5 bg-white/10 rounded text-[9px] font-black uppercase tracking-widest border border-white/10">
                    SMT <?= esc($semester) ?>
                </span>
            </div>
            <i class="fas fa-calendar-check absolute -right-4 -bottom-4 text-white/5 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- Card Mata Pelajaran (KARTU BARU - PENYELESAIAN MASALAH ANDA) -->
        <div class="bg-amber-500 rounded-3xl shadow-xl shadow-amber-200 dark:shadow-none p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-100 opacity-80 leading-none">Mata Pelajaran</p>
                <h3 class="text-3xl font-black mt-3 italic tracking-tight"><?= $jumlah_mapel ?></h3>
                <p class="text-[10px] font-bold text-amber-50 uppercase tracking-widest mt-1">Total Mapel Terinput</p>
            </div>
            <i class="fas fa-book absolute -right-4 -bottom-4 text-white/10 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- 2. LEGER NILAI (MAIN CONTENT) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic">
                        <i class="fas fa-list-ol mr-2 text-indigo-600"></i> Rekapitulasi Nilai (<?= $jumlah_mapel ?> Mapel)
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50 dark:bg-white/5 border-b border-slate-100 dark:border-white/10">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Mata Pelajaran</th>
                                <th class="px-4 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">KKM</th>
                                <th class="px-4 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Akhir</th>
                                <th class="px-4 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Predikat</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Capaian Kompetensi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <?php if(empty($nilai_list)): ?>
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center opacity-30">
                                            <i class="fas fa-folder-open text-5xl mb-4"></i>
                                            <p class="text-sm font-black uppercase tracking-widest">Belum ada data nilai akademik.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                    $processed_mapel = []; 
                                    $no = 1;
                                ?>
                                <?php foreach($nilai_list as $n): ?>
                                    <?php 
                                        if (in_array($n['id_mata_pelajaran'], $processed_mapel)) continue;
                                        $processed_mapel[] = $n['id_mata_pelajaran'];
                                        $akhir = floatval($n['nilai_akhir'] ?? 0);
                                        $grade = $n['nilai_huruf'] ?? '-';
                                    ?>
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-colors group">
                                        <td class="px-8 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="text-[10px] font-black text-slate-300"><?= $no++ ?>.</span>
                                                <div class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase tracking-tight group-hover:text-indigo-600 transition-colors italic">
                                                    <?= esc($n['nama_mapel'] ?? '-') ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="text-[10px] font-bold text-slate-400">75</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="text-sm font-black text-slate-900 dark:text-white"><?= number_format($akhir, 0) ?></span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <?php 
                                                $badge = match($grade) {
                                                    'A' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                    'B' => 'bg-sky-100 text-sky-700 border-sky-200',
                                                    'C' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    default => 'bg-rose-100 text-rose-700 border-rose-200'
                                                };
                                            ?>
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-black border shadow-sm <?= $badge ?>">
                                                <?= esc($grade) ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-4">
                                            <div class="text-[10px] font-medium text-slate-500 dark:text-slate-400 line-clamp-2 italic leading-relaxed" title="<?= esc($n['keterangan'] ?? '') ?>">
                                                <?= esc($n['keterangan'] ?? 'Menunjukkan penguasaan kompetensi yang sangat baik dalam memahami materi pembelajaran.') ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. INPUT DATA RAPOR (SIDEBAR) -->
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-xl border-2 border-slate-100 dark:border-white/5 overflow-hidden sticky top-24">
                <div class="bg-slate-900 px-8 py-5 border-b border-slate-800 flex items-center justify-between">
                    <h3 class="text-xs font-black text-white uppercase tracking-widest italic">
                        <i class="fas fa-edit mr-2 text-indigo-400"></i> Pengaturan Rapor
                    </h3>
                </div>
                
                <form action="<?= base_url('app/akademik/rapor/simpan/' . ($siswa['id_siswa'] ?? 0)) ?>" method="post" class="p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="semester" value="<?= esc($semester) ?>">
                    <input type="hidden" name="id_enrollment" value="<?= esc($siswa['id_enrollment'] ?? '') ?>">
                    <input type="hidden" name="id_kelas" value="<?= esc($siswa['id_kelas'] ?? '') ?>">
                    <input type="hidden" name="kode_jenjang" value="<?= esc($siswa['kode_jenjang'] ?? '') ?>">

                    <!-- Absensi Grid -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 ml-1">Ketidakhadiran (Hari)</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="space-y-1">
                                <label class="block text-[9px] font-black text-slate-400 uppercase text-center">Sakit</label>
                                <input type="number" name="sakit" value="<?= esc($raport['total_sakit'] ?? 0) ?>" 
                                       class="w-full text-center font-black text-sm py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl focus:border-indigo-500 outline-none transition-all shadow-inner">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[9px] font-black text-slate-400 uppercase text-center">Izin</label>
                                <input type="number" name="izin" value="<?= esc($raport['total_izin'] ?? 0) ?>" 
                                       class="w-full text-center font-black text-sm py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl focus:border-indigo-500 outline-none transition-all shadow-inner">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[9px] font-black text-rose-400 uppercase text-center">Alpa</label>
                                <input type="number" name="alpa" value="<?= esc($raport['total_alpa'] ?? 0) ?>" 
                                       class="w-full text-center font-black text-sm py-3 bg-rose-50 dark:bg-rose-950/20 border-2 border-rose-100 dark:border-rose-900/30 rounded-2xl text-rose-600 focus:border-rose-500 outline-none transition-all shadow-inner">
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Wali Kelas -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Catatan Wali Kelas</label>
                        <textarea name="catatan_wali" rows="3" 
                                  class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-bold text-slate-700 dark:text-white focus:border-indigo-500 outline-none transition-all resize-none shadow-inner" 
                                  placeholder="Berikan ulasan perkembangan siswa..."><?= esc($raport['catatan_wali_kelas'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Data Fisik -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                         <div class="space-y-1.5">
                            <label class="block text-[9px] font-black text-slate-400 uppercase ml-1">Tinggi (cm)</label>
                            <input type="number" name="tinggi_badan" value="<?= esc($raport['tinggi_badan'] ?? '') ?>" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-xl text-xs font-bold focus:border-indigo-500 outline-none shadow-inner">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[9px] font-black text-slate-400 uppercase ml-1">Berat (kg)</label>
                            <input type="number" name="berat_badan" value="<?= esc($raport['berat_badan'] ?? '') ?>" 
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-xl text-xs font-bold focus:border-indigo-500 outline-none shadow-inner">
                        </div>
                    </div>

                    <!-- KEPUTUSAN KHUSUS SEMESTER GENAP -->
                    <?php if (strtoupper($semester) === 'GENAP'): ?>
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                         <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-2 ml-1 italic">
                             <i class="fas fa-graduation-cap"></i> Keputusan Akhir Tahun
                         </label>
                         <select name="status_kenaikan" class="w-full px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-100 dark:border-indigo-900/30 rounded-2xl text-xs font-black uppercase text-indigo-700 dark:text-indigo-300 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                             <option value="">-- Pilih Keputusan --</option>
                             <option value="Naik Kelas" <?= ($raport['status_kenaikan'] ?? '') == 'Naik Kelas' ? 'selected' : '' ?>>Naik Kelas</option>
                             <option value="Tinggal Kelas" <?= ($raport['status_kenaikan'] ?? '') == 'Tinggal Kelas' ? 'selected' : '' ?>>Tinggal Kelas</option>
                             <option value="Lulus" <?= ($raport['status_kenaikan'] ?? '') == 'Lulus' ? 'selected' : '' ?>>Lulus</option>
                             <option value="Tidak Lulus" <?= ($raport['status_kenaikan'] ?? '') == 'Tidak Lulus' ? 'selected' : '' ?>>Tidak Lulus</option>
                         </select>
                    </div>
                    <?php endif; ?>

                    <!-- Status Publish -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-700">
                         <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Status Rapor</label>
                         <select name="status_raport" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-xs font-bold text-slate-700 dark:text-white focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                             <option value="Draft" <?= ($raport['status_raport'] ?? '') == 'Draft' ? 'selected' : '' ?>>Draft Mode</option>
                             <option value="Published" <?= ($raport['status_raport'] ?? '') == 'Published' ? 'selected' : '' ?>>Published (Siap Cetak)</option>
                             <option value="Locked" <?= ($raport['status_raport'] ?? '') == 'Locked' ? 'selected' : '' ?>>Locked (Final)</option>
                         </select>
                    </div>

                    <div class="pt-4 space-y-3">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-6 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center text-xs uppercase tracking-[0.2em] border-b-4 border-indigo-800">
                            <i class="fas fa-save mr-3"></i> SIMPAN PERUBAHAN
                        </button>
                        
                        <a href="<?= base_url('app/akademik/rapor/cetak/' . ($siswa['id_siswa'] ?? 0)) ?>" target="_blank" 
                           class="w-full bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-black py-4 px-6 rounded-2xl shadow-sm transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center text-xs uppercase tracking-[0.2em]">
                            <i class="fas fa-print mr-3 text-indigo-500"></i> PRATINJAU CETAK
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }

    /* Fix Input Number Appearance */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<?= $this->endSection() ?>