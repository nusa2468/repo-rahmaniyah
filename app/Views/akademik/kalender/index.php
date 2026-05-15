<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    /** * Initialization of common variables.
     */
    $request = \Config\Services::request();
    $currentPage = (int)($request->getGet('page_default') ?? 1);
    if ($currentPage < 1) $currentPage = 1;
    $perPage = 20; 

    // --- LOGIKA PROTEKSI SCOPE (ANTI-BOCOR) ---
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600">KALENDER PENDIDIKAN</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase italic leading-none">
                Kalender <span class="text-indigo-600">Akademik</span>
            </h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('app/akademik/kalender/calendar') ?>" 
               class="inline-flex items-center px-5 py-3 text-[10px] font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-indigo-400 hover:text-indigo-600 transition-all shadow-sm active:scale-95 rounded-xl">
                <i class="far fa-calendar-alt mr-2"></i> Visual Mode
            </a>
            <a href="<?= base_url('app/akademik/kalender/new') ?>" 
               class="inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95 rounded-xl border-b-4 border-indigo-800">
                <i class="fas fa-plus mr-2"></i> Tambah Agenda
            </a>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK (NEW FEATURE) -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-calendar-day mr-2"></i> Kalender
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
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-rocket mr-2 opacity-50"></i> Kenaikan
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 rounded-2xl bg-emerald-600 p-4 shadow-lg flex items-center text-white border-b-4 border-emerald-800 animate-in fade-in slide-in-from-top-2">
            <i class="fas fa-check-circle text-xl mr-3"></i>
            <p class="text-xs font-black uppercase tracking-widest"><?= session()->getFlashdata('success') ?></p>
            <button type="button" class="ml-auto opacity-50 hover:opacity-100" onclick="this.parentElement.remove();"><i class="fas fa-times"></i></button>
        </div>
    <?php endif; ?>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-100 p-6 flex items-center relative overflow-hidden group border-b-4 border-indigo-900">
            <div class="p-4 bg-white/20 rounded-2xl group-hover:scale-110 transition-transform duration-500 z-10">
                <i class="fas fa-landmark text-white text-2xl"></i>
            </div>
            <div class="ml-5 flex-1 z-10">
                <p class="text-[10px] font-black text-indigo-200 uppercase tracking-widest mb-1 leading-none">Unit Otoritas</p>
                <?php if ($is_restricted): ?>
                    <h3 class="text-lg font-black text-white uppercase italic leading-tight"><?= esc($active_unit_label) ?></h3>
                <?php else: ?>
                    <form action="" method="get">
                        <select name="jenjang" onchange="this.form.submit()" 
                                class="block w-full bg-indigo-800/50 border-2 border-indigo-400/30 text-white text-xs font-black uppercase tracking-widest rounded-xl focus:ring-0 focus:border-white p-2 cursor-pointer transition-all">
                            <option value="ALL">SEMUA UNIT</option>
                            <?php foreach(($list_jenjang ?? []) as $jenjang): ?>
                                <option value="<?= esc($jenjang['kode_jenjang']) ?>" <?= $filter_selected === $jenjang['kode_jenjang'] ? 'selected' : '' ?>>
                                    UNIT <?= esc(strtoupper($jenjang['kode_jenjang'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
            <i class="fas fa-shield-alt absolute -right-6 -bottom-6 text-white/10 text-9xl"></i>
        </div>

        <div class="bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-100 p-6 flex items-center relative overflow-hidden group border-b-4 border-emerald-900">
            <div class="p-4 bg-white/20 rounded-2xl z-10">
                <i class="fas fa-calendar-check text-white text-2xl"></i>
            </div>
            <div class="ml-5 flex-1 z-10">
                <p class="text-[10px] font-black text-emerald-200 uppercase tracking-widest mb-1 leading-none">Tahun Ajaran</p>
                <h3 class="text-lg font-black text-white uppercase italic leading-tight"><?= esc($tahun_ajaran_aktif['tahun_ajaran'] ?? '-') ?></h3>
                <span class="text-[9px] font-black text-emerald-100 bg-emerald-900/40 px-2 py-0.5 rounded mt-1 inline-block uppercase italic">Semester <?= esc($tahun_ajaran_aktif['semester'] ?? '1') ?></span>
            </div>
            <i class="fas fa-clock absolute -right-6 -bottom-6 text-white/10 text-9xl"></i>
        </div>

        <div class="bg-slate-900 rounded-3xl shadow-xl p-6 flex items-center relative overflow-hidden group border-b-4 border-indigo-600 text-white">
            <div class="p-4 bg-white/10 rounded-2xl z-10">
                <i class="fas fa-layer-group text-indigo-400 text-2xl"></i>
            </div>
            <div class="ml-5 flex-1 z-10">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1 leading-none">Agenda Terdaftar</p>
                <h3 class="text-3xl font-black italic"><?= isset($pager) ? $pager->getTotal() : count($kalender ?? []) ?></h3>
            </div>
            <i class="fas fa-list absolute -right-6 -bottom-6 text-white/5 text-9xl"></i>
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden flex flex-col min-h-[400px]">
        <div class="overflow-x-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-16">#</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Waktu Pelaksanaan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Deskripsi Agenda</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Scope</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Label</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-32">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($kalender)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-32 text-center text-slate-300 uppercase font-black text-xs tracking-widest italic opacity-50">Data Agenda Kosong</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                            $no = ($currentPage - 1) * $perPage + 1;
                            foreach ($kalender as $item) : 
                        ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5 text-center font-black text-slate-300 italic group-hover:text-indigo-600 transition-colors"><?= $no++ ?></td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="font-black text-slate-800 dark:text-slate-100 text-sm italic"><?= date('d M Y', strtotime($item['start'])) ?></div>
                                    <?php if (!empty($item['end']) && $item['end'] !== $item['start']): ?>
                                        <div class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">s/d <?= date('d M Y', strtotime($item['end'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-black text-slate-900 dark:text-white uppercase leading-tight group-hover:text-indigo-600 transition-colors"><?= esc($item['title']) ?></div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 italic line-clamp-1 group-hover:line-clamp-none transition-all"><?= esc($item['keterangan'] ?: '---') ?></div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9px] font-black rounded-lg uppercase tracking-tighter shadow-sm border border-slate-200 dark:border-slate-600">
                                        <?= esc($item['kode_jenjang'] ?: 'GLOBAL') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="mx-auto h-5 w-5 rounded-full shadow-lg border-2 border-white dark:border-slate-700" style="background-color: <?= esc($item['color'] ?: '#6366f1') ?>;"></div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all transform translate-x-2 group-hover:translate-x-0">
                                        <a href="<?= base_url('app/akademik/kalender/edit/' . $item['id']) ?>" 
                                           class="w-9 h-9 flex items-center justify-center bg-white border-2 border-amber-200 text-amber-500 rounded-xl shadow-sm hover:bg-amber-500 hover:text-white transition-all active:scale-90" title="Sunting">
                                            <i class="fas fa-pencil-alt text-xs"></i>
                                        </a>
                                        <form action="<?= base_url('app/akademik/kalender/delete/' . $item['id']) ?>" method="post" onsubmit="return confirm('Hapus agenda ini?')" class="contents">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center bg-white border-2 border-rose-100 text-rose-500 rounded-xl shadow-sm hover:bg-rose-500 hover:text-white transition-all active:scale-90" title="Hapus">
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

        <!-- PAGER FOOTER -->
        <?php if (isset($pager) && $pager->getPageCount() > 0): ?>
        <div class="px-10 py-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] italic">
                Agenda Records: <span class="text-indigo-600 font-black"><?= ($currentPage - 1) * $perPage + 1 ?> - <?= min($currentPage * $perPage, $pager->getTotal()) ?></span> of <span class="text-slate-900 dark:text-white"><?= $pager->getTotal() ?></span>
            </div>
            <div class="custom-pagination">
                <?= $pager->links('default', 'tailwind_pagination') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Premium Styling Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Custom Pagination Styling */
    .custom-pagination nav ul { display: flex; gap: 0.35rem; }
    .custom-pagination nav ul li a, 
    .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.5rem; height: 2.5rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white;
        border-radius: 0.75rem; transition: all 0.2s; color: #64748b;
    }
    .custom-pagination nav ul li.active span {
        background: #4f46e5; color: white; border-color: #4f46e5;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }
    .custom-pagination nav ul li a:hover { border-color: #4f46e5; color: #4f46e5; background: #f8fafc; }
</style>

<?= $this->endSection() ?>