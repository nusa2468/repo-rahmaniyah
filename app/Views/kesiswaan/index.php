<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php 
    // --- 1. PREPARE DATA ---
    $isGlobal = in_array(strtoupper($jenjang ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT']); 
    $filterUnit = service('request')->getGet('filter_unit');
    
    // Data JSON untuk Javascript
    $jsonAllAnggota = isset($all_anggota) ? json_encode($all_anggota) : '[]';

    // Hitung Statistik (Fallback)
    $countEkskul    = $stats['total_ekskul'] ?? count($ekskul_list ?? []);
    $countAnggota   = $stats['total_anggota'] ?? count($anggota_list ?? []);
    $countPrestasi  = $stats['total_prestasi'] ?? count($prestasi_list ?? []);
    $countKasus     = $stats['total_kasus'] ?? count($bk_list ?? []);
    $countAlumni    = $stats['total_alumni'] ?? count($alumni_list ?? []);
    $countPresensi  = count($presensi_list ?? []);
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20">
    
    <!-- --- 2. HEADER & CONTROLS SECTION --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Left: Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-indigo-600 rounded-lg shadow-lg shadow-indigo-500/30 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Modul Kesiswaan</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            Manajemen Terpadu
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                <?= esc($jenjang ?? 'GLOBAL') ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Controls (Filters & Actions) -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3 w-full xl:w-auto">
                
                <!-- Grouping Filters -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <!-- 1. Filter Unit (Superadmin Only) -->
                    <?php if($isGlobal): ?>
                    <form action="" method="get" class="flex items-center m-0 p-0 relative group">
                        <input type="hidden" name="tab" value="<?= $tab ?>">
                        <?php if(isset($filter_kategori)): ?><input type="hidden" name="kategori" value="<?= $filter_kategori ?>"><?php endif; ?>
                        
                        <div class="relative">
                            <select name="filter_unit" onchange="this.form.submit()" class="h-9 pl-9 pr-8 bg-transparent text-slate-600 text-sm font-semibold cursor-pointer appearance-none outline-none hover:text-indigo-600 transition-colors">
                                <option value="">Semua Unit</option>
                                <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                                    <?php foreach($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= $filterUnit == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                            Unit <?= $j['nama_jenjang'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback jika data kosong -->
                                    <option value="SD">Unit SD</option>
                                    <option value="SMP">Unit SMP</option>
                                    <option value="SMA">Unit SMA</option>
                                <?php endif; ?>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 group-hover:text-indigo-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="18" x="3" y="3" rx="1"/><path d="M7 3v18"/><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11 6.5c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z"/></svg>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </form>
                    <div class="h-6 w-px bg-slate-100 mx-1"></div>
                    <?php endif; ?>

                    <!-- 2. Badge Tahun Ajar -->
                    <div class="flex items-center gap-2 px-3 h-9 text-sm font-medium text-slate-500 select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-500"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>TA Aktif</span>
                    </div>
                </div>
                
                <!-- 3. Tombol Aksi Cepat (Dynamic) -->
                <?php 
                    $btnBase = "h-11 px-5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 flex items-center gap-2 transition-all transform active:scale-95 border border-transparent";
                    $btnPrimary = $btnBase . " bg-indigo-600 hover:bg-indigo-700 text-white";
                    
                    // Render tombol berdasarkan Tab Aktif
                    if($tab == 'ekskul') {
                        echo '<button onclick="openModal(\'modalEkskul\')" class="'.$btnPrimary.'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Buat Ekskul</button>';
                    }
                    elseif($tab == 'ekskul_anggota') {
                        echo '<button onclick="openModal(\'modalAnggota\')" class="'.$btnPrimary.'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Tambah Anggota</button>';
                    }
                    elseif($tab == 'ekskul_presensi') {
                        echo '<button onclick="openModal(\'modalPresensi\')" class="'.$btnPrimary.'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg> Catat Presensi</button>';
                    }
                    elseif($tab == 'bk') {
                        echo '<button onclick="openModal(\'modalKasus\')" class="'.str_replace('indigo', 'rose', $btnPrimary).'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg> Catat Kasus</button>';
                    }
                    elseif($tab == 'organisasi') {
                        echo '<button onclick="openModal(\'modalOrganisasi\')" class="'.str_replace('indigo', 'amber', $btnPrimary).'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Tambah Pengurus</button>';
                    }
                    elseif($tab == 'alumni') {
                        echo '<button onclick="openModal(\'modalAlumni\')" class="'.str_replace('indigo', 'emerald', $btnPrimary).'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg> Input Alumni</button>';
                    }
                    elseif($tab == 'prestasi') {
                        echo '<button onclick="openModal(\'modalPrestasi\')" class="'.str_replace('indigo', 'violet', $btnPrimary).'"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg> Catat Prestasi</button>';
                    }
                ?>
            </div>
        </div>

        <!-- --- 3. NAVIGATION TABS (SEGMENTED CONTROL STYLE) --- -->
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php 
                    $tabs = [
                        'dashboard' => 'Dashboard', 'ekskul' => 'Data Ekskul', 'ekskul_anggota' => 'Anggota',
                        'ekskul_presensi' => 'Presensi', 'bk' => 'Bimbingan Konseling', 'organisasi' => 'Organisasi',
                        'alumni' => 'Alumni', 'prestasi' => 'Prestasi Siswa',
                        'cetak' => 'Cetak Laporan'
                    ];
                    foreach($tabs as $key => $label):
                        $isActive = $tab == $key;
                        $baseClass = "px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center";
                        // Active State: White card look with shadow
                        $activeClass = $isActive 
                            ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' 
                            : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="?tab=<?= $key ?><?= $filterUnit ? '&filter_unit='.$filterUnit : '' ?>" class="<?= $baseClass ?> <?= $activeClass ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- --- 4. CONTENT SWITCHER --- -->
    <div class="px-6 max-w-screen-2xl mx-auto">
        <div class="transition-all duration-500 animate-fade-in-up">
            <?php 
                switch ($tab) {
                    case 'dashboard':       echo $this->include('kesiswaan/tabs/dashboard'); break;
                    case 'ekskul':          echo $this->include('kesiswaan/tabs/ekskul'); break;
                    case 'ekskul_anggota':  echo $this->include('kesiswaan/tabs/ekskul_anggota'); break;
                    case 'ekskul_presensi': echo $this->include('kesiswaan/tabs/ekskul_presensi'); break;
                    case 'bk':              echo $this->include('kesiswaan/tabs/bk'); break;
                    case 'organisasi':      echo $this->include('kesiswaan/tabs/organisasi'); break;
                    case 'alumni':          echo $this->include('kesiswaan/tabs/alumni'); break;
                    case 'prestasi':        echo $this->include('kesiswaan/tabs/prestasi'); break;
                    case 'cetak':           echo $this->include('kesiswaan/tabs/cetak'); break;
                    default:                echo $this->include('kesiswaan/tabs/dashboard'); break;
                }
            ?>
        </div>
    </div>
</div>

<!-- --- 5. MODALS & SCRIPTS --- -->
<?= $this->include('kesiswaan/components/modals') ?>
<?= $this->include('kesiswaan/components/scripts') ?>

<!-- Custom Animation -->
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
</style>

<?= $this->endSection() ?>