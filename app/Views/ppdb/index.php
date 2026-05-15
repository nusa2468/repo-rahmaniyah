<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- FIX: Load FontAwesome CDN agar ikon muncul -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                Monitoring PPDB & Afiliasi
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Pantau performa pendaftaran dan produktivitas agen marketing secara real-time.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            
            <!-- 1. TOMBOL SCOPE UNIT (Filter) -->
            <?php 
                $currJenjang = 'Semua';
                if (in_array(session('role_name'), ['superadmin', 'yayasan'])) {
                    $request = service('request');
                    $currJenjang = $request->getGet('jenjang') ?: 'Semua'; 
                }
            ?>

            <?php if (in_array(session('role_name'), ['superadmin', 'yayasan'])): ?>
                <div x-data="{ open: false }" class="relative">
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

            <!-- 2. TOMBOL MANAJEMEN AFILIASI (New Navigation Button) -->
            <a href="<?= base_url('app/ppdb/affiliate') ?>"
               class="group inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 font-bold text-xs uppercase tracking-widest rounded-xl shadow-sm hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                <i class="fas fa-handshake text-indigo-500 group-hover:scale-110 transition-transform"></i>
                <span class="hidden sm:inline group-hover:text-indigo-600 transition-colors">Manajemen Afiliasi</span>
            </a>

            <!-- 3. TOMBOL DATABASE (Secondary Action - Clean) -->
            <a href="<?= base_url('app/ppdb/list') . ($currJenjang !== 'Semua' ? '?jenjang=' . esc($currJenjang) : '') ?>"
               class="group inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 font-bold text-xs uppercase tracking-widest rounded-xl shadow-sm hover:shadow-md hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                <i class="fas fa-table text-gray-400 group-hover:text-sky-500 transition-colors"></i>
                <span class="hidden sm:inline group-hover:text-gray-900 dark:group-hover:text-white transition-colors">Data Master</span>
            </a>

            <!-- 4. TOMBOL INPUT BARU (Primary Action - Highlighted) -->
            <a href="<?= base_url('app/ppdb/add') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg shadow-sky-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fas fa-plus-circle"></i>
                <span class="hidden sm:inline">Input Baru</span>
            </a>

        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Total Calon -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-sky-200 dark:hover:border-sky-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center text-sky-600 dark:text-sky-400">
                <i class="fas fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Total Calon</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($stats['total'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Lulus Seleksi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                <i class="fas fa-check-circle text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Lulus Seleksi</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($stats['lolos'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Bayar Lunas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-white/10 p-4 flex items-center gap-3 hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-wallet text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Bayar Lunas</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mt-1"><?= number_format($stats['lunas'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Agen Aktif -->
        <div class="bg-gradient-to-br from-sky-500 to-sky-600 rounded-xl p-4 flex items-center gap-3 text-white shadow-md hover:shadow-lg transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-handshake text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Agen Afiliasi</p>
                <p class="text-xl font-black mt-1"><?= number_format($stats['total_afiliasi'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Estimasi Fee -->
        <div class="bg-gradient-to-br from-gray-800 to-black rounded-xl p-4 flex items-center gap-3 text-white shadow-md hover:shadow-lg transition-shadow">
            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-coins text-sm"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-widest opacity-90">Estimasi Fee</p>
                <p class="text-xl font-black mt-1">Rp <?= number_format($stats['total_fee'] ?? 0, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Pie Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-gray-400"></i> Status Seleksi Akhir
            </h3>
            <div class="h-56 relative">
                <canvas id="ppdbPieChart"></canvas>
            </div>
            <div class="flex justify-center gap-6 mt-4 text-xs">
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-emerald-500"></i> Lulus</span>
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-amber-500"></i> Pending</span>
                <span class="flex items-center gap-1.5 font-medium text-gray-600 dark:text-gray-400"><i class="w-3 h-3 rounded-full bg-red-500"></i> Gagal</span>
            </div>
        </div>

        <!-- Bar Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
            <h3 class="text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-gray-400"></i> Top 5 Performa Agen Marketing
            </h3>
            <div class="h-56">
                <canvas id="afiliasiBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabel Aktivitas Terbaru -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                <i class="fas fa-history text-sky-500"></i> Aktivitas Pendaftaran Terbaru
            </h3>
            <span class="px-3 py-1 bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 text-[10px] font-black uppercase rounded-lg tracking-wide animate-pulse">
                Real-time
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-16">No</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-24">Unit / TA</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Calon Siswa</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Orang Tua</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Asal Sekolah</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Keuangan & Afiliasi</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-28">Seleksi</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($terbaru)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400 dark:text-gray-600">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-40"></i>
                                <p class="text-xs font-black uppercase tracking-widest">Belum Ada Aktivitas</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($terbaru as $row): ?>
                            <?php
                                $payStatus = $row->status_pembayaran ?? 'Belum Bayar';
                                $payColor = ($payStatus === 'Lunas') ? 'emerald' : (($payStatus === 'Menunggu Verifikasi') ? 'blue' : 'amber');
                                $selStatus = $row->status_seleksi ?? 'Pending';
                                $selColor = match(strtolower($selStatus)) { 'lolos' => 'emerald', 'pending' => 'amber', default => 'red' };
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-5 py-3.5 text-center font-bold text-gray-600 dark:text-gray-400 text-sm group-hover:text-sky-600 transition-colors"><?= $i++ ?></td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-block px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <?= esc($row->kode_jenjang ?? '-') ?>
                                    </span>
                                    <div class="text-[10px] font-mono text-gray-500 mt-1"><?= esc($row->tahun_ajaran ?? '-') ?></div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-sky-600 transition-colors"><?= esc($row->nama_lengkap) ?></div>
                                    <div class="text-xs font-mono text-sky-600 dark:text-sky-400 opacity-80"><?= esc($row->no_pendaftaran) ?></div>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center gap-1"><i class="fas fa-male text-gray-400 text-[10px] w-3"></i> <?= esc($row->nama_ayah ?? '-') ?></div>
                                    <div class="flex items-center gap-1 mt-0.5"><i class="fas fa-female text-gray-400 text-[10px] w-3"></i> <?= esc($row->nama_ibu ?? '-') ?></div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white"><?= esc($row->asal_sekolah ?? '-') ?></div>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded border border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800">
                                        <?= esc($row->jalur_masuk) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide bg-<?= $payColor ?>-100 dark:bg-<?= $payColor ?>-500/20 text-<?= $payColor ?>-700 dark:text-<?= $payColor ?>-300 border border-<?= $payColor ?>-200 dark:border-<?= $payColor ?>-500/30">
                                        <i class="fas fa-circle text-[6px]"></i> <?= esc($payStatus) ?>
                                    </span>
                                    <?php if (!empty($row->kode_afiliasi)): ?>
                                        <div class="mt-1.5 text-[10px] font-bold text-sky-600 dark:text-sky-400 flex items-center gap-1">
                                            <i class="fas fa-tag"></i> <?= esc($row->kode_afiliasi) ?>
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
                                        <a href="<?= base_url('app/ppdb/detail/' . $row->pendaftar_id) ?>" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400 hover:bg-sky-600 hover:text-white transition-all shadow-sm group/btn"
                                           title="Lihat Detail">
                                             <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        
                                        <!-- Tombol Edit (Pensil) -->
                                        <a href="<?= base_url('app/ppdb/edit/' . $row->pendaftar_id) ?>" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm group/btn"
                                           title="Edit Data">
                                             <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        
                                        <!-- Tombol Hapus (Tempat Sampah) -->
                                        <a href="<?= base_url('app/ppdb/delete/' . $row->pendaftar_id) ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
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
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pieCtx = document.getElementById('ppdbPieChart')?.getContext('2d');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Lulus', 'Pending', 'Gagal'],
                    datasets: [{
                        data: [<?= $stats['lolos'] ?? 0 ?>, <?= $stats['pending'] ?? 0 ?>, <?= $stats['gagal'] ?? 0 ?>],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }
        
        const barCtx = document.getElementById('afiliasiBarChart')?.getContext('2d');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['MKT-01', 'MKT-02', 'MKT-03', 'MKT-04', 'MKT-05'],
                    datasets: [{
                        data: [19, 15, 12, 8, 5],
                        backgroundColor: '#4f46e5',
                        borderRadius: 6,
                        barThickness: 24,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>