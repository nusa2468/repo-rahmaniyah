<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
    @media print {
        @page { size: landscape; margin: 10mm; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: white; }
        .no-print, nav, header, aside, .btn, .pagination-container { display: none !important; }
        .card { border: 1px solid #ddd; box-shadow: none !important; break-inside: avoid; }
        .chart-container { height: 200px !important; }
        table { font-size: 10px; width: 100% !important; border-collapse: collapse; }
        th, td { padding: 4px !important; border: 1px solid #ddd !important; }
    }
    .custom-scroll::-webkit-scrollbar { height: 4px; width: 4px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<?php
    $session = session();
    $userRole = strtolower($session->get('role') ?? '');
    $userUnit = $session->get('kode_jenjang');

    if (!isset($isSuperAdmin)) {
        $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin', 'yayasan', 'root']) 
                        || in_array(strtoupper($userUnit ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT']);
    }

    $filter_jenjang = $filter_jenjang ?? $userUnit;
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20">
    
    <!-- --- HEADER SECTION (Sticky) --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300 no-print">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-amber-500 rounded-lg shadow-lg shadow-amber-500/30 text-white">
                        <i class="fas fa-file-invoice text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Laporan Pengeluaran</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">
                                REALISASI
                            </span>
                            <span class="text-slate-300">|</span>
                            <span><?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Controls: Dropdown & Print Button -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <!-- DROPDOWN UNIT -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" id="filterFormHeader" class="flex items-center gap-2 m-0 p-0 relative">
                        <input type="hidden" name="start_date" value="<?= $start_date ?>">
                        <input type="hidden" name="end_date" value="<?= $end_date ?>">
                        
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600">
                                <?php if ($isSuperAdmin): ?>
                                    <option value="">SEMUA UNIT</option>
                                <?php endif; ?>
                                
                                <?php if(!empty($jenjang_list)): ?>
                                    <?php foreach($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                            Unit <?= strtoupper($j['nama_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas <?= !$isSuperAdmin ? 'fa-lock' : 'fa-filter' ?> text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                            <?php if(!$isSuperAdmin): ?><input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- TOMBOL CETAK (UPDATED LINK) -->
                <!-- Mengarah ke rute cetak_pengeluaran -->
                <a href="<?= base_url('app/keuangan/laporan/cetak_pengeluaran?start_date='.$start_date.'&end_date='.$end_date.'&jenjang='.$filter_jenjang) ?>" 
                   target="_blank"
                   class="h-10 px-5 bg-slate-800 text-white hover:bg-slate-700 rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-2"
                   title="Cetak Laporan PDF">
                    <i class="fas fa-print"></i> Cetak
                </a>
            </div>
        </div>

        <!-- --- NAVIGATION TABS --- -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    
                    // ========================================================
                    // FIX GANAS: SEMBUNYIKAN TAB AKUNTANSI
                    // ========================================================
                    if (stripos($key, 'akuntan') !== false || stripos($nav['label'] ?? '', 'akuntan') !== false) {
                        continue;
                    }

                    $isActive = ($key === 'pengeluaran' || ($key === 'laporan' && strpos(current_url(), 'pengeluaran') !== false)); 
                    $activeClass = $isActive 
                        ? 'bg-white text-amber-600 shadow-sm ring-1 ring-black/5' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="<?= base_url($nav['url']) ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-2 <?= $activeClass ?>">
                    <i class="fas fa-<?= $nav['icon'] ?>"></i> <?= $nav['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAIN CONTENT (Sama seperti sebelumnya) -->
    <div class="px-6 max-w-screen-2xl mx-auto space-y-6">
        <!-- Filter Bar Date -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 no-print">
            <form action="" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="w-full bg-slate-50 border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-500 text-slate-700 shadow-sm">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="w-full bg-slate-50 border-none rounded-lg px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-500 text-slate-700 shadow-sm">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full py-2 bg-amber-500 text-white rounded-lg text-xs font-black uppercase tracking-widest hover:bg-amber-600 shadow-md transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-amber-500 to-orange-700 p-5 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-amber-100 uppercase tracking-widest opacity-80 mb-1">Total Pengeluaran</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($total_pengeluaran ?? 0, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-wallet absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
            
            <?php 
                $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
                $avg = ($total_pengeluaran ?? 0) / max($days, 1);
            ?>
            <div class="bg-gradient-to-br from-blue-500 to-indigo-700 p-5 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest opacity-80 mb-1">Rata-rata / Hari</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($avg, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-chart-area absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <div class="bg-gradient-to-br from-rose-500 to-pink-700 p-5 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10 overflow-hidden">
                    <p class="text-[10px] font-bold text-rose-100 uppercase tracking-widest opacity-80 mb-1">Pengeluaran Terbesar</p>
                    <h3 class="text-lg font-bold truncate w-full" title="<?= $kpi['item_terbesar'] ?>">
                        <?= $kpi['item_terbesar'] ?>
                    </h3>
                    <p class="text-sm font-black mt-0.5 opacity-90">Rp <?= number_format($kpi['nominal_terbesar'], 0, ',', '.') ?></p>
                </div>
                <i class="fas fa-exclamation-circle absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Table Section -->
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                    <h6 class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Rincian Transaksi</h6>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white text-[10px] uppercase text-slate-400 font-bold border-b border-slate-100">
                                <th class="px-5 py-3 text-center w-10">No</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Kategori & Keterangan</th>
                                <th class="px-5 py-3 text-center">Unit</th>
                                <th class="px-5 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-xs text-slate-600">
                            <?php if(empty($laporan)): ?>
                                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Belum ada data pengeluaran pada periode ini.</td></tr>
                            <?php else: ?>
                                <?php $no = isset($nomor_urut) ? $nomor_urut + 1 : 1; ?>
                                <?php foreach($laporan as $p): ?>
                                <tr class="hover:bg-amber-50/40 transition-colors">
                                    <td class="px-5 py-3 text-center font-mono text-slate-400"><?= $no++ ?></td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="font-bold"><?= date('d/m/y', strtotime($p['tanggal'])) ?></span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-slate-800"><?= esc($p['nama_kategori'] ?? 'Umum') ?></div>
                                        <div class="text-[10px] text-slate-400 italic truncate max-w-[200px]"><?= esc($p['keterangan'] ?? '-') ?></div>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded text-[9px] font-bold border border-slate-200 uppercase">
                                            <?= esc($p['kode_jenjang'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-bold text-amber-600">
                                        Rp <?= number_format($p['jumlah'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                    <?= $pager->links('default', 'tailwind_pagination') ?>
                </div>
            </div>

            <!-- Chart Section (Compact Side) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col">
                <h6 class="text-[11px] font-black text-slate-600 uppercase tracking-widest mb-4">Tren Harian</h6>
                <div class="flex-1 relative min-h-[250px]">
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.addEventListener('load', function() {
        const labels = <?= json_encode($chart_data['labels']) ?>;
        const data = <?= json_encode($chart_data['datasets']) ?>;

        const ctx = document.getElementById('expenseChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pengeluaran',
                        data: data,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { display: false },
                            ticks: { font: { size: 9 }, callback: (val) => (val/1000000).toFixed(1) + 'jt' } 
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>