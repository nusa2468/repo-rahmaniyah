<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title ?></h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Jadwal Pelajaran</h6>
        </div>
        <div class="card-body">
            <?php if (session()->getFlashdata('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('app/jadwalpelajaran/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_tahun_ajaran" class="form-label">Tahun Ajaran Aktif</label>
                            <select class="form-select" id="id_tahun_ajaran" name="id_tahun_ajaran">
                                <?php foreach ($tahun_ajaran as $ta) : ?>
                                    <option value="<?= $ta['id'] ?>"><?= esc($ta['tahun_ajaran']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="id_kelas" name="id_kelas">
                                <?php foreach ($kelas as $k) : ?>
                                    <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select class="form-select" id="id_mata_pelajaran" name="id_mata_pelajaran">
                                <?php foreach ($mapel as $m) : ?>
                                    <option value="<?= $m['id'] ?>"><?= esc($m['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_guru" class="form-label">Guru Pengajar</label>
                            <select class="form-select" id="id_guru" name="id_guru">
                                <?php foreach ($guru as $g) : ?>
                                    <option value="<?= $g['id'] ?>"><?= esc($g['nama_lengkap']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari</label>
                            <select class="form-select" id="hari" name="hari">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai">
                        </div>
                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary shadow-sm">Simpan Jadwal</button>
                    <a href="<?= base_url('app/jadwalpelajaran') ?>" class="btn btn-secondary shadow-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
