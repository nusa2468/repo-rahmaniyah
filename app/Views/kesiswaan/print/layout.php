<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul ?? 'Laporan' ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        /* Style Header Modern */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px double #000; /* Garis ganda lebih formal */
            padding-bottom: 15px; 
            position: relative;
        }
        .header h1 { 
            margin: 0 0 5px 0; 
            font-size: 22px; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .header p { 
            margin: 2px 0; 
            font-size: 13px; 
        }
        .header .kontak {
            font-style: italic;
            font-size: 11px;
        }
        
        .table-container { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-container th, .table-container td { border: 1px solid #444; padding: 8px; text-align: left; }
        .table-container th { background-color: #f2f2f2; font-weight: bold; text-align: center; text-transform: uppercase; font-size: 11px; }
        
        .footer { margin-top: 40px; width: 100%; }
        .sign-table { width: 100%; border: none; }
        .sign-table td { border: none; text-align: center; vertical-align: top; }
        
        /* Hapus elemen browser saat print */
        @media print {
            @page { size: A4; margin: 1cm; }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol Cetak (Hanya muncul di layar) -->
    <?php if(($format ?? '') !== 'excel'): ?>
    <div class="no-print" style="margin-bottom: 20px; text-align: right; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; font-family: sans-serif;">
            🖨️ Cetak Laporan
        </button>
    </div>
    <?php endif; ?>

    <!-- Kop Surat Dinamis -->
    <div class="header">
        <!-- Jika ada logo, bisa diselipkan disini -->
        <!-- <img src="path/to/logo.png" style="position: absolute; left: 0; top: 0; height: 60px;"> -->
        
        <h1><?= esc($identitas['nama'] ?? 'YAYASAN PENDIDIKAN GENERASI JUARA') ?></h1>
        <p><?= esc($identitas['alamat'] ?? 'Alamat Sekolah Belum Diatur') ?></p>
        <p class="kontak"><?= esc($identitas['kontak'] ?? '') ?></p>
    </div>

    <!-- Judul Laporan -->
    <div style="text-align: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 16px; text-transform: uppercase; text-decoration: underline;"><?= $judul ?></h3>
        <p style="margin: 5px 0; font-size: 12px; font-weight: bold;">Periode: <?= $periode ?? date('Y') ?></p>
    </div>

    <!-- Konten Utama -->
    <?= $this->renderSection('content') ?>

    <!-- Tanda Tangan -->
    <div class="footer">
        <table class="sign-table">
            <tr>
                <td width="70%"></td> <!-- Spacer -->
                <td width="30%">
                    <p>Ditetapkan di: Bekasi</p>
                    <p>Pada Tanggal: <?= date('d F Y') ?></p>
                    <br>
                    <p>Koordinator Kesiswaan,</p>
                    <br><br><br><br>
                    <p style="text-decoration: underline; font-weight: bold;">( ..................................... )</p>
                    <p>NIP/NIY: ...........................</p>
                </td>
            </tr>
        </table>
    </div>

    <?php if(($format ?? '') === 'pdf'): ?>
    <script>
        // Otomatis trigger print jika format PDF
        window.onload = function() { window.print(); }
    </script>
    <?php endif; ?>
</body>
</html>