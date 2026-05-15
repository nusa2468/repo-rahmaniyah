<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    $safeAgen = $agen ?? [];
    $totalAgen = count($safeAgen);
?>

<div class="max-w-7xl mx-auto">
    <!-- Header Compact & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                Database Agen Marketing
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manajemen lengkap data agen afiliasi dan performa marketing.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('app/ppdb/affiliate/fee') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-wallet"></i>
                Monitoring Fee
            </a>
            <a href="<?= base_url('app/ppdb/affiliate/addAgen') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-user-plus"></i>
                Registrasi Agen
            </a>
        </div>
    </div>

    <!-- KPI Cards - Compact Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Total Agen</p>
                <p class="text-3xl font-black mt-2"><?= $totalAgen ?></p>
                <p class="text-sm mt-1 opacity-80">Orang</p>
            </div>
            <i class="fas fa-handshake absolute -right-4 -bottom-4 text-white/20 text-6xl"></i>
        </div>

        <div class="bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Total Leads</p>
                <p class="text-3xl font-black mt-2"><?= number_format($stats['total_leads'] ?? 0) ?></p>
            </div>
            <i class="fas fa-users absolute -right-4 -bottom-4 text-white/20 text-6xl"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Konversi Lunas</p>
                <p class="text-3xl font-black mt-2"><?= number_format($stats['total_lunas'] ?? 0) ?></p>
            </div>
            <i class="fas fa-user-check absolute -right-4 -bottom-4 text-white/20 text-6xl"></i>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Komisi Terbayar</p>
                <p class="text-3xl font-black mt-2">Rp <?= number_format($stats['total_fee'] ?? 0, 0, ',', '.') ?></p>
            </div>
            <i class="fas fa-coins absolute -right-4 -bottom-4 text-white/20 text-6xl"></i>
        </div>
    </div>

    <!-- Charts Row - Compact -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Bar Chart: Produktivitas Top Agen -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4">
                Produktivitas Top Agen (Lead vs Lunas)
            </h3>
            <div class="h-64">
                <canvas id="performaChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Distribusi Strategi -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4">
                Distribusi Strategi Marketing
            </h3>
            <div class="h-64 relative">
                <canvas id="strategiChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabel Database Agen - Super Compact & Profesional -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">
                Database Master Agen Marketing
            </h3>
            <span class="px-3 py-1 bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 text-xs font-black uppercase rounded-lg">
                Update: <?= date('d/m/Y') ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-16">No</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Identitas & Strategi</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Kontak & Rekening</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Rate Fee</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-40">Performa</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-48">Achievement</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($safeAgen)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                <i class="fas fa-handshake text-4xl mb-3 opacity-40"></i>
                                <p class="text-xs font-black uppercase tracking-widest">Belum Ada Agen</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $n = 1; foreach ($safeAgen as $row): ?>
                            <?php
                                $target = max($row->target_pendaftar ?? 1, 1);
                                $realisasi = $row->total_lunas ?? 0;
                                $percent = round(($realisasi / $target) * 100);
                                $barColor = $percent >= 100 ? 'bg-emerald-600' : ($percent >= 50 ? 'bg-sky-600' : 'bg-amber-600');
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-3.5 text-center font-bold text-gray-600 dark:text-gray-400 text-sm">
                                    <?= $n++ ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">
                                        <?= esc($row->nama_agen) ?>
                                    </div>
                                    <div class="text-xs font-mono text-sky-600 dark:text-sky-400">
                                        ID: <?= esc($row->kode_agen) ?>
                                    </div>
                                    <span class="inline-block mt-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300">
                                        <?= esc(strtoupper($row->metode_agen ?? 'UMUM')) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fab fa-whatsapp text-emerald-500"></i>
                                        <?= esc($row->no_hp) ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        <?= esc($row->email ?? '-') ?>
                                    </div>
                                    <div class="text-xs font-bold mt-1">
                                        <?= esc($row->nama_bank ?? '-') ?> (<?= esc($row->nomor_rekening ?? '-') ?>)
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center font-bold text-gray-900 dark:text-white">
                                    Rp <?= number_format($row->fee_per_siswa ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="font-bold text-lg text-gray-900 dark:text-white">
                                        <?= $row->total_leads ?? 0 ?>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400 font-normal">Leads</span>
                                    </div>
                                    <span class="inline-block mt-1 px-3 py-1 rounded-lg text-xs font-black bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">
                                        LUNAS: <?= $row->total_lunas ?? 0 ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex justify-between items-center text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        <span><?= $percent ?>%</span>
                                        <span><?= $realisasi ?>/<?= $row->target_pendaftar ?? 0 ?></span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="<?= $barColor ?> h-2 rounded-full transition-all duration-700" style="width: <?= min($percent, 100) ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/ppdb/affiliate/detail/' . $row->affiliate_id) ?>"
                                           class="w-9 h-9 rounded-lg bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center shadow-sm hover:shadow transition-all active:scale-95"
                                           title="Detail">
                                            <i class="fas fa-search text-xs"></i>
                                        </a>
                                        <a href="<?= base_url('app/ppdb/affiliate/editAgen/' . $row->affiliate_id) ?>"
                                           class="w-9 h-9 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center shadow-sm hover:shadow transition-all active:scale-95"
                                           title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="<?= base_url('app/ppdb/affiliate/deleteAgen/' . $row->affiliate_id) ?>"
                                           onclick="return confirm('Hapus agen ini?')"
                                           class="w-9 h-9 rounded-lg bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-sm hover:shadow transition-all active:scale-95"
                                           title="Hapus">
                                            <i class="fas fa-trash text-xs"></i>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rawData = <?= json_encode($safeAgen) ?>;

        // Bar Chart - Produktivitas Top 8 Agen
        const top8 = rawData.slice(0, 8);
        const labels = top8.map(a => a.nama_agen);
        const leads = top8.map(a => a.total_leads || 0);
        const lunas = top8.map(a => a.total_lunas || 0);

        const performaCtx = document.getElementById('performaChart')?.getContext('2d');
        if (performaCtx) {
            new Chart(performaCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Total Leads', data: leads, backgroundColor: 'rgb(59, 130, 246)', borderRadius: 6 },
                        { label: 'Siswa Lunas', data: lunas, backgroundColor: 'rgb(16, 185, 129)', borderRadius: 6 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Doughnut Chart - Distribusi Strategi
        const strategyCounts = {};
        rawData.forEach(a => {
            const m = a.metode_agen || 'Umum';
            strategyCounts[m] = (strategyCounts[m] || 0) + 1;
        });

        const strategiCtx = document.getElementById('strategiChart')?.getContext('2d');
        if (strategiCtx) {
            new Chart(strategiCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(strategyCounts),
                    datasets: [{
                        data: Object.values(strategyCounts),
                        backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#ec4899'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>