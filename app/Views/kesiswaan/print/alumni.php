<?= $this->extend('kesiswaan/print/layout') ?>

<?= $this->section('content') ?>

<table class="table-container">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="25%">Nama Alumni</th>
            <!-- Kolom Unit dihapus -->
            <th width="10%">Tahun Lulus</th>
            <th width="15%">Status</th>
            <th width="20%">Instansi/Kampus</th>
            <th width="25%">Jurusan/Jabatan</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($laporan)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data alumni ditemukan.</td></tr>
        <?php else: ?>
            <?php $no = 1; foreach($laporan as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td>
                    <strong><?= $row['nama_lengkap'] ?></strong><br>
                    <span style="font-size: 10px;">NIS: <?= $row['nis'] ?></span>
                </td>
                <!-- Kolom Unit dihapus -->
                <td style="text-align: center;"><?= $row['tahun_lulus'] ?></td>
                <td style="text-align: center;">
                    <?= $row['status_kegiatan'] ?>
                </td>
                <td><?= $row['nama_instansi'] ?></td>
                <td><?= $row['jabatan_jurusan'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>