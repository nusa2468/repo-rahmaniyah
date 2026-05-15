<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Slip Gaji') ?></title>
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- BANNER HEADER -->
                <div class="relative bg-emerald-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-emerald-600/20">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm border border-white/10">
                                KEUANGAN • <?= esc($tahun_ajaran_label) ?>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-1">Slip Gaji & Honor 💰</h1>
                            <p class="text-emerald-100 text-sm">Riwayat penerimaan gaji bulanan dan tunjangan.</p>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Gaji -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-bold text-slate-800">Riwayat Penerimaan</h3>
                        <button class="text-xs font-bold text-emerald-600 hover:underline">Download Rekap</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="px-6 py-4">Periode</th>
                                    <th class="px-6 py-4">Tanggal Terima</th>
                                    <th class="px-6 py-4 text-right">Gaji Pokok</th>
                                    <th class="px-6 py-4 text-right">Tunjangan</th>
                                    <th class="px-6 py-4 text-right text-rose-500">Potongan</th>
                                    <th class="px-6 py-4 text-right">Total Terima</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if(empty($riwayat_gaji)): ?>
                                    <tr><td colspan="8" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data gaji.</td></tr>
                                <?php else: foreach($riwayat_gaji as $g): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-700"><?= esc($g['bulan']) ?></td>
                                        <td class="px-6 py-4 text-slate-500"><?= date('d/m/Y', strtotime($g['tanggal'])) ?></td>
                                        <td class="px-6 py-4 text-right font-mono text-slate-600"><?= number_format($g['gaji_pokok'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-4 text-right font-mono text-emerald-600">+<?= number_format($g['tunjangan'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-4 text-right font-mono text-rose-500">-<?= number_format($g['potongan'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-4 text-right font-black text-slate-800">Rp <?= number_format($g['total'], 0, ',', '.') ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded uppercase">Lunas</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-emerald-600 hover:text-white transition-all"><i class="fas fa-print"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>
</body>
</html>