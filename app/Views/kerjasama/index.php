<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Header Page -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
                <i class="fas fa-handshake text-primary mr-2"></i><?= esc($title) ?>
            </h1>
            <p class="text-muted small mb-0">Manajemen MOU, PKS, dan Program Kemitraan Strategis Sekolah.</p>
        </div>
        <div class="d-flex mt-3 mt-md-0">
            <button class="btn btn-outline-primary rounded-pill px-4 mr-2 font-weight-bold shadow-sm btn-sm">
                <i class="fas fa-file-export mr-1"></i> Cetak Laporan
            </button>
            <a href="<?= base_url('app/kerjasama/new') ?>" class="btn btn-primary shadow-sm rounded-pill px-4 font-weight-bold btn-sm">
                <i class="fas fa-plus fa-sm mr-2"></i> Tambah Mitra Baru
            </a>
        </div>
    </div>

    <!-- Notifikasi Sukses -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success border-0 shadow-sm border-left-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Alert Notifikasi Expired Dinamis -->
    <?php 
        $urgentCount = 0;
        if(!empty($kerjasama)) {
            foreach($kerjasama as $k) {
                if($k['tgl_akhir']) {
                    $sisa = ceil((strtotime($k['tgl_akhir']) - time()) / (60 * 60 * 24));
                    if($sisa < 30) $urgentCount++;
                }
            }
        }
    ?>
    <?php if($urgentCount > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4 border-left-warning d-flex align-items-center">
        <i class="fas fa-exclamation-triangle mr-3 fa-2x text-warning"></i>
        <div>
            <div class="font-weight-bold text-dark">Sistem Pengingat MOU!</div>
            <span class="small">Terdapat <strong><?= $urgentCount ?> kerjasama</strong> yang akan berakhir dalam waktu dekat (H-30). Mohon segera lakukan peninjauan ulang dokumen.</span>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i> Database Mitra & Legalitas Kerjasama
            </h6>
            <div class="input-group input-group-sm bg-light rounded-pill border px-2 shadow-xs" style="width: 280px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" class="form-control border-0 shadow-none bg-transparent" placeholder="Cari nama atau kategori..." id="searchBox">
            </div>
        </div>
        <div class="card-body p-0 bg-white">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr class="text-dark">
                            <th class="border-0 px-4 py-3 text-center" width="60">NO</th>
                            <th class="border-0 py-3">NAMA MITRA & KONTAK</th>
                            <th class="border-0 py-3">PROGRAM UTAMA</th>
                            <th class="border-0 py-3">MASA BERLAKU MOU</th>
                            <th class="border-0 py-3 text-center">DOKUMEN</th>
                            <th class="border-0 py-3 text-center px-4">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <?php if(!empty($kerjasama)): $no=1; foreach($kerjasama as $k): ?>
                        <tr>
                            <td class="px-4 text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="bg-white rounded p-1 mr-3 border shadow-xs d-none d-md-flex align-items-center justify-content-center overflow-hidden" style="width: 48px; height: 48px;">
                                        <?php if($k['logo']): ?>
                                            <img src="<?= base_url('uploads/kerjasama/mitra/'.$k['logo']) ?>" class="img-fluid" style="max-height: 40px; object-fit: contain;">
                                        <?php else: ?>
                                            <i class="fas fa-building text-gray-200 fa-lg"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark-force mb-0" style="font-size: 0.95rem;"><?= esc($k['nama_mitra']) ?></div>
                                        <div class="small text-muted">
                                            <i class="fas fa-user-tie mr-1"></i> <?= esc($k['kontak_person'] ?: 'PIC Belum Terdata') ?> 
                                            <span class="mx-1 text-gray-300">|</span> 
                                            <i class="fas fa-phone mr-1"></i> <?= esc($k['no_telp'] ?: '-') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="mb-1">
                                    <?php 
                                        $progs = explode(',', $k['program'] ?? '');
                                        if(!empty($progs) && trim($progs[0]) != ''):
                                            foreach($progs as $p):
                                    ?>
                                        <span class="badge badge-info-soft py-1 px-2 mb-1 mr-1" style="font-size: 0.65rem;"><?= trim($p) ?></span>
                                    <?php endforeach; else: ?>
                                        <span class="badge badge-light border py-1 px-2 mb-1" style="font-size: 0.65rem;">Umum</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><i class="fas fa-tag mr-1"></i> <?= esc($k['kategori']) ?></small>
                            </td>
                            <td class="align-middle" style="min-width: 180px;">
                                <?php if($k['tgl_akhir']): ?>
                                    <?php 
                                        $tgl_akhir = strtotime($k['tgl_akhir']);
                                        $sisa = ceil(($tgl_akhir - time()) / (60 * 60 * 24));
                                        $color = $sisa < 30 ? 'danger' : ($sisa < 90 ? 'warning' : 'success');
                                    ?>
                                    <div class="font-weight-bold text-dark small mb-1">Berakhir: <?= date('d M Y', $tgl_akhir) ?></div>
                                    <div class="progress shadow-xs" style="height: 6px; border-radius: 10px;">
                                        <div class="progress-bar bg-<?= $color ?>" role="progressbar" style="width: <?= max(5, min(100, (365 - $sisa)/365 * 100)) ?>%;"></div>
                                    </div>
                                    <small class="text-xs text-<?= $color ?> font-weight-bold mt-1 d-block">
                                        <?= $sisa > 0 ? "Sisa $sisa Hari" : "Sudah Kedaluwarsa" ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted small italic">Tidak ada batas waktu</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle">
                                <?php if($k['file_mou']): ?>
                                    <a href="<?= base_url('uploads/kerjasama/dokumen_mou/'.$k['file_mou']) ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-xs">
                                        <i class="fas fa-file-pdf mr-1"></i> MOU
                                    </a>
                                <?php else: ?>
                                    <small class="text-muted italic">No File</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center align-middle px-4">
                                <div class="d-flex justify-content-center">
                                    <a href="<?= base_url('app/kerjasama/edit/'.$k['id']) ?>" class="btn-action btn-edit mr-2" title="Edit Data">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $k['id'] ?>)" class="btn-action btn-delete" title="Hapus Data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-gray-200 mb-3"></i>
                                    <p class="text-muted mb-0">Belum ada data mitra yang terdaftar.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .text-dark-force { color: #1a1a1a !important; font-weight: 700; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important; }
    
    .btn-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-edit {
        background-color: #eaf2fd;
        color: #4e73df;
    }
    .btn-edit:hover {
        background-color: #4e73df;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(78, 115, 223, 0.3);
    }

    .btn-delete {
        background-color: #fdeaea;
        color: #e74a3b;
    }
    .btn-delete:hover {
        background-color: #e74a3b;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(231, 74, 59, 0.3);
    }

    .badge-info-soft { background-color: #e1f5fe; color: #0288d1; border: 1px solid #b3e5fc; font-weight: 700; }
    
    .table thead th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 800;
        color: #5a5c69;
        border-bottom: 2px solid #e3e6f0 !important;
    }

    .text-xs { font-size: 0.7rem; }
</style>

<script>
    document.getElementById('searchBox').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (row.cells.length > 1) {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(val) ? '' : 'none';
            }
        });
    });

    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data mitra ini?')) {
            window.location.href = "<?= base_url('app/kerjasama/delete') ?>/" + id;
        }
    }
</script>
<?= $this->endSection() ?>