<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// =========================================================================
// KALKULASI STATISTIK (Dari Data Halaman Saat Ini)
// =========================================================================
$jmlMenunggu = 0; $jmlDisetujui = 0; $jmlDitolak = 0; $jmlSelesai = 0;
$estimasiTotal = 0;

if (!empty($pengadaan)) {
    foreach($pengadaan as $p) {
        $estimasiTotal += (float)$p['estimasi_biaya'];
        
        if($p['status'] == 'Menunggu Approval') $jmlMenunggu++;
        elseif($p['status'] == 'Disetujui') $jmlDisetujui++;
        elseif($p['status'] == 'Ditolak') $jmlDitolak++;
        elseif($p['status'] == 'Selesai/Dibeli') $jmlSelesai++;
    }
}
$totalItem = count($pengadaan);
?>

<div x-data="procurementManager()" class="px-4 sm:px-6 py-6 space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="w-full md:w-auto">
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-2 md:mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/sapras/dashboard') ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">SAPRAS</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">PENGADAAN ASET</li>
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
                Manajemen proposal, persetujuan anggaran, dan status pembelian barang.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row w-full md:w-auto items-center gap-3 mt-4 md:mt-0">
            <a href="<?= base_url('app/sapras/pengadaan/new') ?>" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 md:py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-plus text-sm md:text-xs"></i> <span>Buat Pengajuan Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI CARDS (RESPONSIF) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
        
        <!-- Card 1: Estimasi Biaya -->
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-5 text-white shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-file-invoice-dollar absolute -right-4 -bottom-4 text-7xl opacity-20 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1 border-b border-white/20 pb-1 inline-block">Estimasi Biaya (Page)</p>
                <h3 class="text-2xl font-black italic tracking-tighter mt-1">Rp <?= number_format($estimasiTotal, 0, ',', '.') ?></h3>
            </div>
        </div>

        <!-- Card 2: Menunggu Approval -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-hourglass-half absolute -right-4 -bottom-4 text-7xl text-amber-500 opacity-5 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Menunggu Persetujuan</p>
                <h3 class="text-2xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= $jmlMenunggu ?> <span class="text-sm">Proposal</span></h3>
            </div>
        </div>

        <!-- Card 3: Disetujui -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-check-double absolute -right-4 -bottom-4 text-7xl text-emerald-500 opacity-5 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Disetujui / Selesai</p>
                <h3 class="text-2xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= ($jmlDisetujui + $jmlSelesai) ?> <span class="text-sm">Proposal</span></h3>
            </div>
        </div>

        <!-- Card 4: Ditolak -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform">
            <i class="fas fa-times-circle absolute -right-4 -bottom-4 text-7xl text-rose-500 opacity-5 group-hover:scale-110 transition-transform"></i>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 border-b border-slate-100 dark:border-slate-700 pb-1 inline-block">Ditolak / Revisi</p>
                <h3 class="text-2xl font-black italic text-slate-800 dark:text-white tracking-tighter mt-1"><?= $jmlDitolak ?> <span class="text-sm">Proposal</span></h3>
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
                        <th class="px-6 py-4">Nomor & Judul Proposal</th>
                        <th class="px-6 py-4">Kategori & Pemohon</th>
                        <th class="px-6 py-4 text-center">Jml</th>
                        <th class="px-6 py-4 text-right">Estimasi Biaya</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php 
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $startNo = ($page - 1) * 10 + 1; 
                    if(empty($pengadaan)): ?>
                         <tr>
                             <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50/30 dark:bg-gray-800/20">
                                 <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                                 Belum ada data pengajuan pengadaan aset.
                             </td>
                         </tr>
                    <?php else:
                        foreach ($pengadaan as $row): 
                            
                            // Styling Status Workflow
                            $statusClass = 'text-slate-600 bg-slate-100 border-slate-200';
                            if ($row['status'] == 'Menunggu Approval') $statusClass = 'text-blue-600 bg-blue-50 border-blue-200 animate-pulse';
                            if ($row['status'] == 'Disetujui') $statusClass = 'text-emerald-600 bg-emerald-50 border-emerald-200';
                            if ($row['status'] == 'Ditolak') $statusClass = 'text-rose-600 bg-rose-50 border-rose-200';
                            if ($row['status'] == 'Selesai/Dibeli') $statusClass = 'text-purple-600 bg-purple-50 border-purple-200';

                            // Proteksi Edit/Hapus jika sudah disetujui (Sesuai Controller)
                            $isLocked = in_array($row['status'], ['Disetujui', 'Selesai/Dibeli']);
                    ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                            
                            <td class="px-6 py-4">
                                <div class="font-mono text-[10px] font-black text-indigo-600 dark:text-indigo-400 mb-1">
                                    <i class="fas fa-file-invoice mr-1"></i><?= esc($row['no_pengajuan']) ?>
                                </div>
                                <div class="font-black text-slate-800 dark:text-white truncate max-w-xs" title="<?= esc($row['judul_pengajuan']) ?>">
                                    <?= esc($row['judul_pengajuan']) ?>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300 mb-0.5">
                                    <i class="fas fa-tag text-slate-400 w-4"></i> <?= esc($row['nama_kategori'] ?? '-') ?>
                                </div>
                                <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                    <i class="fas fa-user text-slate-400 w-4"></i> <?= esc($row['nama_pemohon'] ?? 'Sistem') ?> (<?= esc($row['kode_jenjang']) ?>)
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center font-black text-slate-700 dark:text-slate-300">
                                <?= esc($row['jumlah_diminta']) ?>
                            </td>

                            <td class="px-6 py-4 text-right font-black italic text-emerald-600 dark:text-emerald-400">
                                Rp <?= number_format($row['estimasi_biaya'], 0, ',', '.') ?>
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
                                            title="Lihat Detail & Catatan">
                                        <i class="fas fa-eye text-sm md:text-xs"></i>
                                    </button>

                                    <!-- TOMBOL EDIT & HAPUS -->
                                    <?php if($canManage): ?>
                                        <a href="<?= base_url('app/sapras/pengadaan/edit/' . $row['id']) ?>" 
                                           class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-amber-500 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg shadow-sm transition-all" 
                                           title="Review / Edit Proposal">
                                            <i class="fas fa-pen text-sm md:text-xs"></i>
                                        </a>
                                        
                                        <?php if(!$isLocked): ?>
                                            <button @click="confirmDelete(<?= $row['id'] ?>, '<?= esc(addslashes($row['no_pengajuan'])) ?>')" 
                                                    class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-rose-500 hover:border-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg shadow-sm transition-all" 
                                                    title="Hapus Draft Proposal">
                                                <i class="fas fa-trash-alt text-sm md:text-xs"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" disabled class="w-10 h-10 md:w-8 md:h-8 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 rounded-lg cursor-not-allowed" title="Terkunci (Sudah Disetujui/Dibeli)">
                                                <i class="fas fa-lock text-sm md:text-xs"></i>
                                            </button>
                                        <?php endif; ?>
                                        
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
    <!-- MODAL 1: QUICK VIEW DETAIL PROPOSAL (RESPONSIF) -->
    <!-- ========================================== -->
    <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-[100] overflow-y-auto" x-transition.opacity>
        <div class="flex items-end md:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="detailModalOpen = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-t-3xl md:rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle max-w-2xl w-full border border-slate-200 dark:border-slate-700">
                
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-5 md:p-6 flex justify-between items-start relative overflow-hidden">
                    <i class="fas fa-shopping-cart absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12"></i>
                    <div class="relative z-10 text-white w-full pr-10">
                        <div class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1" x-text="activeItem.nama_kategori"></div>
                        <h3 class="text-xl md:text-2xl font-black tracking-tight leading-tight mb-2" x-text="activeItem.judul_pengajuan"></h3>
                        <div class="inline-flex items-center gap-2 font-mono text-xs bg-black/30 px-2.5 py-1 rounded-md border border-white/20">
                            <i class="fas fa-file-invoice text-indigo-300"></i>
                            <span x-text="activeItem.no_pengajuan"></span>
                        </div>
                    </div>
                    <button @click="detailModalOpen = false" class="absolute top-4 right-4 z-10 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 w-8 h-8 rounded-full transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-5 md:p-8">
                    
                    <!-- Status Badge Besar -->
                    <div class="mb-6 flex justify-center">
                        <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest border" 
                              :class="{
                                  'bg-slate-100 text-slate-600 border-slate-300': activeItem.status === 'Draft',
                                  'bg-blue-100 text-blue-700 border-blue-300 animate-pulse': activeItem.status === 'Menunggu Approval',
                                  'bg-emerald-100 text-emerald-700 border-emerald-300': activeItem.status === 'Disetujui',
                                  'bg-rose-100 text-rose-700 border-rose-300': activeItem.status === 'Ditolak',
                                  'bg-purple-100 text-purple-700 border-purple-300': activeItem.status === 'Selesai/Dibeli'
                              }" x-text="'Status: ' + activeItem.status"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        
                        <!-- Kolom Info Pemohon -->
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Pemohon / Unit</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="fas fa-user-tie text-indigo-500 w-4"></i> <span x-text="activeItem.nama_pemohon || 'Sistem'"></span> 
                                    (<span x-text="activeItem.kode_jenjang"></span>)
                                </p>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 border-b border-slate-100 dark:border-slate-700 pb-1">Tanggal Pengajuan</h4>
                                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">
                                    <i class="far fa-calendar-alt text-indigo-500 w-4"></i> <span x-text="formatDate(activeItem.created_at)"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Kolom Info Anggaran -->
                        <div class="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jumlah Diminta</h4>
                                <p class="text-lg font-black text-slate-800 dark:text-white" x-text="activeItem.jumlah_diminta + ' Unit'"></p>
                            </div>
                            <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Estimasi Biaya</h4>
                                <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 italic">Rp <span x-text="formatRupiah(activeItem.estimasi_biaya)"></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Text Area (Alasan & Review) -->
                    <div class="mt-6 space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800/50">
                            <h4 class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-2"><i class="fas fa-comment-dots mr-1"></i> Alasan / Latar Belakang Kebutuhan</h4>
                            <p class="text-sm text-slate-700 dark:text-slate-300 font-medium whitespace-pre-line leading-relaxed" x-text="activeItem.alasan_kebutuhan"></p>
                        </div>

                        <template x-if="activeItem.catatan_reviewer">
                            <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2"><i class="fas fa-clipboard-check mr-1"></i> Catatan Reviewer (Manajemen/Yayasan)</h4>
                                <p class="text-sm text-slate-800 dark:text-slate-200 font-bold whitespace-pre-line leading-relaxed" x-text="activeItem.catatan_reviewer"></p>
                            </div>
                        </template>
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
                            <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Proposal Ini?</h3>
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                    Anda akan menghapus pengajuan <strong x-text="activeItem.no_pengajuan" class="text-slate-800 dark:text-slate-200"></strong> secara permanen.<br>
                                    Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-3">
                    <a :href="'<?= base_url('app/sapras/pengadaan/delete/') ?>/' + activeItem.id" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 sm:py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
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
        Alpine.data('procurementManager', () => ({
            detailModalOpen: false,
            deleteModalOpen: false,
            activeItem: {},

            openDetail(data) {
                this.activeItem = data;
                this.detailModalOpen = true;
            },
            
            confirmDelete(id, nomor) {
                this.activeItem = { id: id, no_pengajuan: nomor };
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

            formatDate(datetimeStr) {
                if(!datetimeStr) return '-';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(datetimeStr).toLocaleDateString('id-ID', options);
            }
        }))
    });
</script>

<?= $this->endSection() ?>