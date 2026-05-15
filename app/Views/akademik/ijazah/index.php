<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Ijazah</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Siswa Lulus</h6>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success" role="alert">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%;">No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Nomor Ijazah</th>
                            <th>Tanggal Lulus</th>
                            <th class="text-center" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($siswa_lulus)) : ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data siswa yang berstatus lulus.</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1;
                            foreach ($siswa_lulus as $siswa) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= esc($siswa['nis']) ?></td>
                                    <td><?= esc($siswa['nama_lengkap']) ?></td>
                                    <td>
                                        <form action="<?= base_url('app/ijazah/save') ?>" method="post" class="d-inline-flex">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id_siswa" value="<?= $siswa['id'] ?>">
                                            <input type="text" name="nomor_ijazah" class="form-control form-control-sm me-2" placeholder="Masukkan No. Ijazah" value="<?= esc($siswa['nomor_ijazah'] ?? '') ?>">
                                            <input type="date" name="tanggal_lulus" class="form-control form-control-sm me-2" value="<?= esc($siswa['tanggal_lulus'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-save"></i></button>
                                        </form>
                                    </td>
                                    <td><?= ($siswa['tanggal_lulus'] ?? null) ? date('d F Y', strtotime($siswa['tanggal_lulus'])) : '-' ?></td>
                                    <td class="text-center">
                                        <?php if (($siswa['nomor_ijazah'] ?? false) && ($siswa['tanggal_lulus'] ?? false)) : ?>
                                            <a href="<?= base_url('app/ijazah/view/' . $siswa['id']) ?>" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-eye me-1"></i> Lihat Ijazah
                                            </a>
                                        <?php else : ?>
                                            <button class="btn btn-secondary btn-sm" disabled>Lengkapi Data</button>
                                        <?php endif; ?>
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