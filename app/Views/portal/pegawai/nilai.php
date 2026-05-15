<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Input Nilai') ?></title>
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- BANNER HEADER (Konsisten) -->
                <div class="relative bg-indigo-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-indigo-600/20">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm border border-white/10">
                                AKADEMIK • <?= esc($tahun_ajaran_label) ?>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-1">Input Nilai Siswa 📝</h1>
                            <p class="text-indigo-100 text-sm">Kelola nilai harian, tugas, UTS, dan UAS siswa Anda.</p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-book-open text-indigo-500"></i> Pilih Kelas & Mata Pelajaran
                    </h3>

                    <?php if(empty($kelas_ajar)): ?>
                        <div class="text-center py-10 border-2 border-dashed border-slate-100 rounded-xl">
                            <i class="fas fa-chalkboard-teacher text-3xl text-slate-300 mb-2"></i>
                            <p class="text-slate-500 text-sm">Anda belum memiliki jadwal mengajar aktif.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach($kelas_ajar as $k): ?>
                                <div class="p-4 border border-slate-200 rounded-xl hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer group bg-white">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            <?= esc($k['nama_kelas']) ?>
                                        </span>
                                        <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm"><?= esc($k['nama_mapel']) ?></h4>
                                    <p class="text-xs text-slate-400 mt-1">Klik untuk input nilai</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>