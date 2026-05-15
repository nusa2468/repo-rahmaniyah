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
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">HYBRID ATTENDANCE</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Monitoring <span class="text-indigo-600">Presensi Pegawai</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- TOMBOL REKAPITULASI (FITUR BARU) -->
            <a href="<?= base_url('app/kepegawaian/absensi-pegawai/rekap') ?>" class="inline-flex items-center px-6 py-3 bg-white border-2 border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
                <i class="fas fa-file-invoice mr-2"></i> Rekap Bulanan
            </a>

            <!-- TOMBOL INPUT MASSAL -->
            <button onclick="toggleMassalModal()" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 active:scale-95 border-b-4 border-emerald-800">
                <i class="fas fa-users mr-2"></i> Input Massal
            </button>

            <!-- TOMBOL REAL-TIME TAP -->
            <button onclick="toggleTapModal()" class="inline-flex items-center px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all shadow-xl active:scale-95 border-b-4 border-slate-700">
                <i class="fas fa-fingerprint mr-2"></i> Tap Terminal
            </button>
        </div>
    </div>

    <!-- TAB NAVIGASI MODUL (SINKRON DENGAN AKADEMIK) -->
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

    <!-- STATS (SOLID PREMIUM) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-indigo-600 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-indigo-900">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Log Harian</p>
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
        
        <!-- TOOLBAR -->
        <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="flex p-1 bg-slate-200 dark:bg-slate-900 rounded-xl shadow-inner border border-slate-300 dark:border-slate-700">
                    <a href="?tipe=guru&unit=<?= esc($current_unit) ?>&tanggal=<?= esc($tanggal) ?>" 
                       class="px-6 py-2.5 rounded-lg text-[10px] font-black uppercase transition-all <?= $currentTipe === 'guru' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        TENAGA PENDIDIK
                    </a>
                    <a href="?tipe=staff&unit=<?= esc($current_unit) ?>&tanggal=<?= esc($tanggal) ?>" 
                       class="px-6 py-2.5 rounded-lg text-[10px] font-black uppercase transition-all <?= $currentTipe === 'staff' ? 'bg-white text-indigo-600 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                        KARYAWAN
                    </a>
                </div>
            </div>

            <form action="" method="get" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tipe" value="<?= esc($currentTipe) ?>">
                <div class="relative">
                    <select name="unit" onchange="this.form.submit()" 
                            class="pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase focus:border-indigo-500 outline-none appearance-none cursor-pointer <?= !$isGlobalUser ? 'opacity-50 cursor-not-allowed bg-slate-100' : '' ?>"
                            <?= !$isGlobalUser ? 'disabled' : '' ?>>
                        <option value="GLOBAL">SELURUH UNIT</option>
                        <option value="SD" <?= $current_unit == 'SD' ? 'selected' : '' ?>>UNIT SD</option>
                        <option value="SMP" <?= $current_unit == 'SMP' ? 'selected' : '' ?>>UNIT SMP</option>
                        <option value="SMA" <?= $current_unit == 'SMA' ? 'selected' : '' ?>>UNIT SMA</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[8px]"></i>
                </div>
                <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" onchange="this.form.submit()"
                       class="px-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest focus:border-indigo-500 outline-none">
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Profil Pegawai</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">In / Out</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Metode</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                    <?php if (empty($list_absensi)): ?>
                        <tr><td colspan="6" class="px-8 py-24 text-center opacity-30 italic">Data Presensi Kosong</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($list_absensi as $row): ?>
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-8 py-5 text-center text-xs font-black text-slate-300 group-hover:text-indigo-600"><?= $no++ ?>.</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-black text-slate-800 dark:text-slate-100 uppercase italic group-hover:text-indigo-600 transition-colors"><?= esc($row->nama_pegawai) ?></span>
                                        <span class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-widest">NIP: <?= esc($row->nip_pegawai ?? '-') ?> | Unit: <?= esc($row->kode_jenjang) ?></span>
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
                                        <i class="fas <?= $row->metode_absen === 'manual' ? 'fa-keyboard text-slate-400' : 'fa-fingerprint text-indigo-500' ?> text-xs"></i>
                                        <span class="text-[9px] font-black text-slate-400 uppercase italic"><?= esc($row->metode_absen) ?></span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <button type="button" onclick="openQuickEdit('<?= $row->id ?>', '<?= esc($row->nama_pegawai) ?>', '<?= esc($status) ?>')"
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

<!-- MODAL 1: QUICK EDIT -->
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
                        <div><h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic leading-none">Koreksi Manual</h3><p id="edit_nama" class="text-xs font-bold text-slate-400 mt-2 uppercase italic tracking-tight"></p></div>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Pilih Status Baru</label>
                        <div class="grid grid-cols-1 gap-2">
                            <?php $opts = ['hadir' => 'Hadir Tepat Waktu', 'terlambat' => 'Terlambat Datang', 'sakit' => 'Sakit (Bukti Medis)', 'izin' => 'Izin / Dinas Luar', 'alpa' => 'Alpa / Bolos'];
                                foreach($opts as $val => $lbl): ?>
                                <label class="cursor-pointer group"><input type="radio" name="status" value="<?= $val ?>" class="status-radio peer hidden" required><div class="px-5 py-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 transition-all flex items-center justify-between"><?= $lbl ?><i class="fas fa-check-circle opacity-0 peer-checked:opacity-100"></i></div></label>
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

<!-- MODAL 2: INPUT MASSAL -->
<div id="massalInputModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="toggleMassalModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all max-w-4xl w-full border-t-8 border-emerald-600">
            <form action="<?= base_url('app/kepegawaian/absensi-pegawai/simpanMassal') ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8 border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner"><i class="fas fa-users"></i></div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 uppercase italic leading-none">Presensi Massal</h3>
                                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest">Input Cepat Unit: <?= esc($current_unit) ?></p>
                            </div>
                        </div>
                        <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" class="px-4 py-2 bg-slate-50 border-2 border-slate-100 rounded-xl text-xs font-black outline-none focus:border-emerald-500">
                    </div>

                    <div class="max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-white border-b-2 border-slate-50">
                                <tr>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Pegawai</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Hadir</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Sakit</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Izin</th>
                                    <th class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Alpa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(isset($pegawai_list)): foreach($pegawai_list as $p): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-4">
                                            <p class="text-xs font-black text-slate-700 uppercase"><?= esc($p['nama_lengkap']) ?></p>
                                            <p class="text-[9px] text-slate-400 uppercase">NIP: <?= esc($p['nip'] ?? '-') ?></p>
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
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 flex gap-3">
                        <button type="button" onclick="toggleMassalModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200">BATALKAN</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-emerald-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-emerald-700 border-b-4 border-emerald-900 active:scale-95 transition-all">SIMPAN DATA MASSAL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: INPUT INDIVIDU -->
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
                        <div class="space-y-1.5"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Pegawai</label><select name="id_pegawai" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-indigo-500 transition-all outline-none appearance-none cursor-pointer"><option value="">-- CARI NAMA PEGAWAI --</option><?php if(isset($pegawai_list)): foreach($pegawai_list as $p): ?><option value="<?= $p['id'] ?>"><?= esc($p['nama_lengkap']) ?> (<?= esc($p['kode_jenjang']) ?>)</option><?php endforeach; endif; ?></select></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status</label><select name="status" required class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-black uppercase tracking-widest focus:border-indigo-500 transition-all appearance-none cursor-pointer"><option value="hadir">HADIR (NORMAL)</option><option value="terlambat">TERLAMBAT</option><option value="sakit">SAKIT</option><option value="izin">IZIN / TUGAS LUAR</option><option value="alpa">ALPA</option></select></div>
                        <div class="space-y-1.5"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan</label><textarea name="keterangan" rows="2" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-xs font-medium focus:border-indigo-500 transition-all outline-none resize-none" placeholder="Opsional..."></textarea></div>
                    </div>
                    <div class="mt-8 flex gap-3"><button type="button" onclick="toggleManualModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button><button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-900">SIMPAN</button></div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: TERMINAL TAP -->
<div id="tapModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-indigo-900/90 backdrop-blur-md transition-opacity" onclick="toggleTapModal()"></div>
        <div class="inline-block bg-white rounded-[3rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full"><div class="p-10 text-center"><div class="w-20 h-20 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl shadow-inner border-4 border-white mx-auto mb-6"><i class="fas fa-fingerprint"></i></div><h3 class="text-xl font-black text-slate-900 uppercase italic">Real-time Tap</h3><p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest mb-10">Scan RFID / QR Simulator</p><form action="<?= base_url('app/kepegawaian/absensi-pegawai/prosesTap') ?>" method="post" class="space-y-6"><?= csrf_field() ?><div class="space-y-2 text-left"><label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-4">ID Pegawai</label><input type="number" name="id_pegawai" required placeholder="ID Pegawai..." class="w-full px-6 py-5 bg-slate-100 border-2 border-slate-100 rounded-3xl text-center font-black text-lg focus:border-indigo-500 focus:bg-white transition-all shadow-inner outline-none"></div><button type="submit" class="w-full py-5 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.3em] rounded-3xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-900">SIMULATE TAP</button><button type="button" onclick="toggleTapModal()" class="w-full text-[10px] font-black text-slate-300 uppercase tracking-widest mt-4 hover:text-slate-500 italic transition-colors">BATALKAN</button></form></div></div>
    </div>
</div>

<script>
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
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<?= $this->endSection() ?>