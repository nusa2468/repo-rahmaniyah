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
    // --- 1. INISIALISASI VARIABEL (SAFETY) ---
    $session = session();
    $userRole = strtolower($session->get('role') ?? '');
    $userUnit = $session->get('kode_jenjang');

    // Fallback logic jika controller tidak mengirim $isSuperAdmin
    if (!isset($isSuperAdmin)) {
        $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin', 'yayasan', 'root']) 
                        || in_array(strtoupper($userUnit ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT']);
    }

    $filter_jenjang = $filter_jenjang ?? $userUnit;
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20">
    
    <!-- --- HEADER SECTION (Sticky & Consistent) --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300 no-print">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-emerald-600 rounded-lg shadow-lg shadow-emerald-500/30 text-white">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Laporan Pemasukan</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">
                                PERIODE
                            </span>
                            <span class="text-slate-300">|</span>
                            <span><?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Controls -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <!-- DROPDOWN UNIT CERDAS (ANTI BOCOR) -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" id="filterFormHeader" class="flex items-center gap-2 m-0 p-0 relative">
                        <!-- Pertahankan tanggal saat ganti unit -->
                        <input type="hidden" name="start_date" value="<?= $start_date ?>">
                        <input type="hidden" name="end_date" value="<?= $end_date ?>">
                        
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-emerald-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600">
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

                            <?php if(!$isSuperAdmin): ?>
                                <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Export Buttons -->
                <div class="flex gap-2">
                    <button onclick="window.print()" class="h-10 px-5 bg-slate-800 text-white hover:bg-slate-700 rounded-xl text-xs font-bold shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- --- NAVIGATION TABS --- -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    // Highlight tab aktif (pemasukan ada di controller LaporanPemasukan)
                    $isActive = ($key === 'pembayaran' || ($key === 'laporan' && strpos(current_url(), 'pemasukan') !== false)); 
                    $activeClass = $isActive 
                        ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-black/5' 
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

    <!-- MAIN CONTENT -->
    <div class="px-6 max-w-screen-2xl mx-auto space-y-8">
        
        <!-- Filter Date -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6 no-print">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center">
                <i class="fas fa-calendar-alt mr-2"></i> Filter Periode Laporan
            </h4>
            <form action="" method="get" class="flex flex-col md:flex-row gap-4 items-end">
                <?php if($isSuperAdmin): ?>
                   <!-- Jika superadmin, nilai jenjang diambil dari dropdown header via JS atau user harus set ulang -->
                   <!-- Agar aman, kita bisa include hidden input jenjang dari value GET saat ini -->
                   <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                <?php else: ?>
                   <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                <?php endif; ?>
                
                <div class="w-full md:w-auto">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-emerald-500 text-slate-700 shadow-sm">
                </div>
                <div class="w-full md:w-auto">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-emerald-500 text-slate-700 shadow-sm">
                </div>
                <button type="submit" class="w-full md:w-auto px-8 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition">
                    Tampilkan Data
                </button>
            </form>
        </div>

        <!-- KPI Cards (Modern Solid Gradient) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Unit Card -->
            <div class="bg-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden group border-l-4 border-emerald-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">Unit Laporan</p>
                    <h3 class="text-xl font-black text-white tracking-tighter uppercase italic">
                         <?= !empty($filter_jenjang) ? 'UNIT '.$filter_jenjang : 'AGREGAT (SEMUA)' ?>
                    </h3>
                </div>
                <i class="fas fa-building absolute -right-4 -bottom-4 text-8xl text-white/5 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Total Pemasukan -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-700 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Pemasukan</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($total_pemasukan ?? 0, 0, ',', '.') ?></h3>
                    <div class="mt-2 text-[9px] font-bold bg-white/20 inline-block px-2 py-0.5 rounded backdrop-blur-sm">
                        <?= count($pembayaran ?? []) ?> Transaksi
                    </div>
                </div>
                <i class="fas fa-wallet absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
            
            <!-- Rata-rata -->
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Rata-rata / Hari</p>
                    <?php 
                        $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
                        $avg = ($total_pemasukan ?? 0) / max($days, 1);
                    ?>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($avg, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-calculator absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Metode Dominan -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Metode Dominan</p>
                    <?php 
                        $counts = array_count_values(array_column($pembayaran ?? [], 'metode_pembayaran'));
                        arsort($counts);
                        $topMethod = array_key_first($counts) ?? '-';
                    ?>
                    <h3 class="text-2xl font-black tracking-tighter uppercase"><?= $topMethod ?></h3>
                </div>
                <i class="fas fa-credit-card absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
        </div>

        <!-- Chart -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6 relative overflow-hidden">
             <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
             <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center">
                 <i class="fas fa-chart-area mr-2"></i> Tren Pemasukan Harian
             </h4>
             <div class="relative h-72 w-full">
                 <canvas id="incomeChart"></canvas>
             </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden mb-10">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-list text-slate-400"></i> Rincian Transaksi
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-[10px] uppercase text-slate-400 font-black border-b border-slate-100">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Siswa / Pihak</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Metode</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                        <?php if(empty($pembayaran)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data pemasukan pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php 
                                $no = isset($nomor_urut) ? $nomor_urut + 1 : 1; 
                            ?>
                            <?php foreach($pembayaran as $p): ?>
                            <tr class="hover:bg-emerald-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center font-mono text-slate-400"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= date('d/m/Y', strtotime($p['tanggal_bayar'])) ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono"><?= date('H:i', strtotime($p['tanggal_bayar'])) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800"><?= esc($p['nama_siswa'] ?? 'Umum') ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        <?= !empty($p['nis']) ? 'NIS: '.esc($p['nis']) : '-' ?> 
                                        <?= !empty($p['kode_jenjang']) ? '('.esc($p['kode_jenjang']).')' : '' ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-emerald-700"><?= esc($p['nama_pembayaran'] ?? 'Pembayaran Lain') ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 italic max-w-xs truncate"><?= esc($p['deskripsi_tagihan'] ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php $metode = strtolower($p['metode_pembayaran'] ?? 'tunai'); ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase <?= $metode == 'transfer' ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                                        <?= $metode ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800 tracking-tighter">
                                    Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50">
                <?= $pager->links('default', 'tailwind_pagination') ?>
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

        const ctx = document.getElementById('incomeChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pemasukan',
                        data: data,
                        borderColor: '#10b981',
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
                            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                            return gradient;
                        },
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: '#f3f4f6' },
                            ticks: { 
                                font: { size: 9, weight: 'bold' }, 
                                callback: (val) => val >= 1000000 ? (val/1000000).toFixed(1) + 'jt' : val.toLocaleString('id-ID')
                            } 
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>