<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran #<?= $pembayaran['id'] ?></title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; margin: 0; padding: 20px; color: #333; font-size: 11pt; }
        .container { width: 100%; border: 1px solid #000; padding: 20px; position: relative; max-width: 800px; margin: 0 auto; background: white; }
        
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
        .no-print { 
            margin-bottom: 20px; 
            text-align: center; 
            background: #f8fafc; 
            padding: 15px; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn { 
            padding: 10px 20px; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 6px; 
            font-weight: bold; 
            margin: 0 5px; 
            font-size: 14px; 
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-land { background: #2563eb; }
        .btn-land:hover { background: #1d4ed8; }
        
        .btn-port { background: #059669; }
        .btn-port:hover { background: #047857; }
        
        .btn-close { background: #64748b; }
        .btn-close:hover { background: #475569; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .container { border: none; width: 100%; max-width: 100%; padding: 0; margin: 0; }
        }
    </style>
    
    <!-- Style Dinamis untuk Orientasi Halaman -->
    <style id="page-style">
        @media print {
            @page { size: A5 landscape; margin: 1cm; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <p style="margin: 0 0 10px 0; font-weight: bold; color: #475569;">Pilih Format Cetak:</p>
        <button class="btn btn-land" onclick="printPage('landscape')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Landscape (A5)
        </button>
        <button class="btn btn-port" onclick="printPage('portrait')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Portrait (A5)
        </button>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
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
        function printPage(orientation) {
            var style = document.getElementById('page-style');
            style.innerHTML = `@media print { @page { size: A5 ${orientation}; margin: 1cm; } }`;
            setTimeout(function() {
                window.print();
            }, 200);
        }
    </script>
</body>
</html>