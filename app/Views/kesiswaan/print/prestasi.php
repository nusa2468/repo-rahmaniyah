<?= $this->extend('kesiswaan/print/layout') ?>

<?= $this->section('content') ?>

<table class="table-container">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="25%">Nama Siswa</th>
            <!-- Kolom Unit dihapus -->
            <th width="25%">Nama Prestasi/Lomba</th>
            <th width="15%">Tingkat</th>
            <th width="10%">Peringkat</th>
            <th width="10%">Kategori</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($laporan)): ?>
            <tr><td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data prestasi ditemukan.</td></tr>
        <?php else: ?>
            <?php $no = 1; foreach($laporan as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal_prestasi'])) ?></td>
                <td>
                    <strong><?= $row['nama_lengkap'] ?></strong><br>
                    <span style="font-size: 10px;">NIS: <?= $row['nis'] ?></span>
                </td>
                <!-- Kolom Unit dihapus -->
                <td><?= $row['nama_prestasi'] ?></td>
                <td style="text-align: center;"><?= $row['tingkat'] ?></td>
                <td style="text-align: center; font-weight: bold;"><?= $row['peringkat'] ?></td>
                <td style="text-align: center;"><?= $row['jenis_prestasi'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>