<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran #<?= $pembayaran['id'] ?></title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; margin: 0; padding: 20px; color: #333; font-size: 11pt; }
        .container { width: 100%; border: 1px solid #000; padding: 20px; position: relative; }
        
        /* Header / Kop */
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10pt; }
        
        /* Judul Kwitansi */
        .title { text-align: center; margin-bottom: 25px; }
        .title h2 { margin: 0; text-decoration: underline; font-size: 14pt; }
        .title span { font-size: 10pt; }

        /* Isi Kwitansi */
        .content { margin-left: 20px; }
        .row { display: flex; margin-bottom: 8px; }
        .label { width: 150px; font-weight: bold; }
        .separator { width: 10px; }
        .value { flex: 1; border-bottom: 1px dotted #999; }
        .amount-box { 
            margin-top: 20px; 
            padding: 10px; 
            border: 2px solid #000; 
            display: inline-block; 
            font-weight: bold; 
            font-size: 14pt; 
            background: #eee; 
            border-radius: 8px;
        }

        /* Footer */
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .footer-left { width: 40%; font-size: 9pt; }
        .footer-right { width: 30%; text-align: center; }
        .sign-space { height: 60px; }

        /* Print Controls */
        .no-print { margin-bottom: 20px; text-align: right; }
        .btn { padding: 8px 15px; background: #333; color: white; border: none; cursor: pointer; border-radius: 4px; }
        
        @media print {
            .no-print { display: none; }
            @page { size: A5 landscape; margin: 0.5cm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">Cetak Kwitansi</button>
        <button class="btn" onclick="window.close()" style="background:#666">Tutup</button>
    </div>

    <div class="container">
        <!-- Kop Surat -->
        <div class="header">
            <h1><?= esc($identitas['nama']) ?></h1>
            <p><?= esc($identitas['alamat']) ?></p>
            <p style="font-style: italic;"><?= esc($identitas['kontak']) ?></p>
        </div>

        <!-- Judul -->
        <div class="title">
            <h2>KWITANSI PEMBAYARAN</h2>
            <span>No: KWT/<?= date('Y/m', strtotime($pembayaran['tanggal_bayar'])) ?>/<?= str_pad($pembayaran['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </div>

        <!-- Isi -->
        <div class="content">
            <div class="row">
                <div class="label">Telah Terima Dari</div>
                <div class="separator">:</div>
                <div class="value"><?= esc($pembayaran['nama_lengkap']) ?> (<?= esc($pembayaran['nis']) ?>)</div>
            </div>
            <div class="row">
                <div class="label">Uang Sejumlah</div>
                <div class="separator">:</div>
                <div class="value" style="font-style: italic; text-transform: capitalize;"># <?= esc($terbilang) ?> #</div>
            </div>
            <div class="row">
                <div class="label">Untuk Pembayaran</div>
                <div class="separator">:</div>
                <div class="value">
                    <?= esc($pembayaran['nama_pembayaran']) ?> - <?= esc($pembayaran['deskripsi_tagihan']) ?>
                </div>
            </div>
            <div class="row">
                <div class="label">Metode Bayar</div>
                <div class="separator">:</div>
                <div class="value"><?= strtoupper($pembayaran['metode_pembayaran']) ?></div>
            </div>
        </div>

        <div class="amount-box">
            Rp <?= number_format($pembayaran['jumlah_bayar'], 0, ',', '.') ?>,-
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                Catatan:<br>
                1. Disimpan sebagai bukti pembayaran yang sah.<br>
                2. Uang yang sudah dibayarkan tidak dapat diminta kembali.
            </div>
            <div class="footer-right">
                <p>Bekasi, <?= date('d F Y', strtotime($pembayaran['tanggal_bayar'])) ?></p>
                <p>Penerima,</p>
                <div class="sign-space"></div>
                <p><strong>( <?= esc($user_pencetak) ?> )</strong></p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>