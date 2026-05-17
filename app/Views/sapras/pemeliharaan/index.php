<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// =========================================================================
// KALKULASI STATISTIK (Dari Data Halaman Saat Ini)
// =========================================================================
$jmlDirencanakan = 0; $jmlProses = 0; $jmlSelesai = 0; $jmlBatal = 0;
$totalBiaya = 0;

if (!empty($pemeliharaan)) {
    foreach($pemeliharaan as $p) {
        $totalBiaya += (float)$p['biaya'];
        
        if($p['status'] == 'Direncanakan') $jmlDirencanakan++;
        elseif($p['status'] == 'Sedang Proses') $jmlProses++;
        elseif($p['status'] == 'Selesai') $jmlSelesai++;
        elseif($p['status'] == 'Batal') $jmlBatal++;
    }
}
$totalItem = count($pemeliharaan);

// Tangkap filter dari URL untuk mempertahankan state di View
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? '';
?>

<div x-data="maintenanceManager()" class="px-4 sm:px-6 py-6 space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-auto">
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 md:mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/sapras/dashboard') ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">SAPRAS</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">PEMELIHARAAN ASET</li>
                </ol>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                    <?= esc($title) ?>
                </h1>
                
                <!-- BADGE UNIT AKTIF -->
                <?php if($isGlobal && $filterJenjang === 'GLOBAL'): ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                        Unit <?= esc($filterJenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">
                Pencatatan riwayat servis, perbaikan kerusakan, dan audit biaya pemeliharaan inventaris.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row w-full md:w-auto items-center gap-3 mt-4 md:mt-0">
            <!-- TOMBOL CETAK LAPORAN REKAPITULASI (A4) -->
            <button type="button" onclick="cetakLaporan()" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-[11px] font-black uppercase tracking-widest rounded-xl shadow-sm transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap">
                <i class="fas fa-print text-sm md:text-xs text-indigo-500"></i> <span>Cetak Rekap (A4)</span>
            </button>

            <a href="<?= base_url('app/sapras/pemeliharaan/new') ?>" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-plus text-sm md:text-xs"></i> <span>Buat Log Servis</span>
            </a>
        </div>
    </div>

    <!-- KPI CARDS (RESPONSIF) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-calendar-alt absolute -right-4 -bottom-4 text-6xl text-blue-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Direncanakan</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-blue-700 dark:text-blue-300 tracking-tighter"><?= $jmlDirencanakan ?> <span class="text-xs font-bold opacity-70">Aset</span></h3>
            </div>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform animate-pulse">
            <i class="fas fa-tools absolute -right-4 -bottom-4 text-6xl text-amber-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest mb-1">Sedang Proses Servis</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-amber-700 dark:text-amber-300 tracking-tighter"><?= $jmlProses ?> <span class="text-xs font-bold opacity-70">Aset</span></h3>
            </div>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-check-double absolute -right-4 -bottom-4 text-6xl text-emerald-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Servis Selesai</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-emerald-700 dark:text-emerald-300 tracking-tighter"><?= $jmlSelesai ?> <span class="text-xs font-bold opacity-70">Aset</span></h3>
            </div>
        </div>

        <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-wallet absolute -right-4 -bottom-4 text-6xl text-purple-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest mb-1">Biaya Servis (Page)</p>
                <h3 class="text-lg md:text-xl font-black italic text-purple-700 dark:text-purple-300 tracking-tighter mt-1">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <!-- FILTER BAR LENGKAP (Pencarian & Status) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm relative z-20">
        <form action="" method="get" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <?php if ($isGlobal): ?>
            <!-- Filter Unit -->
            <div class="relative lg:col-span-1">
                <select name="jenjang" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200 uppercase">
                    <option value="GLOBAL" <?= ($filterJenjang === 'GLOBAL') ? 'selected' : '' ?>>SEMUA UNIT</option>
                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= ($filterJenjang == $kode) ? 'selected' : '' ?>>UNIT <?= strtoupper($kode) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>
            <?php endif; ?>

            <!-- Filter Status -->
            <div class="relative <?= $isGlobal ? 'lg:col-span-1' : 'lg:col-span-1' ?>">
                <select name="status" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200">
                    <option value="">Semua Status Servis</option>
                    <option value="Direncanakan" <?= ($filterStatus == 'Direncanakan') ? 'selected' : '' ?>>Direncanakan</option>
                    <option value="Sedang Proses" <?= ($filterStatus == 'Sedang Proses') ? 'selected' : '' ?>>Sedang Proses</option>
                    <option value="Selesai" <?= ($filterStatus == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="Batal" <?= ($filterStatus == 'Batal') ? 'selected' : '' ?>>Batal</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>

            <!-- Search Bar -->
            <div class="relative <?= $isGlobal ? 'lg:col-span-1' : 'lg:col-span-2' ?>">
                <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari Nama Aset, Kode, atau Pelaksana..." 
                       class="w-full pl-10 pr-4 py-3 md:py-2.5 text-xs font-bold bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-indigo-500 outline-none transition-all text-slate-700 dark:text-slate-200">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
            </div>

            <!-- Button Filter -->
            <button type="submit" class="w-full lg:col-span-1 sm:col-span-2 px-5 py-3 md:py-2.5 bg-slate-800 dark:bg-slate-700 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 dark:hover:bg-slate-600 transition-all shadow-sm">
                Terapkan Pencarian
            </button>
        </form>
    </div>

    <!-- ALERT HANDLERS -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- MAIN TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col relative z-0">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-10 text-center">No</th>
                        <th class="px-6 py-4">Aset & Lokasi</th>
                        <th class="px-6 py-4">Jenis Servis & Pelaksana</th>
                        <th class="px-6 py-4 text-center">Jadwal</th>
                        <th class="px-6 py-4 text-right">Biaya (Rp)</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php 
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $startNo = ($page - 1) * 10 + 1; 
                    if(empty($pemeliharaan)): ?>
                         <tr>
                             <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50/30 dark:bg-gray-800/20">
                                 <i class="fas fa-tools text-3xl mb-3 opacity-50 block"></i>
                                 Belum ada data riwayat pemeliharaan / servis.
                             </td>
                         </tr>
                    <?php else:
                        foreach ($pemeliharaan as $row): 
                            
                            // Styling Status Pemeliharaan
                            $statusClass = 'text-slate-600 bg-slate-100 border-slate-200';
                            if ($row['status'] == 'Direncanakan') $statusClass = 'text-blue-600 bg-blue-50 border-blue-200';
                            if ($row['status'] == 'Sedang Proses') $statusClass = 'text-amber-600 bg-amber-50 border-amber-200 animate-pulse';
                            if ($row['status'] == 'Selesai') $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                            if ($row['status'] == 'Batal') $statusClass = 'text-rose-600 bg-rose-50 border-rose-200';

                            // Tipe Pemeliharaan Badge
                            $jenisClass = ($row['jenis_pemeliharaan'] == 'Rutin/Preventif') 
                                ? 'bg-sky-100 text-sky-700 border-sky-200' 
                                : 'bg-red-100 text-red-700 border-red-200';
                    ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                            
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 dark:text-white truncate max-w-[200px]" title="<?= esc($row['nama_aset']) ?>">
                                    <?= esc($row['nama_aset']) ?>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[10px] font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded tracking-widest border border-indigo-100 dark:border-indigo-800">
                                        <i class="fas fa-barcode mr-1"></i><?= esc($row['kode_aset']) ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase"><i class="fas fa-map-marker-alt"></i> <?= esc($row['nama_lokasi'] ?? 'Gudang') ?></span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border <?= $jenisClass ?> dark:bg-opacity-20 dark:border-opacity-30">
                                        <?= esc($row['jenis_pemeliharaan']) ?>
                                    </span>
                                </div>
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    <i class="fas fa-user-cog text-slate-400 w-4"></i> <?= esc($row['pelaksana'] ?? 'Internal / Belum Ditentukan') ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?>
                                </div>
                                <?php if($row['tanggal_selesai']): ?>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        s/d <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <?php if($row['biaya'] > 0): ?>
                                    <span class="font-black italic text-emerald-600 dark:text-emerald-400">
                                        Rp <?= number_format($row['biaya'], 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-slate-400 italic">Gratis / Belum Diinput</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest border <?= $statusClass ?> dark:bg-opacity-10 dark:border-opacity-30">
                                    <?= esc($row['status']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <?php $canManage = $isGlobal || strtoupper($row['kode_jenjang']) === $sessionJenjang; ?>
                                
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <!-- TOMBOL QUICK VIEW -->
                                    <button @click="openDetail(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-blue-500 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg shadow-sm transition-all" 
                                            title="Lihat Detail Kerusakan">
                                        <i class="fas fa-eye text-sm md:text-xs"></i>
                                    </button>

                                    <!-- TOMBOL EDIT & HAPUS -->
                                    <?php if($canManage): ?>
                                        <a href="<?= base_url('app/sapras/pemeliharaan/edit/' . $row['id']) ?>" 
                                           class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-amber-500 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg shadow-sm transition-all" 
                                           title="Update Status / Biaya">
                                            <i class="fas fa-pen text-sm md:text-xs"></i>
                                        </a>
                                        
                                        <button @click="confirmDelete(<?= $row['id'] ?>, '<?= esc(addslashes($row['nama_aset'])) ?>')" 
                                                class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-rose-500 hover:border-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg shadow-sm transition-all" 
                                                title="Hapus Log">
                                            <i class="fas fa-trash-alt text-sm md:text-xs"></i>
                                        </button>
                                        
                                    <?php else: ?>
                                        <i class="fas fa-lock text-slate-300 dark:text-slate-600 w-10 md:w-8 text-center" title="Terkunci (Milik Unit Lain)"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 flex justify-center">
            <?= isset($pager) ? $pager->links('default', 'tailwind_pagination') : '' ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: QUICK VIEW DETAIL PEMELIHARAAN -->
    <!-- ========================================== -->
    <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end md:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="detailModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl md:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-5 md:p-6 flex justify-between items-start relative overflow-hidden">
                    <i class="fas fa-tools absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12"></i>
                    <div class="relative z-10 text-white w-full pr-10">
                        <div class="text-[10px] font-black uppercase tracking-widest text-cyan-200 mb-1" x-text="activeItem.jenis_pemeliharaan"></div>
                        <h3 class="text-xl md:text-2xl font-black tracking-tight leading-tight mb-2" x-text="activeItem.nama_aset"></h3>
                        <div class="inline-flex items-center gap-2 font-mono text-xs bg-black/30 px-2.5 py-1 rounded-md border border-white/20">
                            <i class="fas fa-barcode text-cyan-300"></i>
                            <span x-text="activeItem.kode_aset"></span>
                        </div>
                    </div>
                    <button @click="detailModalOpen = false" class="absolute top-4 right-4 z-10 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 w-8 h-8 rounded-full transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-5 md:p-8">
                    
                    <!-- Status Badge -->
                    <div class="mb-6 flex justify-center">
                        <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border" 
                              :class="{
                                  'bg-blue-100 text-blue-700 border-blue-300': activeItem.status === 'Direncanakan',
                                  'bg-amber-100 text-amber-700 border-amber-300 animate-pulse': activeItem.status === 'Sedang Proses',
                                  'bg-emerald-100 text-emerald-700 border-emerald-300': activeItem.status === 'Selesai',
                                  'bg-rose-100 text-rose-700 border-rose-300': activeItem.status === 'Batal'
                              }" x-text="'Status: ' + activeItem.status"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        
                        <!-- Kolom Detail Pelaksana & Lokasi -->
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Lokasi Aset</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-map-marker-alt text-blue-500 w-4"></i> <span x-text="activeItem.nama_lokasi || 'Tidak ada info lokasi'"></span>
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Teknisi / Vendor Pelaksana</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-user-cog text-blue-500 w-4"></i> <span x-text="activeItem.pelaksana || 'Belum ditentukan'"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Kolom Jadwal & Biaya -->
                        <div class="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Mulai Servis</h4>
                                <p class="text-sm font-black text-slate-800 dark:text-white" x-text="formatDate(activeItem.tanggal_mulai)"></p>
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estimasi / Selesai</h4>
                                <p class="text-sm font-black" 
                                   :class="activeItem.status === 'Selesai' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-300'" 
                                   x-text="activeItem.tanggal_selesai ? formatDate(activeItem.tanggal_selesai) : 'Belum selesai'"></p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Biaya Pemeliharaan</h4>
                                <p class="text-lg font-black text-purple-600 dark:text-purple-400 italic">Rp <span x-text="formatRupiah(activeItem.biaya)"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan / Laporan Kerusakan -->
                    <div class="mt-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2"><i class="fas fa-clipboard-list mr-1"></i> Detail Kerusakan / Tindakan Servis</h4>
                            <p class="text-sm text-slate-700 dark:text-slate-300 font-medium whitespace-pre-line leading-relaxed" x-text="activeItem.keterangan || 'Tidak ada catatan.'"></p>
                        </div>
                    </div>

                </div>
                
                <!-- FOOTER MODAL (AKSI CETAK) -->
                <div class="bg-slate-50 dark:bg-slate-900 px-5 md:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-t border-slate-100 dark:border-slate-800">
                    
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <!-- LINK CETAK STIKER KONTROL SERVIS -->
                        <a :href="'<?= base_url('app/sapras/pemeliharaan/print-label/') ?>/' + activeItem.id" target="_blank" class="w-full sm:w-auto justify-center text-xs font-black text-cyan-600 dark:text-cyan-400 uppercase tracking-widest hover:underline flex items-center gap-2 py-2 sm:py-0">
                            <i class="fas fa-print"></i> Cetak Label Servis
                        </a>

                        <!-- LINK CETAK KARTU RIWAYAT ASET -->
                        <a :href="'<?= base_url('app/sapras/pemeliharaan/print-riwayat/') ?>/' + activeItem.id_aset" target="_blank" class="w-full sm:w-auto justify-center text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest hover:underline flex items-center gap-2 py-2 sm:py-0">
                            <i class="fas fa-clipboard-list"></i> Cetak Riwayat
                        </a>
                    </div>

                    <button type="button" @click="detailModalOpen = false" class="w-full sm:w-auto px-8 py-3 sm:py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors shadow-sm">
                        Tutup Panel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: KONFIRMASI HAPUS (RESPONSIF) -->
    <!-- ========================================== -->
    <div x-show="deleteModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="deleteModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl sm:rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border-t-8 border-rose-600">
                <div class="bg-white dark:bg-slate-800 px-6 pt-6 pb-4 sm:p-8">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-trash-alt text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:ml-4 text-center sm:text-left">
                            <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Log Servis?</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                    Menghapus log servis untuk: <strong x-text="activeItem.nama_aset" class="text-slate-800 dark:text-slate-200"></strong>.<br>
                                    Jika aset belum selesai diservis, sistem akan otomatis mereset status aset di katalog menjadi "Tersedia".
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-3">
                    <a :href="'<?= base_url('app/sapras/pemeliharaan/delete/') ?>/' + activeItem.id" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 sm:py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
                        Ya, Hapus
                    </a>
                    <button type="button" @click="deleteModalOpen = false" class="w-full inline-flex justify-center rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-4 py-3 sm:py-2 bg-white dark:bg-slate-800 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:w-auto transition-all">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT ALPINE JS MANAGER -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('maintenanceManager', () => ({
            detailModalOpen: false,
            deleteModalOpen: false,
            activeItem: {},

            openDetail(data) {
                this.activeItem = data;
                this.detailModalOpen = true;
            },
            
            confirmDelete(id, nama) {
                this.activeItem = { id: id, nama_aset: nama };
                this.deleteModalOpen = true;
            },

            formatRupiah(angka) {
                if (!angka) return '0';
                let number_string = angka.toString().replace(/[^,\d]/g, ''),
                    split = number_string.split(','),
                    sisa = split[0].length % 3,
                    rupiah = split[0].substr(0, sisa),
                    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    separator = sisa ? '.' : '';
                    rupiah += separator + ribuan.join('.');
                }
                return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            },

            formatDate(dateStr) {
                if(!dateStr) return '-';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateStr).toLocaleDateString('id-ID', options);
            }
        }))
    });

    // SCRIPT UNTUK CETAK LAPORAN MEMBAWA DATA FILTER YANG SEDANG AKTIF
    function cetakLaporan() {
        const form = document.querySelector('form'); // Ambil form filter bar jika ada
        if (form) {
            const url = new URL('<?= base_url('app/sapras/pemeliharaan/print-report') ?>');
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (value && key !== 'search') { 
                    url.searchParams.append(key, value);
                }
            }
            window.open(url.toString(), '_blank');
        } else {
            window.open('<?= base_url('app/sapras/pemeliharaan/print-report') ?>', '_blank');
        }
    }
</script>

<?= $this->endSection() ?>