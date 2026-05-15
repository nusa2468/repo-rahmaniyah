<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- FIX: Load FontAwesome CDN agar ikon muncul -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<?php
    // =========================================================================
    // 1. LOGIC FILTER & SECURITY (VIEW LEVEL SCOPING)
    // =========================================================================
    $session = session();
    $role    = $session->get('role_name');
    $myUnit  = $session->get('kode_jenjang');
    
    // Ambil Filter dari URL (Khusus Superadmin)
    $request = service('request');
    $urlJenjang = $request->getGet('jenjang');

    // Filter Data Pendaftar secara Manual di View (Safety Net)
    $filteredPendaftar = [];
    
    foreach ($pendaftar ?? [] as $row) {
        // A. Filter Keamanan untuk Admin Unit
        if (!in_array($role, ['superadmin', 'yayasan'])) {
            // Jika bukan superadmin, dan unit tidak sama, SKIP data ini (Jangan bocor!)
            if ($myUnit && $myUnit !== 'GLOBAL' && isset($row->kode_jenjang) && $row->kode_jenjang !== $myUnit) {
                continue;
            }
        }
        
        // B. Filter Pilihan untuk Superadmin
        if (in_array($role, ['superadmin', 'yayasan']) && $urlJenjang && $urlJenjang !== 'Semua') {
            if (isset($row->kode_jenjang) && $row->kode_jenjang !== $urlJenjang) {
                continue;
            }
        }

        $filteredPendaftar[] = $row;
    }

    // =========================================================================
    // 2. HITUNG STATISTIK (KPI) BERDASARKAN DATA TERFILTER
    // =========================================================================
    $totalPendingFee = 0;
    $totalPaidFee = 0;
    $totalPendaftar = count($filteredPendaftar);
    $paidCount = 0;
    $pendingCount = 0;

    foreach ($filteredPendaftar as $p) {
        $fee = $p->nominal_fee ?? 0;
        $statusFee = $p->status_fee ?? 'Pending';
        
        if ($statusFee === 'Dibayar') {
            $totalPaidFee += $fee;
            $paidCount++;
        } else {
            $totalPendingFee += $fee;
            $pendingCount++;
        }
    }
    $ratio = $totalPendaftar > 0 ? round(($paidCount / $totalPendaftar) * 100) : 0;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header Compact & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                Finansial Afiliasi
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manajemen komisi agen berdasarkan data validasi pelunasan siswa.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            
            <!-- UPDATE: TOMBOL SCOPE UNIT (Khusus Superadmin) -->
            <?php if (in_array($role, ['superadmin', 'yayasan'])): ?>
                <?php $currJenjang = $urlJenjang ?: 'Semua'; ?>
                
                <div x-data="{ open: false }" class="relative mr-2">
                    <button @click="open = !open" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-200 font-black text-xs uppercase tracking-widest rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                        <i class="fas fa-filter text-sky-500"></i>
                        <span>Unit: <?= esc(strtoupper($currJenjang)) ?></span>
                        <i class="fas fa-chevron-down ml-1 text-[10px]"></i>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-white/10 z-50 py-1" 
                         style="display: none;">
                        
                        <a href="<?= current_url() ?>" 
                           class="block px-4 py-2 text-xs font-bold <?= ($currJenjang === 'Semua') ? 'text-sky-600 bg-sky-50 dark:bg-white/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?> uppercase transition-colors">
                            Semua Unit
                        </a>
                        <div class="h-px bg-gray-100 dark:bg-white/5 my-1"></div>

                        <?php 
                        // Logic pengambilan data Jenjang yang Robust
                        $listUnit = [];
                        try {
                            $jenjangModel = new \App\Models\JenjangModel();
                            $listUnit = $jenjangModel->findAll(); 
                        } catch (\Throwable $e) { }
                        ?>

                        <?php if(empty($listUnit)): ?>
                            <div class="px-4 py-2 text-[10px] text-gray-400 italic text-center">Data unit kosong</div>
                        <?php else: ?>
                            <?php foreach($listUnit as $u): ?>
                            <?php 
                                $val = is_array($u) ? ($u['kode_jenjang'] ?? $u['nama']) : ($u->kode_jenjang ?? $u->nama);
                                $isActive = ($currJenjang === $val);
                            ?>
                            <a href="?jenjang=<?= esc($val) ?>" 
                               class="block px-4 py-2 text-xs font-bold <?= $isActive ? 'text-sky-600 bg-sky-50 dark:bg-white/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?> uppercase transition-colors">
                                Unit <?= esc($val) ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
                <i class="fas fa-print"></i>
                Cetak
            </button>
            <a href="<?= base_url('app/ppdb/affiliate') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
        </div>
    </div>

    <!-- KPI Cards - Compact Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card Fee Pending -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-transform">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Fee Pending</p>
                <p class="text-3xl font-black mt-2">Rp <?= number_format($totalPendingFee, 0, ',', '.') ?></p>
                <p class="text-sm mt-1 opacity-80"><?= $pendingCount ?> Transaksi</p>
            </div>
            <i class="fas fa-clock absolute -right-4 -bottom-4 text-white/20 text-6xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Fee Dibayar -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-transform">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Fee Dibayar</p>
                <p class="text-3xl font-black mt-2">Rp <?= number_format($totalPaidFee, 0, ',', '.') ?></p>
                <p class="text-sm mt-1 opacity-80"><?= $paidCount ?> Transaksi</p>
            </div>
            <i class="fas fa-check-double absolute -right-4 -bottom-4 text-white/20 text-6xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Total Pendaftar -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-transform">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Total Pendaftar</p>
                <p class="text-3xl font-black mt-2"><?= $totalPendaftar ?></p>
                <p class="text-sm mt-1 opacity-80">Siswa Referal</p>
            </div>
            <i class="fas fa-user-graduate absolute -right-4 -bottom-4 text-white/20 text-6xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Card Rasio Pencairan -->
        <div class="bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-transform">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Rasio Pencairan</p>
                <p class="text-3xl font-black mt-2"><?= $ratio ?>%</p>
                <p class="text-sm mt-1 opacity-80">Terealisasi</p>
            </div>
            <i class="fas fa-chart-pie absolute -right-4 -bottom-4 text-white/20 text-6xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- Layout: Chart + Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Doughnut Chart: Komposisi Status Fee -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 text-center flex items-center justify-center gap-2">
                <i class="fas fa-chart-pie text-gray-400"></i> Komposisi Status Fee
            </h3>
            <div class="h-64 relative">
                <canvas id="feeStatusChart"></canvas>
            </div>
        </div>

        <!-- Tabel Transaksi Komisi -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                    <i class="fas fa-list text-sky-500"></i> Daftar Transaksi Komisi
                </h3>
                <span class="px-3 py-1 bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 text-[10px] font-black uppercase rounded-lg tracking-wide">
                    Real-time
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-12">No</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Siswa Referal</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Kode Agen</th>
                            <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Unit</th>
                            <th class="px-5 py-3 text-right text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nominal Fee</th>
                            <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status Fee</th>
                            <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php if (empty($filteredPendaftar)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                    <i class="fas fa-search-dollar text-4xl mb-3 opacity-40"></i>
                                    <p class="text-xs font-black uppercase tracking-widest">Belum Ada Data Komisi</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($filteredPendaftar as $row): ?>
                                <?php
                                    $isDibayar = ($row->status_fee ?? 'Pending') === 'Dibayar';
                                    $feeColor  = $isDibayar ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200';
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                    <td class="px-5 py-3.5 text-center font-bold text-gray-600 dark:text-gray-400 text-sm group-hover:text-sky-600 transition-colors"><?= $i++ ?></td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-sky-600 transition-colors"><?= esc($row->nama_lengkap) ?></div>
                                        <div class="text-xs font-mono text-sky-600 dark:text-sky-400 opacity-80"><?= esc($row->no_pendaftaran) ?></div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">
                                            <i class="fas fa-tag text-[10px] text-sky-500"></i> <?= esc($row->kode_afiliasi) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                            <?= esc($row->kode_jenjang ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-black text-gray-900 dark:text-white">
                                        Rp <?= number_format($row->nominal_fee ?? 0, 0, ',', '.') ?>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase border <?= $feeColor ?>">
                                            <?= esc($row->status_fee ?? 'Pending') ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= base_url('app/ppdb/edit/' . $row->pendaftar_id) ?>" 
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm group/btn"
                                               title="Proses Fee">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const paidCount = <?= $paidCount ?>;
        const pendingCount = <?= $pendingCount ?>;

        const ctx = document.getElementById('feeStatusChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Dibayar', 'Pending'],
                    datasets: [{
                        data: [paidCount, pendingCount],
                        backgroundColor: ['rgb(16, 185, 129)', 'rgb(245, 158, 11)'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 20, font: { size: 11 } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>