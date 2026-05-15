<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
        }
        .header h1 { font-size: 14pt; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 12pt; margin: 0; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10pt; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-data th, .table-data td { border: 1px solid black; padding: 5px; }
        .table-data th { background-color: #eee; text-align: center; }
        .text-center { text-align: center; }
        
        .info-table td { padding: 3px; border: none; }
        
        .signature-table { width: 100%; margin-top: 30px; border: none; }
        .signature-table td { border: none; text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Hasil Belajar</h2>
        <h1><?= esc($sekolah['nama_sekolah'] ?? 'SEKOLAH') ?></h1>
        <p><?= esc($sekolah['alamat'] ?? '') ?></p>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%">Nama Siswa</td><td width="2%">:</td><td width="40%"><?= esc($siswa['nama_siswa']) ?></td>
            <td width="15%">Kelas</td><td width="2%">:</td><td><?= esc($siswa['nama_kelas']) ?></td>
        </tr>
        <tr>
            <td>NIS/NISN</td><td>:</td><td><?= esc($siswa['nis']) ?> / <?= esc($siswa['nisn']) ?></td>
            <td>Semester</td><td>:</td><td><?= esc($semester) ?></td>
        </tr>
        <tr>
            <td>Tahun Ajaran</td><td>:</td><td><?= esc($tahun_ajaran['tahun_ajaran']) ?></td>
            <td></td><td></td><td></td>
        </tr>
    </table>

    <h3>A. Nilai Akademik</h3>
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Mata Pelajaran</th>
                <th width="10%">Nilai</th>
                <th width="10%">Predikat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($nilai_list as $n): ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= esc($n['nama_mapel']) ?></td>
                <td class="text-center"><?= number_format($n['nilai_akhir'], 0) ?></td>
                <td class="text-center"><?= esc($n['nilai_huruf']) ?></td>
                <td style="font-size: 10pt;"><?= esc($n['keterangan'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>B. Ketidakhadiran</h3>
    <table class="table-data" style="width: 50%">
        <tr><td>Sakit</td><td class="text-center"><?= esc($raport['total_sakit'] ?? 0) ?> hari</td></tr>
        <tr><td>Izin</td><td class="text-center"><?= esc($raport['total_izin'] ?? 0) ?> hari</td></tr>
        <tr><td>Alpa</td><td class="text-center"><?= esc($raport['total_alpa'] ?? 0) ?> hari</td></tr>
    </table>

    <h3>C. Catatan Wali Kelas</h3>
    <div style="border: 1px solid black; padding: 10px; min-height: 50px;">
        <?= esc($raport['catatan_wali_kelas'] ?? '-') ?>
    </div>

    <table class="signature-table">
        <tr>
            <td width="33%">
                <br>Orang Tua/Wali<br><br><br><br>
                (.........................)
            </td>
            <td width="33%">
                <br>Kepala Sekolah<br><br><br><br>
                <b><?= esc($sekolah['kepala_sekolah'] ?? '(.........................)') ?></b>
            </td>
            <td width="33%">
                Jakarta, <?= date('d F Y') ?><br>Wali Kelas<br><br><br><br>
                <b><?= esc($siswa['nama_wali'] ?? '(.........................)') ?></b>
            </td>
        </tr>
    </table>
</body>
</html>