<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-book-open text-amber-500"></i> <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catatan mutasi transaksi terperinci untuk setiap akun keuangan.
            </p>
        </div>
    </div>

    <!-- GLOBAL NAVIGATION TABS (Dapat digunakan di seluruh View Akuntansi) -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar shadow-inner">
        <a href="<?= base_url('app/akuntansi') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-amber-600 hover:bg-white/50">
            <i class="fas fa-sitemap mr-2"></i> Bagan Akun
        </a>
        <a href="<?= base_url('app/akuntansi/jurnal') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-amber-600 hover:bg-white/50">
            <i class="fas fa-book mr-2"></i> Jurnal Umum
        </a>
        <a href="<?= base_url('app/akuntansi/buku-besar') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-amber-600 shadow-md">
            <i class="fas fa-book-open mr-2"></i> Buku Besar
        </a>
        <a href="<?= base_url('app/akuntansi/laporan/posisi-keuangan') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-amber-600 hover:bg-white/50">
            <i class="fas fa-balance-scale mr-2"></i> Posisi Keuangan
        </a>
        <a href="<?= base_url('app/akuntansi/laporan/aktivitas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-amber-600 hover:bg-white/50">
            <i class="fas fa-chart-line mr-2"></i> Laporan Aktivitas
        </a>
    </div>

    <!-- FILTER CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-6 md:p-8">
        <form action="" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <!-- Filter Unit -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Unit (Tenant)</label>
                <div class="relative">
                    <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 uppercase appearance-none cursor-pointer focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="GLOBAL" <?= $filterJenjang === 'GLOBAL' ? 'selected' : '' ?>>🏢 PUSAT (YAYASAN)</option>
                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= $filterJenjang === $kode ? 'selected' : '' ?>>🏫 UNIT <?= strtoupper($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                </div>
            </div>

            <!-- Filter Akun -->
            <div class="space-y-2 md:col-span-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Akun / Rekening</label>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="relative flex-grow">
                        <select name="id_coa" required class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 appearance-none cursor-pointer focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="" disabled selected>-- Pilih Akun --</option>
                            <?php foreach ($coaList as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($akunTerpilih['id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    [<?= esc($c['kode_akun']) ?>] <?= esc($c['nama_akun']) ?> - (<?= esc($c['nama_kategori']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                    </div>
                    <input type="date" name="start_date" value="<?= $startDate ?>" required class="w-full md:w-auto px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none focus:ring-2 focus:ring-amber-500">
                    <input type="date" name="end_date" value="<?= $endDate ?>" required class="w-full md:w-auto px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none focus:ring-2 focus:ring-amber-500">
                    <button type="submit" class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-95 border-b-4 border-orange-700">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- MAIN LEDGER DISPLAY -->
    <?php if ($akunTerpilih): ?>
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        
        <!-- Header Info Akun -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6 md:p-8 flex flex-col md:flex-row justify-between items-center gap-4 relative overflow-hidden">
            <i class="fas fa-book-open absolute -right-4 -bottom-4 text-white/5 text-8xl transform -rotate-12"></i>
            <div class="relative z-10 text-white">
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-500 mb-1">Nama Akun & Kode</p>
                <h2 class="text-2xl font-black tracking-tight leading-tight">
                    [<?= esc($akunTerpilih['kode_akun']) ?>] <?= esc($akunTerpilih['nama_akun']) ?>
                </h2>
            </div>
            <div class="relative z-10 text-right">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Saldo Normal</p>
                <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-white/10 border border-white/20 text-white">
                    <?= $saldoNormal ?>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">No. Jurnal & Keterangan</th>
                        <th class="px-6 py-4">Ref</th>
                        <th class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-500">Debit (Rp)</th>
                        <th class="px-6 py-4 text-right text-rose-500 dark:text-rose-400">Kredit (Rp)</th>
                        <th class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    <!-- SALDO AWAL -->
                    <tr class="bg-slate-100/50 dark:bg-slate-800/30">
                        <td class="px-6 py-4 text-center text-slate-500 italic" colspan="3">Saldo Awal (Sebelum <?= date('d M Y', strtotime($startDate)) ?>)</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right font-black text-slate-800 dark:text-white">
                            <?= number_format($saldoAwal, 0, ',', '.') ?>
                        </td>
                    </tr>

                    <?php 
                    $saldoBerjalan = $saldoAwal;
                    $totD = 0; $totK = 0;
                    if (empty($mutasi)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada mutasi transaksi pada periode ini.</td>
                        </tr>
                    <?php else: foreach ($mutasi as $m): 
                        // Logic Penambahan/Pengurangan Saldo
                        if ($saldoNormal == 'Debit') {
                            $saldoBerjalan += $m['debit'] - $m['kredit'];
                        } else {
                            $saldoBerjalan += $m['kredit'] - $m['debit'];
                        }
                        
                        $totD += $m['debit'];
                        $totK += $m['kredit'];
                    ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-300">
                                <?= date('d/m/Y', strtotime($m['tanggal'])) ?>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-800 dark:text-white"><?= esc($m['nomor_jurnal']) ?></div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 italic mt-0.5 truncate max-w-xs"><?= esc($m['keterangan'] ?: $m['deskripsi_jurnal']) ?></div>
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-[10px]"><?= esc($m['referensi'] ?: '-') ?></td>
                            <td class="px-6 py-3 text-right font-bold text-emerald-600 dark:text-emerald-500">
                                <?= $m['debit'] > 0 ? number_format($m['debit'], 0, ',', '.') : '-' ?>
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-rose-500 dark:text-rose-400">
                                <?= $m['kredit'] > 0 ? number_format($m['kredit'], 0, ',', '.') : '-' ?>
                            </td>
                            <td class="px-6 py-3 text-right font-black text-slate-800 dark:text-white">
                                <?= number_format($saldoBerjalan, 0, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-900 border-t-2 border-slate-200 dark:border-slate-700 font-black">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right uppercase tracking-widest text-slate-600 dark:text-slate-300 text-xs">Total Mutasi & Saldo Akhir :</td>
                        <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-500"><?= number_format($totD, 0, ',', '.') ?></td>
                        <td class="px-6 py-4 text-right text-rose-500 dark:text-rose-400"><?= number_format($totK, 0, ',', '.') ?></td>
                        <td class="px-6 py-4 text-right text-indigo-600 dark:text-indigo-400 text-lg">Rp <?= number_format($saldoBerjalan, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>