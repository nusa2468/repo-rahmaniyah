<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <a href="<?= base_url('app/kerjasama') ?>" class="text-decoration-none small font-weight-bold text-primary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold mt-2"><?= esc($title) ?></h1>
        </div>
        <div class="text-right d-none d-md-block">
            <span class="badge badge-primary px-3 py-2 shadow-sm">Modul SIM-Kerjasama v2.1</span>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <form action="<?= base_url('app/kerjasama/save') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $kerjasama['id'] ?? '' ?>">

                <div class="row">
                    <!-- Kolom Kiri: Profil & Program -->
                    <div class="col-md-7">
                        <!-- Card Profil -->
                        <div class="card shadow border-0 mb-4">
                            <div class="card-header bg-primary py-3">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-building mr-2"></i> Profil & Kontak Lembaga</h6>
                            </div>
                            <div class="card-body bg-white p-4">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase">Nama Lembaga Mitra <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_mitra" class="form-control form-control-lg" 
                                           value="<?= esc($kerjasama['nama_mitra'] ?? '') ?>" required placeholder="Contoh: PT. Astra International">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase">Nama Contact Person</label>
                                            <input type="text" name="kontak_person" class="form-control" 
                                                   value="<?= esc($kerjasama['kontak_person'] ?? '') ?>" placeholder="Nama PIC Mitra">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark small text-uppercase">No. Telp / WA</label>
                                            <input type="text" name="no_telp" class="form-control" 
                                                   value="<?= esc($kerjasama['no_telp'] ?? '') ?>" placeholder="0812xxxx">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase">Alamat Lengkap Kantor</label>
                                    <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat kantor/pusat mitra..."><?= esc($kerjasama['alamat'] ?? '') ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small text-uppercase">Kategori Industri <span class="text-danger">*</span></label>
                                            <input type="text" name="kategori" class="form-control" 
                                                   value="<?= esc($kerjasama['kategori'] ?? '') ?>" required placeholder="Manufaktur / IT / Jasa">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark small text-uppercase">Situs Web</label>
                                            <input type="url" name="website" class="form-control" 
                                                   value="<?= esc($kerjasama['website'] ?? '') ?>" placeholder="https://">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Program & KPI -->
                        <div class="card shadow border-0 mb-4">
                            <div class="card-header bg-info py-3 text-white">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-tasks mr-2"></i> Ruang Lingkup & Target KPI</h6>
                            </div>
                            <div class="card-body bg-white p-4">
                                <label class="font-weight-bold text-dark mb-3 small text-uppercase">Pilih Program Yang Berjalan:</label>
                                <?php 
                                    $currentPrograms = isset($kerjasama['program']) ? explode(', ', $kerjasama['program']) : [];
                                ?>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="prog1" name="program[]" value="PKL" <?= in_array('PKL', $currentPrograms) ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-dark" for="prog1">Prakerin / PKL Siswa</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="prog2" name="program[]" value="Rekrutmen" <?= in_array('Rekrutmen', $currentPrograms) ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-dark" for="prog2">Rekrutmen Lulusan (BKK)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="prog3" name="program[]" value="GuruTamu" <?= in_array('GuruTamu', $currentPrograms) ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-dark" for="prog3">Guru Tamu / Magang Guru</label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="prog4" name="program[]" value="Beasiswa" <?= in_array('Beasiswa', $currentPrograms) ? 'checked' : '' ?>>
                                            <label class="custom-control-label font-weight-bold text-dark" for="prog4">Donasi / Beasiswa</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase">Target Capaian (KPI) <span class="text-info" title="Apa tujuan utama kerjasama ini?"><i class="fas fa-question-circle"></i></span></label>
                                    <textarea name="target_capaian" class="form-control border-left-info" rows="2" placeholder="Contoh: Penyerapan 10 lulusan per tahun atau sertifikasi untuk 20 guru..."><?= esc($kerjasama['target_capaian'] ?? '') ?></textarea>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold text-dark small text-uppercase">Deskripsi / Catatan Tambahan</label>
                                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan khusus mengenai kerjasama..."><?= esc($kerjasama['deskripsi'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: MOU & Status -->
                    <div class="col-md-5">
                        <div class="card shadow border-0 mb-4">
                            <div class="card-header bg-dark py-3">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-file-contract mr-2"></i> Legalitas MOU / PKS</h6>
                            </div>
                            <div class="card-body bg-white p-4">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase">Tanggal Mulai Kerjasama</label>
                                    <input type="date" name="tgl_mulai" class="form-control" value="<?= $kerjasama['tgl_mulai'] ?? '' ?>">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark small text-uppercase">Tanggal Berakhir MOU</label>
                                    <input type="date" name="tgl_akhir" class="form-control border-left-warning" value="<?= $kerjasama['tgl_akhir'] ?? '' ?>">
                                    <small class="text-warning font-weight-bold mt-1 d-block"><i class="fas fa-bell mr-1"></i> Notifikasi muncul 30 hari sebelum expired.</small>
                                </div>
                                <div class="form-group mb-4">
                                    <label class="font-weight-bold text-dark small text-uppercase">File Dokumen Digital (PDF)</label>
                                    <div class="custom-file">
                                        <input type="file" name="file_mou" class="custom-file-input" id="mouFile" accept=".pdf">
                                        <label class="custom-file-label" for="mouFile">Pilih file PDF...</label>
                                    </div>
                                    <?php if(isset($kerjasama['file_mou']) && $kerjasama['file_mou']): ?>
                                        <div class="mt-2 bg-light p-2 rounded border border-dashed">
                                            <span class="small font-weight-bold"><i class="fas fa-file-pdf text-danger mr-1"></i> <?= $kerjasama['file_mou'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark small text-uppercase">Unit Kerja</label>
                                            <select name="jenjang" class="form-control" required>
                                                <?php foreach(['Global','SD','SMP','SMA'] as $j): ?>
                                                    <option value="<?= $j ?>" <?= (isset($kerjasama['jenjang']) && $kerjasama['jenjang'] == $j) ? 'selected' : '' ?>><?= $j ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold text-dark small text-uppercase">Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="aktif" <?= (isset($kerjasama['status']) && $kerjasama['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                                <option value="nonaktif" <?= (isset($kerjasama['status']) && $kerjasama['status'] == 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow border-0">
                            <div class="card-header bg-light py-3 border-bottom">
                                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-image mr-2"></i> Identitas Visual (Logo)</h6>
                            </div>
                            <div class="card-body bg-white p-4">
                                <div class="text-center mb-4 p-3 bg-light rounded border border-dashed" style="min-height: 140px; display: flex; align-items: center; justify-content: center;">
                                    <?php if(isset($kerjasama['logo']) && $kerjasama['logo']): ?>
                                        <img src="<?= base_url('uploads/mitra/'.$kerjasama['logo']) ?>" class="img-fluid rounded" style="max-height: 120px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                    <?php else: ?>
                                        <div class="text-center">
                                            <i class="fas fa-building fa-4x text-gray-200"></i>
                                            <p class="text-muted small mt-2 mb-0 italic">Belum ada logo</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="logo" class="custom-file-input" id="logoFile" accept="image/*">
                                    <label class="custom-file-label" for="logoFile">Upload Logo Baru...</label>
                                </div>
                                <small class="text-muted mt-2 d-block">Format: JPG, PNG, WEBP (Max 2MB)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="card shadow border-0 mt-4 mb-5">
                    <div class="card-body bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <span class="text-muted small d-none d-md-block"><i class="fas fa-info-circle mr-1"></i> Gunakan data asli sesuai dokumen fisik kemitraan.</span>
                        <div>
                            <a href="<?= base_url('app/kerjasama') ?>" class="btn btn-light px-4 border mr-2 font-weight-bold rounded-pill">Batal</a>
                            <button type="submit" class="btn btn-primary px-5 shadow rounded-pill font-weight-bold">
                                <i class="fas fa-save mr-2"></i> Simpan Data Mitra
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styling Dasar Form */
    .form-control, select.form-control, textarea.form-control {
        color: #1a1a1a !important;
        background-color: #ffffff !important;
        border: 1px solid #d1d3e2 !important;
        font-weight: 500;
        border-radius: 8px;
        padding: 0.65rem 1rem;
    }
    .form-control:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
        background-color: #fff !important;
    }
    .custom-file-label {
        background-color: #ffffff !important;
        color: #495057 !important;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        height: auto;
    }
    .custom-file-label::after {
        height: auto;
        padding: 0.65rem 1rem;
        border-radius: 0 8px 8px 0;
    }
    
    /* Utility */
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    label { letter-spacing: 0.5px; margin-bottom: 0.4rem; }
    .bg-light { background-color: #f8f9fc !important; }
    .italic { font-style: italic; }
</style>

<script>
    // Dinamis Label Custom File Input
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                let fileName = this.files[0].name;
                let nextSibling = e.target.nextElementSibling;
                nextSibling.innerText = fileName;
            }
        });
    });
</script>
<?= $this->endSection() ?>