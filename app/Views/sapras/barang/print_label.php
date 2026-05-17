<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Aset - <?= esc($barang['kode_aset']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        /* Konfigurasi untuk printer label stiker */
        @media print {
            @page { margin: 0; size: 90mm 50mm; } 
            body { margin: 0; padding: 0; background: white; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        body { 
            background: #f1f5f9; 
            display: flex; justify-content: center; align-items: center; 
            min-height: 100vh; font-family: monospace; 
        }
        .label-container {
            width: 90mm;
            height: 50mm;
            background: white;
            border: 1px dashed #cbd5e1;
            padding: 12px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- TOMBOL KONTROL (Disembunyikan saat dicetak) -->
<div class="fixed top-4 right-4 no-print flex gap-2">
    <button onclick="window.close()" class="px-5 py-2.5 bg-slate-500 hover:bg-slate-600 transition-colors text-white rounded-xl shadow-lg text-sm font-bold uppercase tracking-widest">Tutup</button>
    <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 transition-colors text-white rounded-xl shadow-lg text-sm font-bold flex items-center gap-2 uppercase tracking-widest"><i class="fas fa-print"></i> Cetak Stiker</button>
</div>

<!-- MEDIA STIKER LABEL -->
<div class="label-container bg-white">
    <!-- Header Kepemilikan (Dinamis dari Settings) -->
    <div class="w-full border-b-2 border-black pb-1 mb-1">
        <h1 class="text-[12px] font-bold uppercase leading-tight"><?= esc(strtoupper($nama_yayasan ?? 'YAYASAN PENDIDIKAN')) ?></h1>
        <h2 class="text-[10px] font-semibold uppercase tracking-wider mt-0.5">MILIK UNIT <?= esc($barang['kode_jenjang']) ?></h2>
    </div>
    
    <!-- Render Barcode via Javascript -->
    <div class="flex-grow flex items-center justify-center w-full overflow-hidden">
        <svg id="barcode"></svg>
    </div>
    
    <!-- Detail Singkat Aset -->
    <div class="w-full mt-1 border-t border-dashed border-gray-300 pt-1">
        <p class="text-[10px] font-bold truncate leading-tight uppercase"><?= esc($barang['nama_aset']) ?></p>
        <p class="text-[8px] text-gray-700 mt-0.5"><?= esc($barang['nama_kategori']) ?> | <?= esc($barang['nama_lokasi'] ?? 'Gudang') ?></p>
    </div>
</div>

<script>
    // Inisialisasi pembuatan Barcode (Format Code128)
    JsBarcode("#barcode", "<?= esc($barang['kode_aset']) ?>", {
        format: "CODE128",
        width: 1.5,
        height: 45,
        displayValue: true,
        fontSize: 12,
        fontOptions: "bold",
        textMargin: 2,
        margin: 0
    });
    
    window.onload = function() {
        setTimeout(function() { window.print(); }, 500);
    }
</script>

</body>
</html>