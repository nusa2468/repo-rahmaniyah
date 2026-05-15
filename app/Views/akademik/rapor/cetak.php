<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor_<?= esc($siswa['nis'] ?? 'Siswa') ?>_<?= esc($semester) ?></title>
    <!-- Load Font Premium -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- PRINT CONFIGURATION --- */
        @page {
            size: A4 portrait;
            margin: 0.7cm 1.2cm; /* Dipersempit sedikit lagi untuk extra space */
        }

        <?php 
            // LOGIKA SCALING AGRESIF UNTUK TARGET 1 HALAMAN
            $countMapel = count($nilai_list ?? []);
            
            // Default Layout (4-7 Mapel)
            $baseFontSize = '9.5pt';
            $tablePadding = '4px 8px';
            $descFontSize = '8pt';
            $sectionMargin = '10px';
            $sigSpace = '45px';

            // Condensed Layout (8-11 Mapel)
            if ($countMapel >= 8 && $countMapel <= 11) {
                $baseFontSize = '9pt';
                $tablePadding = '3px 7px';
                $descFontSize = '7.8pt';
                $sectionMargin = '8px';
                $sigSpace = '40px';
            }
            
            // Ultra Condensed Layout (> 11 Mapel)
            if ($countMapel > 11) {
                $baseFontSize = '8.5pt';
                $tablePadding = '2px 5px';
                $descFontSize = '7.5pt';
                $sectionMargin = '5px';
                $sigSpace = '35px';
            }
        ?>

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: <?= $baseFontSize ?>;
            color: #0f172a;
            line-height: 1.15;
            margin: 0;
            padding: 0;
            background: white;
        }

        /* --- UTILITIES --- */
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .font-black { font-weight: 800; }
        .italic { font-style: italic; }

        /* --- KOP SURAT --- */
        .kop-surat {
            display: flex;
            align-items: center;
            padding-bottom: 6px;
            margin-bottom: <?= $sectionMargin ?>;
            border-bottom: 2px solid #000;
        }
        .logo-box { width: 60px; height: 60px; margin-right: 15px; }
        .logo-placeholder {
            width: 100%; height: 100%;
            background: #f1f5f9; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 7pt; color: #94a3b8; border: 1px dashed #cbd5e1;
        }
        .kop-info h1 { font-size: 13pt; margin: 0; line-height: 1; letter-spacing: -0.5px; }
        .kop-info p { font-size: 7.5pt; margin: 2px 0 0; color: #64748b; line-height: 1.2; }

        /* --- STUDENT INFO --- */
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
            margin-bottom: <?= $sectionMargin ?>;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 1px 0; font-size: 8.5pt; }
        .label { width: 35%; color: #64748b; font-weight: 600; }
        .value { font-weight: 700; color: #1e293b; }

        /* --- SECTION HEADERS --- */
        .section-header {
            font-size: 7.5pt;
            font-weight: 800;
            background: #0f172a;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            display: inline-block;
            text-transform: uppercase;
        }

        /* --- DATA TABLE --- */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: <?= $sectionMargin ?>;
        }
        .table-data th {
            background: #f1f5f9;
            border: 1px solid #94a3b8;
            padding: <?= $tablePadding ?>;
            font-size: 7.5pt;
            text-transform: uppercase;
        }
        .table-data td {
            border: 1px solid #94a3b8;
            padding: <?= $tablePadding ?>;
            vertical-align: middle;
        }
        .desc-text {
            font-size: <?= $descFontSize ?>;
            line-height: 1.2;
            color: #334155;
        }

        /* --- FOOTER ELEMENTS --- */
        .grid-footer {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 15px;
            margin-bottom: <?= $sectionMargin ?>;
        }
        .signature-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-box { text-align: center; font-size: 8pt; line-height: 1.2; }
        .sig-space { height: <?= $sigSpace ?>; }
        .sig-name { font-weight: 800; text-decoration: underline; display: block; }
        
        .decision-box {
            border: 1.5px solid #000;
            padding: 5px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #fff;
        }

        /* --- SCREEN CONTROLS --- */
        @media print { .no-print { display: none !important; } }
        .no-print {
            position: fixed; top: 0; left: 0; right: 0;
            background: #0f172a; padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 1000;
        }
        .btn { padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 11px; cursor: pointer; border: none; text-decoration: none; }
        .btn-blue { background: #3b82f6; color: white; }
        .btn-gray { background: #475569; color: white; margin-right: 5px; }
        .preview-body { padding-top: 50px; background: #cbd5e1; min-height: 100vh; display: flex; justify-content: center; }
        .a4-paper { background: white; width: 21cm; min-height: 29.7cm; padding: 0.7cm 1.2cm; box-shadow: 0 0 20px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

    <div class="no-print">
        <div style="color: white; font-size: 12px;"><i class="fas fa-print mr-2"></i> Report Engine Scalable v2.1 (A4)</div>
        <div>
            <button onclick="window.close()" class="btn btn-gray">Tutup</button>
            <button onclick="window.print()" class="btn btn-blue">Cetak Rapor</button>
        </div>
    </div>

    <div class="preview-body">
        <div class="a4-paper">
            <!-- KOP SURAT -->
            <div class="kop-surat">
                <div class="logo-box">
                    <div class="logo-placeholder">LOGO</div>
                </div>
                <div class="kop-info">
                    <p class="font-black text-indigo-600 uppercase" style="letter-spacing: 1.5px; font-size: 7pt;">Laporan Capaian Kompetensi Peserta Didik</p>
                    <h1><?= esc($sekolah['nama_sekolah'] ?? 'NAMA LEMBAGA PENDIDIKAN') ?></h1>
                    <p><?= esc($sekolah['alamat'] ?? 'Alamat lengkap institusi belum diatur pada menu Pengaturan.') ?></p>
                    <p>Kontak: <?= esc($sekolah['telepon'] ?? '-') ?> | Email: <?= esc($sekolah['email'] ?? '-') ?></p>
                </div>
            </div>

            <!-- IDENTITAS (FIXED: Null Safe Check for "tingkat") -->
            <div class="info-grid">
                <table class="info-table">
                    <tr><td class="label">Nama Peserta Didik</td><td>:</td><td class="value uppercase"><?= esc($siswa['nama_siswa'] ?? '-') ?></td></tr>
                    <tr><td class="label">NIS / NISN</td><td>:</td><td class="value"><?= esc($siswa['nis'] ?? '-') ?> / <?= esc($siswa['nisn'] ?? '-') ?></td></tr>
                    <tr><td class="label">Kelas / Semester</td><td>:</td><td class="value"><?= esc($siswa['nama_kelas'] ?? '-') ?> / <?= esc($semester) ?></td></tr>
                </table>
                <table class="info-table">
                    <tr><td class="label">Tahun Ajaran</td><td>:</td><td class="value"><?= esc($tahun_ajaran['tahun_ajaran'] ?? '-') ?></td></tr>
                    <tr><td class="label">Wali Kelas</td><td>:</td><td class="value"><?= esc($siswa['nama_wali'] ?? '-') ?></td></tr>
                    <tr>
                        <td class="label">Tingkat</td>
                        <td>:</td>
                        <td class="value">
                            Kelas <?= esc($siswa['tingkat'] ?? '-') ?>
                            <?php if(isset($siswa['tingkat']) && is_numeric($siswa['tingkat'])): ?>
                                <span style="font-weight: normal; color: #94a3b8; font-size: 7.5pt; margin-left: 5px;">
                                    (Fase <?= esc($siswa['tingkat'] > 6 ? ($siswa['tingkat'] > 9 ? 'E/F' : 'D') : 'A/B/C') ?>)
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- NILAI AKADEMIK -->
            <div class="section-header">A. Nilai Akademik</div>
            <table class="table-data">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="32%">Mata Pelajaran</th>
                        <th width="7%">KKM</th>
                        <th width="8%">Nilai</th>
                        <th width="8%">Huruf</th>
                        <th>Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $no = 1; 
                        $processed = [];
                        if (empty($nilai_list)): 
                    ?>
                        <tr><td colspan="6" class="text-center italic">Data nilai belum tersedia.</td></tr>
                    <?php 
                        else:
                        foreach ($nilai_list as $n): 
                            if (in_array($n['id_mata_pelajaran'], $processed)) continue;
                            $processed[] = $n['id_mata_pelajaran'];
                    ?>
                    <tr>
                        <td class="text-center font-bold" style="background:#f8fafc;"><?= $no++ ?></td>
                        <td class="font-bold uppercase" style="font-size: 8pt;"><?= esc($n['nama_mapel']) ?></td>
                        <td class="text-center" style="color: #94a3b8;">75</td>
                        <td class="text-center font-black" style="font-size: 10.5pt;"><?= number_format($n['nilai_akhir'] ?? 0, 0) ?></td>
                        <td class="text-center font-black"><?= esc($n['nilai_huruf'] ?? '-') ?></td>
                        <td class="desc-text italic"><?= esc($n['keterangan'] ?? 'Telah mencapai kompetensi dengan sangat baik.') ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <!-- FOOTER DATA GRID -->
            <div class="grid-footer">
                <div>
                    <div class="section-header">B. Ekstrakurikuler</div>
                    <table class="table-data" style="margin-bottom: 0;">
                        <thead>
                            <tr><th width="10%">No</th><th width="65%">Kegiatan</th><th>Predikat</th></tr>
                        </thead>
                        <tbody>
                            <tr><td class="text-center">1</td><td>Pramuka (Wajib)</td><td class="text-center font-bold italic">Sangat Baik</td></tr>
                            <tr><td class="text-center">2</td><td>-</td><td class="text-center">-</td></tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <div class="section-header">C. Ketidakhadiran</div>
                    <table class="table-data" style="margin-bottom: 0;">
                        <tr><td width="65%" class="font-bold">Sakit</td><td class="text-center font-black"><?= esc($raport['total_sakit'] ?? 0) ?> Hari</td></tr>
                        <tr><td class="font-bold">Izin</td><td class="text-center font-black"><?= esc($raport['total_izin'] ?? 0) ?> Hari</td></tr>
                        <tr><td class="font-bold">Tanpa Keterangan</td><td class="text-center font-black text-rose-600"><?= esc($raport['total_alpa'] ?? 0) ?> Hari</td></tr>
                    </table>
                </div>
            </div>

            <!-- CATATAN WALI KELAS -->
            <div class="section-header">D. Catatan Wali Kelas</div>
            <div style="border: 1px solid #94a3b8; padding: 6px 12px; border-radius: 8px; font-size: 8.5pt; margin-bottom: 8px;" class="italic">
                "<?= esc($raport['catatan_wali_kelas'] ?? 'Pertahankan prestasi belajar dan tingkatkan kedisiplinan dalam beribadah serta belajar.') ?>"
            </div>

            <!-- KEPUTUSAN GENAP -->
            <?php if(strtoupper($semester) === 'GENAP'): ?>
            <div class="decision-box">
                <span style="font-size: 7.5pt; font-weight: 800; text-transform: uppercase; display: block; margin-bottom: 1px;">Keputusan Akhir Tahun Pelajaran:</span>
                <span class="font-black" style="font-size: 11pt; text-decoration: underline; letter-spacing: 1px;">
                    <?= strtoupper(esc($raport['status_kenaikan'] ?? 'NAIK KELAS')) ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- TANDA TANGAN -->
            <div class="signature-row">
                <div class="sig-box">
                    <p>Mengetahui,<br>Orang Tua / Wali,</p>
                    <div class="sig-space"></div>
                    <p>( ..................................... )</p>
                </div>
                <div class="sig-box">
                    <p>Mengetahui,<br>Kepala Sekolah,</p>
                    <div class="sig-space"></div>
                    <span class="sig-name"><?= esc($sekolah['kepala_sekolah'] ?? 'NAMA KEPALA SEKOLAH') ?></span>
                    <p style="font-size: 7.5pt;">NIP. <?= esc($sekolah['nip_kepala_sekolah'] ?? '-') ?></p>
                </div>
                <div class="sig-box">
                    <p>Jakarta, <?= date('d F Y') ?><br>Wali Kelas,</p>
                    <div class="sig-space"></div>
                    <span class="sig-name"><?= esc($siswa['nama_wali'] ?? 'WALI KELAS') ?></span>
                    <p style="font-size: 7.5pt;">NIP. <?= esc($siswa['nip_wali'] ?? '-') ?></p>
                </div>
            </div>

            <!-- FOOTER TIMESTAMP -->
            <div style="margin-top: 10px; border-top: 1px solid #e2e8f0; padding-top: 4px; text-align: right; font-size: 6pt; color: #94a3b8; font-weight: bold;">
                Digital Report System • SIMS Yayasan v2.1 • Generated on <?= date('d/m/Y H:i') ?>
            </div>
        </div>
    </div>

</body>
</html>