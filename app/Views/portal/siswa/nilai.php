<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Nilai Siswa') ?></title>
    
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
                
                <!-- BANNER HEADER (Updated: Ditambahkan Jam & Tanggal di kanan) -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm">
                                AKADEMIK • REKAPITULASI NILAI
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2">
                                Buku Nilai & Rapor 📚
                            </h1>
                            <p class="text-indigo-100 text-sm md:text-base max-w-lg">
                                Pantau perkembangan akademikmu di sini. Lihat nilai harian, tugas, dan rapor semester secara detail.
                            </p>
                        </div>
                        
                        <!-- FIX: Bagian ini sebelumnya tertinggal -->
                        <div class="text-white text-right hidden md:block">
                            <div class="text-3xl font-black"><?= date('H:i') ?></div>
                            <div class="text-indigo-200 text-sm font-medium"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Section 1: Riwayat Rapor -->
                <?php if(!empty($rapor)): ?>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-signature text-fuchsia-500"></i> Riwayat Rapor
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <?php foreach($rapor as $r): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-fuchsia-300 hover:shadow-lg hover:shadow-fuchsia-500/10 transition-all group relative overflow-hidden">
                            <!-- Decor -->
                            <div class="absolute top-0 right-0 w-24 h-24 bg-fuchsia-50 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:bg-fuchsia-100 transition-colors"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-fuchsia-100 text-fuchsia-700 text-[10px] font-black uppercase tracking-wide border border-fuchsia-200">
                                        <?= esc($r['semester'] ?? 'Smt ?') ?>
                                    </span>
                                    <span class="text-xs font-bold text-slate-400 font-mono"><?= esc($r['tahun_ajaran'] ?? '') ?></span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 mb-1 truncate">
                                    <?= esc($r['nama_kelas'] ?? 'Kelas') ?>
                                </h4>
                                <div class="flex items-end gap-2 mt-4">
                                    <div class="text-3xl font-black text-fuchsia-600"><?= number_format((float)($r['rata_rata'] ?? 0), 2) ?></div>
                                    <div class="text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Rata-Rata</div>
                                </div>
                                
                                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-500">
                                        Status: <span class="text-emerald-600"><?= esc($r['status_kenaikan'] ?? 'Aktif') ?></span>
                                    </span>
                                    <a href="<?= base_url('portal/siswa/rapor/' . $r['id']) ?>" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-fuchsia-600 hover:text-white transition-colors" title="Detail Rapor">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Section 2: Riwayat Nilai -->
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-list-check text-indigo-500"></i> Daftar Nilai Terbaru
                    </h2>
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                        <?php if(empty($nilai)): ?>
                            <div class="p-10 text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clipboard-list text-2xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-600">Belum Ada Nilai</h3>
                                <p class="text-slate-400 text-sm">Data nilai harian/ujian belum tersedia saat ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold text-xs tracking-wider">
                                        <tr>
                                            <th class="px-6 py-4 w-10 text-center">#</th>
                                            <th class="px-6 py-4">Mata Pelajaran</th>
                                            <th class="px-6 py-4">Kategori</th>
                                            <th class="px-6 py-4 text-center">Smt</th>
                                            <th class="px-6 py-4 text-right">Nilai Akhir</th>
                                            <th class="px-6 py-4 text-center">Predikat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php $no=1; foreach($nilai as $n): ?>
                                        <tr class="hover:bg-indigo-50/30 transition-colors">
                                            <td class="px-6 py-4 text-center text-slate-400 font-bold text-xs"><?= $no++ ?></td>
                                            <td class="px-6 py-4 font-bold text-slate-700">
                                                <?= esc(!empty($n['nama_mapel']) ? $n['nama_mapel'] : 'Mata Pelajaran Umum') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold border border-slate-200">
                                                    <?= esc($n['nama_penilaian'] ?? 'Tugas') ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center text-slate-500 font-mono text-xs">
                                                <?= esc($n['semester'] ?? '-') ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-base font-black text-indigo-600">
                                                    <?= number_format((float)($n['nilai_angka'] ?? 0), 2) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <?php if(!empty($n['nilai_huruf'])): ?>
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-xs border
                                                    <?= ($n['nilai_huruf'] == 'A') ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 
                                                       (($n['nilai_huruf'] == 'B') ? 'bg-blue-50 text-blue-600 border-blue-200' : 
                                                       (($n['nilai_huruf'] == 'C') ? 'bg-amber-50 text-amber-600 border-amber-200' : 
                                                       'bg-rose-50 text-rose-600 border-rose-200')) ?>">
                                                        <?= esc($n['nilai_huruf']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-slate-300">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
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