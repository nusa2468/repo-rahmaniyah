<?= $this->extend('kesiswaan/print/layout') ?>

<?= $this->section('content') ?>

<table class="table-container">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="25%">Nama Siswa</th>
            <!-- Kolom Unit dihapus -->
            <th width="30%">Pelanggaran/Kasus</th>
            <th width="8%">Poin</th>
            <th width="20%">Tindak Lanjut</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($laporan)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data pelanggaran pada periode ini.</td></tr>
        <?php else: ?>
            <?php $no = 1; foreach($laporan as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal_kejadian'])) ?></td>
                <td>
                    <strong><?= $row['nama_lengkap'] ?></strong><br>
                    <span style="font-size: 10px;">NIS: <?= $row['nis'] ?></span>
                </td>
                <!-- Kolom Unit dihapus -->
                <td>
                    <?= $row['nama_kasus'] ?><br>
                    <span style="font-size: 10px; font-style: italic;"><?= $row['keterangan_detail'] ?></span>
                </td>
                <td style="text-align: center; color: red;">-<?= $row['poin'] ?></td>
                <td><?= $row['tindak_lanjut'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>