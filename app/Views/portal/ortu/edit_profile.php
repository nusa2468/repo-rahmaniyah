<?php 
echo $this->extend('layout/portal_layout'); 
echo $this->section('content');
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded p-4 shadow-sm">
                <h5 class="mb-4">Formulir Edit Profil Orang Tua/Wali</h5>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?= form_open('portal/orangtua/updateProfile') ?>

                    <h6 class="mt-4 border-bottom pb-2">Data Ayah</h6>
                    <div class="mb-3">
                        <label for="nama_ayah" class="form-label">Nama Lengkap Ayah</label>
                        <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" value="<?= old('nama_ayah', $profile['nama_ayah'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
                        <input type="text" class="form-control" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?= old('pekerjaan_ayah', $profile['pekerjaan_ayah'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telepon_ayah" class="form-label">No. Telepon Ayah</label>
                        <input type="text" class="form-control" id="telepon_ayah" name="telepon_ayah" value="<?= old('telepon_ayah', $profile['telepon_ayah'] ?? '') ?>">
                    </div>

                    <h6 class="mt-4 border-bottom pb-2">Data Ibu</h6>
                    <div class="mb-3">
                        <label for="nama_ibu" class="form-label">Nama Lengkap Ibu</label>
                        <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" value="<?= old('nama_ibu', $profile['nama_ibu'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
                        <input type="text" class="form-control" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?= old('pekerjaan_ibu', $profile['pekerjaan_ibu'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telepon_ibu" class="form-label">No. Telepon Ibu</label>
                        <input type="text" class="form-control" id="telepon_ibu" name="telepon_ibu" value="<?= old('telepon_ibu', $profile['telepon_ibu'] ?? '') ?>">
                    </div>
                    
                    <h6 class="mt-4 border-bottom pb-2">Informasi Lain</h6>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= old('alamat', $profile['alamat'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="<?= site_url('portal/orangtua') ?>" class="btn btn-secondary">Batal</a>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<?php 
echo $this->endSection(); 
?>