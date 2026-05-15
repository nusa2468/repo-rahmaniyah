<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Manajemen Jenis Pembayaran
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // --- 1. SESSION & ACCESS CONTROL ---
    $session = session();
    $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT'];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);

    // --- 2. DATA PROCESSING (ULTIMATE FIX) ---
    // Deteksi variabel data dengan berbagai kemungkinan nama (Snake case, Camel case, dll)
    $pembayaranData = $jenis_pembayaran 
                   ?? $jenispembayaran 
                   ?? $pembayaran_list 
                   ?? $pembayaran 
                   ?? $data_pembayaran 
                   ?? []; 
    
    // Pastikan filter aman
    $current_filter = $current_filter ?? [];

    // SAFE TOTAL DATA HANDLING
    $totalData = count($pembayaranData);
    if (isset($pager)) {
        try {
            $pagerTotal = $pager->getTotal('jenis_pembayaran');
            if ($pagerTotal > 0) {
                $totalData = $pagerTotal;
            }
        } catch (\Throwable $e) {
            // Fallback silent jika pager error
        }
    }
    
    // Hitung statistik visual
    $countBulanan = 0; $countBebas = 0;
    foreach ($pembayaranData as $row) {
        $row = (array) $row;
        // FIX: Cek 'tipe' dulu (sesuai model), baru fallback ke 'tipe_pembayaran'
        $tipe = strtoupper($row['tipe'] ?? $row['tipe_pembayaran'] ?? '');
        
        // Cek variasi penulisan tipe
        if (in_array($tipe, ['BULANAN', 'MONTHLY'])) $countBulanan++;
        if (in_array($tipe, ['BEBAS', 'SEKALI_BAYAR', 'ONCE', 'TAHUNAN'])) $countBebas++;
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
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/dashboard') ?>" class="hover:text-indigo-600 transition-colors">MASTER DATA</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600">KEUANGAN</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Jenis Pembayaran
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pengaturan pos tarif pembayaran siswa (SPP, Gedung, dll).
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Unit Filter (Anti Bocor) -->
            <form action="" method="get" class="relative">
                <?php if (!empty($current_filter['search'])): ?>
                    <input type="hidden" name="search" value="<?= esc($current_filter['search']) ?>">
                <?php endif; ?>

                <div class="relative z-20"> 
                    <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-30 pointer-events-none"></i>
                    <select name="unit" onchange="this.form.submit()" 
                            class="pl-8 pr-8 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-200 shadow-sm appearance-none cursor-pointer relative z-20 w-full sm:w-auto font-bold uppercase">
                        
                        <?php if ($isSuperAdmin): ?>
                            <option value="GLOBAL">Semua Unit</option>
                        <?php endif; ?>

                        <?php if(isset($jenjang_list) && is_iterable($jenjang_list)): ?>
                            <?php foreach ($jenjang_list as $j): 
                                $j_kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                // Filter unit agar user unit tidak melihat unit lain
                                if (!$isSuperAdmin && $j_kode !== $userJenjang) continue;
                            ?>
                                <option value="<?= $j_kode ?>" <?= (($current_filter['unit'] ?? '') == $j_kode) ? 'selected' : '' ?>>
                                    Unit <?= strtoupper($j_kode) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none z-30"></i>
                </div>
            </form>

            <!-- Search Bar -->
            <form action="" method="get" class="relative z-10">
                 <input type="hidden" name="unit" value="<?= esc($current_filter['unit'] ?? '') ?>">
                 
                 <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs z-20"></i>
                 <input type="text" name="search" value="<?= esc($current_filter['search'] ?? '') ?>" placeholder="Cari Jenis Pembayaran..." 
                        class="pl-8 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-slate-200 shadow-sm w-full sm:w-48 relative z-10">
            </form>

            <a href="<?= base_url('app/masterdata/jenispembayaran/new') ?>" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-600/20 transition-all hover:-translate-y-0.5 active:scale-95">
                <i class="fas fa-plus-circle"></i> <span>Tambah</span>
            </a>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MASTERDATA -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto custom-scrollbar no-scrollbar">
        <a href="<?= base_url('app/masterdata/kurikulum') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-scroll mr-2 opacity-50"></i> Kurikulum
        </a>
        <a href="<?= base_url('app/masterdata/matapelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-book mr-2 opacity-50"></i> Mapel
        </a>
        <a href="<?= base_url('app/masterdata/kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-door-open mr-2 opacity-50"></i> Rombel
        </a>
        <a href="<?= base_url('app/masterdata/jurusan') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-graduation-cap mr-2 opacity-50"></i> Jurusan
        </a>
        <a href="<?= base_url('app/masterdata/tahunajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-alt mr-2 opacity-50"></i> TA
        </a>
        <a href="<?= base_url('app/masterdata/siswa') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-users mr-2 opacity-50"></i> Siswa
        </a>
        <!-- Active Tab -->
        <a href="<?= base_url('app/masterdata/jenispembayaran') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-sm">
            <i class="fas fa-money-bill-wave mr-2"></i> Jenis Pembayaran
        </a>
    </div>

    <!-- Analytics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card Total -->
        <div class="bg-indigo-600 text-white p-5 rounded-2xl shadow-lg shadow-indigo-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-indigo-100 uppercase tracking-widest mb-1">Total Jenis</p>
                <h3 class="text-3xl font-black text-white"><?= number_format($totalData) ?> <span class="text-xs font-bold text-indigo-100 opacity-80">POS</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-wallet text-xl"></i>
            </div>
        </div>

        <!-- Card Bulanan -->
        <div class="bg-emerald-600 text-white p-5 rounded-2xl shadow-lg shadow-emerald-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-emerald-100 uppercase tracking-widest mb-1">Tipe Bulanan</p>
                <h3 class="text-3xl font-black text-white"><?= $countBulanan ?> <span class="text-xs font-bold text-emerald-100 opacity-80">SPP/Dll</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
        </div>

        <!-- Card Bebas -->
        <div class="bg-amber-600 text-white p-5 rounded-2xl shadow-lg shadow-amber-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-amber-100 uppercase tracking-widest mb-1">Tipe Bebas</p>
                <h3 class="text-3xl font-black text-white"><?= $countBebas ?> <span class="text-xs font-bold text-amber-100 opacity-80">Gedung/Dll</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-hand-holding-usd text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Main Content Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col min-h-[400px]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-10 text-center">No</th>
                        <th class="px-6 py-4">ID & Unit</th>
                        <th class="px-6 py-4">Pos Pembayaran</th>
                        <th class="px-6 py-4 text-center">Tipe</th>
                        <th class="px-6 py-4 text-center">Tahun Ajaran</th>
                        <th class="px-6 py-4 text-center">Tarif Dasar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if(empty($pembayaranData)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400 italic text-xs">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-2xl opacity-50"></i>
                                    <span>Belum ada data jenis pembayaran.</span>
                                    <span class="text-[9px]">Silakan tambah data baru.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Safe Pagination Handling for Row Number
                        $page = isset($current_filter['per_page']) ? (int)$current_filter['per_page'] : 10;
                        $currentPage = 1;
                        if (isset($pager)) {
                            try {
                                $currentPage = (int)$pager->getCurrentPage('jenis_pembayaran');
                            } catch (\Throwable $e) { $currentPage = 1; }
                        }
                        if ($currentPage < 1) $currentPage = 1;
                        $startNo = ($currentPage - 1) * $page + 1;

                        foreach ($pembayaranData as $row): 
                            $p = (array) $row;
                        ?>
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-mono text-gray-400 select-all bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-600">
                                            #<?= esc($p['id'] ?? '-') ?>
                                        </span>
                                        <?php $kj = $p['kode_jenjang'] ?? '-'; ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-black border uppercase tracking-wider shadow-sm <?= getJenjangBadge($kj) ?>">
                                            <?= esc($kj) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-black text-slate-800 dark:text-white"><?= esc($p['nama_pembayaran'] ?? 'Pos Pembayaran') ?></div>
                                    <div class="text-[10px] text-slate-500 italic"><?= esc($p['keterangan'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php 
                                        // FIX TIPE: Gunakan 'tipe' dari model, fallback ke 'tipe_pembayaran' jika null
                                        $tipeRaw = $p['tipe'] ?? $p['tipe_pembayaran'] ?? '-';
                                        $tipe = strtoupper($tipeRaw);
                                        
                                        $bg = (in_array($tipe, ['BULANAN', 'MONTHLY'])) 
                                            ? 'bg-indigo-100 text-indigo-700 border-indigo-200' 
                                            : 'bg-amber-100 text-amber-700 border-amber-200';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider border <?= $bg ?>">
                                        <?= esc($tipe) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-600 text-xs">
                                    <?= esc($p['tahun_ajaran'] ?? 'Semua') ?>
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-slate-700">
                                    Rp <?= number_format((float)($p['nominal'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <!-- Teknik Inline Action Buttons -->
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="<?= base_url('app/masterdata/jenispembayaran/edit/' . ($p['id'] ?? 0)) ?>" 
                                           class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 rounded-lg shadow-sm transition-all" 
                                           title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        
                                        <form action="<?= base_url('app/masterdata/jenispembayaran/delete/' . ($p['id'] ?? 0)) ?>" method="post" class="contents" onsubmit="return confirm('Hapus Pos Pembayaran ini? Data tagihan siswa mungkin akan hilang.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" 
                                                    class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-rose-500 hover:border-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition-all" 
                                                    title="Hapus">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/30">
            <?php if (isset($pager)): ?>
                <?= $pager->links('jenis_pembayaran', 'tailwind_pagination') ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>