<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SlipGaji_<?= $riwayat['no_transaksi'] ?></title>
    <!-- Load Font Premium -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 21cm; /* Lebar A4 */
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }
        /* Header */
        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: #64748b;
            margin-right: 20px;
        }
        .school-info h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            color: #1e293b;
        }
        .school-info p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #64748b;
        }
        
        /* Title Slip */
        .slip-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .slip-title h2 {
            font-size: 16px;
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        .slip-title p {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
            font-weight: 600;
        }

        /* Employee Info */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 12px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            width: 120px;
            font-weight: 600;
            color: #64748b;
        }
        .info-value {
            flex: 1;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* Earnings Table */
        .earnings-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }
        .section-box h3 {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: #334155;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .item-name { color: #475569; }
        .item-amount { font-weight: 700; color: #0f172a; }
        .item-sub { font-size: 10px; color: #94a3b8; font-style: italic; display: block;}

        /* Totals */
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 800;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
            margin-top: 10px;
        }
        .net-salary {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            padding: 15px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .net-label { font-size: 14px; font-weight: 800; color: #475569; text-transform: uppercase; }
        .net-value { font-size: 20px; font-weight: 900; color: #10b981; } /* Emerald */

        /* Footer & Signature */
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .sig-space { height: 80px; }
        .sig-name { font-weight: 800; text-decoration: underline; text-transform: uppercase; }
        
        /* Print Controls */
        .no-print {
            position: fixed;
            top: 20px; right: 20px;
            background: white;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print { background: #0f172a; color: white; }
        .btn-print:hover { background: #334155; }
        .btn-back { background: #f1f5f9; color: #475569; }
        .btn-back:hover { background: #e2e8f0; }

        /* Print Media Query */
        @media print {
            body { background: white; padding: 0; }
            .container { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none; }
            .net-salary { border: 1px solid #000; background: none; }
            .net-value { color: #000; }
            .header { border-bottom: 3px double #000; }
            .section-box h3 { border-bottom: 1px solid #000; color: #000; }
            .total-row { border-top: 1px solid #000; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn btn-back" onclick="window.history.back()">Kembali</button>
        <button class="btn btn-print" onclick="window.print()">Cetak Slip</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-placeholder">LOGO</div>
            <div class="school-info">
                <h1><?= esc($sekolah['nama_sekolah'] ?? 'NAMA SEKOLAH / YAYASAN') ?></h1>
                <p><?= esc($sekolah['alamat'] ?? 'Alamat Sekolah Belum Diatur') ?></p>
                <p>Telp: <?= esc($sekolah['telepon'] ?? '-') ?> | Email: <?= esc($sekolah['email'] ?? '-') ?></p>
            </div>
        </div>

        <!-- Judul -->
        <div class="slip-title">
            <h2>SLIP GAJI PEGAWAI</h2>
            <p>Periode: <?= $nama_bulan ?> <?= $riwayat['tahun'] ?></p>
        </div>

        <!-- Info Pegawai -->
        <div class="info-grid">
            <div>
                <div class="info-row"><span class="info-label">NAMA</span><span class="info-value"><?= esc($riwayat['nama_pegawai']) ?></span></div>
                <div class="info-row"><span class="info-label">JABATAN</span><span class="info-value"><?= esc($riwayat['jabatan_pegawai'] ?? 'Guru / Staff') ?></span></div>
                <div class="info-row"><span class="info-label">UNIT KERJA</span><span class="info-value"><?= esc($riwayat['kode_jenjang']) ?></span></div>
            </div>
            <div>
                <div class="info-row"><span class="info-label">NO. TRANS</span><span class="info-value"><?= esc($riwayat['no_transaksi']) ?></span></div>
                <div class="info-row"><span class="info-label">TANGGAL</span><span class="info-value"><?= date('d/m/Y', strtotime($riwayat['created_at'])) ?></span></div>
                <div class="info-row"><span class="info-label">STATUS</span><span class="info-value"><?= esc($riwayat['status_bayar']) ?></span></div>
            </div>
        </div>

        <!-- Rincian -->
        <div class="earnings-section">
            <!-- Pendapatan -->
            <div class="section-box">
                <h3>Penerimaan</h3>
                <?php foreach ($detail['pendapatan'] as $d): ?>
                    <div class="item-row">
                        <div>
                            <span class="item-name"><?= esc($d['nama']) ?></span>
                            <?php if($d['keterangan']): ?><span class="item-sub"><?= esc($d['keterangan']) ?></span><?php endif; ?>
                        </div>
                        <span class="item-amount">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="total-row">
                    <span>Total Penerimaan</span>
                    <span>Rp <?= number_format($riwayat['total_pendapatan'], 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Potongan -->
            <div class="section-box">
                <h3>Potongan</h3>
                <?php foreach ($detail['potongan'] as $d): ?>
                    <div class="item-row">
                        <div>
                            <span class="item-name"><?= esc($d['nama']) ?></span>
                            <?php if($d['keterangan']): ?><span class="item-sub"><?= esc($d['keterangan']) ?></span><?php endif; ?>
                        </div>
                        <span class="item-amount text-red-600">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="total-row">
                    <span>Total Potongan</span>
                    <span>Rp <?= number_format($riwayat['total_potongan'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <!-- Gaji Bersih -->
        <div class="net-salary">
            <span class="net-label">Gaji Bersih (Take Home Pay)</span>
            <span class="net-value">Rp <?= number_format($riwayat['gaji_bersih'], 0, ',', '.') ?></span>
        </div>

        <!-- Tanda Tangan -->
        <div class="footer-grid">
            <div class="sig-box">
                <p>Penerima,</p>
                <div class="sig-space"></div>
                <span class="sig-name"><?= esc($riwayat['nama_pegawai']) ?></span>
            </div>
            <div class="sig-box">
                <p>Bendahara / Keuangan,</p>
                <div class="sig-space"></div>
                <span class="sig-name">.......................................</span>
            </div>
        </div>
        
        <div style="font-size: 10px; text-align: center; color: #94a3b8; margin-top: 30px;">
            Dicetak melalui Sistem Informasi Manajemen Sekolah pada <?= date('d/m/Y H:i') ?>
        </div>
    </div>

</body>
</html>