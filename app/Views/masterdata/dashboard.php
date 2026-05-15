<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- ================================================================================= -->
<!-- STYLE KHUSUS DASHBOARD (PURE CSS - ANTI GAGAL) -->
<!-- Menggunakan scoping class .db-wrapper agar tidak merusak layout lain -->
<!-- ================================================================================= -->
<style>
    /* Reset & Variabel Lokal */
    .db-wrapper {
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: #334155;
        background-color: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        min-height: 85vh;
    }
    
    /* Utility Grid & Flex */
    .db-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .db-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .db-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .db-flex-between { display: flex; justify-content: space-between; align-items: center; }
    
    /* Responsif Tablet/Mobile */
    @media (max-width: 1024px) {
        .db-grid-4, .db-grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .db-grid-4, .db-grid-3, .db-grid-2 { grid-template-columns: 1fr; }
    }

    /* 1. Header Section */
    .db-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        padding: 30px;
        border-radius: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px -5px rgba(30, 41, 59, 0.3);
        position: relative;
        overflow: hidden;
    }
    .db-header::after {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 200px; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05));
        transform: skewX(-20deg);
    }
    .db-title h1 { margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }
    .db-title p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
    .db-badge {
        background: rgba(255,255,255,0.1);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .db-badge-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981; }

    /* 2. KPI Cards */
    .db-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        overflow: hidden;
    }
    .db-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border-color: #cbd5e1; }
    .db-card-icon {
        position: absolute; top: -10px; right: -10px; font-size: 80px; opacity: 0.05; transform: rotate(15deg);
    }
    .db-card-val { font-size: 32px; font-weight: 800; color: #1e293b; margin: 10px 0 5px; line-height: 1; }
    .db-card-label { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .db-card-sub { font-size: 12px; color: #94a3b8; margin-top: 5px; }
    
    /* Warna Aksen Kartu */
    .border-l-blue { border-left: 5px solid #3b82f6; }
    .border-l-indigo { border-left: 5px solid #6366f1; }
    .border-l-amber { border-left: 5px solid #f59e0b; }
    .border-l-emerald { border-left: 5px solid #10b981; }

    /* 3. Navigation Groups */
    .db-section-title { font-size: 18px; font-weight: 700; color: #334155; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
    
    .db-nav-group {
        background: white;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    .db-nav-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        margin-bottom: 10px;
        background: #f8fafc;
        border-radius: 10px;
        text-decoration: none;
        color: #475569;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .db-nav-item:hover {
        background: #fff;
        border-color: #cbd5e1;
        color: #0f172a;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transform: translateX(5px);
    }
    .db-nav-icon { width: 30px; text-align: center; margin-right: 10px; font-size: 16px; }
    
    /* Warna Icon Navigasi */
    .icon-blue { color: #3b82f6; }
    .icon-indigo { color: #6366f1; }
    .icon-amber { color: #f59e0b; }
    .icon-emerald { color: #10b981; }

    /* 4. Chart Section */
    .db-chart-container {
        background: white;
        border-radius: 20px;
        padding: 25px;
        border: 1px solid #e2e8f0;
    }
</style>

<!-- Pastikan Script Chart JS diload -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="db-wrapper">
    
    <!-- 1. HEADER BARU -->
    <div class="db-header db-flex-between">
        <div class="db-title">
            <div class="db-badge">
                <span class="db-badge-dot"></span> SYSTEM ONLINE
            </div>
            <h1>Dashboard Master Data</h1>
            <p>Pusat kendali referensi akademik, kepegawaian, dan kesiswaan.</p>
        </div>
        <div style="text-align: right; z-index: 2;">
            <p style="font-size: 12px; opacity: 0.7; margin:0; text-transform: uppercase; letter-spacing: 1px;">Tahun Ajaran</p>
            <p style="font-size: 24px; font-weight: 800; margin:0;"><?= esc($tahun_ajaran_aktif['tahun_ajaran'] ?? date('Y')) ?></p>
        </div>
    </div>

    <!-- 2. KARTU STATISTIK (Tanpa CSS Framework, Style Manual) -->
    <div class="db-grid-4" style="margin-bottom: 40px;">
        <!-- Card Siswa -->
        <div class="db-card border-l-blue">
            <i class="fas fa-user-graduate db-card-icon"></i>
            <div class="db-card-label">Total Siswa</div>
            <div class="db-card-val"><?= number_format($stats_siswa['total_aktif'] ?? 0) ?></div>
            <div class="db-card-sub" style="color: #3b82f6;">● Status Aktif</div>
        </div>
        <!-- Card Guru -->
        <div class="db-card border-l-indigo">
            <i class="fas fa-chalkboard-teacher db-card-icon"></i>
            <div class="db-card-label">Guru Pengajar</div>
            <div class="db-card-val"><?= number_format($stats_pegawai['total_guru'] ?? 0) ?></div>
            <div class="db-card-sub" style="color: #6366f1;">● Tenaga Pendidik</div>
        </div>
        <!-- Card Staff -->
        <div class="db-card border-l-amber">
            <i class="fas fa-id-card db-card-icon"></i>
            <div class="db-card-label">Staff & Tendik</div>
            <div class="db-card-val"><?= number_format($stats_pegawai['total_staff'] ?? 0) ?></div>
            <div class="db-card-sub" style="color: #f59e0b;">● Penunjang</div>
        </div>
        <!-- Card Unit -->
        <div class="db-card border-l-emerald">
            <i class="fas fa-school db-card-icon"></i>
            <div class="db-card-label">Unit Sekolah</div>
            <div class="db-card-val"><?= number_format($total_unit ?? 0) ?></div>
            <div class="db-card-sub" style="color: #10b981;">● Jenjang Terdaftar</div>
        </div>
    </div>

    <!-- 3. NAVIGASI 4 GRUP -->
    <div style="margin-bottom: 40px;">
        <h3 class="db-section-title"><i class="fas fa-compass"></i> Navigasi Modul</h3>
        
        <div class="db-grid-4">
            
            <!-- GRUP 1: KELEMBAGAAN -->
            <div class="db-nav-group">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #3b82f6;"><i class="fas fa-building"></i> Kelembagaan</h4>
            
                <?php if (check_menu_access(['superadmin', 'global' , 'yayasan'])): ?>
    
                    <a href="<?= base_url('app/masterdata/jenjang') ?>" class="db-nav-item">
                    <i class="fas fa-layer-group db-nav-icon icon-blue"></i> Jenjang Sekolah
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('app/masterdata/identitas') ?>" class="db-nav-item">
                <i class="fas fa-id-card db-nav-icon icon-blue"></i> Identitas
                </a>
                <a href="<?= base_url('app/masterdata/organisasi') ?>" class="db-nav-item">
                    <i class="fas fa-sitemap db-nav-icon icon-blue"></i> Organisasi
                </a>
                <a href="<?= base_url('app/masterdata/jabatan') ?>" class="db-nav-item">
                    <i class="fas fa-file-signature db-nav-icon icon-blue"></i></i> Jabatan
                </a>
            </div>

            <!-- GRUP 2: SDM -->
            <div class="db-nav-group" style="background: #f8fbff; border-color: #c7d2fe;">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #6366f1;"><i class="fas fa-users-cog"></i> SDM</h4>
                <a href="<?= base_url('app/masterdata/pegawai') ?>" class="db-nav-item" style="background: white; border-left: 3px solid #6366f1;">
                    <i class="fas fa-database db-nav-icon icon-indigo"></i> <b>Database Pegawai</b>
                </a>
                <a href="<?= base_url('app/masterdata/komponen-gaji') ?>" class="db-nav-item">
                    <i class="fas fa-file-invoice-dollar db-nav-icon icon-indigo"></i> Komponen Gaji
                </a>
            </div>

            <!-- GRUP 3: AKADEMIK -->
            <div class="db-nav-group">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #f59e0b;"><i class="fas fa-book-open"></i> Akademik</h4>
                <a href="<?= base_url('app/masterdata/tahunajaran') ?>" class="db-nav-item">
                    <i class="fas fa-calendar-alt db-nav-icon icon-amber"></i> Tahun Ajaran
                </a>
                <a href="<?= base_url('app/masterdata/jurusan') ?>" class="db-nav-item">
                    <i class="fas fa-graduation-cap db-nav-icon icon-amber"></i> Jurusan
                </a>
                <a href="<?= base_url('app/masterdata/kurikulum') ?>" class="db-nav-item">
                    <i class="fas fa-scroll db-nav-icon icon-amber"></i> Kurikulum
                </a>
                <a href="<?= base_url('app/masterdata/matapelajaran') ?>" class="db-nav-item">
                    <i class="fas fa-book db-nav-icon icon-amber"></i> Mata Pelajaran
                </a>
                <a href="<?= base_url('app/masterdata/kelas') ?>" class="db-nav-item">
                    <i class="fas fa-door-open db-nav-icon icon-amber"></i> Kelas
                </a>
            </div>

            <!-- GRUP 4: KESISWAAN -->
            <div class="db-nav-group">
                <h4 style="margin: 0 0 15px 0; font-size: 16px; color: #10b981;"><i class="fas fa-user-graduate"></i> Kesiswaan</h4>
                <a href="<?= base_url('app/masterdata/siswa') ?>" class="db-nav-item">
                    <i class="fas fa-users db-nav-icon icon-emerald"></i> Data Induk Siswa
                </a>
                <a href="<?= base_url('app/ppdb') ?>" class="db-nav-item">
                    <i class="fas fa-file-signature db-nav-icon icon-emerald"></i> PPDB
                </a>
                <!-- Tambahan: Jenis Pembayaran -->
                <a href="<?= base_url('app/masterdata/jenispembayaran') ?>" class="db-nav-item">
                    <i class="fas fa-receipt db-nav-icon icon-emerald"></i> Jenis Pembayaran
                </a>
            </div>

        </div>
    </div>

    <!-- 4. GRAFIK -->
    <div class="db-grid-3">
        <div class="db-chart-container" style="grid-column: span 2;">
            <h4 style="margin: 0 0 20px 0;">Populasi Siswa</h4>
            <div style="height: 300px;">
                <canvas id="chartSiswaUnit"></canvas>
            </div>
        </div>
        <div class="db-chart-container">
            <h4 style="margin: 0 0 20px 0;">Rasio SDM</h4>
            <div style="height: 250px; position: relative;">
                <canvas id="chartSDM"></canvas>
            </div>
            <div class="db-flex-between" style="margin-top: 20px; font-size: 14px; font-weight: bold;">
                <span style="color: #6366f1;">Guru: <?= $chart_pegawai['Guru'] ?? 0 ?></span>
                <span style="color: #f59e0b;">Staff: <?= $chart_pegawai['Staff/Penunjang'] ?? 0 ?></span>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT CHART JS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';

    // Chart Siswa
    new Chart(document.getElementById('chartSiswaUnit'), {
        type: 'bar',
        data: {
            labels: [<?php foreach($chart_siswa_unit as $c) echo "'Unit ".esc($c['kode_jenjang'])."',"; ?>],
            datasets: [{
                label: 'Siswa',
                data: [<?php foreach($chart_siswa_unit as $c) echo $c['jumlah'].","; ?>],
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { borderDash: [5, 5] } }, x: { grid: { display: false } } }
        }
    });

    // Chart SDM
    new Chart(document.getElementById('chartSDM'), {
        type: 'doughnut',
        data: {
            labels: ['Guru', 'Staff'],
            datasets: [{
                data: [<?= (int)($chart_pegawai['Guru'] ?? 0) ?>, <?= (int)($chart_pegawai['Staff/Penunjang'] ?? 0) ?>],
                backgroundColor: ['#6366f1', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
<?= $this->endSection() ?>