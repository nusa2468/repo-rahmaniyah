<?= $this->extend('kesiswaan/print/layout') ?>

<?= $this->section('content') ?>

<table class="table-container">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="15%">Nama Ekskul</th>
            <th width="30%">Materi Kegiatan</th>
            <th width="8%">Hadir</th>
            <th width="8%">Izin</th>
            <th width="8%">Sakit</th>
            <th width="8%">Alpha</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($laporan)): ?>
            <tr><td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data presensi pada periode ini.</td></tr>
        <?php else: ?>
            <?php 
                $no = 1; 
                foreach($laporan as $row): 
                    // Hitung Statistik dari JSON
                    $stats = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
                    $dataJson = json_decode($row['data_presensi'] ?? '[]', true);
                    if(is_array($dataJson)) {
                        foreach($dataJson as $d) {
                            $status = $d['status'] ?? 'A';
                            if(isset($stats[$status])) $stats[$status]++;
                        }
                    }
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++ ?></td>
                <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                <td><?= $row['nama_ekskul'] ?></td>
                <td><?= $row['materi_kegiatan'] ?></td>
                <td style="text-align: center; font-weight: bold;"><?= $stats['H'] ?></td>
                <td style="text-align: center;"><?= $stats['I'] ?></td>
                <td style="text-align: center;"><?= $stats['S'] ?></td>
                <td style="text-align: center; color: red;"><?= $stats['A'] ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>