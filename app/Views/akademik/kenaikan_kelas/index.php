<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- LOGIKA PROTEKSI SCOPE (HAK AKSES DINAMIS) ---
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
    
    // Tentukan unit yang sedang aktif untuk label UI
    $displayUnit = ($current_unit === 'Global' || empty($current_unit)) ? 'Semua Unit' : 'Unit ' . esc($current_unit);
    
    // SAFE DATA HANDLING: Pastikan $list_history adalah array
    $historyData = $list_history ?? [];
    if (!is_array($historyData)) {
        $historyData = [];
    }

    $tahunAjaranAktif = $tahun_ajaran_aktif ?? null;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">KENAIKAN KELAS</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Transisi <span class="text-indigo-600">Kenaikan Kelas</span>
            </h1>
        </div>

        <?php if ($tahunAjaranAktif): ?>
        <div class="bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                <i class="fas fa-rocket"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Fase Transisi</p>
                <p class="text-sm font-black text-slate-800 dark:text-white uppercase italic">End of Period Ready</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-day mr-2 opacity-50"></i> Kalender
        </a>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-clock mr-2 opacity-50"></i> Jadwal
        </a>
        <a href="<?= base_url('app/akademik/absensi-siswa') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-user-check mr-2 opacity-50"></i> Presensi
        </a>
        <a href="<?= base_url('app/akademik/nilai') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-star mr-2 opacity-50"></i> Nilai
        </a>
        <a href="<?= base_url('app/akademik/rapor') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-contract mr-2 opacity-50"></i> E-Rapor
        </a>
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-rocket mr-2"></i> Kenaikan
        </a>
    </div>

    <?php if (!$tahunAjaranAktif) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-8 border-rose-500 p-8 rounded-r-[2rem] shadow-xl mb-6 flex items-start gap-6 animate-pulse">
            <i class="fas fa-exclamation-shield text-rose-500 text-4xl mt-1"></i>
            <div>
                <h3 class="text-lg font-black text-rose-900 dark:text-rose-400 uppercase tracking-widest italic">Tahun Ajaran Tidak Aktif</h3>
                <p class="text-sm text-rose-700 dark:text-rose-300 mt-1 font-bold uppercase tracking-tight">Proses kenaikan kelas ditangguhkan sampai Tahun Ajaran baru diaktifkan.</p>
            </div>
        </div>
    <?php else : ?>

        <!-- 1. KARTU STATISTIK (PREMIUM SOLID STYLE) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-indigo-900">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80 leading-none">Tahun Ajaran</p>
                    <h3 class="text-2xl font-black mt-2 italic"><?= esc($tahunAjaranAktif['tahun_ajaran']) ?></h3>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="px-2 py-1 bg-white/20 rounded-lg text-[9px] font-black uppercase tracking-widest">Semester <?= esc($tahunAjaranAktif['semester']) ?></span>
                    </div>
                </div>
                <i class="fas fa-calendar-alt absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>

            <div class="bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-200 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-emerald-800">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80 leading-none">Otoritas Scope</p>
                    <h3 class="text-2xl font-black mt-2 italic uppercase"><?= strtoupper($displayUnit) ?></h3>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        <span class="text-[10px] font-bold text-emerald-50 uppercase tracking-widest italic opacity-80"><?= $isGlobal ? 'Global Access' : 'Unit Restricted' ?></span>
                    </div>
                </div>
                <i class="fas fa-shield-alt absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>

            <div class="bg-slate-900 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-indigo-600">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 opacity-80 leading-none">Total Riwayat</p>
                    <h3 class="text-2xl font-black mt-2 italic"><?= number_format(count($historyData)) ?> <span class="text-xs font-normal text-slate-400 not-italic uppercase tracking-widest ml-1">Entri</span></h3>
                    <p class="mt-4 text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] italic opacity-60">Halaman Semasa</p>
                </div>
                <i class="fas fa-history absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- FORM PROSES (SIDEBAR) -->
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden sticky top-24 transition-all hover:shadow-xl hover:shadow-slate-200/50">
                    <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic leading-none">
                            <i class="fas fa-edit mr-3 text-indigo-600"></i> Kelola Kenaikan
                        </h3>
                    </div>
                    <form action="<?= base_url('app/akademik/kenaikan_kelas/kelola') ?>" method="get" class="p-8 space-y-8">
                        
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pilih Rombongan Belajar</label>
                            <div class="relative group">
                                <select name="id_kelas" required class="w-full pl-10 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-700 dark:text-white focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                    <option value="">-- PILIH KELAS ASAL --</option>
                                    <?php if(!empty($kelas_list)): foreach ($kelas_list as $kelas) : ?>
                                        <?php 
                                            if (!$isGlobal && strtoupper($kelas['kode_jenjang']) !== strtoupper($sessionUnit)) continue;
                                        ?>
                                        <option value="<?= esc($kelas['id']) ?>">Kelas <?= esc($kelas['nama_kelas']) ?> (<?= esc($kelas['kode_jenjang']) ?>)</option>
                                    <?php endforeach; endif; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-door-open text-xs"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 bg-indigo-50 dark:bg-indigo-900/10 border-l-4 border-indigo-500 rounded-r-2xl flex gap-3">
                             <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                             <p class="text-[10px] font-bold text-indigo-800 dark:text-indigo-300 leading-relaxed uppercase tracking-tight">
                                 Sistem akan melakukan migrasi data siswa ke Tahun Ajaran berikutnya secara otomatis setelah keputusan disimpan.
                             </p>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 px-6 rounded-2xl shadow-xl shadow-indigo-100 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center text-[10px] uppercase tracking-[0.2em] border-b-4 border-indigo-800">
                            BUKA LEMBAR KEPUTUSAN <i class="fas fa-chevron-right ml-3 text-[8px]"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIWAYAT PROSES (MAIN TABLE) -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden h-full flex flex-col min-h-[500px]">
                    
                    <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/5">
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                            
                            <!-- UNIT TABS (RESTRICTED) -->
                            <div class="flex items-center p-1.5 bg-slate-200 dark:bg-slate-900 rounded-2xl w-fit border border-slate-300 dark:border-slate-700 shadow-inner">
                                <?php 
                                    $availableUnits = $isGlobal ? ['Global' => 'All Units', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'] : [$sessionUnit => strtoupper($sessionUnit)];
                                    foreach($availableUnits as $code => $label): 
                                        $isActive = ($current_unit == $code);
                                ?>
                                    <a href="?unit=<?= $code ?>&keyword=<?= esc($keyword) ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-[0.1em] transition-all duration-300 <?= $isActive ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                                        <?= $label ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <form action="" method="get" class="relative group min-w-[280px]">
                                <input type="hidden" name="unit" value="<?= esc($current_unit) ?>">
                                <input type="text" name="keyword" value="<?= esc($keyword) ?>" 
                                       placeholder="Cari Nama Siswa atau NIS..." 
                                       class="w-full pl-11 pr-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-2xl text-[11px] font-bold uppercase tracking-widest focus:border-indigo-500 transition-all outline-none italic shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300 group-focus-within:text-indigo-500">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="flex-grow overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-900 text-white italic">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Identitas Siswa</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center">Rombel Asal</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center">Rombel Tujuan</th>
                                    <th class="px-6 py-5 text-center text-[10px] font-black uppercase tracking-widest w-24">Keputusan</th>
                                    <th class="px-8 py-5 text-center text-[10px] font-black uppercase tracking-widest">Tgl Proses</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                                <?php if (empty($historyData)) : ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-32 text-center">
                                            <div class="flex flex-col items-center opacity-30">
                                                <i class="fas fa-folder-open text-6xl mb-4"></i>
                                                <p class="text-sm font-black uppercase tracking-widest italic">Belum Ada Aktivitas Transisi</p>
                                                <p class="text-[10px] font-bold mt-1 uppercase">Gunakan filter unit atau lakukan kelola kenaikan di sidebar.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($historyData as $row) : if(!$row) continue; ?>
                                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-all group">
                                            <td class="px-8 py-5">
                                                <div class="flex flex-col">
                                                    <span class="text-[13px] font-black text-slate-800 dark:text-slate-100 uppercase italic leading-none group-hover:text-indigo-600 transition-colors"><?= esc($row['nama_siswa'] ?? 'N/A') ?></span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1.5">NIS: <?= esc($row['nis'] ?? '-') ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase"><?= esc($row['kelas_asal'] ?? '-') ?></div>
                                                <p class="text-[8px] font-bold text-slate-400 italic leading-none mt-1">(<?= esc($row['ta_asal'] ?? '-') ?>)</p>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <?php if(!empty($row['kelas_tujuan'])): ?>
                                                    <div class="text-[10px] font-black text-emerald-600 uppercase italic"><?= esc($row['kelas_tujuan']) ?></div>
                                                    <p class="text-[8px] font-bold text-slate-400 italic leading-none mt-1">(<?= esc($row['ta_tujuan'] ?? '-') ?>)</p>
                                                <?php else: ?>
                                                    <span class="text-[10px] font-bold text-slate-300 italic uppercase">Lulus / Keluar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <?php 
                                                    $status = $row['status_kenaikan'] ?? 'Unknown';
                                                    $color = match($status) {
                                                        'Naik'    => 'bg-emerald-100 text-emerald-700 border-emerald-200 shadow-emerald-100',
                                                        'Tinggal' => 'bg-amber-100 text-amber-700 border-amber-200 shadow-amber-100',
                                                        'Lulus'   => 'bg-indigo-100 text-indigo-700 border-indigo-200 shadow-indigo-100',
                                                        default   => 'bg-slate-100 text-slate-600 border-slate-200'
                                                    };
                                                ?>
                                                <span class="inline-block px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border <?= $color ?> shadow-sm">
                                                    <?= esc($status) ?>
                                                </span>
                                            </td>
                                            <td class="px-8 py-4 text-center">
                                                <span class="text-[10px] font-black text-slate-400 italic uppercase tracking-tighter"><?= !empty($row['tanggal_keputusan']) ? date('d M Y', strtotime($row['tanggal_keputusan'])) : '-' ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($pager)) : ?>
                        <div class="px-10 py-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-6 mt-auto">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-none">
                                Analisis Records: <span class="text-indigo-600"><?= $pager->getCurrentPage() ?></span> of <span class="text-slate-900 dark:text-white"><?= $pager->getPageCount() ?> Pages</span>
                            </span>
                            <div class="custom-pagination">
                                <?= $pager->links('default', 'tailwind_pagination') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<style>
    /* Premium Styling Scrollbar & Navigation */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .custom-pagination nav ul { display: flex; gap: 0.35rem; justify-content: center; }
    .custom-pagination nav ul li a, .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.4rem; height: 2.4rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white;
        border-radius: 0.8rem; transition: all 0.2s; color: #64748b;
    }
    .custom-pagination nav ul li.active span {
        background: #4f46e5; color: white; border-color: #4f46e5;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }
    .custom-pagination nav ul li a:hover { border-color: #4f46e5; color: #4f46e5; background: #f8fafc; }
</style>

<?= $this->endSection() ?>