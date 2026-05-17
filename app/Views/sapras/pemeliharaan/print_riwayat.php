<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - <?= esc($barang['kode_aset']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .a4-container { margin: 0 !important; box-shadow: none !important; padding: 0 !important; }
        }
        body { font-family: 'Times New Roman', Times, serif; background: #e2e8f0; }
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 20mm auto;
            padding: 15mm;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #1e293b; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; text-align: center; }
        td.angka { text-align: right; font-weight: bold; }
    </style>
</head>
<body>

<div class="fixed top-4 right-4 no-print flex gap-2">
    <button onclick="window.close()" class="px-5 py-2.5 bg-slate-500 hover:bg-slate-600 transition-colors text-white rounded-xl shadow-lg text-sm font-bold uppercase tracking-widest">Tutup Laporan</button>
    <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 transition-colors text-white rounded-xl shadow-lg text-sm font-bold uppercase tracking-widest flex items-center gap-2"><i class="fas fa-print"></i> Cetak Kartu</button>
</div>

<div class="a4-container">
    
    <div class="text-center border-b-4 border-double border-slate-800 pb-4 mb-6">
        <h1 class="text-2xl font-black uppercase tracking-widest mb-1">YAYASAN PENDIDIKAN RAHMANY</h1>
        <h2 class="text-lg font-bold uppercase tracking-wider text-slate-700">Kartu Riwayat Pemeliharaan Aset</h2>
    </div>

    <!-- IDENTITAS ASET -->
    <div class="mb-6 p-4 border-2 border-slate-800 rounded-lg bg-slate-50">
        <table style="margin-top:0; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 120px; font-weight: bold;">Kode Aset</td>
                <td style="border: none; width: 10px;">:</td>
                <td style="border: none; font-family: monospace; font-weight: bold; font-size: 14px;"><?= esc($barang['kode_aset']) ?></td>
                
                <td style="border: none; width: 120px; font-weight: bold;">Lokasi Penempatan</td>
                <td style="border: none; width: 10px;">:</td>
                <td style="border: none;"><?= esc($barang['nama_lokasi'] ?? 'Gudang') ?> (Unit <?= esc($barang['kode_jenjang']) ?>)</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; font-weight: bold;">Nama Barang</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-weight: bold;"><?= esc($barang['nama_aset']) ?></td>
                
                <td style="border: none; font-weight: bold;">Tgl. Perolehan</td>
                <td style="border: none;">:</td>
                <td style="border: none;"><?= $barang['tanggal_perolehan'] ? date('d F Y', strtotime($barang['tanggal_perolehan'])) : '-' ?></td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; font-weight: bold;">Merk / Spesifikasi</td>
                <td style="border: none;">:</td>
                <td style="border: none; font-size: 10px;"><?= esc($barang['merk_spesifikasi'] ?? '-') ?></td>
                
                <td style="border: none; font-weight: bold;">Penanggung Jawab</td>
                <td style="border: none;">:</td>
                <td style="border: none;"><?= esc($barang['nama_penanggung_jawab'] ?? 'Belum Ditugaskan') ?></td>
            </tr>
        </table>
    </div>

    <!-- TABEL RIWAYAT SERVIS -->
    <h3 class="font-bold uppercase text-sm mb-2">Riwayat Tindakan Perawatan / Perbaikan</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 75px;">Tgl Mulai</th>
                <th style="width: 75px;">Tgl Selesai</th>
                <th style="width: 110px;">Jenis & Pelaksana</th>
                <th>Keterangan / Laporan Tindakan</th>
                <th style="width: 70px;">Status</th>
                <th style="width: 90px;">Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalBiaya = 0;
            if(empty($riwayat)): ?>
                <tr>
                    <td colspan="7" class="text-center italic py-8 text-gray-500">Belum ada riwayat perawatan/servis untuk aset ini.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach($riwayat as $row): 
                    $totalBiaya += (float)$row['biaya'];
                ?>
                    <tr>
                        <td class="text-center font-bold"><?= $no++ ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_mulai'])) ?></td>
                        <td class="text-center"><?= $row['tanggal_selesai'] ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-' ?></td>
                        <td>
                            <strong><?= esc($row['jenis_pemeliharaan']) ?></strong><br>
                            <span style="font-size: 9px; font-style: italic;">Oleh: <?= esc($row['pelaksana'] ?? '-') ?></span>
                        </td>
                        <td style="font-size: 10px;"><?= esc($row['keterangan']) ?></td>
                        <td class="text-center font-bold"><?= esc($row['status']) ?></td>
                        <td class="angka"><?= number_format($row['biaya'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <?php if(!empty($riwayat)): ?>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right; font-weight: bold; text-transform: uppercase;">Total Akumulasi Biaya Perawatan:</td>
                <td class="angka text-purple-700">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>

    <div class="mt-12 flex justify-between" style="page-break-inside: avoid;">
        <div class="text-center text-sm w-56">
            <p>Mengetahui,</p>
            <p class="mb-16 font-bold mt-1">Kepala Tata Usaha / Bag. Keuangan</p>
            <p class="underline font-bold">( .................................................... )</p>
        </div>
        <div class="text-center text-sm w-56">
            <p>Dicetak pada: <?= $tanggalCetak ?></p>
            <p class="mb-16 font-bold mt-1">Penanggung Jawab Sarpras</p>
            <p class="underline font-bold">( .................................................... )</p>
        </div>
    </div>

</div>

<script>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 1000);
    }
</script>

</body>
</html>