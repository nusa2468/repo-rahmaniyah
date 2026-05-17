<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .a4-container { margin: 0 !important; box-shadow: none !important; padding: 0 !important; }
        }
        body { font-family: 'Times New Roman', Times, serif; background: #e2e8f0; }
        .a4-container {
            width: 297mm;
            min-height: 210mm;
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
    <button onclick="window.print()" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 transition-colors text-white rounded-xl shadow-lg text-sm font-bold uppercase tracking-widest flex items-center gap-2"><i class="fas fa-print"></i> Cetak (A4 Landscape)</button>
</div>

<div class="a4-container">
    
    <div class="text-center border-b-4 border-double border-slate-800 pb-5 mb-6">
        <h1 class="text-2xl font-black uppercase tracking-widest mb-1">YAYASAN PENDIDIKAN RAHMANY</h1>
        <h2 class="text-lg font-bold uppercase tracking-wider text-slate-700">Laporan Rekapitulasi Pemeliharaan & Servis Aset</h2>
        <p class="text-sm font-bold mt-2">Filter Unit: <?= strtoupper($filterJenjang) ?> &nbsp;&bull;&nbsp; Tanggal Cetak: <?= $tanggalCetak ?></p>
    </div>

    <?php
        $totalServis = count($pemeliharaan);
        $totalBiaya = 0;
        $statusRekap = ['Direncanakan' => 0, 'Sedang Proses' => 0, 'Selesai' => 0, 'Batal' => 0];
        
        foreach($pemeliharaan as $p) {
            $totalBiaya += (float)$p['biaya'];
            if(isset($statusRekap[$p['status']])) $statusRekap[$p['status']]++;
        }
    ?>
    <div class="flex justify-between items-center text-xs mb-4 font-bold bg-slate-50 p-4 border-2 border-slate-300 rounded-lg">
        <div>Total Log Servis:<br><span class="text-lg font-black text-indigo-700"><?= number_format($totalServis) ?> Tindakan</span></div>
        <div class="text-center">Status Pemeliharaan:<br>
            <span class="text-blue-600">Rencana (<?= $statusRekap['Direncanakan'] ?>)</span> | 
            <span class="text-amber-600">Proses (<?= $statusRekap['Sedang Proses'] ?>)</span> | 
            <span class="text-emerald-700">Selesai (<?= $statusRekap['Selesai'] ?>)</span> | 
            <span class="text-rose-600">Batal (<?= $statusRekap['Batal'] ?>)</span>
        </div>
        <div class="text-right">Total Pengeluaran Servis:<br><span class="text-lg font-black text-purple-700">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">Kode Aset</th>
                <th style="width: 50px;">Unit</th>
                <th>Nama Barang & Lokasi</th>
                <th style="width: 120px;">Jenis Servis & Teknisi</th>
                <th style="width: 140px;">Keterangan</th>
                <th style="width: 70px;">Tgl Mulai</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 90px;">Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($pemeliharaan)): ?>
                <tr>
                    <td colspan="9" class="text-center italic py-8 text-gray-500">Tidak ada data servis yang sesuai dengan kriteria filter saat ini.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach($pemeliharaan as $row): ?>
                    <tr>
                        <td class="text-center font-bold"><?= $no++ ?></td>
                        <td class="font-mono font-bold text-center tracking-wider"><?= esc($row['kode_aset']) ?></td>
                        <td class="text-center font-bold"><?= esc($row['kode_jenjang']) ?></td>
                        <td>
                            <strong><?= esc($row['nama_aset']) ?></strong><br>
                            <span style="font-size: 9px; color: #475569;"><i class="fas fa-map-marker-alt"></i> <?= esc($row['nama_lokasi'] ?? 'Gudang / Lainnya') ?></span>
                        </td>
                        <td>
                            <strong><?= esc($row['jenis_pemeliharaan']) ?></strong><br>
                            <span style="font-size: 9px; font-style: italic;">Oleh: <?= esc($row['pelaksana'] ?? '-') ?></span>
                        </td>
                        <td style="font-size: 9px;"><?= esc($row['keterangan']) ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_mulai'])) ?></td>
                        <td class="text-center font-bold"><?= esc($row['status']) ?></td>
                        <td class="angka"><?= number_format($row['biaya'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="mt-12 flex justify-end" style="page-break-inside: avoid;">
        <div class="text-center text-sm w-56">
            <p>Depok, <?= date('d F Y') ?></p>
            <p class="mb-16 font-bold mt-1">Penanggung Jawab Sarpras / Aset</p>
            <p class="underline font-bold">( .................................................... )</p>
            <p class="text-[10px] mt-1 text-slate-500">NIP / NUPTK.</p>
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