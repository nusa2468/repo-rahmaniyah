<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li><a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="hover:text-indigo-600 transition-colors">PAYROLL</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 dark:text-slate-300 italic">SETTING INDIVIDU</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Detail Gaji <span class="text-indigo-600 dark:text-indigo-400"><?= esc($pegawai['nama_lengkap']) ?></span>
            </h1>
        </div>

        <a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="inline-flex items-center px-6 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:border-indigo-600 dark:hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- ALERT HANDLERS -->
    <?php if (session()->getFlashdata('message')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('message') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- INFO PEGAWAI & THP -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl flex flex-col justify-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl shadow-inner shrink-0">
                    <i class="fas <?= $pegawai['jenis_pegawai'] == 'guru' ? 'fa-chalkboard-teacher' : 'fa-user-tie' ?>"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status Pegawai</p>
                    <?php $labelUnit = in_array(strtoupper($pegawai['kode_jenjang']), ['GLOBAL','YAYASAN','PUSAT']) ? 'YAYASAN' : $pegawai['kode_jenjang']; ?>
                    <p class="text-sm font-black text-slate-800 dark:text-white uppercase italic"><?= esc($pegawai['jenis_pegawai']) ?> (Unit <?= esc($labelUnit) ?>)</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <?php
                    $nomorInduk = '-';
                    $labelInduk = 'ID';
                    if (!empty($pegawai['nip'])) { 
                        $nomorInduk = $pegawai['nip']; $labelInduk = 'NIP'; 
                    } elseif (!empty($pegawai['nipy'])) { 
                        $nomorInduk = $pegawai['nipy']; $labelInduk = 'NIPY'; 
                    } elseif (!empty($pegawai['nik'])) { 
                        $nomorInduk = $pegawai['nik']; $labelInduk = 'NIK'; 
                    }
                ?>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide"><?= $labelInduk ?>: <span class="text-slate-900 dark:text-white"><?= esc($nomorInduk) ?></span></span>
                <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-[9px] font-black rounded uppercase">AKTIF</span>
            </div>
        </div>

        <div class="md:col-span-2 bg-slate-900 dark:bg-black p-6 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden group">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 h-full">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Estimasi Take Home Pay (THP)</p>
                    <h2 class="text-4xl md:text-5xl font-black italic tracking-tighter">Rp <?= number_format($total_pendapatan - $total_potongan, 0, ',', '.') ?></h2>
                </div>
                <div class="flex gap-6 text-right">
                    <div>
                        <p class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Total Pendapatan</p>
                        <p class="text-lg md:text-xl font-black text-emerald-100 tracking-tight">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-rose-400 uppercase tracking-widest mb-1">Total Potongan</p>
                        <p class="text-lg md:text-xl font-black text-rose-100 tracking-tight">Rp <?= number_format($total_potongan, 0, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <i class="fas fa-wallet absolute -right-6 -bottom-6 text-white/5 text-9xl group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- FORM TAMBAH KOMPONEN -->
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden sticky top-24">
                <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10">
                    <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic leading-none">
                        <i class="fas fa-plus-circle mr-2 text-indigo-600 dark:text-indigo-400"></i> Tambah Komponen
                    </h3>
                </div>
                <form action="<?= base_url('app/kepegawaian/gaji-pegawai/simpanKomponen') ?>" method="post" class="p-8 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_pegawai" value="<?= esc($pegawai['id']) ?>">

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jenis Komponen</label>
                        <div class="relative">
                            <select name="id_komponen" required class="w-full pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-white uppercase focus:border-indigo-500 outline-none appearance-none cursor-pointer">
                                <option value="">-- PILIH KOMPONEN --</option>
                                <?php foreach($master_komponen as $m): ?>
                                    <option value="<?= $m['id'] ?>" data-nominal="<?= $m['nominal_default'] ?? 0 ?>">
                                        <?= esc($m['nama_komponen']) ?> (<?= $m['tipe'] == 1 ? '+' : '-' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nominal (Rp)</label>
                        <input type="number" name="jumlah" id="nominal_input" required placeholder="0" 
                               class="w-full px-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl text-sm font-black text-slate-800 dark:text-white focus:border-indigo-500 outline-none">
                    </div>

                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-black text-xs uppercase tracking-[0.2em] rounded-xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 border-b-4 border-indigo-800">
                        SIMPAN
                    </button>
                </form>
            </div>
        </div>

        <!-- LIST KOMPONEN -->
        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden min-h-[500px]">
                <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 flex justify-between items-center">
                    <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic">
                        Rincian Komponen Gaji Aktif
                    </h3>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white italic">
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Nama Komponen</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Jenis</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Nominal</th>
                                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-[11px]">
                            <?php if(empty($list_gaji)): ?>
                                <tr><td colspan="4" class="px-8 py-20 text-center text-slate-400 dark:text-slate-500 italic font-bold">Belum ada komponen gaji yang ditambahkan untuk pegawai ini.</td></tr>
                            <?php else: ?>
                                <?php foreach($list_gaji as $row): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                        <td class="px-8 py-5 font-black text-slate-700 dark:text-slate-200 uppercase"><?= esc($row['nama_komponen']) ?></td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase border <?= $row['tipe_komponen'] == 1 ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-800' ?>">
                                                <?= $row['tipe_komponen'] == 1 ? 'PENDAPATAN' : 'POTONGAN' ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right font-black <?= $row['tipe_komponen'] == 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' ?>">
                                            Rp <?= number_format($row['jumlah_set'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <a href="<?= base_url('app/kepegawaian/gaji-pegawai/hapusKomponen/' . $row['id']) ?>" onclick="return confirm('Anda yakin ingin menghapus komponen gaji ini dari profil pegawai?')" 
                                               class="w-8 h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:border-rose-200 dark:hover:border-rose-800 transition-all shadow-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-fill nominal based on dropdown selection
    document.querySelector('select[name="id_komponen"]').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const nominal = selected.getAttribute('data-nominal');
        if(nominal) {
            document.getElementById('nominal_input').value = nominal;
        } else {
            document.getElementById('nominal_input').value = '';
        }
    });
</script>

<style>
    /* Styling scrollbar untuk table */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?= $this->endSection() ?>