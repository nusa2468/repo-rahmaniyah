<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // =========================================================================
    // 1. PENGAMAN VARIABEL (Mencegah Error Undefined Variable dari Controller)
    // =========================================================================
    $jenjang = $jenjang ?? $filter_jenjang ?? $kode_jenjang ?? $current_unit ?? '';
    $is_superadmin = $is_superadmin ?? $isSuperAdmin ?? false;
    $jenjang_list = $jenjang_list ?? [];
    
    // =========================================================================
    // FIX: OVERRIDE NAVIGASI (Memastikan Tab Buku Kas & Laporan Selalu Muncul)
    // =========================================================================
    $navigation = [
        'dashboard'       => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
        'budget'          => ['label' => 'Anggaran (Budget)', 'icon' => 'chart-pie', 'url' => 'app/keuangan/budget'],
        'tagihan'         => ['label' => 'Tagihan & Piutang', 'icon' => 'file-invoice-dollar', 'url' => 'app/keuangan/tagihan'],
        'pembayaran'      => ['label' => 'Pemasukan SPP', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/pembayaran'],
        'kas-operasional' => ['label' => 'Buku Kas (In/Out)', 'icon' => 'exchange-alt', 'url' => 'app/keuangan/kas-operasional'],
        'laporan'         => ['label' => 'Laporan Ops', 'icon' => 'print', 'url' => 'app/keuangan/laporan/pemasukan'],
    ];
    
    // Pastikan array stats memiliki nilai default jika Controller gagal mengirim data
    $stats = $stats ?? [
        'tahun_aktif' => date('Y'),
        'budget_rencana' => 0,
        'persen_budget' => 0,
        'surplus_defisit' => 0,
        'total_pemasukan' => 0,
        'total_piutang' => 0
    ];
    
    $recent_transactions = $recent_transactions ?? [];
    $chart_cashflow = $chart_cashflow ?? ['labels' => [], 'income' => [], 'expense' => []];
    $chart_distribution = $chart_distribution ?? [];
?>

<!-- Memuat Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20">
    
    <!-- --- HEADER SECTION --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Left: Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-blue-600 rounded-lg shadow-lg shadow-blue-500/30 text-white">
                        <i class="fas fa-university text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Dashboard Keuangan</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            Unit Monitoring: 
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-bold text-blue-600 ring-1 ring-inset ring-slate-500/10">
                                <?= $jenjang ?: 'SEMUA UNIT' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Controls (Filters) -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3 w-full xl:w-auto">
                
                <!-- Filter Unit Dropdown (DINAMIS & ANTI BOCOR) -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    
                    <!-- FIX: Hanya Tampilkan Dropdown Jika Superadmin -->
                    <?php if($is_superadmin): ?>
                    <form action="" method="get" class="flex items-center m-0 p-0 relative group">
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" class="h-9 pl-9 pr-8 bg-transparent text-slate-600 text-sm font-semibold cursor-pointer appearance-none outline-none hover:text-blue-600 transition-colors">
                                <option value="">SEMUA UNIT</option>
                                <?php if(!empty($jenjang_list)): ?>
                                    <?php foreach($jenjang_list as $j): ?>
                                        <option value="<?= esc($j['kode_jenjang']) ?>" <?= $jenjang == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                            Unit <?= esc($j['nama_jenjang'] ?? $j['kode_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400 group-hover:text-blue-500 transition-colors">
                                <i class="fas fa-filter text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </form>
                    <div class="h-6 w-px bg-slate-100 mx-1"></div>
                    <?php endif; ?>

                    <!-- Badge Tahun Ajar -->
                    <div class="flex items-center gap-2 px-3 h-9 text-sm font-medium text-slate-500 select-none">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        <span>TA <?= esc($stats['tahun_aktif']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- --- NAVIGATION TABS (Modul Keuangan) --- -->
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php 
                    if(!empty($navigation)):
                        foreach($navigation as $key => $nav):
                            
                            // ========================================================
                            // FIX: SEMBUNYIKAN TAB AKUNTANSI SEMENTARA WAKTU (STRATEGI)
                            // ========================================================
                            if (strtolower($key) === 'akuntansi' || strtolower($nav['label'] === 'akuntansi')) {
                                continue;
                            }
                            
                            // Highlight dashboard jika di root
                            $isActive = ($key === 'dashboard'); 
                            $baseClass = "px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-2";
                            $activeClass = $isActive 
                                ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' 
                                : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="<?= base_url($nav['url']) ?><?= !empty($jenjang) ? '?jenjang='.urlencode($jenjang) : '' ?>" class="<?= $baseClass ?> <?= $activeClass ?>">
                    <i class="fas fa-<?= esc($nav['icon']) ?>"></i> <?= esc($nav['label']) ?>
                </a>
                <?php 
                        endforeach;
                    endif; 
                ?>
            </div>
        </div>
    </div>

    <div class="px-6 max-w-screen-2xl mx-auto space-y-8">
        
        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Budget Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-blue-800 rounded-2xl p-5 shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute -right-2 -bottom-2 opacity-20 transform group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-chart-pie text-6xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest mb-1 opacity-80">Anggaran (Budget)</div>
                    <div class="text-xl font-black tracking-tighter">Rp <?= number_format($stats['budget_rencana'], 0, ',', '.') ?></div>
                    <div class="mt-4 w-full bg-white/20 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-white h-full transition-all duration-1000" style="width: <?= min($stats['persen_budget'], 100) ?>%"></div>
                    </div>
                    <div class="flex justify-between items-center mt-2 text-[9px] font-black uppercase tracking-widest">
                        <span class="text-indigo-100 opacity-80">Terpakai</span>
                        <span class="bg-white/20 px-2 py-0.5 rounded"><?= esc($stats['persen_budget']) ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Surplus/Defisit Card -->
            <?php 
                $isSurplus = $stats['surplus_defisit'] >= 0;
                $statusBg = $isSurplus ? 'from-emerald-500 to-teal-700' : 'from-red-500 to-rose-700';
                $statusIcon = $isSurplus ? 'fa-plus-circle' : 'fa-minus-circle';
            ?>
            <div class="bg-gradient-to-br <?= $statusBg ?> rounded-2xl p-5 shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute -right-2 -bottom-2 opacity-20 transform group-hover:scale-110 transition-transform duration-500">
                    <i class="fas <?= $statusIcon ?> text-6xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Status Surplus/Defisit</div>
                    <div class="text-xl font-black tracking-tighter">Rp <?= number_format(abs($stats['surplus_defisit']), 0, ',', '.') ?></div>
                    <div class="mt-4 flex items-center">
                        <span class="text-[10px] font-black uppercase bg-white/20 px-3 py-1 rounded-full backdrop-blur-sm shadow-inner border border-white/10">
                            <?= $isSurplus ? 'KAS SURPLUS' : 'KAS DEFISIT' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pemasukan Card -->
            <div class="bg-gradient-to-br from-emerald-500 to-green-700 rounded-2xl p-5 shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute -right-2 -bottom-2 opacity-20 transform group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-arrow-down text-6xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-bold text-emerald-100 uppercase tracking-widest mb-1 opacity-80">Realisasi Pemasukan</div>
                    <div class="text-xl font-black tracking-tighter">Rp <?= number_format($stats['total_pemasukan'], 0, ',', '.') ?></div>
                    <div class="mt-4 flex items-center text-[9px] font-black uppercase tracking-widest">
                        <span class="bg-white/20 px-2 py-1 rounded flex items-center">
                            <i class="fas fa-calendar-check mr-2"></i> 30 Hari Terakhir
                        </span>
                    </div>
                </div>
            </div>

            <!-- Piutang Card -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-700 rounded-2xl p-5 shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="absolute -right-2 -bottom-2 opacity-20 transform group-hover:scale-110 transition-transform duration-500">
                    <i class="fas fa-clock text-6xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-bold text-amber-100 uppercase tracking-widest mb-1 opacity-80">Piutang (Pending)</div>
                    <div class="text-xl font-black tracking-tighter">Rp <?= number_format($stats['total_piutang'], 0, ',', '.') ?></div>
                    <div class="mt-4 flex items-center text-[9px] font-black uppercase tracking-widest">
                        <span class="bg-white/20 px-2 py-1 rounded flex items-center">
                            <i class="fas fa-hourglass-half mr-2"></i> Potensi Pendapatan
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Arus Kas Trend -->
            <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                    <h6 class="text-[11px] font-black text-gray-700 uppercase tracking-widest flex items-center">
                        <i class="fas fa-chart-area mr-2 text-blue-600"></i> Analisis Cashflow Harian
                    </h6>
                </div>
                <div class="p-6">
                    <div class="relative h-64 w-full">
                        <?php if(!empty($chart_cashflow['labels'])): ?>
                            <canvas id="cashflowChart"></canvas>
                        <?php else: ?>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                                <i class="fas fa-chart-line text-4xl mb-2 opacity-20"></i>
                                <span class="text-xs font-bold uppercase tracking-widest">Data Kosong</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Komposisi Pemasukan per Unit -->
            <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center">
                <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/30 text-left">
                    <h6 class="text-[11px] font-black text-gray-700 uppercase tracking-widest flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-purple-600"></i> Pendapatan per Unit
                    </h6>
                </div>
                <div class="p-6">
                    <div class="relative h-56 w-full mb-4 flex items-center justify-center">
                        <?php if(!empty($chart_distribution)): ?>
                            <canvas id="distributionChart"></canvas>
                        <?php else: ?>
                            <div class="text-slate-400">
                                <i class="fas fa-chart-pie text-4xl mb-2 opacity-20"></i><br>
                                <span class="text-xs font-bold uppercase tracking-widest">Data Kosong</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table with Pagination -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/30 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h6 class="text-[11px] font-black text-gray-700 uppercase tracking-widest flex items-center">
                    <i class="fas fa-history mr-2 text-gray-400"></i> Log Transaksi Terkini
                </h6>
                
                <!-- Table Controls -->
                <div class="flex items-center gap-3">
                     <div class="relative">
                        <input type="text" id="search-transaksi" placeholder="Cari transaksi..." class="pl-8 pr-4 py-1.5 border border-slate-200 rounded-lg text-xs w-48 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <i class="fas fa-search absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                    </div>
                    <select id="perPage-transaksi" class="border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                    </select>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse advanced-table" id="table-transaksi">
                    <thead>
                        <tr class="bg-white text-[9px] uppercase text-gray-400 font-black border-b border-gray-100">
                            <th class="px-6 py-4 tracking-widest cursor-pointer sortable" data-sort="date">Waktu <i class="fas fa-sort ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 tracking-widest cursor-pointer sortable" data-sort="string">Kategori <i class="fas fa-sort ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 tracking-widest cursor-pointer sortable" data-sort="string">Pihak Terkait <i class="fas fa-sort ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 tracking-widest text-right cursor-pointer sortable" data-sort="number">Nominal <i class="fas fa-sort ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 tracking-widest text-center">Tipe</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-gray-50 bg-white font-medium">
                        <?php if(!empty($recent_transactions)): ?>
                            <?php foreach($recent_transactions as $trx): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap" data-value="<?= strtotime($trx['tanggal']) ?>">
                                    <div class="font-black text-gray-800 tracking-tighter"><?= date('d M Y', strtotime($trx['tanggal'])) ?></div>
                                    <div class="text-[9px] text-gray-400 uppercase"><?= date('H:i', strtotime($trx['tanggal'])) ?> WIB</div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="text-[10px] font-black <?= ($trx['jenis'] ?? '') == 'masuk' ? 'text-emerald-600' : 'text-red-600' ?> uppercase tracking-tighter mb-0.5">
                                        <?= esc($trx['kategori'] ?? '') ?>
                                    </div>
                                    <div class="text-gray-400 text-[10px] truncate max-w-[200px]"><?= esc($trx['deskripsi'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-3 font-bold text-gray-700 uppercase">
                                    <?= esc($trx['pihak_terkait'] ?: '-') ?>
                                </td>
                                <td class="px-6 py-3 text-right font-black <?= ($trx['jenis'] ?? '') == 'masuk' ? 'text-emerald-700' : 'text-red-700' ?>" data-value="<?= $trx['jumlah'] ?? 0 ?>">
                                    <?= ($trx['jenis'] ?? '') == 'masuk' ? '+' : '-' ?> Rp <?= number_format($trx['jumlah'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-[8px] font-black uppercase border shadow-sm <?= ($trx['jenis'] ?? '') == 'masuk' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' ?>">
                                        <?= esc($trx['jenis'] ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-20 text-gray-400 font-black uppercase tracking-widest opacity-20">Belum Ada Transaksi Tercatat</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-3 flex items-center justify-between" id="pagination-transaksi">
                <span class="text-[10px] font-bold text-gray-400" id="info-table-transaksi">Menampilkan 0 dari 0 data</span>
                <div class="flex gap-1" id="controls-table-transaksi"></div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // --- 1. CHART LOGIC ---
    window.addEventListener('load', function() {
        const cashCtx = document.getElementById('cashflowChart')?.getContext('2d');
        if (cashCtx) {
            new Chart(cashCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($chart_cashflow['labels'] ?? []) ?>,
                    datasets: [
                        {
                            label: 'Masuk',
                            data: <?= json_encode($chart_cashflow['income'] ?? []) ?>,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointRadius: 4,
                            fill: true
                        },
                        {
                            label: 'Keluar',
                            data: <?= json_encode($chart_cashflow['expense'] ?? []) ?>,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointRadius: 4,
                            fill: true
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: true, labels: { font: { size: 10, weight: 'bold' } } } },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 9, weight: 'bold' }, callback: (val) => 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'jt' : val.toLocaleString()) } },
                        x: { ticks: { font: { size: 9, weight: 'bold' } } }
                    }
                }
            });
        }

        const distCtx = document.getElementById('distributionChart')?.getContext('2d');
        if (distCtx) {
            const distLabels = <?= json_encode(array_keys($chart_distribution ?? [])) ?>;
            const distData = <?= json_encode(array_values($chart_distribution ?? [])) ?>;
            const bgColors = ['#9333ea', '#2563eb', '#f59e0b', '#10b981', '#ef4444', '#6366f1', '#ec4899'];
            const chartColors = distLabels.map((_, i) => bgColors[i % bgColors.length]);

            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distData,
                        backgroundColor: chartColors,
                        borderWidth: 5,
                        borderColor: '#ffffff',
                        hoverOffset: 15
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    cutout: '80%',
                    plugins: { legend: { display: true, position: 'bottom', labels: { font: { size: 10, weight: 'bold' }, boxWidth: 10, padding: 15 } } }
                }
            });
        }

        // --- 2. TABLE PAGINATION & SORT LOGIC ---
        initTable('table-transaksi', 'perPage-transaksi', 'search-transaksi', 'info-table-transaksi', 'controls-table-transaksi');
    });

    function initTable(tableId, perPageId, searchId, infoId, controlsId) {
        const table = document.getElementById(tableId);
        if(!table) return;
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        // Jika tabel kosong atau hanya pesan "Belum ada", skip
        if(rows.length <= 1 && rows[0].innerText.includes('Belum')) return;

        const originalRows = [...rows];
        let filteredRows = [...rows];
        let currentPage = 1;
        let perPage = parseInt(document.getElementById(perPageId)?.value || 10);
        let sortCol = null;
        let sortAsc = true;

        const renderTable = () => {
            const totalRows = filteredRows.length;
            const totalPages = Math.ceil(totalRows / perPage);
            if (currentPage < 1) currentPage = 1;
            if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            originalRows.forEach(r => r.style.display = 'none');
            filteredRows.slice(start, end).forEach(r => r.style.display = '');

            // Update Info
            const infoEl = document.getElementById(infoId);
            if(infoEl) infoEl.innerText = `Menampilkan ${totalRows === 0 ? 0 : start + 1} - ${Math.min(end, totalRows)} dari ${totalRows} data`;

            // Update Controls
            const controlsEl = document.getElementById(controlsId);
            if(controlsEl) {
                let html = '';
                html += `<button class="w-6 h-6 flex items-center justify-center rounded border border-gray-200 hover:bg-white text-xs disabled:opacity-50" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage('${tableId}', -1)"><i class="fas fa-chevron-left"></i></button>`;
                html += `<span class="text-[10px] font-bold mx-2">Hal ${currentPage} / ${totalPages || 1}</span>`;
                html += `<button class="w-6 h-6 flex items-center justify-center rounded border border-gray-200 hover:bg-white text-xs disabled:opacity-50" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''} onclick="changePage('${tableId}', 1)"><i class="fas fa-chevron-right"></i></button>`;
                controlsEl.innerHTML = html;
            }
        };

        // Event Listeners
        document.getElementById(perPageId)?.addEventListener('change', (e) => {
            perPage = parseInt(e.target.value);
            currentPage = 1;
            renderTable();
        });

        document.getElementById(searchId)?.addEventListener('keyup', (e) => {
            const term = e.target.value.toLowerCase();
            filteredRows = originalRows.filter(row => row.innerText.toLowerCase().includes(term));
            currentPage = 1;
            renderTable();
        });

        // Sorting
        table.querySelectorAll('th.sortable').forEach((th, index) => {
            th.addEventListener('click', () => {
                const type = th.dataset.sort;
                sortAsc = sortCol === index ? !sortAsc : true;
                sortCol = index;
                
                filteredRows.sort((a, b) => {
                    // Cek jika ada data-value (untuk angka/tanggal)
                    const valA = a.children[index].dataset.value || a.children[index].innerText.trim();
                    const valB = b.children[index].dataset.value || b.children[index].innerText.trim();

                    if(type === 'number' || type === 'date') {
                        return sortAsc ? (parseFloat(valA) - parseFloat(valB)) : (parseFloat(valB) - parseFloat(valA));
                    } else {
                        return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
                    }
                });
                renderTable();
            });
        });

        // Global Page Changer
        window.changePage = (id, dir) => {
            if(id !== tableId) return;
            currentPage += dir;
            renderTable();
        };

        renderTable();
    }
</script>
<?= $this->endSection() ?>