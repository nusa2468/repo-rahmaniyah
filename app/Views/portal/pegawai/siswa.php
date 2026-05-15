<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Data Siswa') ?></title>
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- BANNER HEADER -->
                <div class="relative bg-sky-600 rounded-3xl p-6 md:p-10 overflow-hidden shadow-lg shadow-sky-600/20">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <span class="inline-block px-3 py-1 bg-white/20 text-white text-[10px] font-bold uppercase tracking-widest rounded-full mb-3 backdrop-blur-sm border border-white/10">
                                KESISWAAN • <?= esc($tahun_ajaran_label) ?>
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white mb-1">Data Siswa 👨‍🎓</h1>
                            <p class="text-sky-100 text-sm">Daftar siswa aktif di unit kerja Anda.</p>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                        <div class="relative">
                            <input type="text" placeholder="Cari siswa..." class="pl-9 pr-4 py-2 bg-slate-50 border-none rounded-xl text-sm font-semibold focus:ring-2 focus:ring-sky-500 w-64">
                            <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">Filter Kelas</button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="px-6 py-4">Nama Siswa</th>
                                    <th class="px-6 py-4">NIS / NISN</th>
                                    <th class="px-6 py-4">Kelas</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if(empty($siswa_list)): ?>
                                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Data siswa tidak ditemukan.</td></tr>
                                <?php else: foreach($siswa_list as $s): ?>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($s['nama_lengkap']) ?>&background=random&color=fff" class="w-8 h-8 rounded-full border border-slate-200">
                                                <div class="font-bold text-slate-800"><?= esc($s['nama_lengkap']) ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 font-mono text-xs">
                                            <?= esc($s['nis']) ?> <span class="text-slate-300">/</span> <?= esc($s['nisn'] ?? '-') ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-sky-50 text-sky-700 font-bold rounded text-xs"><?= esc($s['nama_kelas'] ?? 'Belum Masuk Kelas') ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block mr-1"></span> Aktif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button class="text-slate-400 hover:text-sky-600 font-bold text-xs">Detail</button>
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