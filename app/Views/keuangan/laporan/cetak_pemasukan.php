<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Pemasukan</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; margin: 0; padding: 20px; color: #000; }
        
        /* Kop Surat */
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10pt; }
        
        /* Info Laporan */
        .info { margin-bottom: 15px; width: 100%; }
        .info td { vertical-align: top; padding: 2px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; font-size: 14pt; margin-bottom: 15px; text-transform: uppercase; }

        /* Tabel Data */
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 8px; }
        table.data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        /* Utility */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        /* Footer Tanda Tangan */
        .footer { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .sign-box { width: 30%; float: right; text-align: center; }
        .sign-space { height: 70px; }

        /* Tombol Cetak (Layar Saja) */
        .no-print { margin-bottom: 20px; text-align: right; background: #f1f5f9; padding: 10px; border: 1px solid #cbd5e1; }
        .btn { background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-close { background: #64748b; margin-left: 5px; }

        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
    </div>

    <!-- KOP SURAT -->
    <div class="header">
        <h1><?= esc($instansi['nama'] ?? 'YAYASAN PENDIDIKAN GENERASI JUARA') ?></h1>
        <p><?= esc($instansi['alamat'] ?? 'Alamat Sekolah Belum Diatur') ?></p>
        <p><i><?= esc($instansi['kontak'] ?? '') ?></i></p>
    </div>

    <div class="title">LAPORAN REALISASI PEMASUKAN</div>

    <table class="info">
        <tr>
            <td width="15%"><strong>Unit Kerja</strong></td>
            <td width="2%">:</td>
            <td><?= esc($jenjang_label ?? 'SEMUA UNIT') ?></td>
            <td width="15%"><strong>Dicetak Oleh</strong></td>
            <td width="2%">:</td>
            <td><?= esc($user_pencetak ?? 'Admin') ?></td>
        </tr>
        <tr>
            <td><strong>Periode</strong></td>
            <td>:</td>
            <td><?= date('d F Y', strtotime($start_date)) ?> s/d <?= date('d F Y', strtotime($end_date)) ?></td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>:</td>
            <td><?= date('d F Y H:i') ?></td>
        </tr>
    </table>

    <!-- TABEL DATA -->
    <table class="data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="8%">Unit</th>
                <th width="20%">Siswa / Sumber</th>
                <th width="25%">Keterangan Transaksi</th>
                <th width="10%">Metode</th>
                <th width="15%">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grandTotal = 0;
            if(empty($laporan)): 
            ?>
                <tr><td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data transaksi.</td></tr>
            <?php else: ?>
                <?php $no = 1; foreach($laporan as $row): 
                    $grandTotal += $row['jumlah_bayar']; 
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_bayar'])) ?></td>
                    <td class="text-center"><?= esc($row['kode_jenjang']) ?></td>
                    <td>
                        <strong><?= esc($row['nama_siswa'] ?? 'Umum') ?></strong><br>
                        <small>NIS: <?= esc($row['nis'] ?? '-') ?></small>
                    </td>
                    <td>
                        <?= esc($row['nama_pembayaran'] ?? '-') ?><br>
                        <small><i><?= esc($row['deskripsi_tagihan'] ?? '') ?></i></small>
                    </td>
                    <td class="text-center"><?= strtoupper($row['metode_pembayaran']) ?></td>
                    <td class="text-right"><?= number_format($row['jumlah_bayar'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #e2e8f0;">
                <td colspan="6" class="text-right font-bold">TOTAL PEMASUKAN</td>
                <td class="text-right font-bold">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer">
        <div class="sign-box">
            <p>Bekasi, <?= date('d F Y') ?></p>
            <p>Bendahara / Admin Keuangan,</p>
            <div class="sign-space"></div>
            <p><strong>( _______________________ )</strong></p>
        </div>
    </div>

</body>
</html>