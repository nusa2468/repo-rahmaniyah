<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Servis - <?= esc($pemeliharaan['kode_aset']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        /* Standar Stiker Thermal 90x50mm */
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
            padding: 10px 12px;
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

<div class="fixed top-4 right-4 no-print flex gap-2">
    <button onclick="window.close()" class="px-5 py-2.5 bg-slate-500 hover:bg-slate-600 transition-colors text-white rounded-xl shadow-lg text-sm font-bold uppercase tracking-widest">Tutup</button>
    <button onclick="window.print()" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 transition-colors text-white rounded-xl shadow-lg text-sm font-bold flex items-center gap-2 uppercase tracking-widest"><i class="fas fa-print"></i> Cetak Label</button>
</div>

<div class="label-container bg-white">
    <div class="w-full border-b-2 border-black pb-1 mb-1 flex justify-between items-center">
        <h1 class="text-[11px] font-black uppercase leading-tight">MAINTENANCE CONTROL</h1>
        <h2 class="text-[9px] font-bold uppercase tracking-wider bg-black text-white px-2 py-0.5 rounded">UNIT <?= esc($pemeliharaan['kode_jenjang']) ?></h2>
    </div>
    
    <div class="flex-grow flex items-center justify-center w-full overflow-hidden my-1">
        <svg id="barcode"></svg>
    </div>
    
    <div class="w-full border-t border-dashed border-gray-400 pt-1 text-left flex justify-between items-end">
        <div>
            <p class="text-[10px] font-bold truncate leading-tight uppercase mb-0.5"><?= esc($pemeliharaan['nama_aset']) ?></p>
            <p class="text-[8px] text-gray-700 leading-tight">Servis: <?= date('d/m/Y', strtotime($pemeliharaan['tanggal_mulai'])) ?> | Tipe: <?= esc($pemeliharaan['jenis_pemeliharaan']) ?></p>
            <p class="text-[8px] text-gray-700 leading-tight">Teknisi: <?= esc($pemeliharaan['pelaksana'] ?? '-') ?></p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-black uppercase"><?= esc($pemeliharaan['status']) ?></p>
        </div>
    </div>
</div>

<script>
    JsBarcode("#barcode", "<?= esc($pemeliharaan['kode_aset']) ?>", {
        format: "CODE128",
        width: 1.5,
        height: 35,
        displayValue: true,
        fontSize: 11,
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