<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- FIX: Load FontAwesome CDN agar ikon muncul -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<?php
    // FIX LOGIC: Menggunakan Pager untuk Total Database yang akurat (Server-side)
    $totalDB = isset($pager) ? $pager->getTotal() : count($pendaftar ?? []);
    
    // Page level stats
    $pageLolos = 0;
    $pagePending = 0;
    $pageLunas = 0;
    $jalurCounts = [];

    foreach ($pendaftar ?? [] as $p) {
        $status = $p->status_seleksi ?? '';
        if ($status === 'Lolos') $pageLolos++;
        elseif ($status === 'Pending') $pagePending++;
        
        if (($p->status_pembayaran ?? '') === 'Lunas') $pageLunas++;
        
        $jalur = $p->jalur_masuk ?? 'Lainnya';
        $jalurCounts[$jalur] = ($jalurCounts[$jalur] ?? 0) + 1;
    }
?>

<div class="max-w-7xl mx-auto">
    <!-- Header Compact & Profesional -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                Database Master Pendaftar
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manajemen verifikasi dan data akademik calon siswa secara menyeluruh.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            
            <!-- UPDATE: FILTER UNIT (Sama seperti Dashboard) -->
            <?php if (in_array(session('role_name'), ['superadmin', 'yayasan'])): ?>
                <?php 
                    $request = service('request');
                    $currJenjang = $request->getGet('jenjang') ?: 'Semua'; 
                ?>
                <div x-data="{ open: false }" class="relative mr-2">
                    <button @click="open = !open" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 font-bold text-xs uppercase tracking-widest rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-white/5 transition-all group">
                        <i class="fas fa-sliders-h text-indigo-500 group-hover:text-indigo-600 transition-colors"></i>
                        <span class="group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Unit: <?= esc(strtoupper($currJenjang)) ?></span>
                        <i class="fas fa-chevron-down ml-1 text-[10px] text-gray-400"></i>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-white/10 z-50 py-1" 
                         style="display: none;">
                        
                        <a href="<?= current_url() ?>" 
                           class="block px-4 py-2 text-xs font-bold <?= ($currJenjang === 'Semua') ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?> uppercase transition-colors">
                            Semua Unit
                        </a>
                        <div class="h-px bg-gray-100 dark:bg-white/5 my-1"></div>

                        <?php 
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
                                if (is_array($u)) {
                                    $val = $u['kode_jenjang'] ?? $u['nama'] ?? '-';
                                } else {
                                    $val = $u->kode_jenjang ?? $u->nama ?? '-';
                                }
                                $isActive = ($currJenjang === $val);
                            ?>
                            <a href="<?= current_url() ?>?jenjang=<?= esc($val) ?>" 
                               class="block px-4 py-2 text-xs font-bold <?= $isActive ? 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' ?> uppercase transition-colors">
                                Unit <?= esc($val) ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <a href="<?= base_url('app/ppdb/export/excel') . ($currJenjang !== 'Semua' ? '?jenjang='.$currJenjang : '') ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-file-excel"></i>
                <span class="hidden sm:inline">Excel</span>
            </a>
            <a href="<?= base_url('app/ppdb/export/pdf') . ($currJenjang !== 'Semua' ? '?jenjang='.$currJenjang : '') ?>"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-file-pdf"></i>
                <span class="hidden sm:inline">PDF</span>
            </a>
            <a href="<?= base_url('app/ppdb/add') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-user-plus"></i>
                <span class="hidden sm:inline">Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Cards - Compact Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-sky-200 dark:hover:border-sky-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center text-sky-600 dark:text-sky-400">
                <i class="fas fa-database text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Total Database</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($totalDB) ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <i class="fas fa-user-check text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Lolos (Page)</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($pageLolos) ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-amber-200 dark:hover:border-amber-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                <i class="fas fa-clock text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Pending (Page)</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($pagePending) ?></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-wallet text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Lunas (Page)</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($pageLunas) ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Row - Compact -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Bar Chart: Sebaran Jalur Masuk (Page Scope) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-gray-400"></i> Sebaran Jalur Masuk (Halaman Ini)
            </h3>
            <div class="h-56">
                <canvas id="jalurChart"></canvas>
            </div>
        </div>

        <!-- Pie Chart: Komposisi Seleksi (Page Scope) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-gray-400"></i> Komposisi Seleksi (Halaman Ini)
            </h3>
            <div class="h-56 relative">
                <canvas id="statusPieChart"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-4 text-xs">
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-emerald-500"></i> Lolos</span>
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-amber-500"></i> Pending</span>
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-red-500"></i> Gagal</span>
            </div>
        </div>
    </div>

    <!-- Tabel Data Lengkap -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                <i class="fas fa-list text-gray-400"></i> Data Lengkap Calon Siswa
            </h3>
            <!-- Search Input -->
            <form action="" method="get" class="flex items-center">
                <?php if($currJenjang !== 'Semua'): ?>
                    <input type="hidden" name="jenjang" value="<?= esc($currJenjang) ?>">
                <?php endif; ?>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="q" value="<?= esc(service('request')->getGet('q')) ?>" 
                           placeholder="Cari Nama / No. Daftar..." 
                           class="pl-9 pr-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-white/10 rounded-lg text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:border-sky-500 w-48 sm:w-64">
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-16">No</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Profil Calon</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Latar & Skor</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Administrasi</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Seleksi</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($pendaftar)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-40"></i>
                                <p class="text-xs font-black uppercase tracking-widest">Belum Ada Pendaftar</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                            $page = isset($pager) ? $pager->getCurrentPage() : 1;
                            $perPage = isset($pager) ? $pager->getPerPage() : 20;
                            $n = ($page - 1) * $perPage + 1;
                        ?>
                        <?php foreach ($pendaftar as $p): ?>
                            <?php
                                $payStatus = $p->status_pembayaran ?? 'Belum Bayar';
                                $payColor = ($payStatus === 'Lunas') ? 'emerald' : (($payStatus === 'Menunggu Verifikasi') ? 'blue' : 'amber');
                                $selStatus = $p->status_seleksi ?? 'Pending';
                                $selColor = match(strtolower($selStatus)) {
                                    'lolos' => 'emerald',
                                    'pending' => 'amber',
                                    'gagal' => 'red',
                                    default => 'gray'
                                };
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-5 py-3.5 text-center font-bold text-gray-600 dark:text-gray-400 text-sm group-hover:text-sky-600 transition-colors">
                                    <?= $n++ ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 text-white flex items-center justify-center font-black text-lg shadow-md shrink-0">
                                            <?= strtoupper(substr($p->nama_lengkap ?? '?', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-sky-600 transition-colors">
                                                <?= esc($p->nama_lengkap) ?>
                                            </div>
                                            <div class="text-xs font-mono text-sky-600 dark:text-sky-400 opacity-80">
                                                <?= esc($p->no_pendaftaran) ?>
                                            </div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                Unit: <span class="font-bold"><?= esc($p->kode_jenjang ?? '-') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">
                                        <?= esc($p->asal_sekolah ?? '-') ?>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold rounded border border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800">
                                            <?= esc($p->jalur_masuk) ?>
                                        </span>
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-400">
                                            Skor: <span class="text-gray-900 dark:text-white"><?= number_format($p->skor_akhir ?? 0, 2) ?></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-<?= $payColor ?>-100 dark:bg-<?= $payColor ?>-500/20 text-<?= $payColor ?>-700 dark:text-<?= $payColor ?>-300 border border-<?= $payColor ?>-200 dark:border-<?= $payColor ?>-500/30">
                                        <i class="fas fa-circle text-[6px]"></i> <?= esc($payStatus) ?>
                                    </span>
                                    <?php if (!empty($p->kode_afiliasi)): ?>
                                        <div class="mt-1.5 text-[10px] font-bold text-sky-600 dark:text-sky-400 flex items-center gap-1">
                                            <i class="fas fa-tag"></i> <?= esc($p->kode_afiliasi) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase bg-<?= $selColor ?>-100 dark:bg-<?= $selColor ?>-900/30 text-<?= $selColor ?>-600 dark:text-<?= $selColor ?>-400 border border-<?= $selColor ?>-200 dark:border-<?= $selColor ?>-500/30">
                                        <?= esc(strtoupper($selStatus)) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Tombol Detail (Mata) -->
                                        <a href="<?= base_url('app/ppdb/detail/' . $p->pendaftar_id) ?>" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400 hover:bg-sky-600 hover:text-white transition-all shadow-sm group/btn"
                                           title="Detail">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        
                                        <!-- Tombol Edit (Pensil) -->
                                        <a href="<?= base_url('app/ppdb/edit/' . $p->pendaftar_id) ?>" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm group/btn"
                                           title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        
                                        <!-- Tombol Hapus (Tempat Sampah) -->
                                        <a href="<?= base_url('app/ppdb/delete/' . $p->pendaftar_id) ?>" 
                                           onclick="return confirm('Hapus data pendaftar ini secara permanen?')"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-500 hover:text-white transition-all shadow-sm group/btn"
                                           title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Section -->
        <?php if (isset($pager)): ?>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50 dark:bg-gray-800/50">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium order-2 sm:order-1">
                Menampilkan 
                <span class="font-bold text-gray-900 dark:text-white">
                    <?= ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 ?>
                </span> 
                sampai 
                <span class="font-bold text-gray-900 dark:text-white">
                    <?= min($pager->getTotal(), $pager->getCurrentPage() * $pager->getPerPage()) ?>
                </span> 
                dari 
                <span class="font-bold text-gray-900 dark:text-white"><?= number_format($pager->getTotal()) ?></span> 
                data
            </div>
            
            <div class="order-1 sm:order-2">
                <!-- MENGGUNAKAN TEMPLATE TAILWINDPAGINATION -->
                <?= $pager->links('default', 'tailwind_pagination') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Data chart diambil dari halaman saat ini (Client-side visualization)
        const pendaftarData = <?= json_encode($pendaftar ?? []) ?>;

        // Hitung status seleksi dari data page ini
        const statusCounts = { Lolos: 0, Pending: 0, Gagal: 0 };
        const jalurCounts = {};

        pendaftarData.forEach(p => {
            const status = p.status_seleksi || 'Gagal';
            if (statusCounts.hasOwnProperty(status)) {
                statusCounts[status]++;
            } else {
                statusCounts.Gagal++; // Fallback
            }

            const jalur = p.jalur_masuk || 'Lainnya';
            jalurCounts[jalur] = (jalurCounts[jalur] || 0) + 1;
        });

        // Doughnut Chart - Komposisi Seleksi
        const pieCtx = document.getElementById('statusPieChart')?.getContext('2d');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Lolos', 'Pending', 'Gagal'],
                    datasets: [{
                        data: [statusCounts.Lolos, statusCounts.Pending, statusCounts.Gagal],
                        backgroundColor: ['rgb(16, 185, 129)', 'rgb(251, 191, 36)', 'rgb(239, 68, 68)'],
                        borderWidth: 0,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Bar Chart - Sebaran Jalur Masuk
        const barCtx = document.getElementById('jalurChart')?.getContext('2d');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(jalurCounts),
                    datasets: [{
                        data: Object.values(jalurCounts),
                        backgroundColor: 'rgb(79, 70, 229)',
                        borderRadius: 6,
                        barThickness: 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 11 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>