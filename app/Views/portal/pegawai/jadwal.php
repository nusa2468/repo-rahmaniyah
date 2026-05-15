<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Jadwal Mengajar') ?></title>
    
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <!-- Topbar Partial -->
    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        
        <!-- Sidebar Partial -->
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800">Jadwal Mengajar</h1>
                        <p class="text-slate-500 text-sm">Agenda kegiatan belajar mengajar mingguan.</p>
                    </div>
                    <div class="hidden md:block text-right">
                        <div class="text-sm font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                            <?= esc($tahun_ajaran_label ?? 'Tahun Ajaran Aktif') ?>
                        </div>
                    </div>
                </div>

                <!-- Grid Jadwal -->
                <?php if(empty($jadwal)): ?>
                    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-4xl text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-600">Belum Ada Jadwal</h3>
                        <p class="text-slate-400 text-sm max-w-xs mx-auto mt-1">
                            Jadwal mengajar Anda belum dikonfigurasi untuk tahun ajaran ini.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php 
                        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        foreach($days as $day): 
                            if(!isset($jadwal[$day])) continue; 
                        ?>
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full hover:shadow-md transition-shadow">
                                <div class="px-5 py-4 bg-gradient-to-r from-emerald-50 to-white border-b border-emerald-100 flex items-center justify-between">
                                    <h3 class="font-black text-emerald-700 uppercase tracking-wide text-sm flex items-center gap-2">
                                        <i class="fas fa-calendar-day text-emerald-400"></i> <?= $day ?>
                                    </h3>
                                    <span class="text-[10px] font-bold bg-white text-emerald-600 px-2.5 py-1 rounded-lg border border-emerald-100 shadow-sm">
                                        <?= count($jadwal[$day]) ?> Sesi
                                    </span>
                                </div>
                                <div class="divide-y divide-slate-100">
                                    <?php foreach($jadwal[$day] as $j): ?>
                                        <div class="p-4 hover:bg-slate-50 transition-colors group">
                                            <div class="flex items-start justify-between mb-2">
                                                <span class="text-[11px] font-bold text-slate-500 font-mono bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                                    <?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?>
                                                </span>
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                                    <?= esc($j['ruangan'] ?? 'R.Kelas') ?>
                                                </span>
                                            </div>
                                            <h4 class="font-bold text-slate-800 text-sm mb-1 line-clamp-2 leading-snug group-hover:text-emerald-700 transition-colors">
                                                <?= esc($j['nama_mapel']) ?>
                                            </h4>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="text-xs font-bold text-white bg-emerald-500 px-2 py-0.5 rounded">
                                                    <?= esc($j['nama_kelas']) ?>
                                                </span>
                                                <?php if(!empty($j['nama_grup'])): ?>
                                                    <span class="text-xs text-slate-400">• <?= esc($j['nama_grup']) ?></span>
                                                <?php endif; ?>
                                            </div>
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
    
    <script>
        // Mobile sidebar logic
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        if(btn && sidebar) {
            btn.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); });
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024 && !sidebar.contains(e.target) && !btn.contains(e.target) && !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    </script>
</body>
</html>