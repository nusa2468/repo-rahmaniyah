<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- INIT REQUEST SERVICE ---
    $request = \Config\Services::request();
    
    // --- LOGIKA PROTEKSI SCOPE ---
    // Mengambil data sesi dan filter
    $sessionUnit = session()->get('kode_jenjang');
    $filterSelected = $request->getGet('jenjang') ?? ($filter_selected ?? 'ALL');
    
    // Cek apakah user Global/Yayasan
    $isGlobalUser = empty($sessionUnit) || in_array(strtoupper($sessionUnit), ['GLOBAL', 'YAYASAN', 'PUSAT']);

    // --- LOGIKA NOMOR URUT ---
    // Menghitung nomor urut berdasarkan halaman pagination
    $page = $pager->getCurrentPage('default') ?? 1;
    $perPage = $pager->getPerPage('default') ?? 10; // Default fallback 10
    $nomor = ($page - 1) * $perPage + 1;
?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- HEADER & ACTION BAR -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-8">
        <div class="flex items-center gap-4">
            <a href="<?= base_url('app/akademik/dashboard') ?>" 
               class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm group"
               title="Kembali ke Dashboard Akademik">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            </a>
            <div>
                <nav class="flex mb-1" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                        <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                        <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                        <li class="text-slate-600">JADWAL PELAJARAN</li>
                    </ol>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic leading-none">
                        Matriks <span class="text-indigo-600">Penjadwalan</span>
                    </h1>
                    <!-- (0) Label Tahun Ajaran & Semester -->
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                        <?= esc($tahunAjaranInfo ?? 'TA AKTIF') ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- (1) Dropdown Unit Dipindahkan ke Sini -->
            <form action="" method="get" class="contents">
                <?php if($request->getGet('search')): ?>
                    <input type="hidden" name="search" value="<?= esc($request->getGet('search')) ?>">
                <?php endif; ?>
                
                <div class="relative group min-w-[160px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-building text-slate-400 text-xs"></i>
                    </div>
                    <select name="jenjang" onchange="this.form.submit()" 
                            class="w-full pl-9 pr-8 py-3 text-[10px] font-black uppercase tracking-widest bg-white border border-slate-300 text-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all appearance-none cursor-pointer hover:bg-slate-50 disabled:bg-slate-100 disabled:text-slate-400"
                            <?= !$isGlobalUser ? 'disabled' : '' ?>>
                        
                        <option value="ALL" <?= ($filterSelected === 'ALL') ? 'selected' : '' ?>>Semua Unit</option>
                        <option value="SD" <?= ($filterSelected === 'SD') ? 'selected' : '' ?>>Unit SD</option>
                        <option value="SMP" <?= ($filterSelected === 'SMP') ? 'selected' : '' ?>>Unit SMP</option>
                        <option value="SMA" <?= ($filterSelected === 'SMA') ? 'selected' : '' ?>>Unit SMA</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-slate-400 text-[9px]"></i>
                    </div>
                </div>
            </form>

            <a href="<?= base_url('app/akademik/jadwalpelajaran/new') ?>" 
               class="inline-flex justify-center items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95 rounded-xl border-b-4 border-indigo-800">
                <i class="fas fa-plus mr-2"></i> Buat Jadwal
            </a>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-day mr-2 opacity-50"></i> Kalender
        </a>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-clock mr-2"></i> Jadwal
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
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-rocket mr-2 opacity-50"></i> Kenaikan
        </a>
    </div>

    <!-- INFO BAR -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-indigo-600 p-5 rounded-2xl border-b-4 border-indigo-900 shadow-md relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-indigo-200 uppercase tracking-widest">Tahun Ajaran Aktif</p>
                <h3 class="text-base font-black text-white uppercase italic mt-1"><?= esc($tahunAjaranInfo) ?></h3>
            </div>
            <i class="fas fa-calendar-alt absolute -right-3 -bottom-3 text-white/10 text-7xl transition-transform group-hover:scale-110"></i>
        </div>

        <!-- (2) Kartu KPI Unit Diubah Menjadi Solid -->
        <div class="bg-emerald-600 p-5 rounded-2xl border-b-4 border-emerald-800 shadow-md relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-emerald-100 uppercase tracking-widest">Filter Unit / Jenjang</p>
                <h3 class="text-base font-black text-white uppercase italic mt-1">
                    <?php 
                        if (!$isGlobalUser && !empty($sessionUnit)) {
                            echo esc($sessionUnit);
                        } else {
                            echo ($filterSelected !== 'ALL') ? esc($filterSelected) : 'SEMUA UNIT';
                        }
                    ?>
                </h3>
            </div>
            <i class="fas fa-filter absolute -right-3 -bottom-3 text-white/10 text-7xl transition-transform group-hover:scale-110"></i>
        </div>

        <div class="bg-slate-800 p-5 rounded-2xl border-b-4 border-slate-950 shadow-md relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Sesi Jadwal</p>
                <h3 class="text-base font-black text-white uppercase italic mt-1"><?= count($jadwal) ?> Sesi</h3>
            </div>
            <i class="fas fa-clock absolute -right-3 -bottom-3 text-white/5 text-7xl"></i>
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <div class="bg-white border-2 border-slate-200 rounded-[2.5rem] shadow-xl overflow-hidden mb-6">
        
        <!-- TOOLBAR: FILTER & SEARCH -->
        <div class="bg-slate-50 border-b-2 border-slate-100 px-8 py-5">
            <form action="" method="get" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <!-- Placeholder untuk menyeimbangkan layout -->
                    <?php if (!$isGlobalUser): ?>
                        <span class="text-[10px] text-amber-600 font-black bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200 uppercase italic">
                            <i class="fas fa-lock mr-1"></i> Locked Area: <?= esc($sessionUnit) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 w-full md:w-auto">
                    <!-- (2) TOMBOL FILTER JADWAL PELAJARAN -->
                    <button type="button" class="inline-flex items-center justify-center px-5 py-3 text-[10px] font-black uppercase tracking-widest bg-white border-2 border-slate-200 text-slate-600 rounded-xl hover:border-indigo-400 hover:text-indigo-600 transition-all shadow-sm active:scale-95 whitespace-nowrap">
                        <i class="fas fa-sliders-h mr-2"></i> Filter Jadwal
                    </button>

                    <div class="relative w-full md:w-80">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                        <!-- Hidden input untuk menjaga filter jenjang saat search -->
                        <?php if($filterSelected !== 'ALL'): ?>
                            <input type="hidden" name="jenjang" value="<?= esc($filterSelected) ?>">
                        <?php endif; ?>
                        
                        <input type="text" name="search" value="<?= esc($request->getGet('search')) ?>" placeholder="Cari Guru, Mapel, atau Kelas..." 
                               class="w-full pl-10 pr-4 py-3 text-[11px] font-black border-2 border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-0 uppercase tracking-widest transition-all outline-none bg-white">
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <!-- (1) KOLOM NO -->
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-16">No</th>
                        
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-24">Hari</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-36">Waktu</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Unit / Rombel</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Mata Pelajaran</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Tenaga Pengajar</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Ruangan</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($jadwal)) : ?>
                        <tr>
                            <td colspan="8" class="px-6 py-24 text-center bg-slate-50/30">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4 border-2 border-slate-200">
                                    <i class="fas fa-calendar-times text-slate-300 text-3xl"></i>
                                </div>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">Belum ada data jadwal yang diproses.</p>
                                <p class="text-[10px] text-slate-400 mt-1 italic uppercase tracking-tighter">Gunakan tombol "Buat Jadwal" untuk memulai.</p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($jadwal as $row) : ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <!-- (1) ISI KOLOM NO -->
                                <td class="px-6 py-4 text-center text-[10px] font-black text-slate-400 bg-slate-50/50 group-hover:bg-indigo-50/10 border-r border-slate-100">
                                    <?= $nomor++ ?>
                                </td>

                                <td class="px-8 py-4 border-r border-slate-50 bg-slate-50/30 group-hover:bg-indigo-50/10 text-center">
                                    <span class="inline-block px-4 py-1.5 text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 rounded-lg shadow-sm border border-indigo-200">
                                        <?= esc($row['hari']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3 text-slate-700">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                            <i class="far fa-clock text-[10px]"></i>
                                        </div>
                                        <span class="text-[11px] font-black tracking-tighter italic uppercase">
                                            <?= date('H:i', strtotime($row['jam_mulai'])) ?> - <?= date('H:i', strtotime($row['jam_selesai'])) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase leading-tight"><?= esc($row['nama_kelas']) ?></div>
                                    <div class="flex items-center mt-1 text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">
                                        <span class="text-indigo-500 font-black"><?= esc($row['kode_jenjang']) ?></span> 
                                        <span class="mx-1.5 opacity-30">•</span> 
                                        <?= esc($row['nama_grup'] ?? 'Reguler') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-black text-slate-900 dark:text-white uppercase italic group-hover:text-indigo-600 transition-colors"><?= esc($row['nama_mapel']) ?></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5"><?= esc($row['nama_kurikulum'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-9 h-9 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-center mr-3 border-2 border-slate-100 dark:border-white/5 overflow-hidden shadow-sm group-hover:border-indigo-200">
                                            <i class="fas fa-user-tie text-slate-400 text-xs"></i>
                                        </div>
                                        <div class="text-[11px] font-black text-slate-700 dark:text-slate-200 uppercase leading-none truncate max-w-[140px] italic">
                                            <?= esc($row['nama_guru']) ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase italic">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></div>
                                        <?= esc($row['nama_ruangan'] ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                        <a href="<?= base_url('app/akademik/jadwalpelajaran/edit/' . $row['id']) ?>" 
                                           class="w-9 h-9 flex items-center justify-center bg-white border-2 border-amber-200 text-amber-500 rounded-xl shadow-sm hover:bg-amber-500 hover:text-white transition-all active:scale-90"
                                           title="Sunting Data">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <button type="button" 
                                                onclick="confirmDelete('<?= $row['id'] ?>', '<?= esc($row['nama_kelas']) ?>', '<?= esc($row['nama_mapel']) ?>')"
                                                class="w-9 h-9 flex items-center justify-center bg-white border-2 border-rose-100 text-rose-500 rounded-xl shadow-sm hover:bg-rose-500 hover:text-white transition-all active:scale-90"
                                                title="Hapus Data">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGER FOOTER -->
        <?php if (!empty($jadwal) && isset($pager)) : ?>
            <div class="px-10 py-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                    Records: <span class="text-indigo-600 font-black"><?= $pager->getCurrentPage('default') ?></span> of <span class="text-slate-900 dark:text-white font-black"><?= $pager->getPageCount('default') ?> Pages</span>
                </div>
                <div class="custom-pagination">
                    <?= $pager->links('default', 'tailwind_pagination') ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Hapus (Modern Tailwind) -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-b-8 border-rose-600">
            <div class="bg-white px-8 pt-8 pb-6 sm:p-10 sm:pb-8">
                <div class="sm:flex sm:items-start text-center sm:text-left">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-14 w-14 bg-rose-50 border-2 border-rose-100 rounded-2xl sm:mx-0 sm:h-12 sm:w-12">
                        <i class="fas fa-trash-alt text-rose-600 text-lg"></i>
                    </div>
                    <div class="mt-4 sm:mt-0 sm:ml-5 w-full">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic" id="modal-title">Konfirmasi Penghapusan</h3>
                        <div class="mt-3 bg-slate-50 p-4 border-l-4 border-slate-300 rounded-r-xl">
                            <p class="text-[11px] font-bold text-slate-500 uppercase leading-relaxed">
                                Anda akan menghapus permanen jadwal:
                                <span id="modal-delete-info" class="block text-slate-900 mt-2 font-black underline italic tracking-tight text-sm"></span>
                            </p>
                        </div>
                        <p class="mt-4 text-[10px] font-bold text-rose-500 uppercase tracking-widest animate-pulse flex items-center justify-center sm:justify-start gap-2">
                            <i class="fas fa-exclamation-circle"></i> Aksi ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-8 py-5 sm:px-10 sm:flex sm:flex-row-reverse gap-3 border-t border-slate-100">
                <form id="deleteForm" method="post" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="w-full inline-flex justify-center px-8 py-3 bg-rose-600 text-[10px] font-black text-white uppercase tracking-widest hover:bg-rose-700 shadow-xl shadow-rose-200 transition-all active:scale-95 rounded-xl">
                        Ya, Hapus Permanen
                    </button>
                </form>
                <button type="button" onclick="closeModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center px-8 py-3 bg-white border-2 border-slate-200 text-[10px] font-black text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 rounded-xl">
                    Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, kelas, mapel) {
        document.getElementById('modal-delete-info').innerText = `[${kelas}] - ${mapel}`;
        document.getElementById('deleteForm').action = `<?= base_url('app/akademik/jadwalpelajaran/delete/') ?>` + id;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

<style>
    /* Styling khusus Scrollbar & Pagination agar serasi dengan desain Rapat/Solid */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .custom-pagination nav ul { display: flex; gap: 0.35rem; }
    .custom-pagination nav ul li a, 
    .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.5rem; height: 2.5rem; font-size: 10px; font-weight: 900;
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