<?php
    // =========================================================================
    // LOGIC DATA DINAMIS (Mengambil Identitas Sekolah dari SettingsModel)
    // =========================================================================
    use App\Models\SettingsModel;

    $settingsModel = new SettingsModel();
    
    // Tentukan scope jenjang (Jika 'Semua', ambil setting 'Global' / Yayasan)
    $targetJenjang = (isset($unit) && $unit !== 'Semua' && $unit !== 'Global') ? $unit : 'Global';
    
    // Ambil data settings
    $info = $settingsModel->getSettingsAsArray($targetJenjang);

    // Fallback data jika database settings masih kosong
    $namaSekolah = $info['nama_sekolah'] ?? 'SEKOLAH UNGGULAN HARAPAN BANGSA';
    $alamat      = $info['alamat_sekolah'] ?? 'Jl. Pendidikan Karakter No. 123';
    $kota        = $info['kota'] ?? 'Jakarta';
    $telepon     = $info['telepon'] ?? '(021) 12345678';
    
    // Data Pejabat Penandatangan (Bisa Kepala Sekolah atau Ketua Panitia)
    $jabatan     = $info['jabatan_penandatangan'] ?? 'Ketua Panitia PPDB';
    $pejabat     = $info['nama_penandatangan'] ?? 'H. Ahmad Fauzi, M.Pd';
    $nip         = $info['nip_penandatangan'] ?? '19800101 200501 1 001';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Laporan PPDB' ?></title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.3; color: #000; }
        
        /* Header Surat Resmi */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            position: relative;
        }
        .header h2 { margin: 0; font-size: 16pt; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
        .header h3 { margin: 2px 0; font-size: 14pt; font-weight: bold; }
        .header p { margin: 0; font-size: 11pt; }
        .header .sub-info { font-style: italic; font-size: 10pt; }

        table { width: 100%; border-collapse: collapse; }
        
        /* Style untuk Tabel Laporan (List) */
        table.data-table { margin-top: 10px; font-size: 10pt; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: middle; }
        table.data-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        /* Style untuk Tabel Detail (Single) */
        table.detail-table td { padding: 5px; vertical-align: top; border: none; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Kotak Detail Siswa */
        .box { border: 1px solid #000; padding: 15px; margin-bottom: 15px; }
        .box-header { font-weight: bold; border-bottom: 1px solid #ccc; margin-bottom: 10px; padding-bottom: 5px; text-transform: uppercase; background: #f9f9f9; }

        /* Footer Tanda Tangan */
        .footer { margin-top: 40px; text-align: right; page-break-inside: avoid; }
        .signature { display: inline-block; text-align: center; min-width: 250px; margin-right: 30px; }
        .signature-name { margin-top: 80px; font-weight: bold; text-decoration: underline; }

        @media print {
            .no-print { display: none; }
            @page { margin: 1.5cm; size: auto; }
            body { -webkit-print-color-adjust: exact; }
        }
        
        .btn-print {
            background-color: #0ea5e9; color: white; border: none; padding: 10px 20px; 
            border-radius: 5px; cursor: pointer; font-weight: bold; font-family: sans-serif;
            text-decoration: none; display: inline-block; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-print:hover { background-color: #0284c7; }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen</button>
    </div>

    <!-- HEADER DINAMIS -->
    <div class="header">
        <h2>PANITIA PENERIMAAN PESERTA DIDIK BARU</h2>
        <h3><?= esc(strtoupper($namaSekolah)) ?></h3>
        <p><?= esc($alamat) ?> | <?= esc($kota) ?></p>
        <p class="sub-info">Telp: <?= esc($telepon) ?> | Tahun Pelajaran <?= date('Y') ?>/<?= date('Y')+1 ?></p>
    </div>

    <?php if (isset($single) && !empty($single)): ?>
        <!-- ========================================= -->
        <!-- MODE 1: CETAK FORMULIR SATUAN (DETAIL)    -->
        <!-- ========================================= -->
        <h3 style="text-align: center; text-decoration: underline; margin-bottom: 25px;">BUKTI PENDAFTARAN SISWA BARU</h3>
        
        <table class="detail-table" style="width: 100%; margin-bottom: 20px; font-size: 11pt;">
            <tr>
                <td style="width: 200px; font-weight: bold;">No. Pendaftaran</td>
                <td style="width: 10px;">:</td>
                <td style="font-size: 14pt; font-weight: bold; letter-spacing: 1px;"><?= esc($single->no_pendaftaran) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Unit Sekolah</td>
                <td>:</td>
                <td><?= esc($single->kode_jenjang) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Jalur Masuk</td>
                <td>:</td>
                <td><?= esc($single->jalur_masuk) ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Waktu Pendaftaran</td>
                <td>:</td>
                <td><?= date('d F Y, H:i', strtotime($single->created_at)) ?> WIB</td>
            </tr>
        </table>

        <div class="box">
            <div class="box-header">A. DATA CALON SISWA</div>
            <table class="detail-table">
                <tr><td style="width: 200px;">Nama Lengkap</td><td style="width: 10px;">:</td><td><strong><?= esc($single->nama_lengkap) ?></strong></td></tr>
                <tr><td>NISN</td><td>:</td><td><?= esc($single->nisn) ?></td></tr>
                <tr><td>NIK</td><td>:</td><td><?= esc($single->nik) ?></td></tr>
                <tr><td>Jenis Kelamin</td><td>:</td><td><?= ($single->jenis_kelamin == 'L') ? 'Laki-laki' : 'Perempuan' ?></td></tr>
                <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td><?= esc($single->tempat_lahir) ?>, <?= date('d F Y', strtotime($single->tanggal_lahir)) ?></td></tr>
                <tr><td>Asal Sekolah</td><td>:</td><td><?= esc($single->asal_sekolah) ?></td></tr>
                <tr><td>No. Handphone (WA)</td><td>:</td><td><?= esc($single->no_hp_whatsapp) ?></td></tr>
            </table>
        </div>

        <div class="box">
            <div class="box-header">B. DATA ORANG TUA / WALI</div>
            <table class="detail-table">
                <tr><td style="width: 200px;">Nama Ayah</td><td style="width: 10px;">:</td><td><?= esc($single->nama_ayah) ?></td></tr>
                <tr><td>Nama Ibu</td><td>:</td><td><?= esc($single->nama_ibu) ?></td></tr>
                <tr><td>Alamat Rumah</td><td>:</td><td><?= esc($single->alamat_lengkap) ?></td></tr>
            </table>
        </div>

        <div class="box">
            <div class="box-header">C. STATUS KELULUSAN</div>
            <table class="detail-table">
                <tr>
                    <td style="width: 200px;">Status Seleksi</td>
                    <td style="width: 10px;">:</td>
                    <td>
                        <span style="border: 1px solid #000; padding: 2px 10px; font-weight: bold;">
                            <?= strtoupper($single->status_seleksi) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Skor / Nilai Akhir</td>
                    <td>:</td>
                    <td><?= $single->skor_akhir ?></td>
                </tr>
            </table>
        </div>

    <?php else: ?>
        <!-- ========================================= -->
        <!-- MODE 2: CETAK TABEL REKAP (LIST)          -->
        <!-- ========================================= -->
        <h3 style="text-align: center; margin-bottom: 5px;">LAPORAN REKAPITULASI DATA PENDAFTAR</h3>
        <p style="text-align: center; margin-bottom: 20px;">Unit: <strong><?= esc(strtoupper($targetJenjang === 'Global' ? 'SEMUA UNIT' : $targetJenjang)) ?></strong></p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>No. Daftar</th>
                    <th style="width: 5%;">Unit</th>
                    <th>Nama Siswa</th>
                    <th>Asal Sekolah</th>
                    <th>Jalur</th>
                    <th style="width: 5%;">Nilai</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pendaftar)): ?>
                    <?php $i = 1; foreach ($pendaftar as $row): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td><?= esc($row->no_pendaftaran) ?></td>
                        <td class="text-center"><?= esc($row->kode_jenjang) ?></td>
                        <td><?= esc($row->nama_lengkap) ?></td>
                        <td><?= esc($row->asal_sekolah) ?></td>
                        <td class="text-center"><?= esc($row->jalur_masuk) ?></td>
                        <td class="text-center"><?= $row->skor_akhir ?></td>
                        <td class="text-center"><?= esc($row->status_seleksi) ?></td>
                        <td class="text-center"><?= esc($row->status_pembayaran) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">Belum ada data pendaftar pada periode ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- FOOTER DINAMIS -->
    <div class="footer">
        <div class="signature">
            <p><?= esc($kota) ?>, <?= date('d F Y') ?></p>
            <p><?= esc($jabatan) ?>,</p>
            <div class="signature-name"><?= esc($pejabat) ?></div>
            <p>NIP. <?= esc($nip) ?></p>
        </div>
    </div>

</body>
</html>