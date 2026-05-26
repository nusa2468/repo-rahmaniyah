<?php
/**
 * Komponen Kop Surat Laporan (Partial View)
 * Mendeteksi secara dinamis identitas Yayasan atau Unit.
 */

// Pintar Mendeteksi Key Settings (Apakah Setting Unit atau Setting Yayasan)
$namaInstansi = $sekolah['nama_yayasan'] ?? $sekolah['nama_sekolah'] ?? 'NAMA YAYASAN / INSTANSI BELUM DIATUR';
$alamat = $sekolah['alamat_instansi'] ?? $sekolah['alamat'] ?? 'Alamat lengkap instansi belum diatur di menu Pengaturan.';

$kontak = [];
if (!empty($sekolah['telepon_instansi'])) $kontak[] = 'Telp: ' . $sekolah['telepon_instansi'];
elseif (!empty($sekolah['telepon'])) $kontak[] = 'Telp: ' . $sekolah['telepon'];

if (!empty($sekolah['email_instansi'])) $kontak[] = 'Email: ' . $sekolah['email_instansi'];
elseif (!empty($sekolah['email'])) $kontak[] = 'Email: ' . $sekolah['email'];

$stringKontak = implode(' | ', $kontak);

$logoSrc = !empty($sekolah['logo']) ? base_url('uploads/identitas/' . $sekolah['logo']) : '';
?>

<div class="print-header flex items-center justify-between border-b-4 border-double border-slate-800 dark:border-slate-400 pb-4 mb-6">
    <div class="flex items-center gap-6">
        <?php if ($logoSrc): ?>
            <img src="<?= esc($logoSrc) ?>" alt="Logo" class="h-20 w-auto object-contain print-logo">
        <?php else: ?>
            <div class="h-20 w-20 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                <i class="fas fa-landmark text-3xl text-slate-300 dark:text-slate-600"></i>
            </div>
        <?php endif; ?>
        
        <div>
            <h1 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-widest leading-tight">
                <?= esc($namaInstansi) ?>
            </h1>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-1 max-w-xl">
                <?= esc($alamat) ?>
            </p>
            <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-0.5 uppercase tracking-widest">
                <?= esc($stringKontak) ?>
            </p>
        </div>
    </div>
</div>

<style>
    @media print {
        .print-header { border-bottom: 4px double #000 !important; }
        .print-header h1 { color: #000 !important; font-size: 18pt !important; }
        .print-header p { color: #333 !important; }
        .print-logo { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>