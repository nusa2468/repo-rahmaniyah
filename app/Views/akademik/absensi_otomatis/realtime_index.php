<?= $this->extend('layout/blank_layout') // Gunakan layout minimal/blank ?>

<?= $this->section('content') ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow p-4 p-md-5 w-100" style="max-width: 500px;">
        <div class="text-center mb-4">
            <h1 class="h3 text-gray-800">Absensi Kehadiran Sekolah</h1>
            <p class="text-muted mb-1"><?= esc($tanggalHariIni) ?></p>
            <p class="small text-primary font-weight-bold">Tahun Ajaran: <?= esc($tahunAjaranInfo) ?></p>
        </div>

        <!-- Flashdata Message/Error -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger text-center fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('message')) : ?>
            <div class="alert alert-info text-center fade show" role="alert">
                <?= session()->getFlashdata('message') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('app/akademik/absensi-otomatis/proses') ?>" method="post" id="absensiForm">
            <?= csrf_field() ?>
            
            <div class="mb-4">
                <label for="nis_atau_kode" class="form-label text-center d-block">Scan QR atau Masukkan NIS</label>
                <!-- Fokus otomatis dan input besar untuk kemudahan scan/input -->
                <input 
                    type="text" 
                    class="form-control form-control-lg text-center" 
                    id="nis_atau_kode" 
                    name="nis_atau_kode" 
                    placeholder="NIS / Kode Absensi"
                    required 
                    autofocus
                    autocomplete="off"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-fingerprint me-2"></i> Proses Absensi
            </button>
        </form>
    </div>
</div>

<script>
    // Script untuk membersihkan input setelah submit (untuk simulasi perangkat)
    document.getElementById('absensiForm').addEventListener('submit', function() {
        // Biarkan form terkirim, lalu fokus dan kosongkan input setelah halaman dimuat ulang/redirect
        setTimeout(() => {
            const input = document.getElementById('nis_atau_kode');
            input.value = '';
            input.focus();
        }, 50); // Jeda singkat
    });
    
    // Auto-submit saat enter (simulasi scan)
    document.getElementById('nis_atau_kode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('absensiForm').submit();
        }
    });
</script>
<?= $this->endSection() ?>