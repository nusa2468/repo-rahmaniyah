<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    $request = \Config\Services::request();
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobalUser = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
    $currentTipe = $tipe_pegawai ?? 'guru';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <!-- FIX: Breadcrumb mengarah ke rute yang benar (app/kepegawaian) -->
                    <li><a href="<?= base_url('app/kepegawaian') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">HYBRID ATTENDANCE</li>
                </ol>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                    Monitoring <span class="text-indigo-600">Presensi</span>
                </h1>
                
                <!-- BADGE UNIT AKTIF -->
                <?php if($isGlobalUser && $current_unit === 'GLOBAL'): ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200 uppercase tracking-wide">
                        Kantor Pusat (Yayasan)
                    </span>
                <?php elseif(empty($current_unit)): ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                        Semua Unit Tergabung
                    </span>
                <?php else: ?>
                    <span class="px-2 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                        Unit <?= esc($current_unit) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- TOMBOL REKAPITULASI -->
            <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="inline-flex items-center px-6 py-3 bg-white border-2 border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
                <i class="fas fa-file-invoice mr-2"></i> Rekap Bulanan
            </a>

            <!-- TOMBOL INPUT MASSAL -->
            <button onclick="toggleMassalModal()" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 active:scale-95 border-b-4 border-emerald-800">
                <i class="fas fa-users mr-2"></i> Input Massal
            </button>
            
            <!-- TOMBOL ABSENSI ONLINE (NEW) -->
            <button onclick="toggleOnlineModal()" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-purple-700 transition-all shadow-lg shadow-purple-200 active:scale-95 border-b-4 border-purple-800">
                <i class="fas fa-camera mr-2"></i> Absen Online
            </button>

            <!-- TOMBOL REAL-TIME TAP -->
            <button onclick="toggleTapModal()" class="inline-flex items-center px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all shadow-xl active:scale-95 border-b-4 border-slate-700">
                <i class="fas fa-fingerprint mr-2"></i> Tap Terminal
            </button>
        </div>
    </div>

    <!-- TAB NAVIGASI MODUL -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-clock mr-2"></i> Monitoring Harian
        </a>
        <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-invoice mr-2 opacity-50"></i> Rekapitulasi
        </a>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl flex items-center shadow-sm animate-bounce">
            <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
            <p class="text-xs font-black uppercase text-emerald-800 tracking-tight"><?= session()->getFlashdata('message') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle text-rose-500 mr-3"></i>
            <p class="text-xs font-black uppercase text-rose-800 tracking-tight"><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- STATS (SOLID PREMIUM) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-indigo-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-indigo-900">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Log Harian (Page)</p>
            <h3 class="text-3xl font-black mt-2 italic"><?= number_format($stats->total_absen ?? 0) ?></h3>
            <i class="fas fa-id-badge absolute -right-4 -bottom-4 text-white/10 text-7xl group-hover:scale-110 transition-transform"></i>
        </div>
        <div class="bg-emerald-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-emerald-800">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Tepat Waktu</p>
            <h3 class="text-3xl font-black mt-2 italic"><?= number_format($stats->hadir ?? 0) ?></h3>
            <i class="fas fa-check-circle absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
        <div class="bg-amber-500 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-amber-700">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Terlambat</p>
            <h3 class="text-3xl font-black mt-2 italic"><?= number_format($stats->terlambat ?? 0) ?></h3>
            <i class="fas fa-clock absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
        <div class="bg-rose-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-rose-800">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Sakit / Izin / Alpa</p>
            <h3 class="text-3xl font-black mt-2 italic"><?= number_format($stats->absen_izin ?? 0) ?></h3>
            <i class="fas fa-user-times absolute -right-4 -bottom-4 text-white/10 text-7xl"></i>
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
        
        <!-- TOOLBAR FILTER -->
        <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            
            <!-- TABS FILTER JENIS PEGAWAI (DENGAN PENUNJANG) -->
            <div class="flex items-center gap-3 w-full lg:w-auto overflow-x-auto custom-scrollbar pb-2 lg:pb-0">
                <div class="flex p-1 bg-slate-200 dark:bg-slate-900 rounded-xl shadow-inner border border-slate-300 dark:border-slate-700 min-w-max">
                    <a href="?tipe=guru&unit=<?= esc($current_unit) ?>&tanggal=<?= esc($tanggal) ?>" 
                       class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?= $currentTipe === 'guru' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        GURU / PENDIDIK
                    </a>
                    <a href="?tipe=staff&unit=<?= esc($current_unit) ?>&tanggal=<?= esc($tanggal) ?>" 
                       class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?= $currentTipe === 'staff' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        STAF / TENDIK
                    </a>
                    <a href="?tipe=penunjang&unit=<?= esc($current_unit) ?>&tanggal=<?= esc($tanggal) ?>" 
                       class="px-5 py-2.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all <?= $currentTipe === 'penunjang' ? 'bg-white text-emerald-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        PENUNJANG
                    </a>
                </div>
            </div>

            <form action="" method="get" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <input type="hidden" name="tipe" value="<?= esc($currentTipe) ?>">
                
                <!-- FILTER UNIT KERJA (DINAMIS DARI DATABASE) -->
                <div class="relative w-full sm:w-auto flex-1 sm:flex-none">
                    <select name="unit" onchange="this.form.submit()" 
                            class="w-full sm:w-48 pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase focus:border-indigo-500 outline-none appearance-none cursor-pointer <?= !$isGlobalUser ? 'opacity-50 cursor-not-allowed bg-slate-100' : '' ?>"
                            <?= !$isGlobalUser ? 'disabled' : '' ?>>
                        
                        <option value="" <?= ($current_unit === '' || $current_unit === null) ? 'selected' : '' ?>>🌐 SEMUA UNIT</option>
                        <option value="GLOBAL" <?= $current_unit === 'GLOBAL' ? 'selected' : '' ?>>🏢 KANTOR YAYASAN</option>
                        
                        <?php if (isset($jenjang_list)): foreach($jenjang_list as $j): ?>
                            <?php if(in_array(strtoupper($j['kode_jenjang']), ['GLOBAL','YAYASAN'])) continue; ?>
                            <option value="<?= $j['kode_jenjang'] ?>" <?= $current_unit == $j['kode_jenjang'] ? 'selected' : '' ?>>🏫 UNIT <?= $j['kode_jenjang'] ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[8px]"></i>
                </div>

                <!-- FILTER TANGGAL -->
                <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" onchange="this.form.submit()"
                       class="w-full sm:w-auto px-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest focus:border-indigo-500 outline-none cursor-pointer">
            </form>
        </div>

        <!-- TABLE DATA -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Profil Pegawai</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">In / Out</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Metode Log</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($list_absensi)): ?>
                        <tr><td colspan="6" class="px-8 py-24 text-center opacity-30 italic">Data Presensi Kosong pada Tanggal Ini</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($list_absensi as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5 text-center text-xs font-black text-slate-300 group-hover:text-indigo-600"><?= $no++ ?>.</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-black text-slate-800 dark:text-slate-100 uppercase italic group-hover:text-indigo-600 transition-colors"><?= esc($row->nama_pegawai) ?></span>
                                        <?php 
                                            // Format Label Unit Yayasan pada Tabel
                                            $labelUnitTable = in_array(strtoupper($row->unit_pegawai ?? ''), ['GLOBAL','YAYASAN','PUSAT']) ? 'YAYASAN' : ($row->unit_pegawai ?? '-');
                                        ?>
                                        <span class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">
                                            NIP: <?= esc($row->nip_pegawai ?? '-') ?> | Unit: <?= esc($labelUnitTable) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex items-center gap-4 px-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-100 rounded-2xl italic shadow-inner">
                                        <div class="text-center">
                                            <p class="text-[8px] font-black text-slate-400 leading-none mb-1">IN</p>
                                            <span class="text-[11px] font-black text-slate-700 dark:text-slate-200"><?= $row->jam_masuk ? date('H:i', strtotime($row->jam_masuk)) : '--:--' ?></span>
                                        </div>
                                        <div class="h-4 w-[1px] bg-slate-200"></div>
                                        <div class="text-center">
                                            <p class="text-[8px] font-black text-slate-400 leading-none mb-1">OUT</p>
                                            <span class="text-[11px] font-black text-slate-700 dark:text-slate-200"><?= $row->jam_keluar ? date('H:i', strtotime($row->jam_keluar)) : '--:--' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php 
                                        $status = strtolower($row->status);
                                        $style = match($status) {
                                            'hadir'     => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'terlambat' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            default     => 'bg-rose-50 text-rose-600 border-rose-100'
                                        };
                                    ?>
                                    <span class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border <?= $style ?> shadow-sm">
                                        <?= esc($status) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php 
                                            // Pemilihan Ikon Metode Secara Cerdas
                                            $iconName = 'fa-fingerprint text-indigo-500';
                                            if ($row->metode_absen === 'manual') $iconName = 'fa-keyboard text-slate-400';
                                            if ($row->metode_absen === 'online') $iconName = 'fa-camera text-purple-500';
                                        ?>
                                        <i class="fas <?= $iconName ?> text-xs"></i>
                                        <span class="text-[9px] font-black text-slate-400 uppercase italic whitespace-nowrap"><?= esc($row->metode_absen) ?></span>
                                        
                                        <!-- Tombol Foto Selfie (Khusus Online) -->
                                        <?php if(!empty($row->bukti_foto_masuk)): ?>
                                            <a href="<?= base_url('uploads/absensi/'.$row->bukti_foto_masuk) ?>" target="_blank" 
                                               class="w-6 h-6 rounded bg-purple-50 text-purple-500 hover:bg-purple-500 hover:text-white flex items-center justify-center transition-colors shadow-sm ml-1" title="Lihat Swafoto Masuk">
                                                <i class="fas fa-image text-[10px]"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Penanda GPS -->
                                        <?php if(strpos($row->keterangan ?? '', 'GPS:') !== false): ?>
                                            <button type="button" class="w-6 h-6 rounded bg-emerald-50 text-emerald-500 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="<?= esc($row->keterangan) ?>">
                                                <i class="fas fa-map-marker-alt text-[10px]"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button type="button" onclick="openQuickEdit('<?= $row->id ?>', '<?= esc(addslashes($row->nama_pegawai)) ?>', '<?= esc($status) ?>')"
                                            class="w-10 h-10 inline-flex items-center justify-center bg-white border-2 border-slate-100 rounded-xl text-slate-400 hover:text-indigo-600 transition-all active:scale-90 shadow-sm">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 1: QUICK EDIT (Perbarui Status Presensi) -->
<!-- ============================================== -->
<div id="quickEditModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeQuickEdit()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full border-b-8 border-indigo-600">
            <form action="<?= base_url('app/kepegawaian/absensi-pegawai/updateStatus') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="p-10">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-inner"><i class="fas fa-user-edit"></i></div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic leading-none">Koreksi Manual</h3>
                            <p id="edit_nama" class="text-xs font-bold text-slate-400 mt-2 uppercase italic tracking-tight"></p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Status Baru</label>
                        <div class="grid grid-cols-1 gap-2">
                            <?php $opts = ['hadir' => 'Hadir Tepat Waktu', 'terlambat' => 'Terlambat Datang', 'sakit' => 'Sakit (Bukti Medis)', 'izin' => 'Izin / Dinas Luar', 'alpa' => 'Alpa / Bolos'];
                                foreach($opts as $val => $lbl): ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="status" value="<?= $val ?>" class="status-radio peer hidden" required>
                                    <div class="px-5 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-all flex items-center justify-between">
                                        <?= $lbl ?><i class="fas fa-check-circle opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mt-10 flex gap-3">
                        <button type="button" onclick="closeQuickEdit()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-slate-900 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-indigo-600 transition-all border-b-4 border-slate-700 active:scale-95">SIMPAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 2: INPUT MASSAL (Sesuai List Filter) -->
<!-- ============================================== -->
<div id="massalInputModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="toggleMassalModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all max-w-4xl w-full border-t-8 border-emerald-600">
            <form action="<?= base_url('app/kepegawaian/absensi-pegawai/simpanMassal') ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner"><i class="fas fa-users"></i></div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 uppercase italic leading-none">Presensi Massal</h3>
                                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest">
                                    Filter: <?= strtoupper($currentTipe) ?> | Unit: <?= esc(empty($current_unit) ? 'Semua Unit' : ($current_unit === 'GLOBAL' ? 'Yayasan' : $current_unit)) ?>
                                </p>
                            </div>
                        </div>
                        <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" class="px-4 py-2 bg-slate-50 border-2 border-slate-100 rounded-xl text-xs font-black outline-none focus:border-emerald-500">
                    </div>

                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-white border-b-2 border-slate-50 z-10">
                                <tr>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Pegawai & Unit</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Hadir</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Sakit</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Izin</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Alpa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(isset($pegawai_list) && count($pegawai_list) > 0): foreach($pegawai_list as $p): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-4">
                                            <p class="text-xs font-black text-slate-700 uppercase"><?= esc($p['nama_lengkap']) ?></p>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">NIP: <?= esc($p['nip'] ?? '-') ?> | Unit: <?= in_array(strtoupper($p['kode_jenjang']), ['GLOBAL','YAYASAN']) ? 'YAYASAN' : esc($p['kode_jenjang']) ?></p>
                                        </td>
                                        <?php foreach(['hadir', 'sakit', 'izin', 'alpa'] as $st): ?>
                                            <td class="py-4 text-center">
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="massal[<?= $p['id'] ?>]" value="<?= $st ?>" class="peer hidden" <?= $st == 'hadir' ? 'checked' : '' ?>>
                                                    <div class="w-6 h-6 mx-auto rounded-full border-2 border-slate-200 peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-all flex items-center justify-center text-white text-[10px]">
                                                        <i class="fas fa-check scale-0 peer-checked:scale-100"></i>
                                                    </div>
                                                </label>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="5" class="py-10 text-center text-xs italic text-slate-400">Tidak ada pegawai yang sesuai dengan filter di atas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 flex gap-3">
                        <button type="button" onclick="toggleMassalModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATALKAN</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-emerald-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-emerald-700 border-b-4 border-emerald-900 active:scale-95 transition-all">SIMPAN DATA MASSAL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 3: INPUT INDIVIDU MANUAL -->
<!-- ============================================== -->
<div id="manualInputModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="toggleManualModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full border-t-8 border-indigo-600">
            <form action="<?= base_url('app/kepegawaian/absensi-pegawai/simpanManual') ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-10">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner"><i class="fas fa-user-plus"></i></div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tighter italic leading-none">Presensi Manual</h3>
                        <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Input Perorangan Hari Ini</p>
                    </div>
                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Pegawai</label>
                            <select name="id_pegawai" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-indigo-500 transition-all outline-none appearance-none cursor-pointer">
                                <option value="">-- CARI NAMA PEGAWAI --</option>
                                <?php if(isset($pegawai_list)): foreach($pegawai_list as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= esc($p['nama_lengkap']) ?> (<?= in_array(strtoupper($p['kode_jenjang']), ['GLOBAL','YAYASAN']) ? 'YAYASAN' : esc($p['kode_jenjang']) ?>)
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status</label>
                            <select name="status" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black uppercase tracking-widest focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                <option value="hadir">HADIR (NORMAL)</option>
                                <option value="terlambat">TERLAMBAT</option>
                                <option value="sakit">SAKIT</option>
                                <option value="izin">IZIN / TUGAS LUAR</option>
                                <option value="alpa">ALPA</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan / Catatan</label>
                            <textarea name="keterangan" rows="2" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-medium focus:border-indigo-500 transition-all outline-none resize-none" placeholder="Opsional (Misal: Surat Dokter dilampirkan)..."></textarea>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button type="button" onclick="toggleManualModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-900">SIMPAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 4: TERMINAL TAP SIMULATOR -->
<!-- ============================================== -->
<div id="tapModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-indigo-900/90 backdrop-blur-md transition-opacity" onclick="toggleTapModal()"></div>
        <div class="inline-block bg-white rounded-[3rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full">
            <div class="p-10 text-center">
                <div class="w-20 h-20 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl shadow-inner border-4 border-white mx-auto mb-6">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 uppercase italic">Real-time Tap</h3>
                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest mb-10">Scan RFID / QR Simulator</p>
                
                <form action="<?= base_url('app/kepegawaian/absensi-pegawai/prosesTap') ?>" method="post" class="space-y-6">
                    <?= csrf_field() ?>
                    <div class="space-y-2 text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">Scan Barcode / NIK / NIP</label>
                        <!-- Menggunakan tipe "text" agar bisa menerima angka panjang NIK tanpa masalah pemotongan browser -->
                        <input type="text" name="id_pegawai" required placeholder="Contoh: 3201012345678901" class="w-full px-6 py-5 bg-slate-100 border-2 border-slate-100 rounded-3xl text-center font-black text-lg focus:border-indigo-500 focus:bg-white transition-all shadow-inner outline-none">
                    </div>
                    <button type="submit" class="w-full py-5 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.3em] rounded-3xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-900">SIMULATE TAP</button>
                    <button type="button" onclick="toggleTapModal()" class="w-full text-[10px] font-black text-slate-300 uppercase tracking-widest mt-4 hover:text-slate-500 italic transition-colors">BATALKAN</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL 5: ABSENSI ONLINE (SELFIE + GPS) - NEW -->
<!-- ============================================== -->
<div id="onlineModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="toggleOnlineModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full border-t-8 border-purple-600 relative">
            <form id="formOnline" action="<?= base_url('app/kepegawaian/absensi-pegawai/prosesOnline') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="foto_base64" id="foto_base64">
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                
                <div class="p-10">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 shadow-inner"><i class="fas fa-camera"></i></div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tighter italic leading-none">Absensi Online</h3>
                        <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Verifikasi Wajah & Lokasi (GPS)</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Camera Preview -->
                        <div class="relative bg-black rounded-2xl overflow-hidden shadow-inner flex items-center justify-center aspect-video border-2 border-slate-100">
                            <video id="webcam" autoplay playsinline class="w-full h-full object-cover hidden"></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            <div id="camera-loading" class="absolute inset-0 flex flex-col items-center justify-center text-white/50">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-center px-4 mt-2" id="camera-status">Meminta Akses Kamera...</span>
                            </div>
                        </div>

                        <!-- GPS Status -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-rose-500"></i>
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Lokasi (GPS)</span>
                            </div>
                            <span id="gps-status" class="text-[10px] font-bold text-amber-500 uppercase tracking-widest animate-pulse">Mencari...</span>
                        </div>

                        <!-- Pegawai Selection -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Pegawai (WFA/Luar Kota)</label>
                            <select name="id_pegawai" id="id_pegawai_online" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-purple-500 transition-all outline-none appearance-none cursor-pointer">
                                <option value="">-- PILIH PEGAWAI --</option>
                                <?php if(isset($pegawai_list)): foreach($pegawai_list as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= esc($p['nama_lengkap']) ?> (<?= in_array(strtoupper($p['kode_jenjang']), ['GLOBAL','YAYASAN']) ? 'YAYASAN' : esc($p['kode_jenjang']) ?>)
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="button" onclick="toggleOnlineModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button>
                        
                        <button type="button" onclick="takeSnapshotAndSubmit()" class="flex-1 px-6 py-4 bg-purple-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-purple-700 transition-all active:scale-95 border-b-4 border-purple-800 flex items-center justify-center gap-2">
                            <span class="btn-text"><i class="fas fa-camera-retro"></i> FOTO & ABSEN</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Javascript functions for Modals
    function openQuickEdit(id, nama, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').innerText = nama;
        const radios = document.querySelectorAll('.status-radio');
        radios.forEach(r => { if (r.value === status) r.checked = true; });
        document.getElementById('quickEditModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeQuickEdit() {
        document.getElementById('quickEditModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function toggleTapModal() {
        const m = document.getElementById('tapModal');
        m.classList.toggle('hidden');
        document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';
    }
    
    function toggleManualModal() {
        const m = document.getElementById('manualInputModal');
        m.classList.toggle('hidden');
        document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';
    }
    
    function toggleMassalModal() {
        const m = document.getElementById('massalInputModal');
        m.classList.toggle('hidden');
        document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';
    }

    // =========================================================================
    // JS ABSENSI ONLINE (CAMERA & GPS WFA)
    // =========================================================================
    let videoStream = null;

    function toggleOnlineModal() {
        const m = document.getElementById('onlineModal');
        m.classList.toggle('hidden');
        document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';
        
        if (!m.classList.contains('hidden')) {
            startWebcam();
            getLocation();
        } else {
            stopWebcam();
        }
    }

    function startWebcam() {
        const video = document.getElementById('webcam');
        const loading = document.getElementById('camera-loading');
        const statusTxt = document.getElementById('camera-status');
        
        loading.classList.remove('hidden');
        video.classList.add('hidden');
        
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            // Meminta izin kamera depan
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                .then(stream => {
                    videoStream = stream;
                    video.srcObject = stream;
                    video.onloadedmetadata = () => {
                        loading.classList.add('hidden');
                        video.classList.remove('hidden');
                    };
                })
                .catch(err => {
                    console.error("Camera error:", err);
                    loading.innerHTML = '<i class="fas fa-video-slash text-rose-500 text-3xl mb-2"></i>';
                    statusTxt.innerText = 'Kamera Diblokir (Harus izinkan kamera)';
                    statusTxt.classList.add('text-rose-500');
                });
        } else {
            // Terjadi jika diakses dari domain HTTP (tanpa SSL/HTTPS), karena fitur ini butuh Secure Context
            loading.innerHTML = '<i class="fas fa-lock text-rose-500 text-3xl mb-2"></i>';
            statusTxt.innerText = 'Kamera Butuh Akses HTTPS/SSL';
            statusTxt.classList.add('text-rose-500');
        }
    }

    function stopWebcam() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
        }
        const video = document.getElementById('webcam');
        video.srcObject = null;
    }

    function getLocation() {
        const status = document.getElementById('gps-status');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    latInput.value = pos.coords.latitude;
                    lngInput.value = pos.coords.longitude;
                    status.textContent = 'LOKASI AKURAT (DITEMUKAN)';
                    status.classList.remove('text-amber-500', 'animate-pulse');
                    status.classList.add('text-emerald-500');
                },
                err => {
                    console.error("GPS error:", err);
                    status.textContent = 'LOKASI GAGAL DIAKSES';
                    status.classList.remove('text-amber-500', 'animate-pulse');
                    status.classList.add('text-rose-500');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            status.textContent = 'PERANGKAT TIDAK DIDUKUNG';
            status.classList.remove('text-amber-500', 'animate-pulse');
            status.classList.add('text-rose-500');
        }
    }

    function takeSnapshotAndSubmit() {
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const fotoInput = document.getElementById('foto_base64');
        const form = document.getElementById('formOnline');
        const pegSelect = document.getElementById('id_pegawai_online');
        
        if (pegSelect.value === '') {
            alert('Tolong pilih nama pegawai yang akan melakukan absen terlebih dahulu!');
            return;
        }

        if (videoStream && video.videoWidth > 0) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            fotoInput.value = canvas.toDataURL('image/jpeg', 0.85); // 0.85 kualitas agar ringan
        } else {
            if(!confirm('Kamera gagal diakses atau belum dimuat. Tetap ingin memaksakan absen tanpa foto selfie?')) {
                return;
            }
        }
        
        // Mencegah klik ganda saat proses upload sedang berjalan (Loading State)
        const submitBtn = form.querySelector('button[onclick="takeSnapshotAndSubmit()"]');
        if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.querySelector('.btn-text').innerHTML = '<i class="fas fa-spinner fa-spin"></i> MEMPROSES...';
        }
        
        // Kirim Formulir ke rute prosesOnline
        form.submit();
    }
</script>

<style>
    /* Styling Scrollbar Transparan Elegan */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<?= $this->endSection() ?>