<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// =========================================================================
// KALKULASI STATISTIK (Dari Data Halaman Saat Ini)
// =========================================================================
$jmlMenunggu = 0; $jmlDipinjam = 0; $jmlDikembalikan = 0; $jmlTerlambat = 0;

if (!empty($peminjaman)) {
    foreach($peminjaman as $p) {
        if($p['status'] == 'Menunggu') $jmlMenunggu++;
        elseif($p['status'] == 'Dipinjam') $jmlDipinjam++;
        elseif($p['status'] == 'Dikembalikan') $jmlDikembalikan++;
        elseif($p['status'] == 'Terlambat') $jmlTerlambat++;
    }
}
$totalItem = count($peminjaman);
?>

<div x-data="loanManager()" class="px-4 sm:px-6 py-6 space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-auto">
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 md:mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/sapras/dashboard') ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">SAPRAS</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">PEMINJAMAN ASET</li>
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
                Pusat logistik untuk melacak pergerakan aset yang dipinjam oleh pegawai atau siswa.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row w-full md:w-auto items-center gap-3 mt-4 md:mt-0">
            <a href="<?= base_url('app/sapras/peminjaman/new') ?>" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-plus text-sm md:text-xs"></i> <span>Registrasi Peminjaman</span>
            </a>
        </div>
    </div>

    <!-- KPI CARDS (RESPONSIF) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
        
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-clock absolute -right-4 -bottom-4 text-6xl text-amber-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest mb-1">Menunggu</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-amber-700 dark:text-amber-300 tracking-tighter"><?= $jmlMenunggu ?> <span class="text-xs font-bold opacity-70">Log</span></h3>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-people-arrows absolute -right-4 -bottom-4 text-6xl text-blue-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Sedang Dipinjam</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-blue-700 dark:text-blue-300 tracking-tighter"><?= $jmlDipinjam ?> <span class="text-xs font-bold opacity-70">Log</span></h3>
            </div>
        </div>

        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-check-circle absolute -right-4 -bottom-4 text-6xl text-emerald-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Telah Kembali</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-emerald-700 dark:text-emerald-300 tracking-tighter"><?= $jmlDikembalikan ?> <span class="text-xs font-bold opacity-70">Log</span></h3>
            </div>
        </div>

        <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform animate-pulse">
            <i class="fas fa-exclamation-triangle absolute -right-4 -bottom-4 text-6xl text-rose-500 opacity-10 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest mb-1">Terlambat</p>
                <h3 class="text-2xl md:text-3xl font-black italic text-rose-700 dark:text-rose-300 tracking-tighter"><?= $jmlTerlambat ?> <span class="text-xs font-bold opacity-70">Log</span></h3>
            </div>
        </div>
    </div>

    <!-- FILTER BAR (Hanya Untuk Global) -->
    <?php if ($isGlobal): ?>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm relative z-20">
        <form action="" method="get" class="flex flex-col sm:flex-row gap-3 w-full md:w-1/3">
            <div class="relative flex-1">
                <select name="jenjang" onchange="this.form.submit()" class="w-full pl-3 pr-8 py-3 md:py-2.5 text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl appearance-none outline-none text-slate-700 dark:text-slate-200 uppercase">
                    <option value="GLOBAL" <?= ($filterJenjang === 'GLOBAL') ? 'selected' : '' ?>>TAMPILKAN SEMUA UNIT</option>
                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= ($filterJenjang == $kode) ? 'selected' : '' ?>>UNIT <?= strtoupper($kode) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none hidden"></i>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>
        </form>
    </div>
    <?php endif; ?>

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
                        <th class="px-6 py-4">Aset Dipinjam</th>
                        <th class="px-6 py-4">Peminjam</th>
                        <th class="px-6 py-4">Durasi Pinjam</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php 
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $startNo = ($page - 1) * 10 + 1; 
                    if(empty($peminjaman)): ?>
                         <tr>
                             <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50/30 dark:bg-gray-800/20">
                                 <i class="fas fa-clipboard-list text-3xl mb-3 opacity-50 block"></i>
                                 Belum ada data peminjaman aset.
                             </td>
                         </tr>
                    <?php else:
                        foreach ($peminjaman as $row): 
                            
                            // Styling Status Peminjaman
                            $statusClass = 'text-slate-600 bg-slate-100 border-slate-200';
                            if ($row['status'] == 'Menunggu') $statusClass = 'text-amber-600 bg-amber-50 border-amber-200';
                            if ($row['status'] == 'Dipinjam') $statusClass = 'text-blue-600 bg-blue-50 border-blue-200';
                            if ($row['status'] == 'Dikembalikan') $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                            if ($row['status'] == 'Terlambat') $statusClass = 'text-rose-600 bg-rose-50 border-rose-200 animate-pulse';

                            // Tipe Peminjam Badge
                            $tipeClass = ($row['tipe_peminjam'] == 'Pegawai') 
                                ? 'bg-indigo-100 text-indigo-700 border-indigo-200' 
                                : 'bg-orange-100 text-orange-700 border-orange-200';
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
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 dark:text-slate-200 mb-0.5">
                                    <?= esc($row['nama_peminjam'] ?? 'Siswa/Tidak Ditemukan') ?>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest border <?= $tipeClass ?> dark:bg-opacity-20 dark:border-opacity-30">
                                        <i class="fas <?= $row['tipe_peminjam'] == 'Pegawai' ? 'fa-user-tie' : 'fa-user-graduate' ?> mr-1"></i> <?= esc($row['tipe_peminjam']) ?>
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">Unit <?= esc($row['kode_jenjang']) ?></span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">
                                    <i class="fas fa-sign-out-alt text-amber-500 w-4"></i> <?= date('d M Y, H:i', strtotime($row['tanggal_pinjam'])) ?>
                                </div>
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    <i class="fas fa-sign-in-alt <?= $row['status'] == 'Dikembalikan' ? 'text-emerald-500' : 'text-blue-500' ?> w-4"></i> 
                                    <?= $row['status'] == 'Dikembalikan' && $row['tanggal_kembali'] 
                                        ? date('d M Y, H:i', strtotime($row['tanggal_kembali'])) 
                                        : date('d M Y, H:i', strtotime($row['estimasi_kembali'])) . ' (Est)' ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-widest border <?= $statusClass ?> dark:bg-opacity-10 dark:border-opacity-30">
                                    <?= esc($row['status']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <?php $canManage = $isGlobal || strtoupper($row['kode_jenjang']) === $sessionJenjang; ?>
                                
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <!-- TOMBOL QUICK VIEW (Membuka Modal AlpineJS) -->
                                    <button @click="openDetail(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-blue-500 hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg shadow-sm transition-all" 
                                            title="Lihat Detail & Keperluan">
                                        <i class="fas fa-eye text-sm md:text-xs"></i>
                                    </button>

                                    <!-- TOMBOL EDIT & HAPUS -->
                                    <?php if($canManage): ?>
                                        <a href="<?= base_url('app/sapras/peminjaman/edit/' . $row['id']) ?>" 
                                           class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-amber-500 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg shadow-sm transition-all" 
                                           title="Update Status / Edit">
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
    <!-- MODAL 1: QUICK VIEW DETAIL PEMINJAMAN -->
    <!-- ========================================== -->
    <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end md:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="detailModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl md:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-5 md:p-6 flex justify-between items-start relative overflow-hidden">
                    <i class="fas fa-handshake absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12"></i>
                    <div class="relative z-10 text-white w-full pr-10">
                        <div class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1" x-text="'Peminjam: ' + activeItem.nama_peminjam"></div>
                        <h3 class="text-xl md:text-2xl font-black tracking-tight leading-tight mb-2" x-text="activeItem.nama_aset"></h3>
                        <div class="inline-flex items-center gap-2 font-mono text-xs bg-black/30 px-2.5 py-1 rounded-md border border-white/20">
                            <i class="fas fa-barcode text-indigo-300"></i>
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
                                  'bg-amber-100 text-amber-700 border-amber-300': activeItem.status === 'Menunggu',
                                  'bg-blue-100 text-blue-700 border-blue-300': activeItem.status === 'Dipinjam',
                                  'bg-emerald-100 text-emerald-700 border-emerald-300': activeItem.status === 'Dikembalikan',
                                  'bg-rose-100 text-rose-700 border-rose-300 animate-pulse': activeItem.status === 'Terlambat'
                              }" x-text="'Status: ' + activeItem.status"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        
                        <!-- Kolom Detail Peminjam -->
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Kategori Peminjam</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-id-badge text-indigo-500 w-4"></i> <span x-text="activeItem.tipe_peminjam"></span> 
                                    (<span x-text="activeItem.kode_jenjang"></span>)
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Tanggal Peminjaman</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-sign-out-alt text-amber-500 w-4"></i> <span x-text="formatDateTime(activeItem.tanggal_pinjam)"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Kolom Pengembalian -->
                        <div class="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estimasi Kembali</h4>
                                <p class="text-sm font-black text-slate-800 dark:text-white" x-text="formatDateTime(activeItem.estimasi_kembali)"></p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal Aktual Kembali</h4>
                                <p class="text-sm font-black" 
                                   :class="activeItem.tanggal_kembali ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 italic'" 
                                   x-text="activeItem.tanggal_kembali ? formatDateTime(activeItem.tanggal_kembali) : 'Belum dikembalikan'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Keperluan -->
                    <div class="mt-6">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2"><i class="fas fa-clipboard-list mr-1"></i> Keperluan Peminjaman</h4>
                            <p class="text-sm text-slate-700 dark:text-slate-300 font-medium whitespace-pre-line leading-relaxed" x-text="activeItem.keperluan || 'Tidak ada deskripsi keperluan.'"></p>
                        </div>
                    </div>

                </div>
                
                <div class="bg-slate-50 dark:bg-slate-900 px-5 md:px-6 py-4 flex justify-end border-t border-slate-100 dark:border-slate-800">
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
                            <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Log Peminjaman?</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                    Menghapus log peminjaman untuk: <strong x-text="activeItem.nama_aset" class="text-slate-800 dark:text-slate-200"></strong>.<br>
                                    Jika aset belum kembali, sistem akan memaksanya menjadi "Tersedia" kembali di katalog.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-3">
                    <a :href="'<?= base_url('app/sapras/peminjaman/delete/') ?>/' + activeItem.id" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 sm:py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
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
        Alpine.data('loanManager', () => ({
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

            formatDateTime(datetimeStr) {
                if(!datetimeStr) return '-';
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(datetimeStr).toLocaleDateString('id-ID', options);
            }
        }))
    });
</script>

<?= $this->endSection() ?>