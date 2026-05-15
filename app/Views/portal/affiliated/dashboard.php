<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>

<!-- Load FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<?php
// --- MENANGKAP DATA DARI CONTROLLER ---
// Data logika (Fee, Bonus, Waves) diambil dari variabel $gamification yang dikirim controller.
// Tidak ada lagi hardcode angka/tanggal di sini.

$g = $gamification ?? [];

$modeSkema       = $g['mode_skema'] ?? 'moderat';
$waves           = $g['waves'] ?? [];
$activeWave      = $g['active_wave'] ?? 0;
$currentFee      = $g['current_fee'] ?? 0;
$feeDasar        = $g['fee_dasar'] ?? 0;
$bonusDidapat    = $g['bonus_didapat'] ?? 0;
$targetStep      = $g['target_step'] ?? 3;
$nextTarget      = $g['next_target'] ?? 3;
$sisaTarget      = $g['sisa_target'] ?? 3;
$bonusAmount     = $g['bonus_amount'] ?? 0;

$totalValid      = $stats['total_valid'] ?? 0;
$totalPendapatan = $feeDasar + $bonusDidapat;

// --- SETUP TEMPLATE WA & LINK REFERRAL ---
$refLink     = base_url('portal/ppdb/register?ref=' . $mitra['mitra_kode']);
$namaSekolah = $foundation['nama_sekolah'] ?? 'Sekolah Unggulan'; 

$waTemplates = [
    [
        'label' => 'Formal & Sopan',
        'desc'  => 'Cocok untuk grup orang tua atau kerabat senior.',
        'text'  => "Assalamualaikum Wr. Wb.\n\nBapak/Ibu yang sedang mencari sekolah terbaik untuk putra-putrinya, Penerimaan Peserta Didik Baru (PPDB) *$namaSekolah* telah dibuka.\n\n✅ Fasilitas Lengkap\n✅ Kurikulum Unggulan\n✅ Lingkungan Nyaman\n\nSegera daftarkan melalui link resmi berikut untuk mendapatkan prioritas seleksi:\n$refLink\n\nTerima kasih."
    ],
    [
        'label' => 'Santai & Akrab',
        'desc'  => 'Cocok untuk teman dekat atau media sosial.',
        'text'  => "Hai bestie! 👋 Masih bingung cari sekolah? Gabung aja ke *$namaSekolah*! 🏫✨\n\nDi sini belajarnya seru, gurunya asik, dan ekskulnya banyak banget lho. Yuk kepoin dan daftar sekarang biar nggak kehabisan kuota!\n\nKlik link ini ya: $refLink\n\nDitunggu ya! 😉"
    ],
    [
        'label' => 'Promo / Urgency',
        'desc'  => 'Gunakan saat mendekati akhir gelombang.',
        'text'  => "🔥 *INFO PENTING! KUOTA TERBATAS!* 🔥\n\nPendaftaran *$namaSekolah* hampir ditutup! Dapatkan kesempatan bergabung di sekolah favorit sekarang juga.\n\nJangan sampai terlewat, klik link di bawah ini untuk amankan kursimu:\n👉 $refLink\n\nSiapa cepat dia dapat! 🚀"
    ]
];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- WELCOME HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 border border-gray-200 dark:border-gray-600">
                    MITRA RESMI
                </span>
                <?php if($modeSkema === 'progresif'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600 animate-pulse">
                        🔥 HOT PROMO
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                Halo, <?= esc($mitra['mitra_nama']) ?> 👋
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">
                Kejar targetmu sebelum pendaftaran ditutup!
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div class="hidden sm:block bg-gray-50 dark:bg-gray-900/50 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700">
                <span class="text-[10px] text-gray-400 uppercase font-bold block">Kode Anda</span>
                <span class="text-xl font-mono font-black text-gray-800 dark:text-gray-200 tracking-wider"><?= esc($mitra['mitra_kode']) ?></span>
            </div>

            <a href="<?= base_url('portal/affiliated') ?>" class="flex items-center gap-2 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-md hover:shadow-lg transition-all active:scale-95 group" title="Kembali ke Halaman Utama">
                <i class="fas fa-home text-blue-200 group-hover:text-white"></i>
                <span class="text-sm">Portal Depan</span>
            </a>

            <a href="<?= base_url('portal/affiliated/logout') ?>" class="flex items-center gap-2 px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold shadow-md hover:shadow-lg transition-all active:scale-95 group" title="Keluar dari Akun">
                <i class="fas fa-sign-out-alt text-red-200 group-hover:text-white"></i>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </div>

    <!-- MAIN MARKETING CARD (Link + Brosur) -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-gray-500/20 mb-8 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16"></div>
        
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-link text-blue-400"></i> Link Referral Anda
                    </h3>
                    <p class="text-gray-300 text-sm">
                        Bagikan link ini. Siswa yang mendaftar otomatis terdata sebagai referensi Anda.
                    </p>
                </div>
                <!-- TOMBOL DOWNLOAD BROSUR -->
                <a href="<?= base_url('uploads/brosur_ppdb_terbaru.pdf') ?>" target="_blank" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-bold transition-all flex items-center gap-2 group">
                    <i class="fas fa-file-pdf text-red-400 group-hover:text-red-300"></i>
                    Download Brosur Digital
                </a>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 bg-black/30 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/10 flex items-center">
                    <code class="text-sm font-mono text-blue-300 truncate w-full" id="refLink">
                        <?= $refLink ?>
                    </code>
                </div>
                <button onclick="copyToClipboard('<?= $refLink ?>')" class="px-6 py-3 bg-white text-gray-900 font-bold rounded-xl hover:bg-gray-100 transition-colors shadow-lg active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-copy"></i> Salin Link
                </button>
            </div>
        </div>
    </div>

    <?php if ($modeSkema === 'progresif'): ?>
        <!-- GAMIFICATION PROGRESS -->
        <div class="bg-gradient-to-br from-indigo-600 to-blue-600 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-blue-500/20 mb-8 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h3 class="text-lg font-bold text-white mb-2"><i class="fas fa-trophy text-yellow-400 mr-2"></i>Kejar Bonus Target!</h3>
                    <p class="text-blue-100 text-sm mb-4">
                        Dapatkan <strong>Rp <?= number_format($bonusAmount,0,',','.') ?></strong> setiap <?= $targetStep ?> siswa valid. Kurang <strong><?= $sisaTarget ?> siswa</strong> lagi!
                    </p>
                    <div class="relative pt-1">
                        <div class="overflow-hidden h-3 mb-2 text-xs flex rounded-full bg-black/20 border border-white/10">
                            <?php 
                                $percent = ($totalValid % $targetStep) == 0 && $totalValid > 0 ? 0 : (($totalValid % $targetStep) / $targetStep) * 100;
                            ?>
                            <div style="width:<?= $percent ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-yellow-400 transition-all duration-1000"></div>
                        </div>
                    </div>
                </div>
                <div class="bg-white/10 rounded-2xl p-4 border border-white/10">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-blue-200 uppercase">Total Pendapatan</span>
                        <span class="text-xs bg-green-500 text-white px-2 py-0.5 rounded font-bold">Cairkan</span>
                    </div>
                    <h2 class="text-3xl font-black text-white">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></h2>
                    <p class="text-xs text-blue-200 mt-1">Termasuk Bonus: Rp <?= number_format($bonusDidapat, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- STATS SUMMARY -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Referensi</p>
            <h3 class="text-3xl font-black text-gray-900 dark:text-white"><?= number_format($stats['total_daftar'] ?? 0) ?></h3>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Siswa Valid</p>
            <h3 class="text-3xl font-black text-gray-900 dark:text-white"><?= number_format($stats['total_valid']) ?></h3>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Estimasi Fee Dasar</p>
            <h3 class="text-2xl font-black text-gray-900 dark:text-white">Rp <?= number_format($feeDasar, 0, ',', '.') ?></h3>
            <p class="text-[10px] text-gray-400 mt-1">Rate Saat Ini: Rp <?= number_format($currentFee, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- CONTENT GRID: TEMPLATES & HISTORY -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- KOLOM KIRI: MARKETING KIT / TEMPLATE WA -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fab fa-whatsapp text-green-500 text-lg"></i> Template Pesan Promosi
                    </h3>
                </div>
                
                <div class="p-5 space-y-6">
                    <?php foreach ($waTemplates as $idx => $tmpl): ?>
                        <div class="relative pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                            <div class="mb-2">
                                <h4 class="text-xs font-bold text-gray-800 dark:text-white uppercase"><?= $tmpl['label'] ?></h4>
                                <p class="text-[10px] text-gray-400"><?= $tmpl['desc'] ?></p>
                            </div>
                            
                            <!-- Preview Text -->
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 text-xs text-gray-600 dark:text-gray-300 font-mono mb-3 leading-relaxed max-h-24 overflow-y-auto border border-gray-100 dark:border-gray-800">
                                <?= nl2br($tmpl['text']) ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button onclick="copyToClipboard(`<?= $tmpl['text'] ?>`)" class="flex-1 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-xs font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-1 active:scale-95">
                                    <i class="fas fa-copy"></i> Salin
                                </button>
                                <a href="https://wa.me/?text=<?= urlencode($tmpl['text']) ?>" target="_blank" class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1 active:scale-95 shadow-sm">
                                    <i class="fab fa-whatsapp"></i> Kirim WA
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: TABEL RIWAYAT -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden h-full">
                <div class="p-6 border-b border-gray-100 dark:border-white/5 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Pendaftaran Terakhir</h3>
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">5 Data Teratas</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-[10px] uppercase font-bold text-gray-500">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Nama Siswa</th>
                                <th class="px-6 py-4">Unit</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php if (empty($referrals)) : ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <i class="fas fa-inbox text-2xl opacity-20"></i>
                                            <p>Belum ada data referensi siswa.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach (array_slice($referrals, 0, 5) as $siswa) : ?>
                                    <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-6 py-4 text-gray-500"><?= date('d/m/y', strtotime($siswa['created_at'])) ?></td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                            <?= esc($siswa['nama_lengkap']) ?>
                                            <span class="block text-xs font-normal text-gray-400 mt-0.5"><?= esc($siswa['no_pendaftaran']) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                                <?= esc($siswa['kode_jenjang']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 rounded text-xs font-bold <?= $siswa['status_pembayaran'] == 'Lunas' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                                <?= esc($siswa['status_pembayaran']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Fungsi Copy Universal
    function copyToClipboard(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            alert('Teks berhasil disalin!'); 
        }).catch(err => {
            console.error('Gagal menyalin: ', err);
        });
    }
</script>

<?= $this->endSection() ?>