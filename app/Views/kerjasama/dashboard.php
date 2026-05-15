<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Memuat Chart.js untuk Grafik -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold"><?= esc($title) ?></h1>
            <p class="text-muted small mb-0">Analisis KPI (Key Performance Indicator) dan tren kemitraan strategis.</p>
        </div>
        <div class="d-flex">
            <div class="dropdown mr-2">
                <button class="btn btn-sm btn-light border dropdown-toggle shadow-sm px-3" type="button" data-toggle="dropdown">
                    <i class="fas fa-calendar-alt mr-1 text-primary"></i> Tahun <?= date('Y') ?>
                </button>
                <div class="dropdown-menu shadow border-0">
                    <a class="dropdown-item small" href="#">2025</a>
                    <a class="dropdown-item small" href="#">2024</a>
                </div>
            </div>
            <a href="<?= base_url('app/kerjasama/new') ?>" class="btn btn-sm btn-primary shadow-sm px-3 font-weight-bold">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Mitra
            </a>
        </div>
    </div>

    <!-- Row 1: Ringkasan KPI Utama (Dinamis) -->
    <div class="row">
        <!-- KPI: Total Mitra -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jejaring Mitra</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total']) ?></div>
                            <div class="text-xs mt-2 text-success font-weight-bold">
                                <i class="fas fa-database mr-1"></i> Terdata <span class="text-muted font-weight-normal">di sistem</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI: Rasio Aktif -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <?php 
                                $rasio = $stats['total'] > 0 ? ($stats['aktif'] / $stats['total']) * 100 : 0;
                            ?>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Rasio Kemitraan Aktif</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= number_format($rasio, 1) ?>%</div>
                            <div class="progress progress-sm mt-3 shadow-xs" style="height: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $rasio ?>%"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI: Dokumen Urgent (Dinamis dari Expiring) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">MOU Mendekati Expired</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= sprintf("%02d", $stats['expiring']) ?> <small class="text-muted small">File</small></div>
                            <div class="text-xs mt-2 <?= $stats['expiring'] > 0 ? 'text-danger' : 'text-muted' ?> font-weight-bold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> <?= $stats['expiring'] > 0 ? 'Perlu Review Segera' : 'Semua dokumen aman' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI: Penyerapan Lulusan (KPI Statis/Manual) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2 border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Status Dokumen Expired</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= sprintf("%02d", $stats['expired']) ?> <small class="text-muted small">Mitra</small></div>
                            <div class="text-xs mt-2 text-info font-weight-bold">
                                <i class="fas fa-history mr-1"></i> Menunggu Pembaruan
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-200"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Grafik Analisis -->
    <div class="row">
        <!-- Grafik Tren Pertumbuhan -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-area mr-2"></i> Tren Pertumbuhan Mitra (12 Bulan Terakhir)</h6>
                </div>
                <div class="card-body bg-white">
                    <div class="chart-area" style="height: 300px;">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Kategori Kerjasama -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie mr-2"></i> Ruang Lingkup Program</h6>
                </div>
                <div class="card-body bg-white">
                    <div class="chart-pie pt-2 pb-2" style="height: 250px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2"><i class="fas fa-circle text-primary"></i> PKL</span>
                        <span class="mr-2"><i class="fas fa-circle text-success"></i> Rekrutmen</span>
                        <span class="mr-2"><i class="fas fa-circle text-info"></i> Guru Tamu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Kolom Kiri: Expiring & Unit -->
        <div class="col-lg-7">
            <!-- Distribusi Unit (Dinamis) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Kemitraan Per Unit Kerja</h6>
                </div>
                <div class="card-body bg-white py-4">
                    <?php 
                    $units = [
                        'SMA' => ['primary', 'bg-primary'],
                        'SMP' => ['success', 'bg-success'],
                        'SD' => ['danger', 'bg-danger'],
                        'Global' => ['info', 'bg-info']
                    ];
                    foreach($units as $u => $style): 
                        $count = $stats['per_unit'][$u] ?? 0;
                        $pct = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-bold text-dark small">Unit <?= $u ?></span>
                            <span class="small font-weight-bold"><?= $count ?> Mitra</span>
                        </div>
                        <div class="progress shadow-xs" style="height: 8px;">
                            <div class="progress-bar <?= $style[1] ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- List MOU Expiring Soon (Dinamis dari Controller) -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-history mr-2 text-warning"></i> Monitoring Kedaluwarsa Dokumen (H-30)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small font-weight-bold">
                                <tr>
                                    <th class="px-4 py-2 border-0">NAMA MITRA</th>
                                    <th class="py-2 border-0">STATUS DOKUMEN</th>
                                    <th class="py-2 border-0 text-center">SISA HARI</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php if(!empty($expiring)): foreach($expiring as $ex): ?>
                                <?php 
                                    $diff = (strtotime($ex['tgl_akhir']) - time()) / (60 * 60 * 24);
                                    $days = ceil($diff);
                                ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-weight-bold text-dark small"><?= esc($ex['nama_mitra']) ?></div>
                                        <div class="text-xs text-muted"><?= esc($ex['kategori']) ?> - Exp: <?= date('d M Y', strtotime($ex['tgl_akhir'])) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill <?= $days <= 7 ? 'badge-danger' : 'badge-warning' ?> px-3 py-1 text-dark">
                                            <?= $days <= 7 ? 'Urgent' : 'Mendekati Expired' ?>
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold <?= $days <= 7 ? 'text-danger' : 'text-warning' ?> small"><?= $days ?> Hari</td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted small italic">Tidak ada dokumen yang akan berakhir dalam waktu dekat.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center py-2 border-top-0">
                    <a href="<?= base_url('app/kerjasama') ?>" class="small font-weight-bold text-primary">Kelola Semua Dokumen <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Aktivitas & Akses -->
        <div class="col-lg-5">
            <!-- Akses Cepat -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-6 p-1">
                            <a href="<?= base_url('app/kerjasama/new') ?>" class="btn btn-light border btn-block py-3 text-center rounded shadow-xs hover-card">
                                <i class="fas fa-plus-circle text-success fa-2x mb-2"></i>
                                <div class="small font-weight-bold text-dark">Tambah Mitra</div>
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="<?= base_url('app/kerjasama') ?>" class="btn btn-light border btn-block py-3 text-center rounded shadow-xs hover-card">
                                <i class="fas fa-list text-primary fa-2x mb-2"></i>
                                <div class="small font-weight-bold text-dark">Data Lengkap</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mitra Terbaru (Simulasi Visual) -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-star mr-2 text-primary"></i> Baru Saja Bergabung</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush bg-white">
                        <div class="list-group-item py-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-soft text-primary rounded p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark small">Yayasan Pendidikan Maju</div>
                                    <div class="text-xs text-muted">Ditambahkan baru-baru ini</div>
                                </div>
                                <span class="badge badge-primary-soft">SMA</span>
                            </div>
                        </div>
                        <div class="list-group-item py-3 border-0 border-top">
                            <div class="d-flex align-items-center">
                                <div class="bg-success-soft text-success rounded p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark small">PT. Manufaktur Unggul</div>
                                    <div class="text-xs text-muted">Industri Manufaktur</div>
                                </div>
                                <span class="badge badge-success-soft">SD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: #eaf2fd; color: #4e73df; }
    .bg-success-soft { background-color: #eafaf1; color: #1cc88a; }
    .badge-primary-soft { background-color: #eaf2fd; color: #4e73df; border: 1px solid #d1e3f9; }
    .badge-success-soft { background-color: #eafaf1; color: #1cc88a; border: 1px solid #d4f2e1; }
    
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    .hover-card:hover { background-color: #f8f9fc !important; transform: translateY(-3px); transition: all 0.3s; }
    
    .table th { font-size: 0.65rem; letter-spacing: 0.8px; text-transform: uppercase; color: #858796; }
    .progress { border-radius: 10px; background-color: #eaecf4; }
</style>

<script>
    // Konfigurasi Grafik Pertumbuhan (Line Chart)
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
            datasets: [{
                label: "Jumlah Mitra",
                data: [12, 15, 14, 18, 22, 25, 24, 28, 32, 35, 38, <?= $stats['total'] ?>], // Dinamis di bulan terakhir
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                borderColor: "rgba(78, 115, 223, 1)",
                borderWidth: 3,
                pointRadius: 3,
                pointBackgroundColor: "rgba(78, 115, 223, 1)",
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { drawBorder: false, color: '#f2f2f2' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Konfigurasi Grafik Kategori (Doughnut Chart)
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: ["PKL", "Rekrutmen", "Guru Tamu", "Lainnya"],
            datasets: [{
                data: [45, 25, 20, 10], // Bisa dibuat dinamis jika ada query group by kategori
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
</script>
<?= $this->endSection() ?>