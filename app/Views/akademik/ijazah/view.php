<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            background-color: #f8f9fa;
        }
        .ijazah-container {
            width: 21cm;
            height: 29.7cm;
            margin: 2cm auto;
            padding: 1.5cm;
            border: 10px double #000;
            background: #fff;
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .kop-surat {
            text-align: center;
            border-bottom: 4px double #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .kop-surat img {
            width: 80px;
            position: absolute;
            top: 1.5cm;
            left: 2cm;
        }
        /* Penyesuaian Style Setelah H1 Dihapus */
        .kop-surat h2 {
            font-size: 22pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .kop-surat p {
            font-size: 10pt;
            margin: 0;
        }
        .ijazah-title {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .ijazah-number {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 30px;
        }
        .ijazah-body p {
            line-height: 1.8;
            text-align: justify;
        }
        .ijazah-body table {
            width: 100%;
            margin-left: 20px;
        }
        .ijazah-body table td {
            padding: 2px 5px;
        }
        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11pt;
        }
        .nilai-table th, .nilai-table td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
        }
        .nilai-table th {
            background-color: #e9ecef;
        }
        .nilai-table .mapel {
            text-align: left;
        }
        .ttd-section {
            margin-top: 50px;
            width: 100%;
        }
        .ttd-section .ttd-right {
            float: right;
            width: 40%;
            text-align: center;
        }
        .pas-foto {
            width: 3cm;
            height: 4cm;
            border: 1px solid #000;
            position: absolute;
            left: 2cm;
            bottom: 3cm;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10pt;
        }
        @media print {
            body { background-color: #fff; }
            .ijazah-container { margin: 0; border: none; box-shadow: none; }
            .btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="text-center py-3">
        <button onclick="window.print()" class="btn btn-primary">Cetak Ijazah</button>
    </div>

    <div class="ijazah-container">
        <!-- START: Kop Surat menggunakan data $sekolah -->
        <div class="kop-surat">
            <img src="https://placehold.co/100x100?text=LOGO" alt="Logo Sekolah">
            <!-- BARIS "PEMERINTAH KOTA CONTOH" SUDAH DIHAPUS TOTAL -->
            <h2><?= esc($sekolah['nama_sekolah'] ?? 'NAMA SEKOLAH') ?></h2>
            <p>
                <?= esc($sekolah['alamat'] ?? 'Alamat Sekolah, Telepon, Email') ?><br>
                Website: <?= esc($sekolah['website'] ?? 'www.sekolah.sch.id') ?>
            </p>
        </div>
        <!-- END: Kop Surat -->

        <p class="ijazah-title">IJAZAH</p>
        <p class="ijazah-number">Nomor: <?= esc($siswa['nomor_ijazah']) ?></p>

        <div class="ijazah-body">
            <p>Yang bertanda tangan di bawah ini, Kepala <?= esc($sekolah['nama_sekolah'] ?? 'Nama Sekolah') ?>, menerangkan bahwa:</p>
            <table>
                <tr>
                    <td style="width: 30%;">Nama</td>
                    <td>:</td>
                    <td style="font-weight: bold;"><?= esc($siswa['nama_lengkap']) ?></td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>:</td>
                    <td><?= esc($siswa['tempat_lahir'] ?? '-') ?>, <?= date('d F Y', strtotime($siswa['tanggal_lahir'] ?? '2000-01-01')) ?></td>
                </tr>
                <tr>
                    <td>NIS / NISN</td>
                    <td>:</td>
                    <!-- FIX: Use null coalescing operator (??) to prevent "Undefined array key" error if 'nis' or 'nisn' is missing. -->
                    <td><?= esc($siswa['nis'] ?? '-') ?> / <?= esc($siswa['nisn'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Asal Sekolah</td>
                    <td>:</td>
                    <td><?= esc($siswa['asal_sekolah'] ?? '-') ?></td>
                </tr>
            </table>

            <p style="margin-top: 20px;">
                Dinyatakan <b style="font-size: 14pt;">LULUS</b> dari Satuan Pendidikan berdasarkan hasil ujian sekolah dan kriteria kelulusan
                sesuai dengan peraturan perundang-undangan yang berlaku.
            </p>
        </div>

        <p class="text-center fw-bold mt-4">DAFTAR NILAI</p>
        <table class="nilai-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th class="mapel">Mata Pelajaran</th>
                    <th>Nilai Akhir</th>
                    <th>Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    // FIX: Use null coalescing operator to ensure $leger_nilai is always an iterable array.
                    $dataLeger = $leger_nilai ?? [];
                    $no = 1; 
                    foreach($dataLeger as $nilai): 
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="mapel"><?= esc($nilai['nama_mapel']) ?></td>
                    <td><?= number_format($nilai['nilai_akhir'], 2) ?></td>
                    <td>
                        <?php 
                            if ($nilai['nilai_akhir'] >= 85) echo 'A';
                            elseif ($nilai['nilai_akhir'] >= 75) echo 'B';
                            elseif ($nilai['nilai_akhir'] >= 65) echo 'C';
                            else echo 'D';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($dataLeger)): // Check the safe variable $dataLeger ?>
                    <tr><td colspan="4">Data nilai tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pas-foto">
            Pas Foto<br>3x4
        </div>
        
        <!-- START: TTD Section menggunakan data $sekolah (Nama Kepala Sekolah dan NIP) -->
        <div class="ttd-section">
            <div class="ttd-right">
                <p>
                    Ditetapkan di: <?= esc($sekolah['kota'] ?? 'Kota Sekolah') ?><br>
                    Pada Tanggal: <?= date('d F Y', strtotime($siswa['tanggal_lulus'] ?? date('Y-m-d'))) ?>
                </p>
                <p>Kepala Sekolah,</p>
                <br><br><br>
                <p style="text-decoration: underline; font-weight: bold; margin-bottom: 0;">
                    <?php 
                        // Mencoba beberapa kemungkinan kunci (key) untuk nama Kepala Sekolah
                        $namaKepsek = $sekolah['nama_kepala_sekolah'] ?? 
                                        $sekolah['Nama Kepala Sekolah'] ?? 
                                        $sekolah['kepala_sekolah'] ?? 
                                        '(Nama Kepala Sekolah Belum Diatur)';
                        echo esc($namaKepsek);
                    ?>
                </p>
                <p>NIP. 
                    <?php 
                        // Mencoba beberapa kemungkinan kunci (key) untuk NIP Kepala Sekolah
                        $nipKepsek = $sekolah['nip_kepala_sekolah'] ?? 
                                           $sekolah['NIP'] ?? 
                                           $sekolah['nip'] ?? 
                                           '19xxxxxxxxxxxxxxxx';
                        echo esc($nipKepsek);
                    ?>
                </p>
            </div>
        </div>
        <!-- END: TTD Section -->

    </div>
</body>
</html>