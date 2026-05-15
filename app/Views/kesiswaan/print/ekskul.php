<?= $this->extend('kesiswaan/print/layout') ?>

<?= $this->section('content') ?>

<table class="table-container">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="10%">Unit</th>
            <th width="25%">Nama Ekstrakurikuler</th>
            <th width="15%">Kategori</th>
            <th width="20%">Guru Pembina</th>
            <th width="25%">Jadwal Latihan</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($laporan)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data ditemukan.</td>
            </tr>
        <?php else: ?>
            <?php $no = 1; foreach($laporan as $row): ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td style="text-align: center;"><?= $row['kode_jenjang'] ?></td>
                <td><strong><?= $row['nama_ekskul'] ?></strong></td>
                <td style="text-align: center;"><?= $row['kategori'] ?></td>
                <td><?= $row['nama_pembina'] ?? '-' ?></td>
                <td>
                    <?= $row['hari_latihan'] ?>, 
                    <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>