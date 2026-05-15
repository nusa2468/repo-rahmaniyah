<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard Siswa') ?></title>
    
    <!-- Include Assets Partial (CSS & Font) -->
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar Mobile -->
    <?= view('portal/siswa/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Include Sidebar Desktop/Mobile -->
        <?= view('portal/siswa/partials/sidebar'); ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Security Alert (Flashdata) -->
                <?php if(!empty($security_alert)): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex items-start gap-3 animate-fade-in-down">
                        <i class="fas fa-shield-halved text-amber-500 mt-0.5 text-lg"></i>
                        <div>
                            <h4 class="text-sm font-bold text-amber-800">Pemberitahuan Keamanan</h4>
                            <p class="text-xs text-amber-700 mt-1"><?= esc($security_alert) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Welcome Banner -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <!-- Dekorasi Background -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <!-- Label Tahun Ajaran Dinamis -->
                            <!-- Mengambil data dari Controller: $tahun_ajaran_label -->
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm border border-white/10">
                                <?= esc($sekolah->jenjang_aktif !== 'Global' ? $sekolah->jenjang_aktif : 'Portal Akademik') ?> • <?= esc($tahun_ajaran_label ?? 'Tahun Ajaran Aktif') ?>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2">
                                Halo, <?= esc(strtok($siswa['nama_lengkap'] ?? 'Siswa', " ")) ?>! 👋
                            </h1>
                            <p class="text-indigo-100 text-sm md:text-base max-w-lg">
                                Selamat datang di portal siswa <strong><?= esc($sekolah->nama_sekolah ?? 'Portal Sekolah') ?></strong>. 
                                Semangat belajar hari ini! Kamu memiliki <span class="font-bold text-white"><?= count($jadwal_hari_ini ?? []) ?> mata pelajaran</span> yang harus diikuti.
                            </p>
                        </div>
                        
                        <!-- Jam Digital -->
                        <div class="text-white text-right hidden md:block">
                            <div class="text-3xl font-black tracking-tight"><?= date('H:i') ?></div>
                            <div class="text-indigo-200 text-sm font-medium"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column (Akademik & Jadwal) -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Info Rapor Terakhir (Jika Ada) -->
                        <?php if(!empty($rapor_terbaru)): ?>
                        <div class="bg-gradient-to-r from-violet-500 to-fuchsia-500 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                            
                            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold">Ringkasan Akademik</h3>
                                    <p class="text-violet-100 text-xs mt-1">
                                        <?= esc($rapor_terbaru['tahun_ajaran'] ?? '-') ?> • Semester <?= esc($rapor_terbaru['semester'] ?? '-') ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-2xl font-black"><?= number_format((float)($rapor_terbaru['rata_rata'] ?? 0), 2) ?></div>
                                        <div class="text-[10px] text-violet-200 uppercase tracking-wider">Rata-Rata Nilai</div>
                                    </div>
                                    <div class="h-8 w-[1px] bg-white/20"></div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold bg-white/20 px-3 py-1 rounded-lg backdrop-blur-sm border border-white/10">
                                            <?= esc($rapor_terbaru['status_kenaikan'] ?? 'Aktif') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Jadwal Hari Ini -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-clock text-indigo-500"></i> Jadwal Hari Ini (<?= $hari_ini ?? date('l') ?>)
                                </h3>
                                <a href="<?= base_url('portal/siswa/jadwal') ?>" class="text-xs font-bold text-indigo-600 hover:underline">Lihat Semua</a>
                            </div>

                            <div class="space-y-4">
                                <?php if(empty($jadwal_hari_ini)): ?>
                                    <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                        <i class="fas fa-mug-hot text-4xl text-slate-300 mb-3"></i>
                                        <p class="text-slate-500 font-medium">Tidak ada jadwal pelajaran hari ini.</p>
                                        <p class="text-slate-400 text-xs mt-1">Gunakan waktu ini untuk belajar mandiri.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="relative pl-4 border-l-2 border-slate-100 space-y-6">
                                        <?php foreach($jadwal_hari_ini as $jadwal): ?>
                                            <div class="relative pl-6 group">
                                                <!-- Dot Indicator -->
                                                <div class="absolute -left-[21px] top-1 w-4 h-4 rounded-full border-2 border-white bg-indigo-500 shadow-sm group-hover:scale-110 transition-transform"></div>
                                                
                                                <!-- Card Jadwal -->
                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-slate-50 hover:bg-white p-4 rounded-xl border border-slate-100 hover:border-indigo-100 hover:shadow-md transition-all">
                                                    <div>
                                                        <div class="text-xs font-bold text-indigo-600 mb-1 flex items-center gap-1">
                                                            <i class="far fa-clock"></i>
                                                            <?= date('H:i', strtotime($jadwal['jam_mulai'])) ?> - <?= date('H:i', strtotime($jadwal['jam_selesai'])) ?>
                                                        </div>
                                                        <h4 class="font-bold text-slate-800 text-base">
                                                            <?= esc($jadwal['nama_mapel'] ?? 'Mapel Tidak Diketahui') ?>
                                                        </h4>
                                                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                                            <i class="fas fa-chalkboard-teacher"></i> 
                                                            <?= esc($jadwal['nama_guru'] ?? 'Guru Tidak Diketahui') ?>
                                                        </p>
                                                    </div>
                                                    <div class="mt-3 sm:mt-0">
                                                        <span class="px-3 py-1 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg shadow-sm flex items-center gap-1">
                                                            <i class="fas fa-door-open text-slate-400"></i>
                                                            <?= esc($jadwal['nama_ruangan'] ?? $jadwal['ruangan'] ?? $jadwal['ruangan_alt'] ?? '-') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Tagihan & Nilai & Riwayat) -->
                    <div class="space-y-6">
                        
                        <!-- Tagihan Alert -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-rose-50 to-white">
                                <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                                    <i class="fas fa-bell text-rose-500"></i> Tagihan Aktif
                                </h3>
                                <?php if(!empty($tagihan_aktif)): ?>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[10px] font-black rounded-full"><?= count($tagihan_aktif) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="p-5">
                                <?php if(empty($tagihan_aktif)): ?>
                                    <div class="flex flex-col items-center justify-center py-6 text-center">
                                        <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-3 shadow-sm">
                                            <i class="fas fa-check text-emerald-600 text-xl"></i>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700">Tidak Ada Tagihan</p>
                                        <p class="text-xs text-slate-400">Keuangan Anda aman.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach($tagihan_aktif as $tagihan): ?>
                                            <div class="p-3 bg-rose-50/50 border border-rose-100 rounded-xl hover:bg-rose-50 transition-colors">
                                                <div class="flex justify-between items-start mb-1">
                                                    <h5 class="text-xs font-bold text-slate-800 line-clamp-1"><?= esc($tagihan['nama_pembayaran']) ?></h5>
                                                    <span class="text-[10px] font-black text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded uppercase border border-rose-200">
                                                        <?= esc(str_replace('_', ' ', $tagihan['status'])) ?>
                                                    </span>
                                                </div>
                                                <div class="flex justify-between items-end">
                                                    <p class="text-xs text-slate-500"><?= date('d M Y', strtotime($tagihan['tanggal_tagihan'])) ?></p>
                                                    <p class="text-sm font-black text-rose-600">Rp <?= number_format($tagihan['jumlah'], 0, ',', '.') ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Riwayat Pembayaran Terbaru -->
                        <?php if(!empty($riwayat_pembayaran)): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-history text-indigo-500"></i> Pembayaran Terakhir
                                </h3>
                                <a href="<?= base_url('portal/siswa/keuangan') ?>" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600">Lihat Semua</a>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <?php foreach($riwayat_pembayaran as $history): ?>
                                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                        <div>
                                            <h5 class="text-xs font-bold text-slate-800"><?= esc($history['nama_tagihan'] ?? 'Pembayaran') ?></h5>
                                            <p class="text-[10px] text-slate-500 mt-0.5">
                                                <?= date('d M Y', strtotime($history['tanggal_bayar'])) ?> • <?= esc($history['metode_pembayaran']) ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-black text-emerald-600">+ Rp <?= number_format($history['jumlah_bayar'], 0, ',', '.') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Nilai Terbaru -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-star text-amber-400"></i> Nilai Terbaru
                                </h3>
                                <a href="<?= base_url('portal/siswa/nilai') ?>" class="text-[10px] font-bold text-slate-400 hover:text-indigo-600">Lihat Semua</a>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <?php if(empty($nilai_terakhir)): ?>
                                    <div class="p-6 text-center text-xs text-slate-400 italic">Belum ada data nilai terbaru.</div>
                                <?php else: ?>
                                    <?php foreach($nilai_terakhir as $nilai): ?>
                                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                                            <div>
                                                <h5 class="text-sm font-bold text-slate-800">
                                                    <?= esc($nilai['nama_mapel'] ?? 'Mapel') ?>
                                                </h5>
                                                <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                                    <?= esc($nilai['nama_penilaian'] ?? 'Tugas') ?> 
                                                    <?= !empty($nilai['semester']) ? ' - Sem. ' . esc($nilai['semester']) : '' ?>
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-black text-indigo-600">
                                                    <?= number_format((float)($nilai['nilai_angka'] ?? 0), 2) ?>
                                                </div>
                                                <div class="text-[9px] text-slate-400 uppercase font-bold tracking-wider">
                                                    <?= isset($nilai['nilai_huruf']) ? 'Predikat: ' . esc($nilai['nilai_huruf']) : 'Nilai' ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script Handling untuk Mobile Nav -->
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        // Kita menggunakan ID 'sidebar' yang ada di dalam partial sidebar.php
        const sidebar = document.getElementById('sidebar');

        if(btn && sidebar) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Klik di luar sidebar untuk menutupnya
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