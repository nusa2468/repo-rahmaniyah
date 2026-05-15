<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="mb-4">
        <a href="<?= base_url('app/cms/pengumuman') ?>" class="text-decoration-none small text-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold mt-2"><?= esc($title) ?></h1>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger border-left-danger shadow-sm mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i> <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('app/cms/pengumuman/save') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $post['id'] ?? '' ?>">

        <div class="row">
            <!-- Kolom Utama: Konten -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold small text-uppercase text-muted">Judul Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control form-control-lg border-0 bg-light font-weight-bold" 
                                   placeholder="Contoh: Pengumuman Libur Semester"
                                   value="<?= esc($post['judul'] ?? '') ?>" required>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small text-uppercase text-muted">Isi Pengumuman <span class="text-danger">*</span></label>
                            <!-- PENTING: Jangan gunakan esc() agar tag HTML terbaca oleh editor -->
                            <textarea name="konten" id="editor_pengumuman" class="form-control"><?= $post['konten'] ?? '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Samping: Pengaturan & Publikasi -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white font-weight-bold small py-3 text-uppercase border-bottom">
                        <i class="fas fa-cog mr-1 text-primary"></i> Pengaturan Publikasi
                    </div>
                    <div class="card-body">
                        <!-- SINKRONISASI: Target Unit Sekolah -->
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-uppercase">Target Unit <span class="text-danger">*</span></label>
                            <select name="jenjang" class="form-control border-0 bg-light" required>
                                <option value="Global" <?= (isset($post['jenjang']) && $post['jenjang'] == 'Global') ? 'selected' : '' ?>>Global (Yayasan)</option>
                                <option value="SD" <?= (isset($post['jenjang']) && $post['jenjang'] == 'SD') ? 'selected' : '' ?>>Unit SD</option>
                                <option value="SMP" <?= (isset($post['jenjang']) && $post['jenjang'] == 'SMP') ? 'selected' : '' ?>>Unit SMP</option>
                                <option value="SMA" <?= (isset($post['jenjang']) && $post['jenjang'] == 'SMA') ? 'selected' : '' ?>>Unit SMA</option>
                            </select>
                            <small class="text-muted italic">Tentukan pengumuman ini muncul di unit mana.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-uppercase">Tanggal Berakhir</label>
                            <input type="date" name="tanggal_berakhir" class="form-control border-0 bg-light" 
                                   value="<?= $post['tanggal_berakhir'] ?? '' ?>">
                            <small class="text-muted">Kosongkan jika pengumuman berlaku selamanya.</small>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold shadow-sm py-2">
                            <i class="fas fa-save mr-1"></i> Simpan Pengumuman
                        </button>
                    </div>
                </div>

                <div class="alert alert-info border-0 shadow-sm small">
                    <i class="fas fa-info-circle mr-1"></i> Pengumuman yang sudah melewati <strong>Tanggal Berakhir</strong> tidak akan muncul secara otomatis di Landing Page.
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<!-- SINKRONISASI: Menggunakan section 'scripts' sesuai layout utama -->
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/tinymce/tinymce.min.js') ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#editor_pengumuman',
                license_key: 'gpl',
                height: 450,
                menubar: false,
                plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount emoticons',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link emoticons table | removeformat code',
                branding: false,
                promotion: false,
                content_style: 'body { font-family: "Nunito", sans-serif; font-size: 14px; }',
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save(); // Sinkronisasi ke textarea asli
                    });
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>