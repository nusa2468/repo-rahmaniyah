<?= $this->extend('layout/blank_layout') // Gunakan layout minimal/blank ?>

<?= $this->section('content') ?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow p-4 p-md-5 w-100 text-center" style="max-width: 500px;">
        
        <?php if ($success): ?>
            <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
            <h1 class="h3 text-success mb-2">ABSENSI BERHASIL!</h1>
            <!-- FIX: Pastikan $siswa diakses sebagai array -->
            <p class="lead">Selamat datang, **<?= esc($siswa['nama_lengkap'] ?? 'Siswa') ?>**.</p>
            <div class="alert alert-success p-2 small">
                <?= esc($message) ?><br>
                Waktu Absen: **<?= esc($waktuAbsen) ?>**
            </div>
        <?php else: ?>
            <i class="fas fa-times-circle text-danger mb-3" style="font-size: 4rem;"></i>
            <h1 class="h3 text-danger mb-2">ABSENSI GAGAL!</h1>
            <p class="lead text-dark"><?= esc($message) ?></p>
        <?php endif; ?>

        <div class="mt-4">
             <!-- Tombol untuk kembali ke form input otomatis, akan auto-refresh setelah 5 detik -->
            <a href="<?= base_url('app/akademik/absensi-otomatis') ?>" class="btn btn-primary w-100" id="kembali-otomatis">
                Kembali ke Layar Absensi (<span id="countdown">5</span>)
            </a>
        </div>
    </div>
</div>

<script>
    // Hitungan mundur dan redirect otomatis
    let countdown = 5;
    const countdownElement = document.getElementById('countdown');

    const timer = setInterval(() => {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }

        if (countdown <= 0) {
            clearInterval(timer);
            window.location.href = '<?= base_url('app/akademik/absensi-otomatis') ?>';
        }
    }, 1000);
</script>
<?= $this->endSection() ?>