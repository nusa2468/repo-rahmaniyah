<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Jadwal Pelajaran') ?></title>
    
    <!-- Include Assets Partial (CSS Lokal & Font) -->
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- 1. Include Topbar (Navigasi Mobile) -->
    <?= view('portal/siswa/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- 2. Include Sidebar (Navigasi Desktop) -->
        <?= view('portal/siswa/partials/sidebar'); ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- BANNER HEADER (Updated: Agar sama dengan Dashboard) -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm">
                                AKADEMIK • JADWAL MINGGUAN
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-2">
                                Jadwal Pelajaran 📅
                            </h1>
                            <p class="text-indigo-100 text-sm md:text-base max-w-lg">
                                Berikut adalah jadwal pelajaran aktif untuk kelas Anda. Pastikan mempersiapkan buku dan peralatan yang sesuai.
                            </p>
                        </div>
                        <div class="text-white text-right hidden md:block">
                            <div class="text-3xl font-black"><?= date('H:i') ?></div>
                            <div class="text-indigo-200 text-sm font-medium"><?= date('l, d F Y') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Konten Grid Jadwal -->
                <?php if(empty($jadwal)): ?>
                    <!-- State: Kosong -->
                    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300 shadow-sm">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-600">Jadwal Belum Tersedia</h3>
                        <p class="text-slate-400 text-sm max-w-xs mx-auto mt-1">
                            Belum ada jadwal yang dipublikasikan untuk kelas Anda. Silakan hubungi bagian kurikulum.
                        </p>
                    </div>
                <?php else: ?>
                    <!-- State: Ada Data -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php 
                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        foreach($days as $day): 
                            // Skip hari jika tidak ada jadwal
                            if(!isset($jadwal[$day])) continue; 
                        ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full hover:shadow-md transition-shadow duration-300">
                                <!-- Header Hari -->
                                <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-white border-b border-indigo-100 flex items-center justify-between">
                                    <h3 class="font-black text-indigo-700 uppercase tracking-wide text-sm flex items-center gap-2">
                                        <i class="fas fa-calendar-check text-indigo-400"></i> <?= $day ?>
                                    </h3>
                                    <span class="text-[10px] font-bold bg-white text-indigo-600 px-2.5 py-1 rounded-lg border border-indigo-100 shadow-sm">
                                        <?= count($jadwal[$day]) ?> Mapel
                                    </span>
                                </div>
                                
                                <!-- List Mapel -->
                                <div class="divide-y divide-slate-100">
                                    <?php foreach($jadwal[$day] as $j): ?>
                                        <div class="p-4 hover:bg-slate-50 transition-colors group">
                                            <div class="flex items-start justify-between mb-2">
                                                <!-- Jam -->
                                                <span class="text-[11px] font-bold text-slate-500 font-mono bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                                    <?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?>
                                                </span>
                                                <!-- Ruangan -->
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                                    <?= esc($j['ruangan'] ?? $j['ruangan_alt'] ?? 'R.Kelas') ?>
                                                </span>
                                            </div>
                                            <!-- Nama Mapel -->
                                            <h4 class="font-bold text-slate-800 text-sm mb-1 line-clamp-2 leading-snug group-hover:text-indigo-700 transition-colors">
                                                <?= esc(!empty($j['nama_mapel']) ? $j['nama_mapel'] : 'Mata Pelajaran') ?>
                                            </h4>
                                            <!-- Guru -->
                                            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                                                <i class="fas fa-chalkboard-teacher text-[10px] opacity-70"></i> 
                                                <?= esc(!empty($j['nama_guru']) ? $j['nama_guru'] : 'Guru Pengampu') ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- Script Handling -->
    <script>
        // Logika untuk toggle sidebar mobile
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');

        if(btn && sidebar) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
            
            // Tutup sidebar jika klik di luar area
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