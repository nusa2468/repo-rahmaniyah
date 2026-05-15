<?php 
// Menggunakan layout utama portal yang baru dibuat
echo $this->extend('layout/portal_layout'); 
echo $this->section('content');
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <h3 class="mb-3">Selamat Datang di Portal Orang Tua!</h3>
        </div>
        
        <!-- Info Siswa -->
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light rounded p-4 shadow-sm">
                <h6 class="mb-4">Data Anak: <?= esc($siswa['nama_lengkap'] ?? 'N/A') ?></h6>
                <p><strong>NIS:</strong> <?= esc($siswa['nis'] ?? 'N/A') ?></p>
                <p><strong>Kelas:</strong> <?= esc($siswa['id_kelas'] ?? 'N/A') ?></p>
                
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success mt-3"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profil Orang Tua -->
        <div class="col-sm-12 col-xl-6">
            <div class="bg-light rounded p-4 shadow-sm">
                <h6 class="mb-4">Profil Orang Tua/Wali</h6>
                <?php if (isset($profile) && $profile): ?>
                    <p><strong>Nama Ayah:</strong> <?= esc($profile['nama_ayah']) ?></p>
                    <p><strong>Telepon Ayah:</strong> <?= esc($profile['telepon_ayah']) ?></p>
                    <a href="<?= site_url('portal/orangtua/editProfile') ?>" class="btn btn-primary btn-sm mt-2">Edit Profil</a>
                <?php else: ?>
                    <p class="text-danger">Data profil orang tua belum diisi.</p>
                    <a href="<?= site_url('portal/orangtua/editProfile') ?>" class="btn btn-warning btn-sm mt-2">Isi Profil Sekarang</a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Menu Navigasi Cepat -->
        <div class="col-12">
            <h6 class="mt-4 mb-3">Akses Cepat</h6>
            <div class="row g-4">
                <div class="col-md-3">
                    <a href="<?= site_url('portal/orangtua/rekapAbsensiSiswa') ?>" class="card bg-info text-white text-center p-3 shadow-sm">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p class="mb-0">Rekap Absensi</p>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('portal/tagihan') ?>" class="card bg-warning text-dark text-center p-3 shadow-sm">
                        <i class="fas fa-money-check-alt fa-2x mb-2"></i>
                        <p class="mb-0">Tagihan</p>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('portal/nilai') ?>" class="card bg-success text-white text-center p-3 shadow-sm">
                        <i class="fas fa-book-open fa-2x mb-2"></i>
                        <p class="mb-0">Rekap Nilai</p>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('portal/pengumuman') ?>" class="card bg-danger text-white text-center p-3 shadow-sm">
                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                        <p class="mb-0">Pengumuman</p>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php 
echo $this->endSection(); 
?>