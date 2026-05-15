<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Keuangan Siswa') ?></title>
    
    <!-- Include Assets Partial -->
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar -->
    <?= view('portal/siswa/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Include Sidebar -->
        <?= view('portal/siswa/partials/sidebar'); ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- BANNER HEADER (Desain Biru Konsisten) -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm">
                                KEUANGAN • ADMINISTRASI
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2">
                                Info Keuangan 💰
                            </h1>
                            <p class="text-indigo-100 text-sm md:text-base max-w-lg">
                                Cek status tagihan sekolah dan riwayat pembayaran Anda. Pastikan administrasi lancar untuk kelancaran belajar.
                            </p>
                        </div>
                        <!-- Jam & Tanggal (Konsisten dengan Dashboard) -->
                        <div class="text-white text-right hidden md:block">
                            <div class="text-3xl font-black"><?= date('H:i') ?></div>
                            <div class="text-indigo-200 text-sm font-medium"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Tagihan -->
                    <div class="bg-rose-500 rounded-2xl p-6 text-white shadow-lg shadow-rose-500/20 relative overflow-hidden col-span-1 md:col-span-2">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full"></div>
                        <div class="relative z-10">
                            <p class="text-rose-100 text-sm font-medium mb-1">Total Tagihan Belum Lunas</p>
                            <h3 class="text-3xl font-black">
                                <?php 
                                    $total = 0; 
                                    foreach($tagihan as $t) $total += $t['jumlah']; 
                                    echo 'Rp ' . number_format($total, 0, ',', '.');
                                ?>
                            </h3>
                            <p class="text-xs text-rose-200 mt-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i> <?= count($tagihan) ?> Item tagihan aktif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Info Pembayaran -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-center text-center">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-700">Metode Bayar</h4>
                        <p class="text-xs text-slate-400 mt-1">Transfer Bank / Tunai di TU</p>
                    </div>
                </div>

                <!-- Section: Tagihan Belum Lunas -->
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fas fa-file-invoice text-rose-500"></i> Tagihan Belum Lunas
                    </h3>
                    
                    <?php if(empty($tagihan)): ?>
                        <div class="bg-white p-8 rounded-2xl border border-slate-200 text-center">
                            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check-double text-2xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-700">Tidak Ada Tagihan</h4>
                            <p class="text-slate-400 text-sm">Terima kasih, administrasi Anda sudah lunas.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-4">
                            <?php foreach($tagihan as $t): ?>
                                <div class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center shrink-0">
                                            <i class="fas fa-file-invoice text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800"><?= esc($t['nama_pembayaran']) ?></h4>
                                            <p class="text-xs text-slate-500 mt-1">
                                                Jatuh Tempo: <?= date('d F Y', strtotime($t['tanggal_tagihan'])) ?>
                                            </p>
                                            <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 uppercase tracking-wide">
                                                <?= esc(str_replace('_', ' ', $t['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-black text-slate-800">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></div>
                                        <!-- Tombol Bayar (Placeholder) -->
                                        <button class="mt-2 px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors">
                                            Konfirmasi Bayar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section: Riwayat Pembayaran (NEW) -->
                <div class="space-y-4 pt-4 border-t border-slate-200">
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <i class="fas fa-history text-indigo-500"></i> Riwayat Pembayaran Terakhir
                    </h3>

                    <?php if(empty($riwayat_pembayaran)): ?>
                        <div class="p-6 text-center text-sm text-slate-400 italic bg-slate-100/50 rounded-xl border border-dashed border-slate-200">
                            Belum ada riwayat pembayaran yang tercatat.
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase text-xs tracking-wider">
                                        <tr>
                                            <th class="px-6 py-4">Pembayaran</th>
                                            <th class="px-6 py-4">Tanggal</th>
                                            <th class="px-6 py-4">Metode</th>
                                            <th class="px-6 py-4 text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php foreach($riwayat_pembayaran as $history): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-slate-700">
                                                <?= esc($history['nama_tagihan'] ?? 'Pembayaran') ?>
                                            </td>
                                            <td class="px-6 py-4 text-slate-600">
                                                <?= date('d M Y H:i', strtotime($history['tanggal_bayar'])) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg">
                                                    <?= esc($history['metode_pembayaran']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-emerald-600">
                                                Rp <?= number_format($history['jumlah_bayar'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
    
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');

        if(btn && sidebar) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024) { 
                    if (!sidebar.contains(e.target) && !btn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.add('-translate-x-full');
                    }
                }
            });
        }
    </script>
</body>
</html>