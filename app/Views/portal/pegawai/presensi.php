<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Riwayat Presensi') ?></title>
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 scrollbar-default">

    <?= view('portal/pegawai/partials/topbar'); ?>

    <div class="flex h-screen overflow-hidden pt-14 lg:pt-0">
        <?= view('portal/pegawai/partials/sidebar'); ?>

        <main class="flex-1 overflow-y-auto bg-slate-50 h-full p-4 lg:p-8">
            <div class="max-w-5xl mx-auto space-y-6">
                
                <!-- Header & Filter -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                    <div>
                        <h1 class="text-xl font-black text-slate-800">Riwayat Kehadiran</h1>
                        <p class="text-xs text-slate-500">Rekapitulasi absensi bulanan Anda.</p>
                    </div>
                    
                    <form action="" method="get" class="flex items-center gap-2">
                        <select name="bulan" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 outline-none" onchange="this.form.submit()">
                            <?php 
                                $bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                foreach($bulanIndo as $k => $v): 
                            ?>
                                <option value="<?= $k ?>" <?= ($filter['bulan'] == $k) ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="tahun" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 focus:ring-2 focus:ring-emerald-500 outline-none" onchange="this.form.submit()">
                            <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                                <option value="<?= $y ?>" <?= ($filter['tahun'] == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-emerald-500 rounded-2xl p-4 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-16 h-16 bg-white/10 rounded-full -mr-4 -mt-4"></div>
                        <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider mb-1">Hadir</p>
                        <h3 class="text-3xl font-black"><?= $summary['hadir'] ?></h3>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Izin</p>
                        <h3 class="text-3xl font-black text-amber-500"><?= $summary['izin'] ?></h3>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Sakit</p>
                        <h3 class="text-3xl font-black text-blue-500"><?= $summary['sakit'] ?></h3>
                    </div>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Alpha</p>
                        <h3 class="text-3xl font-black text-rose-500"><?= $summary['alpha'] ?></h3>
                    </div>
                </div>

                <!-- Detail Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-700 text-sm">Detail Harian</h3>
                    </div>
                    
                    <?php if(empty($detail)): ?>
                        <div class="text-center py-10">
                            <i class="fas fa-calendar-times text-3xl text-slate-300 mb-2"></i>
                            <p class="text-slate-500 text-sm">Tidak ada data presensi pada periode ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wide border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3">Tanggal</th>
                                        <th class="px-6 py-3">Jam Masuk</th>
                                        <th class="px-6 py-3">Jam Pulang</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                        <th class="px-6 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach($detail as $d): 
                                        $hari = date('l', strtotime($d['tanggal']));
                                        $indoHari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'][$hari];
                                        
                                        $badgeColor = 'bg-slate-100 text-slate-600';
                                        if($d['status'] == 'Hadir') $badgeColor = 'bg-emerald-100 text-emerald-700';
                                        elseif($d['status'] == 'Izin') $badgeColor = 'bg-amber-100 text-amber-700';
                                        elseif($d['status'] == 'Sakit') $badgeColor = 'bg-blue-100 text-blue-700';
                                        elseif($d['status'] == 'Alpha') $badgeColor = 'bg-rose-100 text-rose-700';
                                    ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3 font-medium text-slate-700">
                                            <span class="text-xs text-slate-400 block"><?= $indoHari ?></span>
                                            <?= date('d/m/Y', strtotime($d['tanggal'])) ?>
                                        </td>
                                        <td class="px-6 py-3 font-mono text-emerald-600"><?= $d['jam_masuk'] ?: '--:--' ?></td>
                                        <td class="px-6 py-3 font-mono text-rose-600"><?= $d['jam_pulang'] ?: '--:--' ?></td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="px-2 py-1 rounded text-[10px] font-black uppercase <?= $badgeColor ?>">
                                                <?= esc($d['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-slate-500 text-xs truncate max-w-[200px]">
                                            <?= esc($d['keterangan'] ?: '-') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>