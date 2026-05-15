<?php 
echo $this->extend('layout/portal_layout'); 
echo $this->section('content');
?>
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4 shadow-sm">
        <h5 class="mb-4">Daftar Pengumuman Sekolah</h5>
        <p>Konten untuk menampilkan pengumuman terbaru yang ditujukan untuk orang tua akan ditampilkan di sini.</p>
        <!-- Logika untuk menampilkan pengumuman dari model Pengumuman -->
    </div>
</div>
<?php 
echo $this->endSection(); 
?>