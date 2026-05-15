<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Form Tambah Jenis Pembayaran</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Jenis Pembayaran</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('app/jenispembayaran/create') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nama_pembayaran" class="form-label">Nama Pembayaran</label>
                    <input type="text" class="form-control <?= (validation_show_error('nama_pembayaran')) ? 'is-invalid' : '' ?>" id="nama_pembayaran" name="nama_pembayaran" value="<?= old('nama_pembayaran') ?>">
                    <div class="invalid-feedback">
                        <?= validation_show_error('nama_pembayaran') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tipe" class="form-label">Tipe Pembayaran</label>
                    <select class="form-select <?= (validation_show_error('tipe')) ? 'is-invalid' : '' ?>" id="tipe" name="tipe">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="bulanan" <?= old('tipe') == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="sekali_bayar" <?= old('tipe') == 'sekali_bayar' ? 'selected' : '' ?>>Sekali Bayar</option>
                    </select>
                    <div class="invalid-feedback">
                        <?= validation_show_error('tipe') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="nominal" class="form-label">Nominal</label>
                    <input type="number" class="form-control <?= (validation_show_error('nominal')) ? 'is-invalid' : '' ?>" id="nominal" name="nominal" value="<?= old('nominal') ?>">
                    <div class="invalid-feedback">
                        <?= validation_show_error('nominal') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran Aktif</label>
                    <select class="form-select <?= (validation_show_error('id_tahun_ajaran')) ? 'is-invalid' : '' ?>" id="id_tahun_ajaran" name="id_tahun_ajaran">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_ajaran as $item) : ?>
                            <option value="<?= $item['id'] ?>" <?= old('id_tahun_ajaran') == $item['id'] ? 'selected' : '' ?>><?= $item['tahun_ajaran'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        <?= validation_show_error('id_tahun_ajaran') ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Data</button>
                <a href="<?= base_url('app/jenispembayaran') ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
