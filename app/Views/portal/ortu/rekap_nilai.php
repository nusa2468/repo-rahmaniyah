<?php 
echo $this->extend('layout/portal_layout'); 
echo $this->section('content');
?>
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4 shadow-sm">
        <h5 class="mb-4">Rekap Nilai Siswa</h5>
        <p>Konten untuk menampilkan transkrip atau nilai rapor siswa per semester akan ditampilkan di sini.</p>
        <!-- Logika untuk menampilkan data nilai dari model Nilai/Rapor -->
    </div>
</div>
<?php 
echo $this->endSection(); 
?>