<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Master Data - Guru
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
/**
 * Helper Warna Jenjang (Disesuaikan untuk Tailwind & Dark Mode)
 */
if (!function_exists('getJenjangBadge')) {
    function getJenjangBadge($kode) {
        $kode = strtoupper($kode ?? '');
        switch ($kode) {
            case 'SD': case 'MI': 
                return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 border-red-200 dark:border-red-500/30';
            case 'SMP': case 'MTS': 
                return 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 border-blue-200 dark:border-blue-500/30';
            case 'SMA': case 'SMK': case 'MA': 
                return 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 border-slate-200 dark:border-slate-500/30';
            case 'TK': case 'PAUD': 
                return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30';
            case 'GLOBAL': case 'YAYASAN': 
                return 'bg-gray-800 text-white dark:bg-gray-700 dark:text-gray-200 border-gray-700';
            default: 
                return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200 dark:border-gray-700';
        }
    }
}

// Data Preparation
$gurusData = $gurus ?? [];
$countSertifikasi = 0;
$countNonSertifikasi = 0;
$countASN = 0;
$totalGuru = count($gurusData);

foreach ($gurusData as $data) {
    $kepeg = (object)($data['kepegawaian_aktif'] ?? []);
    if (!empty($kepeg->no_sertifikasi)) $countSertifikasi++;
    else $countNonSertifikasi++;
    
    $st_peg = strtoupper($kepeg->status_kepegawaian ?? '');
    if (in_array($st_peg, ['PNS', 'PPPK'])) $countASN++;
}
?>

<div class="space-y-6" x-data="tablePagination()" x-init="initTable()">

    <!-- Header & Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                Database Pendidik
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Kelola data pokok, riwayat kepegawaian, dan penugasan guru.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Limit Selector -->
            <div class="relative">
                <select x-model="itemsPerPage" @change="changeLimit()"
                        class="pl-3 pr-8 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-gray-200 shadow-sm appearance-none cursor-pointer">
                    <option value="10">10 Baris</option>
                    <option value="25">25 Baris</option>
                    <option value="50">50 Baris</option>
                    <option value="100">100 Baris</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>

            <!-- Unit Filter -->
            <form action="" method="get" class="relative">
                <i class="fas fa-filter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <select name="unit" onchange="this.form.submit()" 
                        class="pl-8 pr-8 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:text-gray-200 shadow-sm appearance-none cursor-pointer">
                    <option value="GLOBAL">Semua Unit</option>
                    <?php if(isset($jenjangs) && is_iterable($jenjangs)): ?>
                        <?php foreach ($jenjangs as $j): 
                            $j_kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                            if (strtoupper($j_kode) === 'GLOBAL') continue;
                        ?>
                            <option value="<?= $j_kode ?>" <?= (($current_filter['unit'] ?? '') == $j_kode) ? 'selected' : '' ?>>
                                Unit <?= strtoupper($j_kode) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </form>

            <a href="<?= route_to('guru_new') ?>" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-sky-600/20 transition-all active:scale-95 focus:ring-2 focus:ring-offset-2 focus:ring-sky-600">
                <i class="fas fa-plus text-xs"></i> <span>Tambah Guru</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards (SOLID STYLE) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card Total (SOLID BLUE) -->
        <div class="bg-gradient-to-br from-sky-500 to-sky-600 text-white p-5 rounded-2xl shadow-lg shadow-sky-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-sky-100 uppercase tracking-wider mb-1">Total Guru</p>
                <h3 class="text-3xl font-extrabold text-white"><?= $totalGuru ?> <span class="text-sm font-normal text-sky-100 opacity-80">Jiwa</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>

        <!-- Card ASN (SOLID EMERALD) -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-5 rounded-2xl shadow-lg shadow-emerald-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider mb-1">Status ASN</p>
                <h3 class="text-3xl font-extrabold text-white"><?= $countASN ?> <span class="text-sm font-normal text-emerald-100 opacity-80">Orang</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-id-card-alt text-xl"></i>
            </div>
        </div>

        <!-- Card Sertifikasi (SOLID AMBER) -->
        <div class="bg-gradient-to-br from-amber-400 to-amber-500 text-white p-5 rounded-2xl shadow-lg shadow-amber-500/20 border-0 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-amber-50 uppercase tracking-wider mb-1">Tersertifikasi</p>
                <h3 class="text-3xl font-extrabold text-white"><?= $countSertifikasi ?> <span class="text-sm font-normal text-amber-50 opacity-80">Jiwa</span></h3>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-certificate text-xl"></i>
            </div>
        </div>

        <!-- Card Chart (SOLID CONTAINER) -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border-2 border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="relative w-16 h-16 shrink-0">
                <canvas id="chartSertifikasi"></canvas>
            </div>
            <div>
                <p class="text-xs font-extrabold text-gray-500 dark:text-gray-400 uppercase">Rasio Sertifikasi</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-gray-800 dark:text-white">
                        <?= ($totalGuru > 0) ? round(($countSertifikasi / $totalGuru) * 100) : 0 ?>%
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Terdata</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden flex flex-col min-h-[500px]">
        
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-800 overflow-x-auto custom-scrollbar">
            <nav class="flex px-4 gap-6 min-w-max" aria-label="Tabs">
                <button @click="switchTab('master')" 
                        :class="activeTab === 'master' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-address-book"></i> Data Pokok
                </button>
                <button @click="switchTab('kepegawaian')" 
                        :class="activeTab === 'kepegawaian' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-briefcase"></i> Kepegawaian
                </button>
                <button @click="switchTab('pendidikan')" 
                        :class="activeTab === 'pendidikan' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-graduation-cap"></i> Pendidikan
                </button>
                <button @click="switchTab('penugasan')" 
                        :class="activeTab === 'penugasan' ? 'border-sky-500 text-sky-600 dark:text-sky-400 bg-sky-50/50 dark:bg-sky-900/10' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="py-4 px-3 border-b-2 font-bold text-sm whitespace-nowrap transition-all flex items-center gap-2">
                    <i class="fas fa-chalkboard-user"></i> Penugasan
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="p-0 flex-1 relative">
            
            <!-- TAB 1: MASTER DATA -->
            <div x-show="activeTab === 'master'" x-transition:enter.opacity.duration.300ms class="overflow-x-auto h-full">
                <table id="table-master" class="w-full text-left text-sm whitespace-nowrap data-table">
                    <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                        <tr>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider w-10 text-center">No</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider">Identitas</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider text-center">Unit</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider">NUPTK / NIK</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider text-center">L/P</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider text-center">Status</th>
                            <th class="px-6 py-4 text-xs uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php $no = 1; foreach ($gurusData as $data): 
                            $guru = (object)($data['guru'] ?? $data); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 text-center text-gray-400 font-bold"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center text-sky-700 dark:text-sky-300 text-sm font-bold shrink-0 ring-2 ring-white dark:ring-gray-800">
                                            <?= substr($guru->nama_lengkap ?? '?', 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white"><?= esc($guru->nama_lengkap ?? 'N/A') ?></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium"><?= esc($guru->email ?? '-') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php $badge = getJenjangBadge($guru->kode_jenjang ?? 'GLOBAL'); ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border shadow-sm <?= $badge ?>">
                                        <?= strtoupper($guru->kode_jenjang ?? 'Global') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-mono text-xs text-gray-600 dark:text-gray-300">
                                        <span class="block text-sky-600 dark:text-sky-400 font-black"><?= esc($guru->nuptk ?? '-') ?></span>
                                        <span class="block mt-0.5 opacity-70 font-semibold">NIK: <?= esc($guru->nik ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-300"><?= esc($guru->jenis_kelamin ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php $isActive = strtolower($guru->status ?? 'aktif') === 'aktif'; ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold <?= $isActive ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300' : 'bg-gray-100 text-gray-600' ?>">
                                        <?= strtoupper($guru->status ?? 'Aktif') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- SOLID ACTION BUTTONS -->
                                        <a href="<?= route_to('guru_show', $guru->id) ?>" class="w-8 h-8 flex items-center justify-center bg-sky-500 hover:bg-sky-600 text-white rounded-lg shadow-sm shadow-sky-500/30 transition-all hover:-translate-y-0.5" title="Lihat">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="<?= route_to('guru_edit', $guru->id) ?>" class="w-8 h-8 flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow-sm shadow-amber-500/30 transition-all hover:-translate-y-0.5" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form action="<?= route_to('guru_delete', $guru->id) ?>" method="post" class="inline-block" onsubmit="return confirm('Hapus data?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="w-8 h-8 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-sm shadow-red-500/30 transition-all hover:-translate-y-0.5" title="Hapus">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 2: KEPEGAWAIAN -->
            <div x-show="activeTab === 'kepegawaian'" x-cloak class="overflow-x-auto h-full">
                <table id="table-kepegawaian" class="w-full text-left text-sm whitespace-nowrap data-table">
                    <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                        <tr>
                            <th class="px-6 py-4 text-xs uppercase">Nama Pendidik</th>
                            <th class="px-6 py-4 text-xs uppercase text-center">Unit</th>
                            <th class="px-6 py-4 text-xs uppercase">Status Pegawai</th>
                            <th class="px-6 py-4 text-xs uppercase">Golongan</th>
                            <th class="px-6 py-4 text-xs uppercase">Jenis PTK</th>
                            <th class="px-6 py-4 text-xs uppercase text-center">Sertifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php foreach ($gurusData as $data): 
                            $guru = (object)($data['guru'] ?? $data);
                            $kepeg = (object)($data['kepegawaian_aktif'] ?? []); 
                            $statusKepeg = $kepeg->status_kepegawaian ?? 'Non-ASN';
                            $unit = strtoupper($guru->kode_jenjang ?? '-');
                        ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white"><?= esc($guru->nama_lengkap ?? 'N/A') ?></div>
                                    <div class="text-xs text-gray-500 font-medium">NIP: <?= esc($kepeg->nip ?? '-') ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-1 shadow-sm <?= ($unit == 'SD') ? 'bg-red-500' : 'bg-sky-500' ?>"></span>
                                    <span class="text-gray-700 dark:text-gray-300 font-bold"><?= esc($unit) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold <?= ($statusKepeg == 'PNS' || $statusKepeg == 'PPPK') ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' ?>">
                                        <?= esc($statusKepeg) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-medium"><?= esc($kepeg->pangkat_golongan ?? '-') ?></td>
                                <td class="px-6 py-4 text-gray-500"><?= esc($kepeg->jenis_ptk ?? 'Guru Mapel') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($kepeg->no_sertifikasi)): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300"><i class="fas fa-check mr-1.5"></i> Ada</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs font-medium">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 3: PENDIDIKAN -->
            <div x-show="activeTab === 'pendidikan'" x-cloak class="overflow-x-auto h-full">
                 <table id="table-pendidikan" class="w-full text-left text-sm whitespace-nowrap data-table">
                    <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                        <tr>
                            <th class="px-6 py-4 text-xs uppercase">Nama Pendidik</th>
                            <th class="px-6 py-4 text-xs uppercase">Jenjang</th>
                            <th class="px-6 py-4 text-xs uppercase">Jurusan</th>
                            <th class="px-6 py-4 text-xs uppercase">Kampus/Sekolah</th>
                            <th class="px-6 py-4 text-xs uppercase text-center">Lulus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php foreach ($gurusData as $data): 
                            $guru = (object)($data['guru'] ?? $data);
                            $pend = (object)($data['pendidikan_tertinggi'] ?? []); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white"><?= esc($guru->nama_lengkap ?? 'N/A') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs rounded font-bold border border-violet-200 dark:border-violet-500/20"><?= esc($pend->jenjang_pendidikan ?? '-') ?></span>
                                </td>
                                <td class="px-6 py-4 text-sky-600 dark:text-sky-400 font-semibold"><?= esc($pend->program_studi ?? '-') ?></td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400"><?= esc($pend->satuan_pendidikan ?? '-') ?></td>
                                <td class="px-6 py-4 text-center font-mono text-gray-500 font-bold"><?= esc($pend->tahun_lulus ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- TAB 4: PENUGASAN -->
            <div x-show="activeTab === 'penugasan'" x-cloak class="overflow-x-auto h-full">
                 <table id="table-penugasan" class="w-full text-left text-sm whitespace-nowrap data-table">
                    <thead class="bg-gray-50 dark:bg-gray-950/50 text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-800 font-extrabold">
                        <tr>
                            <th class="px-6 py-4 text-xs uppercase">Nama Pendidik</th>
                            <th class="px-6 py-4 text-xs uppercase">TA</th>
                            <th class="px-6 py-4 text-xs uppercase">Mapel Utama</th>
                            <th class="px-6 py-4 text-xs uppercase">Tugas Tambahan</th>
                            <th class="px-6 py-4 text-xs uppercase text-center">JJM</th>
                            <th class="px-6 py-4 text-xs uppercase text-center">Wali Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php foreach ($gurusData as $data): 
                            $guru = (object)($data['guru'] ?? $data);
                            $penugas = (object)($data['penugasan_terkini'] ?? []); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white"><?= esc($guru->nama_lengkap ?? 'N/A') ?></td>
                                <td class="px-6 py-4 text-gray-500 font-mono text-xs font-bold"><?= esc($penugas->tahun_ajaran ?? '-') ?></td>
                                <td class="px-6 py-4 font-semibold text-sky-600 dark:text-sky-400"><?= esc($penugas->mapel_diampu ?? 'Belum set') ?></td>
                                <td class="px-6 py-4 text-gray-500 italic"><?= esc($penugas->tugas_tambahan ?? '-') ?></td>
                                <td class="px-6 py-4 text-center font-black text-gray-800 dark:text-gray-200"><?= esc($penugas->jam_mengajar ?? '0') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($guru->is_wali)): ?>
                                        <div class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/50">
                                            <i class="fas fa-star text-amber-500 text-xs"></i>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-300">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Pagination Controls Footer -->
        <div class="border-t border-gray-200 dark:border-gray-800 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 bg-gray-50/50 dark:bg-gray-900/50">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Menampilkan <span x-text="startRecord" class="font-bold text-gray-900 dark:text-white"></span> 
                sampai <span x-text="endRecord" class="font-bold text-gray-900 dark:text-white"></span> 
                dari <span x-text="totalRecords" class="font-bold text-gray-900 dark:text-white"></span> data
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="prevPage" :disabled="currentPage === 1" 
                        class="p-2 rounded-lg text-gray-500 hover:bg-white hover:shadow-sm dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                
                <div class="flex items-center gap-1">
                    <template x-for="page in totalPages" :key="page">
                        <button @click="gotoPage(page)" 
                                x-show="showPageBtn(page)"
                                :class="currentPage === page ? 'bg-sky-600 text-white shadow-md shadow-sky-500/20' : 'text-gray-600 hover:bg-white hover:shadow-sm dark:text-gray-400 dark:hover:bg-gray-800'"
                                class="w-8 h-8 rounded-lg text-xs font-bold transition-all"
                                x-text="page"></button>
                    </template>
                </div>

                <button @click="nextPage" :disabled="currentPage === totalPages"
                        class="p-2 rounded-lg text-gray-500 hover:bg-white hover:shadow-sm dark:hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function tablePagination() {
        return {
            activeTab: 'master',
            itemsPerPage: 10,
            currentPage: 1,
            totalRecords: 0,
            rows: [],
            
            initTable() {
                this.$nextTick(() => { 
                    this.updateRowsSource(); 
                    this.renderPage();
                });
            },

            switchTab(tab) {
                this.activeTab = tab;
                this.currentPage = 1;
                this.$nextTick(() => { 
                    this.updateRowsSource(); 
                    this.renderPage();
                });
            },

            updateRowsSource() {
                const tableId = `table-${this.activeTab}`;
                const table = document.getElementById(tableId);
                if (table) {
                    this.rows = Array.from(table.querySelectorAll('tbody tr'));
                    this.totalRecords = this.rows.length;
                } else {
                    this.rows = [];
                    this.totalRecords = 0;
                }
            },

            renderPage() {
                const start = (this.currentPage - 1) * parseInt(this.itemsPerPage);
                const end = start + parseInt(this.itemsPerPage);
                
                this.rows.forEach((row, index) => {
                    if (index >= start && index < end) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            },

            changeLimit() {
                this.currentPage = 1;
                this.renderPage();
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.renderPage();
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.renderPage();
                }
            },

            gotoPage(page) {
                this.currentPage = page;
                this.renderPage();
            },

            get totalPages() {
                return Math.ceil(this.totalRecords / parseInt(this.itemsPerPage)) || 1;
            },

            get startRecord() {
                return this.totalRecords === 0 ? 0 : ((this.currentPage - 1) * this.itemsPerPage) + 1;
            },

            get endRecord() {
                let end = this.currentPage * this.itemsPerPage;
                return end > this.totalRecords ? this.totalRecords : end;
            },

            showPageBtn(page) {
                if (this.totalPages <= 7) return true;
                if (page === 1 || page === this.totalPages) return true;
                if (page >= this.currentPage - 1 && page <= this.currentPage + 1) return true;
                return false;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const ctxSert = document.getElementById('chartSertifikasi');
        if (ctxSert) {
            // PIE CHART CONFIGURATION (SOLID)
            const colorSertif = '#f59e0b'; // Amber 500
            const colorNon = '#d1d5db'; // Gray 300

            new Chart(ctxSert.getContext('2d'), {
                type: 'pie', // Changed to Pie for Solid Look
                data: {
                    labels: ['Sertifikasi', 'Belum'],
                    datasets: [{
                        data: [<?= $countSertifikasi ?>, <?= $countNonSertifikasi ?>],
                        backgroundColor: [colorSertif, colorNon],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { 
                    responsive: true,
                    maintainAspectRatio: false, 
                    plugins: { legend: { display: false }, tooltip: { enabled: true } },
                    animation: { animateScale: true, animateRotate: true }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>