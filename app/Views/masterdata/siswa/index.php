<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Manajemen Data Siswa
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // --- 1. SESSION & ACCESS CONTROL ---
    $session = session();
    $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT'];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);

    // Ambil Filter dari Request
    $request = \Config\Services::request();
    $currentUnit = $request->getGet('unit') ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang);
    $currentAngkatan = $request->getGet('angkatan') ?? '';
    $search = $request->getGet('search') ?? '';
    $perPage = $request->getGet('per_page') ?? 10;
    
    // --- 2. DATA PROCESSING & FALLBACKS ---
    $siswaData = $siswa_data ?? [];
    
    // FIX: Mencegah Error Fatal jika $pager tidak dikirim oleh Controller
    $totalSiswa = isset($pager) ? $pager->getTotal('siswa') : count($siswaData); 
    $currentPage = isset($pager) ? $pager->getCurrentPage('siswa') : 1;
    $totalPages = isset($pager) ? $pager->getPageCount('siswa') : 1;
    
    // FIX: Mencegah Dropdown Unit 'Mati' jika $jenjang_list kosong dari Controller
    if (empty($jenjang_list)) {
        $db = \Config\Database::connect();
        if ($db->tableExists('jenjang_sekolah')) {
            $jenjang_list = $db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get()->getResultArray();
        }
    }
    
    // Hitung statistik visual
    $countAktif = 0; $countL = 0; $countP = 0;
    $statusDist = ['aktif'=>0, 'terdaftar'=>0, 'lulus'=>0, 'pindah'=>0, 'mutasi'=>0, 'dikeluarkan'=>0, 'meninggal'=>0];

    foreach ($siswaData as $row) {
        $s = (array) $row;
        $st = strtolower($s['status'] ?? 'terdaftar');
        if ($st == 'aktif') $countAktif++;
        
        // Safe count for charts
        if (array_key_exists($st, $statusDist)) {
            $statusDist[$st]++;
        }

        $jk = strtoupper($s['jenis_kelamin'] ?? '');
        if ($jk == 'L') $countL++;
        if ($jk == 'P') $countP++;
    }

    // --- 3. HELPER FUNCTIONS ---
    if (!function_exists('getJenjangBadge')) {
        function getJenjangBadge($kode) {
            $kode = strtoupper($kode ?? '');
            return match ($kode) {
                'SD', 'MI' => 'bg-rose-100 text-rose-700 border-rose-200',
                'SMP', 'MTS' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'SMA', 'SMK', 'MA' => 'bg-sky-100 text-sky-700 border-sky-200',
                'TK', 'PAUD' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        }
    }
    
    $buildPageUrl = function($pageNum) use ($currentUnit, $currentAngkatan, $search, $perPage) {
        return current_url() . '?' . http_build_query([
            'unit' => $currentUnit,
            'angkatan' => $currentAngkatan,
            'search' => $search,
            'per_page' => $perPage,
            'page_siswa' => $pageNum
        ]);
    };

    // Helper value extractor (Aman untuk Object/Array)
    $getValue = function($data, $key) {
        if (is_object($data)) return $data->{$key} ?? null;
        if (is_array($data)) return $data[$key] ?? null;
        return null;
    };
?>

<div class="space-y-6" x-data="{ activeTab: 'master' }">

    <!-- HEADER SECTION -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/dashboard') ?>" class="hover:text-indigo-600 transition-colors">MASTER DATA</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600">SISWA</li>
                </ol>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Database Master Siswa
                </h1>
                
                <!-- BADGE UNIT AKTIF (Informasi Visual) -->
                <?php if($isSuperAdmin && $currentUnit === 'GLOBAL'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide border border-indigo-200">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide border border-emerald-200">
                        Unit <?= esc($currentUnit) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Manajemen identitas, demografi, dan riwayat akademik siswa.
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            
            <!-- 1. DROPDOWN UNIT DI HEADER (Superadmin Only) -->
            <?php if ($isSuperAdmin): ?>
                <form action="" method="get" class="relative group z-50">
                    <!-- Pertahankan filter lain saat ganti unit -->
                    <input type="hidden" name="search" value="<?= esc($search) ?>">
                    <input type="hidden" name="angkatan" value="<?= esc($currentAngkatan) ?>">
                    <input type="hidden" name="per_page" value="<?= esc($perPage) ?>">

                    <div class="relative">
                        <select name="unit" onchange="this.form.submit()" 
                                class="pl-10 pr-10 py-2.5 text-[11px] font-black bg-white border-2 border-slate-100 rounded-2xl appearance-none cursor-pointer focus:border-indigo-500 shadow-sm uppercase tracking-wider min-w-[160px] transition-all outline-none text-slate-700 hover:border-indigo-300">
                            <option value="GLOBAL" <?= ($currentUnit === 'GLOBAL') ? 'selected' : '' ?>>SEMUA UNIT</option>
                            <?php 
                                // Loop aman untuk jenjang_list
                                if(!empty($jenjang_list) && is_iterable($jenjang_list)): 
                                    foreach ($jenjang_list as $j): 
                                        $kode = $getValue($j, 'kode_jenjang');
                                        if (strtoupper($kode) === 'GLOBAL') continue;
                            ?>
                                <option value="<?= $kode ?>" <?= ($currentUnit == $kode) ? 'selected' : '' ?>>
                                    UNIT <?= strtoupper($kode) ?>
                                </option>
                            <?php 
                                    endforeach; 
                                endif; 
                            ?>
                        </select>
                        <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] pointer-events-none"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 text-[9px] pointer-events-none"></i>
                    </div>
                </form>
            <?php endif; ?>

            <a href="<?= route_to('siswa_new') ?>" 
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-indigo-600/20 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-user-plus text-xs"></i> <span>Tambah Siswa</span>
            </a>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-indigo-600 p-5 rounded-[2rem] shadow-xl shadow-indigo-100 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Total Database</p>
                <h3 class="text-3xl font-black italic"><?= number_format($totalSiswa) ?> <span class="text-xs font-bold text-indigo-100 opacity-80">Siswa</span></h3>
            </div>
            <i class="fas fa-database absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>
        
        <!-- Aktif -->
        <div class="bg-emerald-600 p-5 rounded-[2rem] shadow-xl shadow-emerald-100 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Status Aktif (Page)</p>
                <h3 class="text-3xl font-black italic"><?= $countAktif ?> <span class="text-xs font-bold text-emerald-100 opacity-80">Jiwa</span></h3>
            </div>
            <i class="fas fa-user-check absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Gender -->
        <div class="bg-rose-600 p-5 rounded-[2rem] shadow-xl shadow-rose-100 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Gender (Page)</p>
                <h3 class="text-3xl font-black">
                    <?= $countL ?><span class="text-sm font-bold opacity-80 mx-1">L</span>
                    <span class="opacity-50 text-sm">/</span> 
                    <?= $countP ?><span class="text-sm font-bold opacity-80 ml-1">P</span>
                </h3>
            </div>
            <i class="fas fa-venus-mars absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Chart -->
        <div class="bg-white p-5 rounded-[2rem] border-2 border-slate-50 flex items-center gap-4 relative overflow-hidden">
            <div class="w-16 h-16 shrink-0 relative z-10">
                <canvas id="chartStatusSiswa"></canvas>
            </div>
            <div class="relative z-10">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Rasio Aktif</p>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">
                     <?= count($siswaData) > 0 ? round(($countAktif / count($siswaData)) * 100) : 0 ?>%
                </h3>
            </div>
        </div>
    </div>

    <!-- Alert Handler -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between animate-fade-in-down">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
                <span class="text-sm font-bold text-emerald-800 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between animate-fade-in-down">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-rose-500 mr-3"></i>
                <span class="text-sm font-bold text-rose-800 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif ?>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col min-h-[500px] relative z-0">
        
        <!-- 2. FILTER BAR (SEARCH, ANGKATAN, LIMIT) -->
        <div class="border-b border-slate-100 p-5 bg-slate-50/50">
             <form action="" method="get" class="flex flex-col md:flex-row gap-3 w-full md:w-auto items-center justify-between">
                <!-- Keep Unit Filter Hidden Value if exists -->
                <?php if ($currentUnit && $currentUnit !== 'GLOBAL'): ?>
                    <input type="hidden" name="unit" value="<?= esc($currentUnit) ?>">
                <?php endif; ?>

                <!-- SEARCH INPUT -->
                <div class="relative group w-full md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari Nama/NIS/NISN..." 
                           class="w-full pl-10 pr-4 py-2.5 text-xs font-bold bg-white border-2 border-slate-100 rounded-2xl focus:border-indigo-500 shadow-sm uppercase tracking-wider transition-all outline-none">
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <!-- FILTER ANGKATAN (PENGGANTI JURUSAN) -->
                    <div class="relative w-full md:w-48">
                         <select name="angkatan" onchange="this.form.submit()" 
                                class="w-full pl-3 pr-8 py-2.5 text-xs font-bold bg-white border-2 border-slate-100 rounded-2xl cursor-pointer focus:border-indigo-500 shadow-sm outline-none appearance-none uppercase text-slate-600">
                            <option value="">Semua Angkatan</option>
                            <?php 
                            $tahunIni = date('Y');
                            // Loop 12 tahun terakhir
                            for ($i = $tahunIni; $i >= $tahunIni - 12; $i--): 
                            ?>
                                <option value="<?= $i ?>" <?= ($currentAngkatan == $i) ? 'selected' : '' ?>>
                                    Angkatan <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <i class="fas fa-calendar-check absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-[9px] pointer-events-none"></i>
                    </div>

                    <!-- PAGINATION LIMIT -->
                    <div class="relative w-28">
                        <select name="per_page" onchange="this.form.submit()" 
                                class="w-full pl-3 pr-8 py-2.5 text-xs font-bold bg-white border-2 border-slate-100 rounded-2xl cursor-pointer focus:border-indigo-500 shadow-sm outline-none appearance-none uppercase text-slate-600">
                            <?php foreach([10, 25, 50, 100] as $lim): ?>
                                <option value="<?= $lim ?>" <?= ($perPage == $lim) ? 'selected' : '' ?>><?= $lim ?> DATA</option>
                            <?php endforeach; ?>
                        </select>
                         <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-300 text-[9px] pointer-events-none"></i>
                    </div>
                    
                    <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-700 transition-all shadow-sm">
                        Filter
                    </button>
                </div>
             </form>
        </div>

        <!-- 3. TAB NAVIGATION (DI BAWAH FILTER BAR, DI ATAS TABEL) -->
        <div class="border-b border-slate-200 dark:border-slate-800 overflow-x-auto custom-scrollbar bg-white dark:bg-slate-950">
            <nav class="flex px-4 gap-6 min-w-max" aria-label="Tabs">
                <?php foreach(['master'=>'Data Pokok', 'demografi'=>'Demografi', 'akademik'=>'Akademik', 'keluarga'=>'Keluarga'] as $key => $label): ?>
                <button @click="activeTab = '<?= $key ?>'" 
                        :class="activeTab === '<?= $key ?>' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                        class="py-4 px-4 border-b-2 font-black text-xs uppercase tracking-widest whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas <?= $key == 'master' ? 'fa-id-card' : ($key == 'demografi' ? 'fa-map-marker-alt' : ($key == 'akademik' ? 'fa-graduation-cap' : 'fa-users')) ?>"></i> <?= $label ?>
                </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="p-0 flex-1 relative bg-white dark:bg-slate-900">
            <!-- TAB 1: DATA POKOK -->
            <div x-show="activeTab === 'master'" x-transition:enter.opacity.duration.300ms class="overflow-x-auto h-full">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                        <tr>
                            <th class="px-6 py-4 w-10 text-center">No</th>
                            <th class="px-6 py-4">Identitas / NIS</th>
                            <th class="px-6 py-4 text-center">Jenjang</th>
                            <th class="px-6 py-4">Nama Lengkap</th>
                            <th class="px-6 py-4 text-center">Gender</th>
                            <th class="px-6 py-4 text-center">Angkatan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php 
                        $startNo = ($currentPage - 1) * $perPage + 1;
                        if(empty($siswaData)): ?>
                             <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                        <?php else:
                            foreach ($siswaData as $row): 
                                $s = (array) $row;
                        ?>
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-black text-indigo-600 dark:text-indigo-400 mb-0.5"><?= esc($s['nis'] ?? '-') ?></div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono bg-slate-100 px-1.5 py-0.5 rounded inline-block">NISN: <?= esc($s['nisn'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php $kj = $s['kode_jenjang'] ?? '-'; ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-black border uppercase tracking-wider shadow-sm <?= getJenjangBadge($kj) ?>">
                                        <?= esc($kj) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-white"><?= esc($s['nama_lengkap'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-center font-bold text-slate-600 dark:text-slate-400"><?= esc($s['jenis_kelamin'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300"><?= esc($s['angkatan'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                    $st = strtolower($s['status'] ?? 'terdaftar');
                                    $statusClass = match($st) {
                                        'aktif', 'terdaftar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'lulus' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        default => 'bg-amber-100 text-amber-700 border-amber-200'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider border <?= $statusClass ?>">
                                        <?= esc($st) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <?php $id = $s['id'] ?? 0; ?>
                                        <a href="<?= route_to('siswa_show', $id) ?>" 
                                           class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-indigo-500 hover:border-indigo-500 hover:bg-indigo-50 rounded-lg shadow-sm transition-all" 
                                           title="Profil">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="<?= route_to('siswa_edit', $id) ?>" 
                                           class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 rounded-lg shadow-sm transition-all" 
                                           title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $id ?>, '<?= esc($s['nama_lengkap']) ?>')" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-rose-500 hover:border-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition-all" 
                                                title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 2: DEMOGRAFI -->
            <div x-show="activeTab === 'demografi'" x-cloak class="overflow-x-auto h-full">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                        <tr>
                            <th class="px-6 py-4 w-10 text-center">No</th>
                            <th class="px-6 py-4">Nama Siswa</th>
                            <th class="px-6 py-4">Alamat Tinggal</th>
                            <th class="px-6 py-4">Orang Tua</th>
                            <th class="px-6 py-4">Telepon Ortu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php 
                        $startNo = ($currentPage - 1) * $perPage + 1;
                        if(empty($siswaData)): ?>
                             <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                        <?php else:
                        foreach ($siswaData as $row): 
                            $s = (array) $row;
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white"><?= esc($s['nama_lengkap'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <div class="truncate max-w-[250px] font-medium text-slate-600 dark:text-slate-300 italic" title="<?= esc($s['alamat_demografi'] ?? '') ?>"><?= esc($s['alamat_demografi'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-400">
                                    <div class="flex flex-col gap-1">
                                        <div><span class="font-black text-slate-400 uppercase mr-1">Ayah:</span> <span class="font-bold"><?= esc($s['nama_ayah'] ?? '-') ?></span></div>
                                        <div><span class="font-black text-slate-400 uppercase mr-1">Ibu:</span> <span class="font-bold"><?= esc($s['nama_ibu'] ?? '-') ?></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600 dark:text-slate-400 font-bold">
                                    <?= esc($s['telepon_ortu'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 3: AKADEMIK -->
            <div x-show="activeTab === 'akademik'" x-cloak class="overflow-x-auto h-full">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                        <tr>
                            <th class="px-6 py-4 w-10 text-center">No</th>
                            <th class="px-6 py-4">Nama Siswa</th>
                            <th class="px-6 py-4 text-center">Kelas Saat Ini</th>
                            <th class="px-6 py-4 text-center">Tahun Ajaran</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php 
                        $startNo = ($currentPage - 1) * $perPage + 1;
                        if(empty($siswaData)): ?>
                             <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50/30">Data tidak ditemukan.</td></tr>
                        <?php else:
                        foreach ($siswaData as $row): 
                            $s = (array) $row;
                            $en = $s['akademik'] ?? []; // Struktur dari Controller
                        ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white"><?= esc($s['nama_lengkap'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($s['nama_kelas'])): ?>
                                        <span class="inline-flex px-3 py-1 rounded-md bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-black uppercase tracking-wider">
                                            <?= esc($s['nama_kelas']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs italic font-bold">Belum Masuk Kelas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-sky-600 dark:text-sky-400 text-xs"><?= esc($s['tahun_ajaran'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <a href="<?= route_to('siswa_show', $s['id'] ?? 0) ?>" class="text-indigo-600 hover:text-indigo-800 underline decoration-2 font-bold text-xs">Lihat Histori</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 4: KELUARGA (Placeholder) -->
            <div x-show="activeTab === 'keluarga'" x-cloak class="overflow-x-auto h-full p-12 text-center text-slate-500 border-t border-slate-100">
                <i class="fas fa-users text-4xl mb-4 text-slate-300"></i>
                <h5 class="font-bold text-slate-600 uppercase tracking-widest text-xs">Data Keluarga Detail</h5>
                <p class="text-xs mt-2 text-slate-400">Silakan klik tombol "Profil" (ikon mata) pada Tab Data Pokok untuk melihat rincian keluarga.</p>
            </div>

        </div>

        <!-- Pagination Controls Footer -->
        <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-900/50">
            <?php 
                $startRecord = ($totalSiswa == 0) ? 0 : ($currentPage - 1) * $perPage + 1;
                $endRecord = min($currentPage * $perPage, $totalSiswa);
                
                // Logic window halaman
                $delta = 2; 
                $range = [];
                for ($i = max(1, $currentPage - $delta); $i <= min($totalPages, $currentPage + $delta); $i++) {
                    $range[] = $i;
                }
                if ($range && $range[0] > 1) {
                    if ($range[0] > 2) array_unshift($range, '...');
                    array_unshift($range, 1);
                }
                if ($range && end($range) < $totalPages) {
                    if (end($range) < $totalPages - 1) $range[] = '...';
                    $range[] = $totalPages;
                }
            ?>
            <div class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wide">
                Menampilkan <span class="text-slate-900 dark:text-white"><?= $startRecord ?></span> - <span class="text-slate-900 dark:text-white"><?= $endRecord ?></span> dari <span class="text-slate-900 dark:text-white"><?= number_format($totalSiswa) ?></span> data
            </div>
            
            <div class="flex items-center gap-1.5">
                <!-- Prev -->
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $buildPageUrl($currentPage - 1) ?>" 
                       class="w-8 h-8 flex items-center justify-center rounded-lg border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-300 transition-all shadow-sm" title="Sebelumnya">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                <?php else: ?>
                    <button disabled class="w-8 h-8 flex items-center justify-center rounded-lg border-2 border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                <?php endif; ?>
                
                <!-- Pages -->
                <div class="hidden sm:flex items-center gap-1">
                    <?php foreach ($range as $p): ?>
                        <?php if ($p === '...'): ?>
                            <span class="w-8 h-8 flex items-center justify-center text-slate-400 text-xs font-black tracking-widest">...</span>
                        <?php else: ?>
                            <a href="<?= $buildPageUrl($p) ?>" 
                               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-black border-2 transition-all shadow-sm
                               <?= $currentPage == $p 
                                   ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700' 
                                   : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-300' 
                               ?>">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Next -->
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $buildPageUrl($currentPage + 1) ?>" 
                       class="w-8 h-8 flex items-center justify-center rounded-lg border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-300 transition-all shadow-sm" title="Selanjutnya">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                <?php else: ?>
                    <button disabled class="w-8 h-8 flex items-center justify-center rounded-lg border-2 border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-t-8 border-rose-600">
            <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-trash-alt text-rose-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-black text-slate-900 uppercase italic tracking-tight">Hapus Siswa?</h3>
                        <div class="mt-2">
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                Anda akan menghapus data siswa: <strong id="deleteTargetName" class="text-slate-800 underline"></strong>.<br>
                                Data ini akan dipindahkan ke sampah (Soft Delete) dan dapat dipulihkan jika diperlukan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <form id="deleteForm" action="" method="post" class="w-full sm:w-auto">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
                        Ya, Hapus
                    </button>
                </form>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-slate-200 shadow-sm px-4 py-2 bg-white text-xs font-black text-slate-700 uppercase tracking-widest hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctxSiswa = document.getElementById('chartStatusSiswa');
        if (ctxSiswa) {
            new Chart(ctxSiswa.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Terdaftar', 'Lulus', 'Lainnya'],
                    datasets: [{
                        data: [
                            <?= $statusDist['aktif'] ?>, 
                            <?= $statusDist['terdaftar'] ?>, 
                            <?= $statusDist['lulus'] ?>, 
                            <?= $statusDist['pindah'] + $statusDist['mutasi'] + $statusDist['dikeluarkan'] + $statusDist['meninggal'] ?>
                        ],
                        backgroundColor: ['#10b981', '#0ea5e9', '#6366f1', '#f59e0b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    });

    // Modal Logic
    function confirmDelete(id, nama) {
        const modal = document.getElementById('deleteModal');
        const nameSpan = document.getElementById('deleteTargetName');
        const form = document.getElementById('deleteForm');
        
        nameSpan.textContent = nama;
        form.action = '<?= base_url('app/masterdata/siswa/delete/') ?>/' + id;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") closeModal();
    });
</script>
<?= $this->endSection() ?>