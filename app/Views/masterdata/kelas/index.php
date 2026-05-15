<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Manajemen Rombongan Belajar
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $listKelas = $kelas ?? [];
    
    // 1. PAGINATION & STATS PRE-PROCESSING
    $totalRecords = (isset($pager)) ? $pager->getTotal('kelas') : count($listKelas);
    
    $countAktif = 0; 
    $countNon = 0;
    
    foreach(($stats ?? []) as $s) {
        if (is_array($s)) {
            if(($s['is_aktif'] ?? 0) == 1) $countAktif = (int)$s['total'];
            else $countNon = (int)$s['total'];
        }
    }

    // 2. SCOPE AUTH & FILTERS
    $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT'];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);

    $currentUnit = $current_filter['unit'] ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang);
    $unitLabel = ($currentUnit === 'GLOBAL') ? 'SELURUH UNIT' : 'UNIT ' . strtoupper($currentUnit);
    $search = $current_filter['search'] ?? '';

    // 3. UI HELPERS
    if (!function_exists('getJenjangColor')) {
        function getJenjangColor($kode) {
            $kode = strtoupper($kode ?? '');
            return match($kode) {
                'SD' => 'bg-rose-600 shadow-rose-200', 
                'SMP' => 'bg-sky-600 shadow-sky-200', 
                'SMA' => 'bg-indigo-600 shadow-indigo-200', 
                default => 'bg-slate-900 shadow-slate-200'
            };
        }
    }
?>

<div class="container mx-auto px-4 py-4 space-y-6 animate-fade-in font-sans antialiased text-slate-800">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/dashboard') ?>" class="hover:text-indigo-600 transition-colors">MASTER DATA</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600">ROMBEL</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">
                Rombongan Belajar <span class="text-indigo-600 font-medium opacity-50 ml-1">/ <?= $unitLabel ?></span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Unit Tetap di Header (Hanya untuk SuperAdmin) -->
            <?php if ($isSuperAdmin): ?>
            <form action="" method="get" class="relative group">
                <!-- Pertahankan search query saat ganti unit -->
                <?php if($search): ?><input type="hidden" name="search" value="<?= esc($search) ?>"><?php endif; ?>
                
                <select name="unit" onchange="this.form.submit()" 
                        class="pl-10 pr-10 py-2.5 text-[11px] font-black bg-white border-2 border-slate-100 rounded-2xl appearance-none cursor-pointer focus:border-indigo-500 shadow-sm uppercase tracking-wider min-w-[160px] transition-all outline-none">
                    <option value="GLOBAL">SEMUA UNIT</option>
                    <?php foreach (($jenjang_list ?? []) as $j): 
                        $kodeJenjang = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                    ?>
                        <option value="<?= $kodeJenjang ?>" <?= ($currentUnit == $kodeJenjang) ? 'selected' : '' ?>>
                            UNIT <?= strtoupper($kodeJenjang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] group-focus-within:text-indigo-500"></i>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 text-[9px]"></i>
            </form>
            <?php endif; ?>

            <a href="<?= base_url('app/masterdata/kelas/new') ?>" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-[11px] font-black rounded-2xl uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95 border-b-4 border-slate-700">
                <i class="fas fa-plus-circle text-[12px]"></i> Tambah Rombel
            </a>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto custom-scrollbar no-scrollbar">
        <a href="<?= base_url('app/masterdata/kurikulum') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-scroll mr-2 opacity-50"></i> Kurikulum
        </a>
        <a href="<?= base_url('app/masterdata/matapelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-book mr-2 opacity-50"></i> Mata Pelajaran
        </a>
        <a href="<?= base_url('app/masterdata/kelas') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-sm">
            <i class="fas fa-door-open mr-2"></i> Rombongan Belajar
        </a>
        <a href="<?= base_url('app/masterdata/jurusan') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-graduation-cap mr-2 opacity-50"></i> Jurusan
        </a>
        <a href="<?= base_url('app/masterdata/tahunajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-alt mr-2 opacity-50"></i> Tahun Ajaran
        </a>
    </div>

    <!-- ANALYTICS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-indigo-600 p-6 rounded-[2rem] shadow-xl shadow-indigo-100 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Total Rombel</p>
                <h3 class="text-3xl font-black italic"><?= number_format($totalRecords) ?></h3>
                <p class="mt-4 text-[9px] font-bold uppercase tracking-tight opacity-80 italic">Database Terdaftar</p>
            </div>
            <i class="fas fa-door-open absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>
        
        <div class="bg-emerald-600 p-6 rounded-[2rem] shadow-xl shadow-emerald-100 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Status Aktif</p>
                <h3 class="text-3xl font-black italic"><?= $countAktif ?></h3>
                <p class="mt-4 text-[9px] font-bold uppercase tracking-tight opacity-80 italic">Kelas Berjalan</p>
            </div>
            <i class="fas fa-check-circle absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-slate-800 p-6 rounded-[2rem] shadow-xl shadow-slate-200 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Status Archive</p>
                <h3 class="text-3xl font-black italic"><?= $countNon ?></h3>
                <p class="mt-4 text-[9px] font-bold uppercase tracking-tight opacity-80 italic">Riwayat Selesai</p>
            </div>
            <i class="fas fa-archive absolute -right-6 -bottom-6 text-9xl opacity-10 group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border-2 border-slate-50 flex items-center gap-5 group shadow-sm">
            <div class="w-16 h-16 shrink-0 flex items-center justify-center relative">
                <canvas id="chartStatusKelas" class="max-w-full max-h-full"></canvas>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1 italic">Page Info</p>
                <h3 class="text-xl font-black text-slate-800 tracking-tight italic uppercase"><?= count($listKelas) ?> Rombel</h3>
                <div class="mt-1 h-1 w-8 bg-indigo-500 rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border-2 border-slate-50 overflow-hidden flex flex-col min-h-[400px]">
        
        <!-- FITUR FILTER & SEARCH (DI ATAS TABEL) -->
        <div class="border-b border-slate-100 p-5 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form action="" method="get" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <?php if($currentUnit !== 'GLOBAL'): ?>
                    <input type="hidden" name="unit" value="<?= esc($currentUnit) ?>">
                <?php endif; ?>
                
                <div class="relative group w-full md:w-80">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-indigo-500 transition-colors"></i>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Cari nama kelas / wali kelas..." 
                           class="w-full pl-10 pr-4 py-2.5 text-xs font-bold bg-white border-2 border-slate-100 rounded-2xl focus:border-indigo-500 shadow-sm uppercase tracking-wider transition-all outline-none">
                </div>

                <div class="flex gap-2">
                    <select name="per_page" onchange="this.form.submit()" 
                            class="pl-4 pr-8 py-2.5 text-xs font-bold bg-white border-2 border-slate-100 rounded-2xl cursor-pointer focus:border-indigo-500 shadow-sm outline-none appearance-none">
                        <option value="10" <?= ($current_filter['per_page'] ?? 10) == 10 ? 'selected' : '' ?>>10 Baris</option>
                        <option value="25" <?= ($current_filter['per_page'] ?? 10) == 25 ? 'selected' : '' ?>>25 Baris</option>
                        <option value="50" <?= ($current_filter['per_page'] ?? 10) == 50 ? 'selected' : '' ?>>50 Baris</option>
                    </select>
                    <button type="submit" class="px-5 py-2.5 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-2xl hover:bg-slate-700 transition-all shadow-sm">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-center w-16">#</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em]">Identitas Rombel</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em]">Pimpinan Rombel</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em]">Data Akademik</th>
                        <th class="px-6 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-center">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black uppercase tracking-[0.2em] text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    <?php if ($listKelas): 
                        $currentPage = (isset($pager)) ? $pager->getCurrentPage('kelas') : 1;
                        $perPage = ($current_filter['per_page'] ?? 10);
                        $no = ($currentPage - 1) * $perPage + 1;
                        foreach ($listKelas as $item): ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-8 py-5 text-center font-black text-slate-300 italic group-hover:text-indigo-600 transition-colors"><?= $no++ ?></td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-[10px] text-white shadow-lg <?= getJenjangColor($item['kode_jenjang']) ?>">
                                        <?= strtoupper($item['kode_jenjang'] ?? '-') ?>
                                    </span>
                                    <div>
                                        <div class="font-black text-slate-900 text-sm uppercase italic leading-tight"><?= esc($item['nama_kelas']) ?></div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">TINGKAT <?= esc($item['tingkat']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-700 uppercase italic group-hover:text-indigo-600 transition-colors">
                                    <?= esc($item['nama_wali_kelas'] ?? 'Belum Ditentukan') ?>
                                </div>
                                <div class="text-[9px] font-bold text-blue-500 uppercase tracking-widest mt-1 flex items-center gap-2">
                                    <i class="fas fa-user-shield text-[10px]"></i> Wali Kelas
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-700 uppercase leading-tight"><?= esc($item['tahun_ajaran'] ?? '-') ?></div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 border border-slate-200"><?= esc($item['nama_kurikulum'] ?? 'K13') ?></span>
                                    <span class="opacity-50">•</span>
                                    <span>CAP: <?= esc($item['kapasitas'] ?? 36) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (($item['is_aktif'] ?? 0) == 1): ?>
                                    <span class="px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-700 font-black text-[9px] uppercase tracking-widest border border-emerald-200 shadow-sm">AKTIF</span>
                                <?php else: ?>
                                    <span class="px-4 py-1.5 rounded-full bg-slate-100 text-slate-400 font-black text-[9px] uppercase tracking-widest border border-slate-200">OFF</span>
                                <?php endif ?>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                    <!-- TOMBOL DETAIL (NEW) -->
                                    <a href="<?= base_url('app/masterdata/kelas/show/'.$item['id']) ?>" 
                                       class="w-9 h-9 flex items-center justify-center bg-white border-2 border-sky-200 text-sky-500 rounded-xl shadow-sm hover:bg-sky-500 hover:text-white transition-all active:scale-90" title="Lihat Detail">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>

                                    <a href="<?= base_url('app/masterdata/kelas/edit/'.$item['id']) ?>" 
                                       class="w-9 h-9 flex items-center justify-center bg-white border-2 border-amber-200 text-amber-500 rounded-xl shadow-sm hover:bg-amber-500 hover:text-white transition-all active:scale-90" title="Sunting Data">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>

                                    <form action="<?= base_url('app/masterdata/kelas/delete/'.$item['id']) ?>" method="post" onsubmit="return confirm('Hapus data Rombel ini secara permanen?')" class="contents">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center bg-white border-2 border-rose-100 text-rose-500 rounded-xl shadow-sm hover:bg-rose-500 hover:text-white transition-all active:scale-90" title="Hapus Data">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="px-6 py-32 text-center text-slate-300 uppercase font-black text-xs tracking-widest italic opacity-50">Data Rombongan Belajar Kosong</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pager) && $pager->getPageCount('kelas') > 0): ?>
        <div class="px-10 py-8 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                Halaman <span class="text-indigo-600 font-black"><?= $currentPage ?></span> dari <span class="text-slate-900"><?= $pager->getPageCount('kelas') ?></span>
            </div>
            <div class="custom-pagination">
                <?= $pager->links('kelas', 'tailwind_pagination') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('chartStatusKelas');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Aktif', 'Non-Aktif'],
                    datasets: [{
                        data: [<?= $countAktif ?>, <?= $countNon ?>],
                        backgroundColor: ['#10b981', '#cbd5e1'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: { legend: { display: false }, tooltip: { enabled: true } },
                    maintainAspectRatio: false
                }
            });
        }
    });
</script>

<style>
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